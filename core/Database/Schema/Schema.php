<?php

namespace Core\Database\Schema;

use Core\Database;

class Schema
{
    /**
     * Create a new table
     */
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        $sql = $blueprint->toSql();
        Database::execute($sql);
    }
    
    /**
     * Drop table if exists
     */
    public static function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        Database::execute($sql);
    }
}
