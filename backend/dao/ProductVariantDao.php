<?php

require_once __DIR__ . '/BaseDao.php';

class ProductVariantDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('product_variants', [
            'product_id', 'sku', 'size', 'colour', 'stock_quantity'
        ]);
    }

    public function getByProduct($productId)
    {
        return $this->query(
            "SELECT * FROM product_variants
             WHERE product_id = :product_id
             ORDER BY colour, FIELD(size, 'XS', 'S', 'M', 'L', 'XL', 'XXL')",
            ['product_id' => $productId]
        );
    }

    public function getBySku($sku)
    {
        return $this->queryOne("SELECT * FROM product_variants WHERE sku = :sku", ['sku' => $sku]);
    }

    public function getLowStock($limit = 3)
    {
        return $this->query(
            "SELECT v.*, p.name AS product_name
             FROM product_variants v
             JOIN products p ON p.id = v.product_id
             WHERE v.stock_quantity <= :limit
             ORDER BY v.stock_quantity",
            ['limit' => $limit]
        );
    }


    public function decreaseStock($id, $quantity)
    {
        $statement = $this->connection->prepare(
            "UPDATE product_variants
             SET stock_quantity = stock_quantity - :quantity
             WHERE id = :id AND stock_quantity >= :quantity2"
        );
        $statement->execute(['quantity' => $quantity, 'quantity2' => $quantity, 'id' => $id]);
        return $statement->rowCount() > 0;
    }
}
