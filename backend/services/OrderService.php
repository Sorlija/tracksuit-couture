<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class OrderService extends BaseService
{
    public const STATUSES = ['processing', 'shipped', 'delivered', 'cancelled'];
    public const PAYMENT_METHODS = ['cash_on_delivery', 'card_on_delivery'];

    private const ALLOWED_STATUS_CHANGES = [
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered', 'cancelled'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    private $orderItemDao;
    private $variantDao;
    private $productDao;
    private $userDao;

    public function __construct()
    {
        parent::__construct(new OrderDao(), 'Order');
        $this->orderItemDao = new OrderItemDao();
        $this->variantDao = new ProductVariantDao();
        $this->productDao = new ProductDao();
        $this->userDao = new UserDao();
    }

    public function getAll($userId = null, $status = null)
    {
        if ($userId !== null) {
            return $this->dao->getByUser($userId);
        }
        if ($status !== null) {
            if (!in_array($status, self::STATUSES, true)) {
                throw new ApiException('Status must be one of: ' . implode(', ', self::STATUSES) . '.', 400);
            }
            return $this->dao->getByStatus($status);
        }
        return $this->dao->getAllWithCustomer();
    }

    public function getDetails($id)
    {
        $order = $this->getById($id);
        $order['items'] = $this->orderItemDao->getByOrder($id);
        return $order;
    }

    public function add($data)
    {
        $errors = [];
        $order = [
            'user_id'          => $this->integer($data, 'user_id', 'User', false, $errors, 1),
            'shipping_address' => $this->text($data, 'shipping_address', 'Shipping address', false, $errors, 150),
            'shipping_city'    => $this->text($data, 'shipping_city', 'Shipping city', false, $errors, 60),
            'shipping_zip'     => $this->text($data, 'shipping_zip', 'ZIP', false, $errors, 10, false),
            'payment_method'   => $this->oneOf($data, 'payment_method', 'Payment method', self::PAYMENT_METHODS, false, $errors, false),
        ];

        if ($order['user_id'] !== null && !isset($errors['user_id']) && $this->userDao->getById($order['user_id']) === null) {
            $errors['user_id'] = 'This user does not exist.';
        }

        $quantities = []; 
        $items = $data['items'] ?? null;
        if (!is_array($items) || count($items) === 0) {
            $errors['items'] = 'An order needs at least one item.';
        } else {
            foreach ($items as $index => $item) {
                $variantId = filter_var($item['variant_id'] ?? null, FILTER_VALIDATE_INT);
                $quantity  = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                if ($variantId === false || $variantId < 1) {
                    $errors["items.$index.variant_id"] = 'Variant id must be a positive whole number.';
                } elseif ($quantity === false || $quantity < 1) {
                    $errors["items.$index.quantity"] = 'Quantity must be at least 1.';
                } else {
                    $quantities[$variantId] = ($quantities[$variantId] ?? 0) + $quantity;
                }
            }
        }
        $this->throwIfErrors($errors);

        $lines = [];
        $total = 0;
        foreach ($quantities as $variantId => $quantity) {
            $variant = $this->variantDao->getById($variantId);
            if ($variant === null) {
                $errors["variant_$variantId"] = "Variant $variantId does not exist.";
                continue;
            }
            $product = $this->productDao->getById($variant['product_id']);
            if (!$product['is_active']) {
                $errors["variant_$variantId"] = "{$product['name']} is not available anymore.";
            } elseif ($variant['stock_quantity'] < $quantity) {
                $errors["variant_$variantId"] = "Only {$variant['stock_quantity']} left of {$product['name']} ({$variant['size']}, {$variant['colour']}).";
            }
            $lines[] = ['variant_id' => $variantId, 'quantity' => $quantity, 'unit_price' => $product['price']];
            $total += $quantity * $product['price'];
        }
        $this->throwIfErrors($errors);


        $this->dao->beginTransaction();
        try {
            $order['status'] = 'processing';
            $order['total_amount'] = round($total, 2);
            $newOrder = $this->dao->add($this->withoutNulls($order));

            foreach ($lines as $line) {
                $line['order_id'] = $newOrder['id'];
                $this->orderItemDao->add($line);
                if (!$this->variantDao->decreaseStock($line['variant_id'], $line['quantity'])) {
                    throw new ApiException('Stock changed while placing the order. Please try again.', 400);
                }
            }
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }

        return $this->getDetails($newOrder['id']);
    }


    public function update($id, $data)
    {
        $order = $this->getById($id);
        $clean = $this->validate($data, true, $id);
        if (empty($clean)) {
            throw new ApiException('Nothing to update. Send at least one field.', 400);
        }

        $isCancelling = ($clean['status'] ?? null) === 'cancelled' && $order['status'] !== 'cancelled';

        $this->dao->beginTransaction();
        try {
            if ($isCancelling) {
                $this->restoreStock($id);
            }
            $this->dao->update($id, $clean);
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }

        return $this->getDetails($id);
    }

    public function delete($id)
    {
        $order = $this->getById($id);

        $this->dao->beginTransaction();
        try {
            if (in_array($order['status'], ['processing', 'shipped'], true)) {
                $this->restoreStock($id);
            }
            $this->dao->delete($id); 
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $order = $this->getById($id);
        $errors = [];
        $clean = [
            'status'           => $this->oneOf($data, 'status', 'Status', self::STATUSES, true, $errors),
            'shipping_address' => $this->text($data, 'shipping_address', 'Shipping address', true, $errors, 150),
            'shipping_city'    => $this->text($data, 'shipping_city', 'Shipping city', true, $errors, 60),
            'shipping_zip'     => $this->text($data, 'shipping_zip', 'ZIP', true, $errors, 10, false),
            'payment_method'   => $this->oneOf($data, 'payment_method', 'Payment method', self::PAYMENT_METHODS, true, $errors),
        ];


        $newStatus = $clean['status'];
        if ($newStatus !== null && !isset($errors['status']) && $newStatus !== $order['status']
            && !in_array($newStatus, self::ALLOWED_STATUS_CHANGES[$order['status']], true)) {
            $errors['status'] = "An order that is {$order['status']} cannot be changed to $newStatus.";
        }

    
        $changesShipping = $clean['shipping_address'] !== null || $clean['shipping_city'] !== null
            || $clean['shipping_zip'] !== null || $clean['payment_method'] !== null;
        if ($changesShipping && $order['status'] !== 'processing') {
            $errors['shipping'] = 'Shipping and payment details can only be changed while the order is processing.';
        }


        foreach (['user_id', 'total_amount'] as $field) {
            if ($this->has($data, $field)) {
                $errors[$field] = "$field cannot be changed.";
            }
        }

        $this->throwIfErrors($errors);
        return $this->withoutNulls($clean);
    }

    private function restoreStock($orderId)
    {
        foreach ($this->orderItemDao->getByOrder($orderId) as $item) {
            $this->variantDao->increaseStock($item['variant_id'], $item['quantity']);
        }
    }
}
