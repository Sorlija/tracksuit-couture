<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/CurrencyService.php';

class UserService extends BaseService
{
    private $orderDao;

    public function __construct()
    {
        parent::__construct(new UserDao(), 'User');
        $this->orderDao = new OrderDao();
    }

    public function getByRole($role)
    {
        return $this->dao->getByRole($role);
    }

    protected function validate($data, $isUpdate, $id = null)
    {
        $errors = [];
        $clean = [
            'first_name'         => $this->text($data, 'first_name', 'First name', $isUpdate, $errors, 50),
            'last_name'          => $this->text($data, 'last_name', 'Last name', $isUpdate, $errors, 50),
            'email'              => $this->text($data, 'email', 'Email', $isUpdate, $errors, 100),
            'role'               => $this->oneOf($data, 'role', 'Role', ['admin', 'customer'], $isUpdate, $errors, false),
            'phone'              => $this->text($data, 'phone', 'Phone', $isUpdate, $errors, 30, false),
            'address'            => $this->text($data, 'address', 'Address', $isUpdate, $errors, 150, false),
            'city'               => $this->text($data, 'city', 'City', $isUpdate, $errors, 60, false),
            'preferred_currency' => $this->oneOf($data, 'preferred_currency', 'Preferred currency',
                                        CurrencyService::SUPPORTED, $isUpdate, $errors, false),
        ];

        // Email must be valid and not used by another user
        if ($clean['email'] !== null && !isset($errors['email'])) {
            $clean['email'] = strtolower($clean['email']);
            if (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email is not valid.';
            } else {
                $existing = $this->dao->getByEmail($clean['email']);
                if ($existing !== null && (int) $existing['id'] !== (int) $id) {
                    $errors['email'] = 'This email is already registered.';
                }
            }
        }

        // The client sends a plain "password". It is hashed here and never stored as plain text.
        // A "password_hash" sent by the client is ignored, because it is not read anywhere.
        if ($this->has($data, 'password')) {
            $password = (string) $data['password'];
            if (strlen($password) < 8) {
                $errors['password'] = 'Password must have at least 8 characters.';
            } else {
                $clean['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }
        } elseif (!$isUpdate) {
            $errors['password'] = 'Password is required.';
        }

        $this->throwIfErrors($errors);
        return $this->withoutNulls($clean);
    }

    protected function beforeDelete($id)
    {
        if ($this->orderDao->countByUser($id) > 0) {
            throw new ApiException('This user has orders and cannot be deleted, so the order history is kept.', 400);
        }
    }
}
