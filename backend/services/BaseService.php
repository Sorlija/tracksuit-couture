<?php

require_once __DIR__ . '/../utils/ApiException.php';

abstract class BaseService
{
    protected $dao;
    protected $entityName;

    public function __construct($dao, $entityName)
    {
        $this->dao = $dao;
        $this->entityName = $entityName; 
    }

    public function getAll()
    {
        return $this->dao->getAll();
    }

    public function getById($id)
    {
        $entity = $this->dao->getById($id);
        if ($entity === null) {
            throw new ApiException("{$this->entityName} not found", 404);
        }
        return $entity;
    }

    public function add($data)
    {
        $clean = $this->validate($data, false);
        return $this->dao->add($clean);
    }

    public function update($id, $data)
    {
        $this->getById($id); 
        $clean = $this->validate($data, true, $id);
        if (empty($clean)) {
            throw new ApiException('Nothing to update. Send at least one field.', 400);
        }
        return $this->dao->update($id, $clean);
    }

    public function delete($id)
    {
        $this->getById($id);       
        $this->beforeDelete($id);  
        $this->dao->delete($id);
    }

    abstract protected function validate($data, $isUpdate, $id = null);

    protected function beforeDelete($id)
    {
    }

    protected function has($data, $field)
    {
        return is_array($data) && array_key_exists($field, $data);
    }


    protected function text($data, $field, $label, $isUpdate, &$errors, $maxLength = 255, $required = true)
    {
        if (!$this->has($data, $field)) {
            if ($required && !$isUpdate) {
                $errors[$field] = "$label is required.";
            }
            return null;
        }

        $value = trim((string) $data[$field]);
        if ($value === '' && $required) {
            $errors[$field] = "$label cannot be empty.";
        } elseif (mb_strlen($value) > $maxLength) {
            $errors[$field] = "$label can have at most $maxLength characters.";
        }
        return $value;
    }

    protected function integer($data, $field, $label, $isUpdate, &$errors, $min = 0, $required = true)
    {
        if (!$this->has($data, $field)) {
            if ($required && !$isUpdate) {
                $errors[$field] = "$label is required.";
            }
            return null;
        }

        $value = filter_var($data[$field], FILTER_VALIDATE_INT);
        if ($value === false) {
            $errors[$field] = "$label must be a whole number.";
            return null;
        }
        if ($value < $min) {
            $errors[$field] = "$label must be at least $min.";
        }
        return $value;
    }

    protected function oneOf($data, $field, $label, $options, $isUpdate, &$errors, $required = true)
    {
        if (!$this->has($data, $field)) {
            if ($required && !$isUpdate) {
                $errors[$field] = "$label is required.";
            }
            return null;
        }

        $value = $data[$field];
        if (!in_array($value, $options, true)) {
            $errors[$field] = "$label must be one of: " . implode(', ', $options) . '.';
        }
        return $value;
    }

    protected function throwIfErrors($errors)
    {
        if (!empty($errors)) {
            throw new ApiException('Validation failed', 400, $errors);
        }
    }

    protected function withoutNulls($data)
    {
        return array_filter($data, fn($value) => $value !== null);
    }
}
