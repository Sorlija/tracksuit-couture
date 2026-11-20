<?php

require_once __DIR__ . '/BaseDao.php';

class OrderDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('orders', [
            'user_id', 'status', 'total_amount', 'shipping_address',
            'shipping_city', 'shipping_zip', 'payment_method'
        ]);
    }

    public function getAllWithCustomer()
    {
        return $this->query(
            "SELECT o.*, u.email AS customer_email,
                    (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
             FROM orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC"
        );
    }

    public function getByUser($userId)
    {
        return $this->query(
            "SELECT o.*,
                    (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
             FROM orders o
             WHERE o.user_id = :user_id
             ORDER BY o.created_at DESC",
            ['user_id' => $userId]
        );
    }

    public function getByStatus($status)
    {
        return $this->query(
            "SELECT * FROM orders WHERE status = :status ORDER BY created_at DESC",
            ['status' => $status]
        );
    }
}
