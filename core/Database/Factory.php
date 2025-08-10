<?php

namespace Core\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator;

abstract class Factory
{
    /**
     * The model the factory is for
     */
    protected string $model;
    
    /**
     * The number of models to generate
     */
    protected int $count = 1;
    
    /**
     * The current Faker instance
     */
    protected Generator $faker;
    
    /**
     * The model states
     */
    protected array $states = [];
    
    /**
     * The after making callbacks
     */
    protected array $afterMaking = [];
    
    /**
     * The after creating callbacks
     */
    protected array $afterCreating = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->faker = FakerFactory::create('tr_TR'); // Turkish locale by default
        $this->model = $this->getModelClass();
    }
    
    /**
     * Get the model class name
     */
    protected function getModelClass(): string
    {
        $factoryClass = get_class($this);
        $modelName = str_replace(['Factory', 'factory'], '', $this->getClassName($factoryClass));
        
        return "App\\Models\\{$modelName}";
    }
    
    /**
     * Define the model's default state
     */
    abstract public function definition(): array;
    
    /**
     * Create a new factory instance for the given model
     */
    public static function new(): static
    {
        return new static();
    }
    
    /**
     * Set the number of models to generate
     */
    public function count(int $count): static
    {
        $this->count = $count;
        return $this;
    }
    
    /**
     * Set the state to be applied to the model
     */
    public function state(string|callable $state, mixed $value = null): static
    {
        if (is_callable($state)) {
            $this->states[] = $state;
        } else {
            $this->states[$state] = $value;
        }
        
        return $this;
    }
    
    /**
     * Set multiple states
     */
    public function states(array $states): static
    {
        foreach ($states as $state => $value) {
            if (is_numeric($state)) {
                $this->state($value);
            } else {
                $this->state($state, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Add an after making callback
     */
    public function afterMaking(callable $callback): static
    {
        $this->afterMaking[] = $callback;
        return $this;
    }
    
    /**
     * Add an after creating callback
     */
    public function afterCreating(callable $callback): static
    {
        $this->afterCreating[] = $callback;
        return $this;
    }
    
    /**
     * Create model instances without persisting them
     */
    public function make(array $attributes = []): Model|array
    {
        if ($this->count === 1) {
            return $this->makeInstance($attributes);
        }
        
        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $models[] = $this->makeInstance($attributes);
        }
        
        return $models;
    }
    
    /**
     * Create and persist model instances
     */
    public function create(array $attributes = []): Model|array
    {
        if ($this->count === 1) {
            return $this->createInstance($attributes);
        }
        
        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $models[] = $this->createInstance($attributes);
        }
        
        return $models;
    }
    
    /**
     * Make a single model instance
     */
    protected function makeInstance(array $attributes = []): Model
    {
        $definition = $this->definition();
        
        // Apply states
        foreach ($this->states as $state => $value) {
            if (is_callable($state)) {
                $definition = array_merge($definition, $state($this->faker) ?: []);
            } elseif (is_string($state) && method_exists($this, $state)) {
                $definition = array_merge($definition, $this->$state($value) ?: []);
            } elseif (is_array($value)) {
                $definition = array_merge($definition, $value);
            } else {
                $definition[$state] = $value;
            }
        }
        
        // Merge provided attributes
        $definition = array_merge($definition, $attributes);
        
        // Create model instance
        $model = new $this->model($definition);
        
        // Apply after making callbacks
        foreach ($this->afterMaking as $callback) {
            $callback($model, $this->faker);
        }
        
        return $model;
    }
    
    /**
     * Create and persist a single model instance
     */
    protected function createInstance(array $attributes = []): Model
    {
        $model = $this->makeInstance($attributes);
        $model->save();
        
        // Apply after creating callbacks
        foreach ($this->afterCreating as $callback) {
            $callback($model, $this->faker);
        }
        
        return $model;
    }
    
    /**
     * Create models for a specific relationship
     */
    public function for(Model $parent, string $relationship = null): static
    {
        $relationship = $relationship ?: $this->guessRelationship($parent);
        
        if ($relationship) {
            $foreignKey = $this->getForeignKeyForRelationship($parent, $relationship);
            $this->state($foreignKey, $parent->getKey());
        }
        
        return $this;
    }
    
    /**
     * Guess the relationship name
     */
    protected function guessRelationship(Model $parent): ?string
    {
        $parentClass = $this->getClassName(get_class($parent));
        $modelClass = $this->getClassName($this->model);
        
        // Try common relationship patterns
        $possibleRelations = [
            strtolower($parentClass) . '_id',
            $this->toSnakeCase($parentClass) . '_id',
            strtolower($parentClass),
            $this->toSnakeCase($parentClass)
        ];
        
        foreach ($possibleRelations as $relation) {
            if (method_exists($parent, $relation)) {
                return $relation;
            }
        }
        
        return null;
    }
    
    /**
     * Get foreign key for relationship
     */
    protected function getForeignKeyForRelationship(Model $parent, string $relationship): string
    {
        $parentClass = $this->getClassName(get_class($parent));
        return strtolower($parentClass) . '_id';
    }
    
    /**
     * Get class name without namespace
     */
    private function getClassName(string $class): string
    {
        $parts = explode('\\', $class);
        return end($parts);
    }
    
    /**
     * Convert string to snake_case
     */
    private function toSnakeCase(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }
    
    /**
     * Create models with specific relationships
     */
    public function has(string $factory, int $count = 1, array $attributes = []): static
    {
        $this->afterCreating(function ($model) use ($factory, $count, $attributes) {
            $factoryClass = "Database\\Factories\\{$factory}Factory";
            
            if (class_exists($factoryClass)) {
                $factoryInstance = new $factoryClass();
                $factoryInstance->count($count)->for($model)->create($attributes);
            }
        });
        
        return $this;
    }
    
    /**
     * Sequence through different states
     */
    public function sequence(...$states): static
    {
        $index = 0;
        
        $this->afterMaking(function ($model) use ($states, &$index) {
            $state = $states[$index % count($states)];
            
            if (is_callable($state)) {
                $attributes = $state($index);
            } else {
                $attributes = $state;
            }
            
            if (is_array($attributes)) {
                foreach ($attributes as $key => $value) {
                    $model->setAttribute($key, $value);
                }
            }
            
            $index++;
        });
        
        return $this;
    }
    
    /**
     * Create models with random selection from given options
     */
    public function randomElements(string $attribute, array $options): static
    {
        return $this->state($attribute, $this->faker->randomElement($options));
    }
    
    /**
     * Create models with weighted random selection
     */
    public function weightedRandomElements(string $attribute, array $options): static
    {
        return $this->state($attribute, $this->faker->randomElement(array_keys($options)));
    }
    
    /**
     * Set custom faker locale
     */
    public function locale(string $locale): static
    {
        $this->faker = FakerFactory::create($locale);
        return $this;
    }
    
    /**
     * Get faker instance
     */
    public function faker(): Generator
    {
        return $this->faker;
    }
    
    /**
     * Create a new factory instance for given model
     */
    public static function factoryForModel(string $model): ?static
    {
        $parts = explode('\\', $model);
        $modelClass = end($parts);
        $factoryClass = "Database\\Factories\\{$modelClass}Factory";
        
        if (class_exists($factoryClass)) {
            return new $factoryClass();
        }
        
        return null;
    }
    
    /**
     * Magic method to handle state methods
     */
    public function __call(string $method, array $arguments): static
    {
        // Check if method exists on this class (state method)
        if (method_exists($this, $method)) {
            $result = $this->$method(...$arguments);
            
            // If it returns an array, treat it as a state
            if (is_array($result)) {
                return $this->state(function() use ($result) {
                    return $result;
                });
            }
        }
        
        // Treat as a state name
        $value = $arguments[0] ?? true;
        return $this->state($method, $value);
    }
    
    /**
     * Clone the factory instance
     */
    public function __clone()
    {
        $this->states = [];
        $this->afterMaking = [];
        $this->afterCreating = [];
        $this->count = 1;
    }
}