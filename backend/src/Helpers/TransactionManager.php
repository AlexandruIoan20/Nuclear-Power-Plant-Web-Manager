<?php

class TransactionManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function begin(): void {
        if ($this->db->inTransaction()) {
            return;
        }
        $this->db->beginTransaction();
    }

    public function commit(): void {
        $this->db->commit();
    }

    public function rollback(): void {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    public function run(callable $callback): mixed {
        $this->begin();
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}