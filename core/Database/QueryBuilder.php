<?php

namespace Core\Database;

use Core\Database;

class QueryBuilder
{
    /**
     * Database connection
     */
    private $connection;
    
    /**
     * Query components
     */
    private array $select = ['*'];
    private ?string $table = null;
    private array $joins = [];
    private array $wheres = [];
    private array $orderBy = [];
    private array $groupBy = [];
    private array $having = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $bindings = [];
    
    /**
     * Constructor
     */
    public function __construct($connection = null)
    {
        $this->connection = $connection ?: Database::getConnection();
    }
    
    /**
     * Set table
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Set select columns
     */
    public function select(...$columns): self
    {
        $this->select = empty($columns) ? ['*'] : $columns;
        return $this;
    }
    
    /**
     * Add select column
     */
    public function addSelect(string $column): self
    {
        $this->select[] = $column;
        return $this;
    }
    
    /**
     * Select distinct
     */
    public function distinct(): self
    {
        $this->select = array_unique($this->select);
        return $this;
    }
    
    /**
     * Add WHERE clause
     */
    public function where($column, $operator = null, $value = null, string $boolean = 'AND'): self
    {
        // Handle where($column, $value) syntax
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * Add OR WHERE clause
     */
    public function orWhere($column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }
    
    /**
     * WHERE IN clause
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }
    
    /**
     * WHERE NOT IN clause
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'not_in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }
    
    /**
     * WHERE NULL clause
     */
    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }
    
    /**
     * WHERE NOT NULL clause
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }
    
    /**
     * WHERE BETWEEN clause
     */
    public function whereBetween(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }
    
    /**
     * WHERE LIKE clause
     */
    public function whereLike(string $column, string $value, string $boolean = 'AND'): self
    {
        return $this->where($column, 'LIKE', $value, $boolean);
    }
    
