<?php

namespace Core\Database\Relations;

use Core\Database\Model;
use Core\Database\QueryBuilder;

class BelongsToMany extends Relation
{
    /**
     * Pivot table name
     */
    protected string $table;
    
    /**
     * Related pivot key
     */
    protected string $relatedPivotKey;
    
    /**
     * Parent pivot key
     */
    protected string $parentPivotKey;
    
    /**
     * Pivot columns to retrieve
     */
    protected array $pivotColumns = [];
    
    /**
     * Constructor
     */
    public function __construct(
        Model $parent,
        string $related,
        string $table,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ) {
        $this->table = $table;
        $this->parentPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $this->relatedPivotKey = $relatedPivotKey ?: $this->getRelatedForeignKey();
        
        parent::__construct($parent, $related, null, $parentKey);
        
        $this->localKey = $relatedKey ?: (new $this->related)->getKeyName();
    }
    
    /**
     * Get related foreign key
     */
    protected function getRelatedForeignKey(): string
    {
        $relatedClass = (new \ReflectionClass($this->related))->getShortName();
        return strtolower($relatedClass) . '_id';
    }
    
    /**
     * Add relationship constraints
     */
    protected function addConstraints(): void
    {
        $this->performJoin();
        
        if ($this->parent->exists) {
            $this->addWhereConstraints();
        }
    }
    
    /**
     * Perform join with pivot table
     */
    protected function performJoin(?QueryBuilder $query = null): void
    {
        $query = $query ?: $this->query;
        
        $baseTable = (new $this->related)->getTable();
        
        $query->join(
            $this->table,
            $baseTable . '.' . $this->localKey,
            '=',
            $this->table . '.' . $this->relatedPivotKey
        );
    }
    
    /**
     * Add where constraints
     */
    protected function addWhereConstraints(): void
    {
        $this->query->where(
            $this->table . '.' . $this->parentPivotKey,
            $this->parent->getAttribute($this->getLocalKeyName())
        );
    }
    
    /**
     * Get relationship results
     */
    public function getResults(): array
    {
        return !is_null($this->parent->getAttribute($this->getLocalKeyName()))
            ? $this->get()
            : [];
    }
    
    /**
     * Get all results with pivot data
     */
    public function get(): array
    {
        $columns = $this->shouldSelectPivotColumns() 
            ? $this->getSelectColumns() 
            : [(new $this->related)->getTable() . '.*'];
        
        $this->query->select(...$columns);
        
        $results = $this->query->get();
        
        return array_map(function($data) {
            return $this->hydratePivotRelation($data);
        }, $results);
    }
    
    /**
     * Check if pivot columns should be selected
     */
    protected function shouldSelectPivotColumns(): bool
    {
        return !empty($this->pivotColumns);
    }
    
    /**
     * Get select columns including pivot
     */
    protected function getSelectColumns(): array
    {
        $baseTable = (new $this->related)->getTable();
        $columns = [$baseTable . '.*'];
        
        foreach ($this->pivotColumns as $column) {
            $columns[] = $this->table . '.' . $column . ' as pivot_' . $column;
        }
        
        return $columns;
    }
    
