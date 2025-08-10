<?php

namespace Core\Database\Relations;

use Core\Database\Model;
use Core\Database\QueryBuilder;

class BelongsTo extends Relation
{
    /**
     * Child model instance
     */
    protected Model $child;
    
    /**
     * Constructor
     */
    public function __construct(Model $child, string $related, ?string $foreignKey = null, ?string $ownerKey = null)
    {
        $this->child = $child;
        
        parent::__construct($child, $related, $foreignKey, $ownerKey);
    }
    
    /**
     * Get default foreign key
     */
    protected function getForeignKey(): string
    {
        $relatedClass = (new \ReflectionClass($this->related))->getShortName();
        return strtolower($relatedClass) . '_id';
    }
    
    /**
     * Get default local key
     */
    protected function getLocalKey(): string
    {
        return (new $this->related)->getKeyName();
    }
    
    /**
     * Add relationship constraints
     */
    protected function addConstraints(): void
    {
        if ($this->child->exists) {
            $foreignKeyValue = $this->child->getAttribute($this->foreignKey);
            
            if ($foreignKeyValue !== null) {
                $this->query->where($this->localKey, $foreignKeyValue);
            }
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
     * Associate the model with the given parent
     */
    public function associate(Model $model): Model
    {
        $this->child->setAttribute($this->foreignKey, $model->getAttribute($this->localKey));
        
        return $this->child;
    }
    
    /**
     * Dissociate the model from its parent
     */
    public function dissociate(): Model
    {
        $this->child->setAttribute($this->foreignKey, null);
        
        return $this->child;
    }
    
    /**
     * Update the parent model
     */
    public function update(array $attributes): int
    {
        $foreignKeyValue = $this->child->getAttribute($this->foreignKey);
        
        if ($foreignKeyValue !== null) {
            return $this->query
                ->where($this->localKey, $foreignKeyValue)
                ->update($attributes);
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
            $key = $model->getAttribute($this->foreignKey);
            
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
            $key = $model->getAttribute($this->localKey);
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
            $key = $model->getAttribute($this->foreignKey);
            if ($key !== null) {
                $keys[] = $key;
            }
        }
        
        return $this->query->whereIn($this->localKey, array_unique($keys));
    }
    
    /**
     * Get the child model
     */
    public function getChild(): Model
    {
        return $this->child;
    }
    
    /**
     * Get or create related model
     */
    public function firstOrCreate(array $attributes = [], array $values = []): Model
    {
        $foreignKeyValue = $this->child->getAttribute($this->foreignKey);
        
        if ($foreignKeyValue !== null) {
            $existing = $this->first();
            if ($existing) {
                return $existing;
            }
        }
        
        // Create new related model
        $attributes[$this->localKey] = $attributes[$this->localKey] ?? null;
        $related = call_user_func([$this->related, 'create'], array_merge($attributes, $values));
        
        // Associate with child
        $this->associate($related);
        
        return $related;
    }
    
    /**
     * Create or update related model
     */
    public function updateOrCreate(array $attributes = [], array $values = []): Model
    {
        $foreignKeyValue = $this->child->getAttribute($this->foreignKey);
        
        if ($foreignKeyValue !== null) {
            $existing = $this->first();
            if ($existing) {
                $existing->fill($values);
                $existing->save();
                return $existing;
            }
        }
        
        return $this->firstOrCreate($attributes, $values);
    }
}
