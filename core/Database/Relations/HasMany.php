<?php

namespace Core\Database\Relations;

use Core\Database\Model;
use Core\Database\QueryBuilder;

class HasMany extends Relation
{
    /**
     * Add relationship constraints
     */
    protected function addConstraints(): void
    {
        if ($this->parent->exists) {
            $this->query->where(
                $this->foreignKey,
                $this->parent->getAttribute($this->localKey)
            );
        }
    }
    
    /**
     * Get relationship results
     */
    public function getResults(): array
    {
        return $this->get();
    }
    
    /**
     * Create a new related model
     */
    public function create(array $attributes = []): Model
    {
        $attributes[$this->foreignKey] = $this->parent->getAttribute($this->localKey);
        
        $related = new $this->related;
        $related->fill($attributes);
        $related->save();
        
        return $related;
    }
    
    /**
     * Create multiple related models
     */
    public function createMany(array $records): array
    {
        $instances = [];
        
        foreach ($records as $record) {
            $instances[] = $this->create($record);
        }
        
        return $instances;
    }
    
    /**
     * Save a model and associate it with the parent
     */
    public function save(Model $model): Model
    {
        $model->setAttribute($this->foreignKey, $this->parent->getAttribute($this->localKey));
        $model->save();
        
        return $model;
    }
    
    /**
     * Save multiple models
     */
    public function saveMany(array $models): array
    {
        foreach ($models as $model) {
            $this->save($model);
        }
        
        return $models;
    }
    
    /**
     * Update all related models
     */
    public function update(array $attributes): int
    {
        if ($this->parent->exists) {
            return $this->query
                ->where($this->foreignKey, $this->parent->getAttribute($this->localKey))
                ->update($attributes);
        }
        
        return 0;
    }
    
    /**
     * Delete all related models
     */
    public function delete(): int
    {
        if ($this->parent->exists) {
            return $this->query
                ->where($this->foreignKey, $this->parent->getAttribute($this->localKey))
                ->delete();
        }
        
        return 0;
    }
    
    /**
     * Find specific related model
     */
    public function find($id): ?Model
    {
        $result = $this->query
            ->where($this->foreignKey, $this->parent->getAttribute($this->localKey))
            ->where((new $this->related)->getKeyName(), $id)
            ->first();
        
        if ($result) {
            return (new $this->related)->newFromArray($result);
        }
        
        return null;
    }
    
    /**
     * Find multiple related models
     */
    public function findMany(array $ids): array
    {
        $results = $this->query
            ->where($this->foreignKey, $this->parent->getAttribute($this->localKey))
            ->whereIn((new $this->related)->getKeyName(), $ids)
            ->get();
        
        return array_map(function($data) {
            return (new $this->related)->newFromArray($data);
        }, $results);
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
            $key = $model->getAttribute($this->localKey);
            
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
            $model = (new $this->related)->newFromArray($result);
            $key = $model->getAttribute($this->foreignKey);
            
            if (!isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }
            
            $dictionary[$key][] = $model;
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
            $key = $model->getAttribute($this->localKey);
            if ($key !== null) {
                $keys[] = $key;
            }
        }
        
        return $this->query->whereIn($this->foreignKey, array_unique($keys));
    }
    
    /**
     * Add a basic where clause to the query
     */
    public function whereHas(callable $callback): self
    {
        $callback($this->query);
        return $this;
    }
    
    /**
     * Add a relationship count query
     */
    public function withCount(): array
    {
        if (!$this->parent->exists) {
            return [];
        }
        
        $count = $this->query
            ->where($this->foreignKey, $this->parent->getAttribute($this->localKey))
            ->count();
        
        return [$this->getForeignKeyName() . '_count' => $count];
    }
}
