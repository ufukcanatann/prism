<?php

namespace Core\Database\Relations;

use Core\Database\Model;
use Core\Database\QueryBuilder;

class HasOne extends Relation
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
    public function getResults()
    {
        return $this->first();
    }
    
    /**
     * Associate a model with the parent
     */
    public function associate(Model $model): Model
    {
        $model->setAttribute($this->foreignKey, $this->parent->getAttribute($this->localKey));
        return $model;
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
     * Save a model and associate it with the parent
     */
    public function save(Model $model): Model
    {
        $this->associate($model);
        $model->save();
        
        return $model;
    }
    
    /**
     * Update related model
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
     * Delete related model
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
     * Initialize relation on a set of models
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
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
            $dictionary[$key] = $model;
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
}
