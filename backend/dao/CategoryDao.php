<?php

require_once __DIR__ . '/BaseDao.php';

class CategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('categories', ['name', 'description']);
    }

    public function getByName($name)
    {
        return $this->queryOne("SELECT * FROM categories WHERE name = :name", ['name' => $name]);
    }

    public function getAllWithProductCount()
    {
        return $this->query(
            "SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.id"
        );
    }
}
