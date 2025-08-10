<?php

namespace Core\Database;

use Core\Database;

abstract class Model
{
    /**
     * The table associated with the model
     */
    protected string $table = '';
    
    /**
     * The primary key for the model
     */
    protected string $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [];
    
    /**
     * The attributes that should be hidden for serialization
     */
    protected array $hidden = [];
    
    /**
     * The attributes that should be cast
     */
    protected array $casts = [];
    
    /**
     * Indicates if the model should be timestamped
     */
    protected bool $timestamps = true;
    
    /**
     * The name of the "created at" column
     */
    protected string $createdAt = 'created_at';
    
    /**
     * The name of the "updated at" column
     */
    protected string $updatedAt = 'updated_at';
    
    /**
     * The model's attributes
     */
    protected array $attributes = [];
    
    /**
     * The model's original attributes
     */
    protected array $original = [];
    
    /**
     * Indicates if the model exists
     */
    public bool $exists = false;
    
    /**
     * The loaded relationships for the model
     */
    protected array $relations = [];
    
    /**
     * Pivot data for many-to-many relationships
     */
    protected array $pivot = [];
    
    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable();
        $this->fill($attributes);
    }
    
    /**
     * Set the table name
     */
    protected function setTable(): void
    {
        if (empty($this->table)) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
    }
    
    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Check if attribute is fillable
     */
    public function isFillable(string $key): bool
    {
        return in_array($key, $this->fillable);
    }
    
    /**
     * Set an attribute
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Get an attribute
     */
    public function getAttribute(string $key)
    {
        $value = $this->attributes[$key] ?? null;
        
        // Apply casting
        if (isset($this->casts[$key])) {
            $value = $this->castAttribute($key, $value);
        }
        
        return $value;
    }
    
    /**
     * Cast attribute to specified type
     */
    protected function castAttribute(string $key, $value)
    {
        $castType = $this->casts[$key];
        
        if ($value === null) {
            return null;
        }
        
        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
                
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
                
            case 'string':
                return (string) $value;
                
            case 'bool':
            case 'boolean':
                return (bool) $value;
                
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
                
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
                
            case 'date':
                return is_string($value) ? new \DateTime($value) : $value;
                
            default:
                return $value;
        }
    }
    
    /**
     * Get all attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Set original attributes
     */
    public function syncOriginal(): self
    {
        $this->original = $this->attributes;
        return $this;
    }
    
    /**
     * Get dirty attributes
     */
    public function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * Check if model has changes
     */
    public function isDirty(string $attribute = null): bool
    {
        if ($attribute !== null) {
            return array_key_exists($attribute, $this->getDirty());
        }
        
        return !empty($this->getDirty());
    }
    
    /**
     * Get query builder for model
     */
    public function newQuery(): QueryBuilder
    {
        return Database::queryBuilder()->table($this->table);
    }
    
    /**
     * Save the model
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }
    
    /**
     * Perform insert
     */
    protected function performInsert(): bool
    {
        $attributes = $this->getInsertAttributes();
        
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $attributes[$this->createdAt] = $now;
            $attributes[$this->updatedAt] = $now;
        }
        
        $id = $this->newQuery()->insertGetId($attributes);
        
        if ($id) {
            $this->setAttribute($this->primaryKey, $id);
            $this->exists = true;
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }
    
    /**
     * Perform update
     */
    protected function performUpdate(): bool
    {
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true;
        }
        
        if ($this->timestamps && !array_key_exists($this->updatedAt, $dirty)) {
            $dirty[$this->updatedAt] = date('Y-m-d H:i:s');
        }
        
        $affected = $this->newQuery()
            ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
            ->update($dirty);
        
        if ($affected > 0) {
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get attributes for insert
     */
    protected function getInsertAttributes(): array
    {
        $attributes = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($this->isFillable($key) || $key === $this->primaryKey) {
                $attributes[$key] = $value;
            }
        }
        
        return $attributes;
    }
    
    /**
     * Delete the model
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $affected = $this->newQuery()
            ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
            ->delete();
        
        if ($affected > 0) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new model instance
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }
    
    /**
     * Find model by ID
     */
    public static function find($id): ?static
    {
        $instance = new static();
        $data = $instance->newQuery()->where($instance->primaryKey, $id)->first();
        
        if ($data) {
            return $instance->newFromArray($data);
        }
        
        return null;
    }
    
    /**
     * Find model by ID or fail
     */
    public static function findOrFail($id): static
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }
        
        return $model;
    }
    
    /**
     * Get all models
     */
    public static function all(): array
    {
        $instance = new static();
        $results = $instance->newQuery()->get();
        
        return array_map(function($data) use ($instance) {
            return $instance->newFromArray($data);
        }, $results);
    }
    
    /**
     * Create new model from array
     */
    public function newFromArray(array $data): static
    {
        $model = new static();
        $model->attributes = $data;
        $model->syncOriginal();
        $model->exists = true;
        
        return $model;
    }
    
    /**
     * Get where query
     */
    public static function where($column, $operator = null, $value = null): QueryBuilder
    {
        $instance = new static();
        return $instance->newQuery()->where($column, $operator, $value);
    }
    
    /**
     * Magic getter
     */
    public function __get(string $key)
    {
        // Try to get normal attribute first
        $value = $this->getAttribute($key);
        
        if ($value !== null) {
            return $value;
        }
        
        // Try to get relationship
        return $this->getRelationValue($key);
    }
    
    /**
     * Magic setter
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Magic isset
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Magic unset
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }
    
    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        
        // Apply casting
        foreach ($attributes as $key => $value) {
            if (isset($this->casts[$key])) {
                $attributes[$key] = $this->castAttribute($key, $value);
            }
        }
        
        // Remove hidden attributes
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        return $attributes;
    }
    
    /**
     * Convert model to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    
    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
    
    /**
     * Update or create model
     */
    public static function updateOrCreate(array $attributes, array $values = []): static
    {
        $instance = new static();
        $query = $instance->newQuery();
        
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        
        $model = $query->first();
        
        if ($model) {
            $modelInstance = $instance->newFromArray($model);
            $modelInstance->fill($values);
            $modelInstance->save();
            return $modelInstance;
        } else {
            return static::create(array_merge($attributes, $values));
        }
    }
    
    /**
     * First or create model
     */
    public static function firstOrCreate(array $attributes, array $values = []): static
    {
        $instance = new static();
        $query = $instance->newQuery();
        
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        
        $model = $query->first();
        
        if ($model) {
            return $instance->newFromArray($model);
        } else {
            return static::create(array_merge($attributes, $values));
        }
    }
    
    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get primary key
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * Get primary key value
     */
    public function getKey()
    {
        return $this->getAttribute($this->primaryKey);
    }
    
    /**
     * Define a one-to-one relationship
     */
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): \Core\Database\Relations\HasOne
    {
        return new \Core\Database\Relations\HasOne($this, $related, $foreignKey, $localKey);
    }
    
    /**
     * Define a one-to-many relationship
     */
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): \Core\Database\Relations\HasMany
    {
        return new \Core\Database\Relations\HasMany($this, $related, $foreignKey, $localKey);
    }
    
    /**
     * Define an inverse one-to-one or one-to-many relationship
     */
    public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): \Core\Database\Relations\BelongsTo
    {
        return new \Core\Database\Relations\BelongsTo($this, $related, $foreignKey, $ownerKey);
    }
    
    /**
     * Define a many-to-many relationship
     */
    public function belongsToMany(
        string $related,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ): \Core\Database\Relations\BelongsToMany {
        // Auto-generate pivot table name if not provided
        if (is_null($table)) {
            $models = [
                strtolower((new \ReflectionClass($this))->getShortName()),
                strtolower((new \ReflectionClass($related))->getShortName())
            ];
            sort($models);
            $table = implode('_', $models);
        }
        
        return new \Core\Database\Relations\BelongsToMany(
            $this, $related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey
        );
    }
    
    /**
     * Get a relationship value
     */
    public function getRelationValue(string $key)
    {
        // Check if relation is already loaded
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }
        
        // Check if a relationship method exists
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            
            if ($relation instanceof \Core\Database\Relations\Relation) {
                return $this->relations[$key] = $relation->getResults();
            }
        }
        
        return null;
    }
    
    /**
     * Set a relationship value
     */
    public function setRelation(string $key, $value): self
    {
        $this->relations[$key] = $value;
        return $this;
    }
    
    /**
     * Get all loaded relations
     */
    public function getRelations(): array
    {
        return $this->relations;
    }
    
    /**
     * Check if relation is loaded
     */
    public function relationLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }
    
    /**
     * Load relationships eagerly
     */
    public function load(...$relations): self
    {
        $this->loadRelations($relations);
        return $this;
    }
    
    /**
     * Load relationships eagerly on collection
     */
    public static function with(...$relations): \Core\Database\QueryBuilder
    {
        $instance = new static();
        $query = $instance->newQuery();
        
        // Store relations to load for eager loading
        $query->eagerLoad = $relations;
        
        return $query;
    }
    
    /**
     * Load relations on the model
     */
    protected function loadRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            if (!$this->relationLoaded($relation)) {
                $this->setRelation($relation, $this->getRelationValue($relation));
            }
        }
    }
    
    /**
     * Set pivot data
     */
    public function setPivot(array $pivot): self
    {
        $this->pivot = $pivot;
        return $this;
    }
    
    /**
     * Get pivot data
     */
    public function getPivot(): array
    {
        return $this->pivot;
    }
    
    /**
     * Get specific pivot attribute
     */
    public function getPivotAttribute(string $key)
    {
        return $this->pivot[$key] ?? null;
    }
    
    /**
     * Check if pivot has attribute
     */
    public function hasPivotAttribute(string $key): bool
    {
        return array_key_exists($key, $this->pivot);
    }
    
    /**
     * Create a new factory instance for the model
     */
    public static function factory(): ?\Core\Database\Factory
    {
        $modelClass = static::class;
        $parts = explode('\\', $modelClass);
        $modelName = end($parts);
        $factoryClass = "Database\\Factories\\{$modelName}Factory";
        
        if (class_exists($factoryClass)) {
            return new $factoryClass();
        }
        
        return null;
    }
    
    /**
     * Create fake instances using factory
     */
    public static function fake(int $count = 1, array $attributes = []): static|array
    {
        $factory = static::factory();
        
        if (!$factory) {
            throw new \Exception("Factory not found for model: " . static::class);
        }
        
        return $factory->count($count)->create($attributes);
    }
}