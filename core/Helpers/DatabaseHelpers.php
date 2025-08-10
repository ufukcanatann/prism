<?php

if (!function_exists('db')) {
    /**
     * Get database instance or query builder
     */
    function db(?string $table = null): \Core\Database|\Core\Database\QueryBuilder
    {
        $db = \Core\Database::getInstance();
        
        if ($table) {
            return $db->table($table);
        }
        
        return $db;
    }
}

if (!function_exists('table')) {
    /**
     * Get query builder for table
     */
    function table(string $table): \Core\Database\QueryBuilder
    {
        return \Core\Database::staticTable($table);
    }
}

if (!function_exists('raw')) {
    /**
     * Create raw database expression
     */
    function raw(string $expression): \Core\Database\Expression
    {
        return new \Core\Database\Expression($expression);
    }
}

if (!function_exists('paginate')) {
    /**
     * Paginate query builder results
     */
    function paginate(\Core\Database\QueryBuilder $query, int $page = 1, int $perPage = 15): array
    {
        return $query->paginate($page, $perPage);
    }
}

if (!function_exists('transaction')) {
    /**
     * Execute callback in database transaction
     */
    function transaction(callable $callback)
    {
        \Core\Database::beginTransaction();
        
        try {
            $result = $callback();
            \Core\Database::commit();
            return $result;
        } catch (\Exception $e) {
            \Core\Database::rollback();
            throw $e;
        }
    }
}
