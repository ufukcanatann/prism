<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $connection = null;
    private static $instance = null;

    public static function connect()
    {
        if (self::$connection === null) {
            try {
                $host = Config::get('database.host');
                $port = Config::get('database.port');
                $database = Config::get('database.database');
                $username = Config::get('database.username');
                $password = Config::get('database.password');

                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                return self::$connection;
            } catch (PDOException $e) {
                throw new \Exception("Veritabanı bağlantı hatası: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getConnection()
    {
        if (self::$connection === null) {
            return self::connect();
        }
        return self::$connection;
    }

    public static function executeQuery($sql, $params = [])
    {
        $connection = self::getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function execute($sql, $params = [])
    {
        $connection = self::getConnection();
        $stmt = $connection->prepare($sql);
        return $stmt->execute($params);
    }

    public static function staticQuery($sql, $params = [])
    {
        $connection = self::getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch($sql, $params = [])
    {
        $stmt = self::staticQuery($sql, $params);
        return $stmt->fetch();
    }

    public static function fetchAll($sql, $params = [])
    {
        $stmt = self::staticQuery($sql, $params);
        return $stmt->fetchAll();
    }

    public static function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $fieldList = implode(', ', $fields);
        
        $sql = "INSERT INTO {$table} ({$fieldList}) VALUES ({$placeholders})";
        
        $connection = self::getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        
        return $connection->lastInsertId();
    }

    public static function update($table, $data, $where, $whereParams = [])
    {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_values($data);
        $params = array_merge($params, $whereParams);
        
        $connection = self::getConnection();
        $stmt = $connection->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $connection = self::getConnection();
        $stmt = $connection->prepare($sql);
        return $stmt->execute($params);
    }

    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit()
    {
        return self::getConnection()->commit();
    }

    public static function rollback()
    {
        return self::getConnection()->rollback();
    }

    /**
     * Get Query Builder instance
     */
    public static function query(): \Core\Database\QueryBuilder
    {
        return new \Core\Database\QueryBuilder(self::getConnection());
    }

    /**
     * Get Query Builder for table
     */
    public static function table(string $table): \Core\Database\QueryBuilder
    {
        return self::query()->table($table);
    }

    /**
     * Static Query Builder
     */
    public static function queryBuilder(): \Core\Database\QueryBuilder
    {
        return new \Core\Database\QueryBuilder(self::getConnection());
    }

    /**
     * Static table method
     */
    public static function staticTable(string $table): \Core\Database\QueryBuilder
    {
        return self::queryBuilder()->table($table);
    }
}
