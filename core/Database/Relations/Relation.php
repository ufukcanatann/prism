<?php

namespace Core\Database\Relations;

use Core\Database\QueryBuilder;
use Core\Database\Model;

abstract class Relation
{
    /**
     * Parent model instance
     */
    protected Model $parent;
    
    /**
     * Related model class
     */
    protected string $related;
    
    /**
     * Query builder instance
     */
    protected QueryBuilder $query;
    
    /**
     * Foreign key
     */
    protected string $foreignKey;
    
    /**
     * Local key
     */
    protected string $localKey;
    
    /**
     * Constructor
     */
    public function __construct(Model $parent, string $related, ?string $foreignKey = null, ?string $localKey = null)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->query = $this->getRelatedQuery();
        
        $this->foreignKey = $foreignKey ?: $this->getForeignKey();
        $this->localKey = $localKey ?: $this->getLocalKey();
        
        $this->addConstraints();
    }
    
    /**
     * Get related model query
     */
    protected function getRelatedQuery(): QueryBuilder
    {
        $model = new $this->related;
        return $model->newQuery();
    }
    
    /**
     * Get default foreign key
     */
    protected function getForeignKey(): string
    {
        $parentClass = (new \ReflectionClass($this->parent))->getShortName();
        return strtolower($parentClass) . '_id';
    }
    
    /**
     * Get default local key
     */
    protected function getLocalKey(): string
    {
        return $this->parent->getKeyName();
    }
    
    /**
     * Add relationship constraints
     */
    abstract protected function addConstraints(): void;
    
    /**
     * Get relationship results
     */
    abstract public function getResults();
    
    /**
     * Add where constraint
     */
    public function where($column, $operator = null, $value = null): self
    {
        $this->query->where($column, $operator, $value);
        return $this;
    }
    
    /**
     * Add where in constraint
     */
    public function whereIn(string $column, array $values): self
    {
        $this->query->whereIn($column, $values);
        return $this;
    }
    
    /**
     * Add order by
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }
    
    /**
     * Limit results
     */
    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }
    
    /**
     * Get first result
     */
    public function first()
    {
        $result = $this->query->first();
        
        if ($result) {
            return (new $this->related)->newFromArray($result);
        }
        
        return null;
    }
    
    /**
     * Get all results
     */
    public function get(): array
    {
        $results = $this->query->get();
        
        return array_map(function($data) {
            return (new $this->related)->newFromArray($data);
        }, $results);
    }
    
    /**
     * Count results
     */
    public function count(): int
    {
        return $this->query->count();
    }
    
    /**
     * Check if results exist
     */
    public function exists(): bool
    {
        return $this->query->exists();
    }
    
    /**
     * Paginate results
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $results = $this->query->paginate($page, $perPage);
        
        $results['data'] = array_map(function($data) {
            return (new $this->related)->newFromArray($data);
        }, $results['data']);
        
        return $results;
    }
    
    /**
     * Get query builder
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }
    
    /**
     * Get parent model
     */
    public function getParent(): Model
    {
        return $this->parent;
    }
    
    /**
     * Get related model class
     */
    public function getRelated(): string
    {
        return $this->related;
    }
    
    /**
     * Get foreign key
     */
    public function getForeignKeyName(): string
    {
        return $this->foreignKey;
    }
    
    /**
     * Get local key
     */
    public function getLocalKeyName(): string
    {
        return $this->localKey;
    }
    
    /**
     * Magic method to proxy to query builder
     */
    public function __call(string $method, array $arguments)
    {
        $result = $this->query->$method(...$arguments);
        
        if ($result instanceof QueryBuilder) {
            return $this;
        }
        
        return $result;
    }
}
