<?php

namespace Core\Database\Schema;

class Blueprint
{
    protected string $table;
    protected array $columns = [];
    
    public function __construct(string $table)
    {
        $this->table = $table;
    }
    
    /**
     * Add ID column
     */
    public function id(string $column = 'id'): self
    {
        $this->columns[] = "{$column} INT PRIMARY KEY AUTO_INCREMENT";
        return $this;
    }
    
    /**
     * Add timestamps
     */
    public function timestamps(): self
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }
    
    /**
     * Convert to SQL
     */
    public function toSql(): string
    {
        $columns = implode(', ', $this->columns);
        return "CREATE TABLE {$this->table} ({$columns})";
    }
}
