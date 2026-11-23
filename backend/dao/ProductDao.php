<?php

require_once __DIR__ . '/BaseDao.php';

class ProductDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('products', [
            'category_id', 'name', 'description', 'fit', 'price', 'image_url', 'is_active'
        ]);
    }

    public function getAllWithCategory()
    {
        return $this->query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             ORDER BY p.id"
        );
    }

    public function getActive()
    {
        return $this->query(
            "SELECT p.*, c.name AS category_name,
                    ROUND(AVG(r.rating), 1) AS avg_rating,
                    COUNT(r.id) AS review_count
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN reviews r ON r.product_id = p.id
             WHERE p.is_active = 1
             GROUP BY p.id
             ORDER BY p.created_at DESC, p.id DESC"
        );
    }

    public function getByCategory($categoryId)
    {
        return $this->query(
            "SELECT * FROM products WHERE category_id = :category_id ORDER BY id",
            ['category_id' => $categoryId]
        );
    }
}
