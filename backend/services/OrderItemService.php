<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';

class OrderItemService extends BaseService
{
    private $orderDao;
    private $variantDao;
    private $productDao;

    public function __construct()
    {
        parent::__construct(new OrderItemDao(), 'Order item');
        $this->orderDao = new OrderDao();
        $this->variantDao = new ProductVariantDao();
        $this->productDao = new ProductDao();
    }

    public function getAll($orderId = null)
    {
        if ($orderId === null) {
            return $this->dao->getAll();
        }
        $this->getProcessingOrder($orderId, false); 
        return $this->dao->getByOrder($orderId);
    }

    public function add($data)
    {
        $errors = [];
        $orderId   = $this->integer($data, 'order_id', 'Order', false, $errors, 1);
        $variantId = $this->integer($data, 'variant_id', 'Variant', false, $errors, 1);
        $quantity  = $this->integer($data, 'quantity', 'Quantity', false, $errors, 1);
        $this->throwIfErrors($errors);

        $this->getProcessingOrder($orderId);

        $variant = $this->variantDao->getById($variantId);
        if ($variant === null) {
            throw new ApiException('Variant not found', 404);
        }
        $product = $this->productDao->getById($variant['product_id']);
        if (!$product['is_active']) {
            throw new ApiException("{$product['name']} is not available anymore.", 400);
        }
        if ($this->dao->getByOrderAndVariant($orderId, $variantId) !== null) {
            throw new ApiException('This variant is already in the order. Change the quantity of that line instead.', 400);
        }

        $this->dao->beginTransaction();
        try {
            if (!$this->variantDao->decreaseStock($variantId, $quantity)) {
                throw new ApiException("Only {$variant['stock_quantity']} left in stock.", 400);
            }
            $item = $this->dao->add([
                'order_id'   => $orderId,
                'variant_id' => $variantId,
                'quantity'   => $quantity,
                'unit_price' => $product['price'],
            ]);
            $this->updateOrderTotal($orderId);
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }
        return $item;
    }

    public function update($id, $data)
    {
        $item = $this->getById($id);
        $clean = $this->validate($data, true, $id);
        $this->getProcessingOrder($item['order_id']);

        $difference = $clean['quantity'] - $item['quantity'];

        $this->dao->beginTransaction();
        try {
            if ($difference > 0 && !$this->variantDao->decreaseStock($item['variant_id'], $difference)) {
                throw new ApiException('Not enough stock for this quantity.', 400);
            }
            if ($difference < 0) {
                $this->variantDao->increaseStock($item['variant_id'], -$difference);
            }
            $updated = $this->dao->update($id, $clean);
            $this->updateOrderTotal($item['order_id']);
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }
        return $updated;
    }

    public function delete($id)
    {
        $item = $this->getById($id);
        $this->getProcessingOrder($item['order_id']);

        if (count($this->dao->getByOrder($item['order_id'])) <= 1) {
            throw new ApiException('An order needs at least one item. Cancel the order instead.', 400);
        }

        $this->dao->beginTransaction();
        try {
            $this->variantDao->increaseStock($item['variant_id'], $item['quantity']);
            $this->dao->delete($id);
            $this->updateOrderTotal($item['order_id']);
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollBack();
            throw $e;
        }
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $errors = [];
        foreach (['order_id', 'variant_id', 'unit_price'] as $field) {
            if ($this->has($data, $field)) {
                $errors[$field] = "$field cannot be changed. Only quantity can be updated.";
            }
        }
        $quantity = $this->integer($data, 'quantity', 'Quantity', false, $errors, 1);
        $this->throwIfErrors($errors);
        return ['quantity' => $quantity];
    }

    private function getProcessingOrder($orderId, $mustBeProcessing = true)
    {
        $order = $this->orderDao->getById($orderId);
        if ($order === null) {
            throw new ApiException('Order not found', 404);
        }
        if ($mustBeProcessing && $order['status'] !== 'processing') {
            throw new ApiException("Items can only be changed while the order is processing (this order is {$order['status']}).", 400);
        }
        return $order;
    }

    private function updateOrderTotal($orderId)
    {
        $this->orderDao->update($orderId, ['total_amount' => $this->dao->getOrderTotal($orderId)]);
    }
}