    /**
     * Hydrate pivot relation on model
     */
    protected function hydratePivotRelation(array $data): Model
    {
        $model = (new $this->related)->newFromArray($data);
        
        // Extract pivot data
        $pivotData = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'pivot_') === 0) {
                $pivotKey = substr($key, 6); // Remove 'pivot_' prefix
                $pivotData[$pivotKey] = $value;
                unset($data[$key]);
            }
        }
        
        // Add pivot keys
        $pivotData[$this->parentPivotKey] = $this->parent->getAttribute($this->getLocalKeyName());
        $pivotData[$this->relatedPivotKey] = $model->getAttribute($this->localKey);
        
        $model->setPivot($pivotData);
        
        return $model;
    }
    
    /**
     * Attach models to the parent
     */
    public function attach($id, array $attributes = []): void
    {
        if ($id instanceof Model) {
            $id = $id->getAttribute($this->localKey);
        }
        
        if (is_array($id)) {
            foreach ($id as $singleId) {
                $this->attach($singleId, $attributes);
            }
            return;
        }
        
        $record = [
            $this->parentPivotKey => $this->parent->getAttribute($this->getLocalKeyName()),
            $this->relatedPivotKey => $id
        ];
        
        $record = array_merge($record, $attributes);
        
        \Core\Database::table($this->table)->insert($record);
    }
    
    /**
     * Detach models from the parent
     */
    public function detach($ids = null): int
    {
        $query = \Core\Database::table($this->table)
            ->where($this->parentPivotKey, $this->parent->getAttribute($this->getLocalKeyName()));
        
        if ($ids !== null) {
            if ($ids instanceof Model) {
                $ids = $ids->getAttribute($this->localKey);
            }
            
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            
            $query->whereIn($this->relatedPivotKey, $ids);
        }
        
        return $query->delete();
    }
    
    /**
     * Sync the intermediate table with a list of IDs
     */
    public function sync(array $ids): array
    {
        $changes = [
            'attached' => [],
            'detached' => [],
            'updated' => []
        ];
        
        // Get current relationships
        $current = $this->newPivotQuery()
            ->where($this->parentPivotKey, $this->parent->getAttribute($this->getLocalKeyName()))
            ->get();
        
        $currentIds = array_column($current, $this->relatedPivotKey);
        
        // Determine changes
        $detach = array_diff($currentIds, array_keys($ids));
        $attach = array_diff(array_keys($ids), $currentIds);
        
        // Detach removed relationships
        if (!empty($detach)) {
            $this->detach($detach);
            $changes['detached'] = $detach;
        }
        
        // Attach new relationships
        foreach ($attach as $id) {
            $this->attach($id, $ids[$id] ?? []);
            $changes['attached'][] = $id;
        }
        
        // Update existing relationships
        foreach ($currentIds as $id) {
            if (isset($ids[$id]) && !empty($ids[$id])) {
                $this->updateExistingPivot($id, $ids[$id]);
                $changes['updated'][] = $id;
            }
        }
        
        return $changes;
    }
    
    /**
     * Update existing pivot record
     */
    public function updateExistingPivot($id, array $attributes): int
    {
        return \Core\Database::table($this->table)
            ->where($this->parentPivotKey, $this->parent->getAttribute($this->getLocalKeyName()))
            ->where($this->relatedPivotKey, $id)
            ->update($attributes);
    }
    
    /**
     * Get new pivot query
     */
    protected function newPivotQuery(): QueryBuilder
    {
        return \Core\Database::table($this->table);
    }
    
    /**
     * Toggle model attachment
     */
    public function toggle($ids): array
    {
        $changes = ['attached' => [], 'detached' => []];
        
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        foreach ($ids as $id) {
            if ($this->pivotExists($id)) {
                $this->detach($id);
                $changes['detached'][] = $id;
            } else {
                $this->attach($id);
                $changes['attached'][] = $id;
            }
        }
        
        return $changes;
    }
    
    /**
     * Check if relationship exists for given ID
     */
    public function pivotExists($id): bool
    {
        if ($id instanceof Model) {
            $id = $id->getAttribute($this->localKey);
        }
        
        return $this->newPivotQuery()
            ->where($this->parentPivotKey, $this->parent->getAttribute($this->getLocalKeyName()))
            ->where($this->relatedPivotKey, $id)
            ->exists();
    }
    
    /**
     * Specify pivot columns to retrieve
     */
    public function withPivot(...$columns): self
    {
        $this->pivotColumns = array_merge($this->pivotColumns, $columns);
        return $this;
    }
    
    /**
     * Add timestamps to pivot table
     */
    public function withTimestamps(): self
    {
        return $this->withPivot('created_at', 'updated_at');
    }
    
    /**
     * Get pivot table name
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Initialize relation on a set of models
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, []);
        }
        
        return $models;
    }
    
    /**
     * Match eagerly loaded results to their parents
     */
    public function match(array $models, array $results, string $relation): array
    {
        $dictionary = $this->buildDictionary($results);
        
        foreach ($models as $model) {
            $key = $model->getAttribute($this->getLocalKeyName());
            
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            } else {
                $model->setRelation($relation, []);
            }
        }
        
        return $models;
    }
    
    /**
     * Build model dictionary for matching
     */
    protected function buildDictionary(array $results): array
    {
        $dictionary = [];
        
        foreach ($results as $result) {
            $model = $this->hydratePivotRelation($result);
            $key = $model->getPivot()[$this->parentPivotKey] ?? null;
            
            if ($key !== null) {
                if (!isset($dictionary[$key])) {
                    $dictionary[$key] = [];
                }
                
                $dictionary[$key][] = $model;
            }
        }
        
        return $dictionary;
    }
    
    /**
     * Get relation query for eager loading
     */
    public function getEagerQuery(array $models): QueryBuilder
    {
        $keys = [];
        
        foreach ($models as $model) {
            $key = $model->getAttribute($this->getLocalKeyName());
            if ($key !== null) {
                $keys[] = $key;
            }
        }
        
        $query = (new $this->related)->newQuery();
        $this->performJoin($query);
        
        $columns = $this->shouldSelectPivotColumns() 
            ? $this->getSelectColumns() 
            : [(new $this->related)->getTable() . '.*'];
        
        return $query
            ->select(...$columns)
            ->whereIn($this->table . '.' . $this->parentPivotKey, array_unique($keys));
    }
}
