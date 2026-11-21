<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';

class ProductService extends BaseService
{
    private $categoryDao;
    private $orderItemDao;

    public function __construct()
    {
        parent::__construct(new ProductDao(), 'Product');
        $this->categoryDao = new CategoryDao();
        $this->orderItemDao = new OrderItemDao();
    }


    public function getAll($activeOnly = false, $categoryId = null)
    {
        $products = $activeOnly ? $this->dao->getActive() : $this->dao->getAllWithCategory();

        if ($categoryId !== null) {
            $products = array_values(array_filter(
                $products,
                fn($product) => (int) $product['category_id'] === (int) $categoryId
            ));
        }
        return $products;
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $errors = [];
        $clean = [
            'name'        => $this->text($data, 'name', 'Name', $isUpdate, $errors, 100),
            'description' => $this->text($data, 'description', 'Description', $isUpdate, $errors, 2000, false),
            'fit'         => $this->oneOf($data, 'fit', 'Fit', ['men', 'women', 'unisex'], $isUpdate, $errors, false),
            'image_url'   => $this->text($data, 'image_url', 'Image URL', $isUpdate, $errors, 255, false),
            'category_id' => $this->integer($data, 'category_id', 'Category', $isUpdate, $errors, 1),
        ];

        // Category must exist
        if ($clean['category_id'] !== null && !isset($errors['category_id'])
            && $this->categoryDao->getById($clean['category_id']) === null) {
            $errors['category_id'] = 'This category does not exist.';
        }

        // Price: a number, 0 or more, rounded to 2 decimals (stored in BAM)
        if ($this->has($data, 'price')) {
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $errors['price'] = 'Price must be a number of 0 or more.';
            } else {
                $clean['price'] = round((float) $data['price'], 2);
            }
        } elseif (!$isUpdate) {
            $errors['price'] = 'Price is required.';
        }

        // is_active: true/false, stored as 1/0
        if ($this->has($data, 'is_active')) {
            $active = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($active === null) {
                $errors['is_active'] = 'Active must be true or false.';
            } else {
                $clean['is_active'] = $active ? 1 : 0;
            }
        }

        if ($clean['image_url'] !== null && $clean['image_url'] !== ''
            && !filter_var($clean['image_url'], FILTER_VALIDATE_URL)) {
            $errors['image_url'] = 'Image URL is not a valid URL.';
        }

        $this->throwIfErrors($errors);
        return $this->withoutNulls($clean);
    }

    protected function beforeDelete($id)
    {
        // Old orders point to this product's variants, so deleting it would break order history
        if ($this->orderItemDao->countByProduct($id) > 0) {
            throw new ApiException('This product has been ordered and cannot be deleted. Set is_active to false to hide it from the shop.', 400);
        }
    }
}
