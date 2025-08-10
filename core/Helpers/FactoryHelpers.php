<?php

if (!function_exists('factory')) {
    /**
     * Create a model factory instance
     */
    function factory(string $model, int $count = 1): ?\Core\Database\Factory
    {
        $modelClass = $model;
        
        // If it's just a class name, assume it's in App\Models
        if (!str_contains($model, '\\')) {
            $modelClass = "App\\Models\\{$model}";
        }
        
        // Get factory class name
        $modelName = class_basename($modelClass);
        $factoryClass = "Database\\Factories\\{$modelName}Factory";
        
        if (class_exists($factoryClass)) {
            return (new $factoryClass())->count($count);
        }
        
        return null;
    }
}

if (!function_exists('fake')) {
    /**
     * Get Faker instance
     */
    function fake(string $locale = 'tr_TR'): \Faker\Generator
    {
        return \Faker\Factory::create($locale);
    }
}

if (!function_exists('seed_with_factory')) {
    /**
     * Seed database with factory
     */
    function seed_with_factory(string $model, int $count = 10, array $attributes = []): array
    {
        $factory = factory($model, $count);
        
        if (!$factory) {
            throw new \Exception("Factory not found for model: {$model}");
        }
        
        return $factory->create($attributes);
    }
}

if (!function_exists('make_fake')) {
    /**
     * Make fake models without persisting
     */
    function make_fake(string $model, int $count = 1, array $attributes = []): \Core\Database\Model|array
    {
        $factory = factory($model, $count);
        
        if (!$factory) {
            throw new \Exception("Factory not found for model: {$model}");
        }
        
        return $factory->make($attributes);
    }
}

if (!function_exists('create_fake')) {
    /**
     * Create and persist fake models
     */
    function create_fake(string $model, int $count = 1, array $attributes = []): \Core\Database\Model|array
    {
        $factory = factory($model, $count);
        
        if (!$factory) {
            throw new \Exception("Factory not found for model: {$model}");
        }
        
        return $factory->create($attributes);
    }
}

if (!function_exists('fake_sequence')) {
    /**
     * Create models with sequence
     */
    function fake_sequence(string $model, array $sequence, int $times = 1): array
    {
        $factory = factory($model, count($sequence) * $times);
        
        if (!$factory) {
            throw new \Exception("Factory not found for model: {$model}");
        }
        
        return $factory->sequence(...$sequence)->create();
    }
}

if (!function_exists('random_factory_state')) {
    /**
     * Apply random factory state
     */
    function random_factory_state(\Core\Database\Factory $factory, array $states): \Core\Database\Factory
    {
        $randomState = fake()->randomElement($states);
        return $factory->$randomState();
    }
}

if (!function_exists('create_realistic_blog_data')) {
    /**
     * Create realistic blog data structure
     */
    function create_realistic_blog_data(int $userCount = 10, int $postCount = 50, int $commentCount = 200): array
    {
        echo "Creating realistic blog data...\n";
        
        // Create users
        echo "Creating {$userCount} users...\n";
        $users = factory('User', $userCount)->create();
        
        // Create categories
        echo "Creating categories...\n";
        $techCategories = factory('Category', 5)->tech()->create();
        $lifestyleCategories = factory('Category', 3)->lifestyle()->create();
        $businessCategories = factory('Category', 4)->business()->create();
        
        // Create posts with relationships
        echo "Creating {$postCount} posts...\n";
        $posts = [];
        for ($i = 0; $i < $postCount; $i++) {
            $user = fake()->randomElement($users);
            
            $post = factory('Post')
                ->forUser($user->id)
                ->state(fake()->randomElement(['published', 'draft']))
                ->create();
            
            // Attach random categories
            $categories = fake()->randomElements(
                array_merge($techCategories, $lifestyleCategories, $businessCategories),
                fake()->numberBetween(1, 3)
            );
            
            foreach ($categories as $category) {
                $post->categories()->attach($category->id);
            }
            
            $posts[] = $post;
        }
        
        // Create comments
        echo "Creating {$commentCount} comments...\n";
        for ($i = 0; $i < $commentCount; $i++) {
            $user = fake()->randomElement($users);
            $post = fake()->randomElement($posts);
            
            factory('Comment')
                ->byUser($user->id)
                ->forPost($post->id)
                ->state(fake()->randomElement(['approved', 'pending', 'positive', 'question']))
                ->create();
        }
        
        echo "Blog data created successfully!\n";
        
        return [
            'users' => $users,
            'posts' => $posts,
            'categories' => array_merge($techCategories, $lifestyleCategories, $businessCategories),
            'comment_count' => $commentCount
        ];
    }
}

if (!function_exists('create_test_scenario')) {
    /**
     * Create specific test scenario
     */
    function create_test_scenario(string $scenario): array
    {
        switch ($scenario) {
            case 'blog':
                return create_realistic_blog_data(5, 20, 50);
                
            case 'ecommerce':
                return [
                    'users' => factory('User', 20)->create(),
                    'categories' => factory('Category', 10)->business()->create(),
                ];
                
            case 'minimal':
                return [
                    'users' => factory('User', 2)->create(),
                    'posts' => factory('Post', 5)->create(),
                    'comments' => factory('Comment', 10)->create(),
                ];
                
            default:
                throw new \Exception("Unknown test scenario: {$scenario}");
        }
    }
}