    /**
     * Raw WHERE clause
     */
    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean
        ];
        
        $this->bindings = array_merge($this->bindings, $bindings);
        
        return $this;
    }
    
    /**
     * JOIN clause
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }
    
    /**
     * LEFT JOIN clause
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }
    
    /**
     * RIGHT JOIN clause
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }
    
    /**
     * ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        
        return $this;
    }
    
    /**
     * ORDER BY DESC
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }
    
    /**
     * Latest records (order by created_at DESC)
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderByDesc($column);
    }
    
    /**
     * Oldest records (order by created_at ASC)
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'ASC');
    }
    
    /**
     * GROUP BY clause
     */
    public function groupBy(...$columns): self
    {
        $this->groupBy = array_merge($this->groupBy, $columns);
        return $this;
    }
    
    /**
     * HAVING clause
     */
    public function having(string $column, string $operator, $value): self
    {
        $this->having[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * LIMIT clause
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * OFFSET clause
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * SKIP alias for offset
     */
    public function skip(int $offset): self
    {
        return $this->offset($offset);
    }
    
    /**
     * TAKE alias for limit
     */
    public function take(int $limit): self
    {
        return $this->limit($limit);
    }
    
    /**
     * Pagination
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalQuery = clone $this;
        $total = $totalQuery->count();
        
        // Get paginated results
        $results = $this->offset($offset)->limit($perPage)->get();
        
        return [
            'data' => $results,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Execute query and get all results
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }
    
    /**
     * Get first result
     */
    public function first(): ?array
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }
    
    /**
     * Find by ID
     */
    public function find($id, string $column = 'id'): ?array
    {
        return $this->where($column, $id)->first();
    }
    
    /**
     * Get count
     */
    public function count(string $column = '*'): int
    {
        $originalSelect = $this->select;
        $this->select = ["COUNT({$column}) as count"];
        
        $result = $this->first();
        
        $this->select = $originalSelect;
        
        return (int) ($result['count'] ?? 0);
    }
    
    /**
     * Get sum
     */
    public function sum(string $column): float
    {
        $originalSelect = $this->select;
        $this->select = ["SUM({$column}) as sum"];
        
        $result = $this->first();
        
        $this->select = $originalSelect;
        
        return (float) ($result['sum'] ?? 0);
    }
    
    /**
     * Get average
     */
    public function avg(string $column): float
    {
        $originalSelect = $this->select;
        $this->select = ["AVG({$column}) as avg"];
        
        $result = $this->first();
        
        $this->select = $originalSelect;
        
        return (float) ($result['avg'] ?? 0);
    }
    
    /**
     * Get maximum
     */
    public function max(string $column)
    {
        $originalSelect = $this->select;
        $this->select = ["MAX({$column}) as max"];
        
        $result = $this->first();
        
        $this->select = $originalSelect;
        
        return $result['max'] ?? null;
    }
    
    /**
     * Get minimum
     */
    public function min(string $column)
    {
        $originalSelect = $this->select;
        $this->select = ["MIN({$column}) as min"];
        
        $result = $this->first();
        
        $this->select = $originalSelect;
        
        return $result['min'] ?? null;
    }
    
    /**
     * Check if records exist
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }
    
    /**
     * Insert data
     */
    public function insert(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        // Handle array of arrays for bulk insert
        if (is_array($data[0] ?? null)) {
            return $this->insertMultiple($data);
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute(array_values($data));
    }
    
    /**
     * Insert and get ID
     */
    public function insertGetId(array $data): ?int
    {
        if ($this->insert($data)) {
            return $this->connection->lastInsertId();
        }
        
        return null;
    }
    
    /**
     * Insert multiple records
     */
    public function insertMultiple(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        $columns = array_keys($data[0]);
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $allPlaceholders = array_fill(0, count($data), $placeholders);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES " . implode(', ', $allPlaceholders);
        
        $bindings = [];
        foreach ($data as $row) {
            $bindings = array_merge($bindings, array_values($row));
        }
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($bindings);
    }
    
    /**
     * Update records
     */
    public function update(array $data): int
    {
        if (empty($data)) {
            return 0;
        }
        
        $sets = [];
        $bindings = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= $this->buildWheres();
            $bindings = array_merge($bindings, $this->bindings);
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($bindings);
        
        return $stmt->rowCount();
    }
    
    /**
     * Increment column
     */
    public function increment(string $column, int $amount = 1): int
    {
        return $this->update([$column => new \Core\Database\Expression("{$column} + {$amount}")]);
    }
    
    /**
     * Decrement column
     */
    public function decrement(string $column, int $amount = 1): int
    {
        return $this->update([$column => new \Core\Database\Expression("{$column} - {$amount}")]);
    }
    
    /**
     * Delete records
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= $this->buildWheres();
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);
        
        return $stmt->rowCount();
    }
    
    /**
     * Build SQL query
     */
    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";
        
        // Add JOINs
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        
        // Add WHERE clauses
        if (!empty($this->wheres)) {
            $sql .= $this->buildWheres();
        }
        
        // Add GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }
        
        // Add HAVING
        if (!empty($this->having)) {
            $havingClauses = [];
            foreach ($this->having as $having) {
                $havingClauses[] = "{$having['column']} {$having['operator']} ?";
            }
            $sql .= " HAVING " . implode(' AND ', $havingClauses);
        }
        
        // Add ORDER BY
        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        // Add LIMIT
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        // Add OFFSET
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
    
    /**
     * Build WHERE clauses
     */
    private function buildWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        
        $sql = ' WHERE ';
        $clauses = [];
        
        foreach ($this->wheres as $i => $where) {
            $clause = '';
            
            if ($i > 0) {
                $clause .= " {$where['boolean']} ";
            }
            
            switch ($where['type']) {
                case 'basic':
                    $clause .= "{$where['column']} {$where['operator']} ?";
                    break;
                    
                case 'in':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $clause .= "{$where['column']} IN ({$placeholders})";
                    break;
                    
                case 'not_in':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $clause .= "{$where['column']} NOT IN ({$placeholders})";
                    break;
                    
                case 'null':
                    $clause .= "{$where['column']} IS NULL";
                    break;
                    
                case 'not_null':
                    $clause .= "{$where['column']} IS NOT NULL";
                    break;
                    
                case 'between':
                    $clause .= "{$where['column']} BETWEEN ? AND ?";
                    break;
                    
                case 'raw':
                    $clause .= $where['sql'];
                    break;
            }
            
            $clauses[] = $clause;
        }
        
        return $sql . implode('', $clauses);
    }
    
    /**
     * Get query bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    /**
     * Reset query builder
     */
    public function reset(): self
    {
        $this->select = ['*'];
        $this->table = null;
        $this->joins = [];
        $this->wheres = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = [];
        $this->limit = null;
        $this->offset = null;
        $this->bindings = [];
        
        return $this;
    }
    
    /**
     * Clone query builder
     */
    public function __clone()
    {
        // Create deep copies of arrays
        $this->select = [...$this->select];
        $this->joins = [...$this->joins];
        $this->wheres = [...$this->wheres];
        $this->orderBy = [...$this->orderBy];
        $this->groupBy = [...$this->groupBy];
        $this->having = [...$this->having];
        $this->bindings = [...$this->bindings];
    }
}
