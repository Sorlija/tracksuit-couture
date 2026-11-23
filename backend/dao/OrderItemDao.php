<?php

require_once __DIR__ . '/BaseDao.php';

class OrderItemDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('order_items', ['order_id', 'variant_id', 'quantity', 'unit_price']);
    }

    public function getByOrder($orderId)
    {
        return $this->query(
            "SELECT oi.*, v.sku, v.size, v.colour, p.id AS product_id, p.name AS product_name,
                    (oi.quantity * oi.unit_price) AS line_total
             FROM order_items oi
             JOIN product_variants v ON v.id = oi.variant_id
             JOIN products p ON p.id = v.product_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id",
            ['order_id' => $orderId]
        );
    }

    public function getOrderTotal($orderId)
    {
        $row = $this->queryOne(
            "SELECT COALESCE(SUM(quantity * unit_price), 0) AS total FROM order_items WHERE order_id = :order_id",
            ['order_id' => $orderId]
        );
        return (float) $row['total'];
    }
}
