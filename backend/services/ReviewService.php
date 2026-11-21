<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class ReviewService extends BaseService
{
    private $productDao;
    private $userDao;

    public function __construct()
    {
        parent::__construct(new ReviewDao(), 'Review');
        $this->productDao = new ProductDao();
        $this->userDao = new UserDao();
    }

    /** All reviews (admin), or the reviews of one product (product page). */
    public function getAll($productId = null)
    {
        if ($productId === null) {
            return $this->dao->getAllWithDetails();
        }
        if ($this->productDao->getById($productId) === null) {
            throw new ApiException('Product not found', 404);
        }
        return $this->dao->getByProduct($productId);
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $errors = [];
        $clean = [
            'rating'  => $this->integer($data, 'rating', 'Rating', $isUpdate, $errors, 1),
            'comment' => $this->text($data, 'comment', 'Comment', $isUpdate, $errors, 1000, false),
        ];

        if ($clean['rating'] !== null && $clean['rating'] > 5) {
            $errors['rating'] = 'Rating must be between 1 and 5.';
        }

        if ($isUpdate) {
            // A review always stays on the same product and user
            foreach (['product_id', 'user_id'] as $field) {
                if ($this->has($data, $field)) {
                    $errors[$field] = "$field cannot be changed.";
                }
            }
        } else {
            $clean['product_id'] = $this->integer($data, 'product_id', 'Product', false, $errors, 1);
            $clean['user_id']    = $this->integer($data, 'user_id', 'User', false, $errors, 1);

            if ($clean['product_id'] !== null && !isset($errors['product_id']) && $this->productDao->getById($clean['product_id']) === null) {
                $errors['product_id'] = 'This product does not exist.';
            }
            if ($clean['user_id'] !== null && !isset($errors['user_id']) && $this->userDao->getById($clean['user_id']) === null) {
                $errors['user_id'] = 'This user does not exist.';
            }
            // One review per user per product
            if (empty($errors) && $this->dao->getByProductAndUser($clean['product_id'], $clean['user_id']) !== null) {
                $errors['product_id'] = 'This user already reviewed this product. Update the existing review instead.';
            }
        }

        $this->throwIfErrors($errors);
        return $this->withoutNulls($clean);
    }
}
