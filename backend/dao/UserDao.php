<?php

require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    private const PUBLIC_COLUMNS = 'id, first_name, last_name, email, role, phone, address, city, preferred_currency, created_at';

    public function __construct()
    {
        parent::__construct('users', [
            'first_name', 'last_name', 'email', 'password_hash', 'role',
            'phone', 'address', 'city', 'preferred_currency'
        ]);
    }


    public function getAll()
    {
        return $this->query("SELECT " . self::PUBLIC_COLUMNS . " FROM users ORDER BY id");
    }

    public function getById($id)
    {
        return $this->queryOne("SELECT " . self::PUBLIC_COLUMNS . " FROM users WHERE id = :id", ['id' => $id]);
    }

    public function getByEmail($email)
    {
        return $this->queryOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    public function getByRole($role)
    {
        return $this->query("SELECT " . self::PUBLIC_COLUMNS . " FROM users WHERE role = :role ORDER BY id", ['role' => $role]);
    }
}
