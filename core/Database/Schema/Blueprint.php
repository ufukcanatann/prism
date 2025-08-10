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

    public function id(string $column = 'id'): self
    {
        $this->columns[] = "{$column} INT PRIMARY KEY AUTO_INCREMENT";
        return $this;
    }

    public function string(string $column, int $length = 255, bool $nullable = false, ?string $default = null): self
    {
        $sql = "{$column} VARCHAR({$length})";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT '{$default}'";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function text(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} TEXT" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function mediumText(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} MEDIUMTEXT" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function longText(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} LONGTEXT" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function integer(string $column, bool $nullable = false, ?int $default = null): self
    {
        $sql = "{$column} INT";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT {$default}";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function bigInteger(string $column, bool $nullable = false, ?int $default = null): self
    {
        $sql = "{$column} BIGINT";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT {$default}";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function smallInteger(string $column, bool $nullable = false, ?int $default = null): self
    {
        $sql = "{$column} SMALLINT";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT {$default}";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function float(string $column, int $total = 8, int $places = 2, bool $nullable = false, ?float $default = null): self
    {
        $sql = "{$column} FLOAT({$total}, {$places})";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT {$default}";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function double(string $column, int $total = 16, int $places = 8, bool $nullable = false, ?float $default = null): self
    {
        $sql = "{$column} DOUBLE({$total}, {$places})";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT {$default}";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function decimal(string $column, int $total = 8, int $places = 2, bool $nullable = false, ?float $default = null): self
    {
        $sql = "{$column} DECIMAL({$total}, {$places})";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT {$default}";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function boolean(string $column, bool $nullable = false, ?bool $default = null): self
    {
        $sql = "{$column} TINYINT(1)";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT " . ($default ? 1 : 0);
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function date(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} DATE" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function datetime(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} DATETIME" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function time(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} TIME" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function timestamp(string $column, bool $nullable = false, bool $useCurrent = false): self
    {
        $sql = "{$column} TIMESTAMP";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if ($useCurrent) {
            $sql .= " DEFAULT CURRENT_TIMESTAMP";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function json(string $column, bool $nullable = false): self
    {
        $this->columns[] = "{$column} JSON" . ($nullable ? " NULL" : " NOT NULL");
        return $this;
    }

    public function enum(string $column, array $values, bool $nullable = false, ?string $default = null): self
    {
        $valList = "'" . implode("','", $values) . "'";
        $sql = "{$column} ENUM({$valList})";
        $sql .= $nullable ? " NULL" : " NOT NULL";
        if (!is_null($default)) {
            $sql .= " DEFAULT '{$default}'";
        }
        $this->columns[] = $sql;
        return $this;
    }

    public function timestamps(): self
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function toSql(): string
    {
        $columns = implode(', ', $this->columns);
        return "CREATE TABLE {$this->table} ({$columns})";
    }
}
