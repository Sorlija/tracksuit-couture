<?php

require_once __DIR__ . '/Database.php';

abstract class BaseDao
{
    protected $connection;
    protected $table;
    protected $columns;

    public function __construct($table, $columns)
    {
        $this->connection = Database::connect();
        $this->table = $table;
        $this->columns = $columns;
    }

    // ---------- READ ----------

    public function getAll()
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY id");
    }

    public function getById($id)
    {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE id = :id", ['id' => $id]);
    }

    // ---------- CREATE ----------

    public function add($entity)
    {
        $data = $this->onlyAllowedColumns($entity);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid fields to insert.');
        }

        $columnList   = implode(', ', array_keys($data));                                    // name, price
        $placeholders = implode(', ', array_map(fn($col) => ':' . $col, array_keys($data))); // :name, :price

        $sql = "INSERT INTO {$this->table} ($columnList) VALUES ($placeholders)";
        $statement = $this->connection->prepare($sql);
        $statement->execute($data);

        return $this->getById($this->connection->lastInsertId());
    }

    // ---------- UPDATE ----------

    public function update($id, $entity)
    {
        $data = $this->onlyAllowedColumns($entity);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid fields to update.');
        }

        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = :$column";                                              // name = :name
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $data['id'] = $id;

        $statement = $this->connection->prepare($sql);
        $statement->execute($data);

        return $this->getById($id);
    }

    // ---------- DELETE ----------

    public function delete($id)
    {
        $statement = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    // ---------- Helpers for child DAOs ----------

    protected function query($sql, $params = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    protected function queryOne($sql, $params = [])
    {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }

    protected function onlyAllowedColumns($entity)
    {
        return array_intersect_key($entity, array_flip($this->columns));
    }
}
