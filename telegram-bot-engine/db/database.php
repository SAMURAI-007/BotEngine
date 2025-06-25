<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = getenv('DB_HOST');
        $db   = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new \PDO($dsn, $user, $pass, $options);
    }

    // Singleton instance
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get PDO object
    public function getConnection()
    {
        return $this->pdo;
    }

    // Static quick query
    public static function query($sql, $params = [])
    {
        $db = self::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Dynamic select builder
    public function select($table, $where = [], $fields = ['*'])
    {
        $sql = "SELECT " . implode(',', $fields) . " FROM `$table`";
        $params = [];
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Dynamic insert builder
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        $sql = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    // Dynamic update builder
    public function update($table, $data, $where)
    {
        $set = [];
        $params = [];
        foreach ($data as $key => $value) {
            $set[] = "`$key` = :set_$key";
            $params["set_$key"] = $value;
        }
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = :where_$key";
            $params["where_$key"] = $value;
        }
        $sql = "UPDATE `$table` SET " . implode(',', $set) . " WHERE " . implode(' AND ', $conditions);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}