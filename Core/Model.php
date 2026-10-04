<?php
class Model {
    protected $db;
    protected $table;

    /**
     * Columns that may be written by create()/update().
     *
     * An empty array means "no explicit whitelist". Child models should
     * declare their own list so that user input can never be used to build
     * column names (create()/update() build SQL from the array keys, so
     * passing $_POST straight through would be an injection vector).
     */
    protected $fillable = [];

    /**
     * Columns that are always stripped from writes, even when they appear
     * in $fillable (e.g. 'password' when callers pass hashes themselves).
     */
    protected $guarded = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findBy($column, $value) {
        $column = $this->safeColumn($column);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value");
        $stmt->execute(['value' => $value]);
        return $stmt->fetch();
    }

    public function findManyBy($column, $value) {
        $column = $this->safeColumn($column);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value");
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }

    public function existsBy($column, $value, $excludeId = null) {
        $column = $this->safeColumn($column);
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$column} = :value";
        $params = ['value' => $value];
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Filter $data down to the writable columns of this table.
     */
    protected function filterData($data) {
        $clean = [];

        foreach ($data as $key => $value) {
            if (!is_string($key) || in_array($key, $this->guarded, true)) {
                continue;
            }

            if (!empty($this->fillable)) {
                if (!in_array($key, $this->fillable, true)) {
                    continue;
                }
            } elseif (!$this->isRealColumn($key)) {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * Reject anything that is not a plain identifier, so a column name can
     * never carry SQL through filter_var()/string interpolation.
     */
    protected function safeColumn($column) {
        if (!is_string($column) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column)) {
            throw new InvalidArgumentException('Invalid column name.');
        }
        return $column;
    }

    /**
     * True when $column exists on this table.
     */
    protected function isRealColumn($column) {
        static $columns = [];

        if (!isset($columns[$this->table])) {
            $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table}");
            $columns[$this->table] = array_column($stmt->fetchAll(), 'Field');
        }

        return in_array($column, $columns[$this->table], true);
    }

    public function create($data) {
        $data = $this->filterData($data);

        if (empty($data)) {
            return false;
        }

        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $data = $this->filterData($data);

        if (empty($data)) {
            return true;
        }

        $fields = '';
        foreach (array_keys($data) as $key) {
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ', ');

        $data['id'] = $id;

        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getConnection() {
        return $this->db;
    }
}