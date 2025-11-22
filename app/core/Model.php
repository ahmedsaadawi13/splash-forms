<?php
// FILE: /app/core/Model.php

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $tenantColumn = 'tenant_id';
    protected $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all($where = [], $orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $key) {
                $conditions[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        return $this->db->fetchAll($sql, array_values($where));
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $column = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$value]);
    }

    public function where($conditions, $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE ";

        $clauses = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $clauses[] = "$key = ?";
            $params[] = $value;
        }

        $sql .= implode(' AND ', $clauses);

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }

    public function delete($id) {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }

    public function count($where = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $key) {
                $conditions[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        return $this->db->fetchColumn($sql, array_values($where));
    }

    public function exists($column, $value) {
        return $this->count([$column => $value]) > 0;
    }

    public function paginate($page = 1, $perPage = 20, $where = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        $total = $this->count($where);

        $sql .= " LIMIT $perPage OFFSET $offset";
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    protected function scopeTenant($query, $params) {
        $tenantId = Auth::tenantId();

        if ($tenantId && $this->tenantColumn) {
            $query .= (strpos($query, 'WHERE') !== false ? ' AND ' : ' WHERE ');
            $query .= "{$this->tenantColumn} = ?";
            $params[] = $tenantId;
        }

        return ['query' => $query, 'params' => $params];
    }
}
