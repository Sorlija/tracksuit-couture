<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';

class CategoryService extends BaseService
{
    private $productDao;

    public function __construct()
    {
        parent::__construct(new CategoryDao(), 'Category');
        $this->productDao = new ProductDao();
    }

    public function getAll()
    {
        return $this->dao->getAllWithProductCount();
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $errors = [];
        $clean = [
            'name'        => $this->text($data, 'name', 'Name', $isUpdate, $errors, 50),
            'description' => $this->text($data, 'description', 'Description', $isUpdate, $errors, 255, false),
        ];


        if ($clean['name'] !== null && !isset($errors['name'])) {
            $existing = $this->dao->getByName($clean['name']);
            if ($existing !== null && (int) $existing['id'] !== (int) $id) {
                $errors['name'] = 'A category with this name already exists.';
            }
        }

        $this->throwIfErrors($errors);
        return $this->withoutNulls($clean);
    }

    protected function beforeDelete($id)
    {
        if (count($this->productDao->getByCategory($id)) > 0) {
            throw new ApiException('This category still has products. Move or delete them first.', 400);
        }
    }
}
