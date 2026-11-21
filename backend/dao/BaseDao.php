<?php

require_once __DIR__ . '/Database.php';

/**
 * Reusable CRUD methods shared by all DAOs.
 *
 * Every child DAO passes its table name and the list of columns that may be
 * written. Only those columns are used in INSERT/UPDATE, so a request can never
 * inject its own column names into the SQL. All values go through prepared
 * statements (named placeholders like :name).
 */
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

    /** Inserts a row and returns it (with its new id). */
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

    /**
     * Updates only the fields that were sent (works for both PUT and PATCH).
     * Returns the updated row, or null if the id does not exist.
     */
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

    /** Returns true if a row was deleted, false if the id did not exist. */
    public function delete($id)
    {
        $statement = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    // ---------- Transactions ----------
    // All DAOs share one PDO connection, so a transaction started here also
    // covers queries made through other DAOs (used when placing an order).

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollBack()
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    // ---------- Helpers for child DAOs ----------

    /** Runs a SELECT and returns all rows. */
    protected function query($sql, $params = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** Runs a SELECT and returns the first row, or null if nothing was found. */
    protected function queryOne($sql, $params = [])
    {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }

    /** Keeps only the keys that are real, writable columns of this table. */
    protected function onlyAllowedColumns($entity)
    {
        return array_intersect_key($entity, array_flip($this->columns));
    }
}
