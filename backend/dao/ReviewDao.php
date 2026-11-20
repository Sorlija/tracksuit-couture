<?php

require_once __DIR__ . '/BaseDao.php';

class ReviewDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('reviews', ['product_id', 'user_id', 'rating', 'comment']);
    }

    public function getAllWithDetails()
    {
        return $this->query(
            "SELECT r.*, p.name AS product_name, u.email AS user_email
             FROM reviews r
             JOIN products p ON p.id = r.product_id
             JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC"
        );
    }

    public function getByProduct($productId)
    {
        return $this->query(
            "SELECT r.*, u.first_name, LEFT(u.last_name, 1) AS last_initial
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = :product_id
             ORDER BY r.created_at DESC",
            ['product_id' => $productId]
        );
    }

    public function getByProductAndUser($productId, $userId)
    {
        return $this->queryOne(
            "SELECT * FROM reviews WHERE product_id = :product_id AND user_id = :user_id",
            ['product_id' => $productId, 'user_id' => $userId]
        );
    }
}
