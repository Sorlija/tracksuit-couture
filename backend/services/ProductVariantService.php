<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ProductVariantDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';

class ProductVariantService extends BaseService
{
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    private $productDao;
    private $orderItemDao;

    public function __construct()
    {
        parent::__construct(new ProductVariantDao(), 'Variant');
        $this->productDao = new ProductDao();
        $this->orderItemDao = new OrderItemDao();
    }

    public function getByProduct($productId)
    {
        if ($this->productDao->getById($productId) === null) {
            throw new ApiException('Product not found', 404);
        }
        return $this->dao->getByProduct($productId);
    }

    public function getLowStock($limit = 3)
    {
        return $this->dao->getLowStock($limit);
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $errors = [];
        $clean = [
            'product_id'     => $this->integer($data, 'product_id', 'Product', $isUpdate, $errors, 1),
            'size'           => $this->oneOf($data, 'size', 'Size', self::SIZES, $isUpdate, $errors),
            'colour'         => $this->text($data, 'colour', 'Colour', $isUpdate, $errors, 30),
            'stock_quantity' => $this->integer($data, 'stock_quantity', 'Stock quantity', $isUpdate, $errors, 0, false),
            'sku'            => $this->text($data, 'sku', 'SKU', $isUpdate, $errors, 40, false),
        ];

        $product = null;
        if ($clean['product_id'] !== null && !isset($errors['product_id'])) {
            $product = $this->productDao->getById($clean['product_id']);
            if ($product === null) {
                $errors['product_id'] = 'This product does not exist.';
            }
        }
        $this->throwIfErrors($errors);

        // On update, fill in the values that were not sent, so the checks below see the full variant
        $current = $isUpdate ? $this->dao->getById($id) : [];
        $productId = $clean['product_id'] ?? $current['product_id'];
        $size      = $clean['size'] ?? $current['size'];
        $colour    = $clean['colour'] ?? $current['colour'];

        // Only one variant per product + size + colour
        $sameVariant = $this->dao->getByProductSizeColour($productId, $size, $colour);
        if ($sameVariant !== null && (int) $sameVariant['id'] !== (int) $id) {
            $errors['size'] = "This product already has a $size / $colour variant.";
        }

        // SKU: generated automatically on create if it was not sent, and must be unique
        if (!$isUpdate && empty($clean['sku'])) {
            $product = $product ?? $this->productDao->getById($productId);
            $clean['sku'] = $this->generateSku($product['name'], $colour, $size);
        }
        if (!empty($clean['sku'])) {
            $clean['sku'] = strtoupper($clean['sku']);
            $sameSku = $this->dao->getBySku($clean['sku']);
            if ($sameSku !== null && (int) $sameSku['id'] !== (int) $id) {
                $errors['sku'] = 'This SKU is already used by another variant.';
            }
        }

        $this->throwIfErrors($errors);
        return $this->withoutNulls($clean);
    }

    /** "Monaco Velour Tracksuit", "Black", "M" -> "MON-BLA-M" */
    private function generateSku($productName, $colour, $size)
    {
        $productPart = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $productName), 0, 3));
        $colourPart  = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $colour), 0, 3));
        return "$productPart-$colourPart-$size";
    }

    protected function beforeDelete($id)
    {
        if ($this->orderItemDao->countByVariant($id) > 0) {
            throw new ApiException('This variant has been ordered and cannot be deleted. Set its stock to 0 instead.', 400);
        }
    }
}
