<?php

namespace Core\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GeneratorCommands extends Command
{
    protected array $generators = [
        'make:controller' => 'makeController',
        'make:model' => 'makeModel',
        'make:migration' => 'makeMigration',
        'make:factory' => 'makeFactory',
        'make:seeder' => 'makeSeeder',
        'make:middleware' => 'makeMiddleware',
        'make:request' => 'makeRequest',
        'make:scaffold' => 'makeScaffold',
    ];

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('make')
            ->setDescription('Generate framework components')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of component to generate (controller, model, migration, factory, seeder, middleware, request, scaffold)')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the component')
            ->addOption('resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller')
            ->addOption('api', null, InputOption::VALUE_NONE, 'Generate an API controller')
            ->addOption('migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model')
            ->addOption('factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model')
            ->addOption('seeder', 's', InputOption::VALUE_NONE, 'Create a new seeder for the model')
            ->addOption('controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Generate migration, factory, seeder, and controller')
            ->addOption('create', null, InputOption::VALUE_OPTIONAL, 'The table to be created')
            ->addOption('table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate')
            ->addOption('model', null, InputOption::VALUE_OPTIONAL, 'The name of the model')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The location where the migration file should be created')
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'Database fields for the model (e.g., "name:string,email:string,age:integer")')
            ->addOption('views', null, InputOption::VALUE_NONE, 'Generate views for the scaffold')
            ->addOption('routes', null, InputOption::VALUE_NONE, 'Generate routes for the scaffold')
            ->addOption('relations', null, InputOption::VALUE_OPTIONAL, 'Model relations (e.g., "hasMany:Product,belongsTo:User")')
            ->addOption('fillable', null, InputOption::VALUE_OPTIONAL, 'Fillable fields (e.g., "name,email,password")')
            ->addOption('hidden', null, InputOption::VALUE_OPTIONAL, 'Hidden fields (e.g., "password,remember_token")')
            ->addOption('casts', null, InputOption::VALUE_OPTIONAL, 'Field casts (e.g., "price:decimal,active:boolean")');
    }

    /**
     * Handle the command
     */
    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $name = $input->getArgument('name');

        if (!array_key_exists("make:{$type}", $this->generators)) {
            $this->error("Unknown generator type: {$type}");
            $this->showAvailableGenerators();
            return 1;
        }

        $method = $this->generators["make:{$type}"];
        return $this->$method($input, $output);
    }

    /**
     * Show available generators
     */
    protected function showAvailableGenerators(): void
    {
        $this->section('Available Generators');
        
        foreach (array_keys($this->generators) as $generator) {
            $type = str_replace('make:', '', $generator);
            $this->text("  <info>{$type}</info>");
        }
        
        $this->newLine();
        $this->comment('Usage: php prism make <type> <name>');
        $this->comment('Example: php prism make controller UserController');
        $this->comment('Example: php prism make scaffold User --fields="name:string,email:string,age:integer" --views --routes');
    }

    /**
     * Make controller
     */
    protected function makeController(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $isResource = $input->getOption('resource');
        $isApi = $input->getOption('api');

        $this->info("Creating controller: {$name}");

        // Ensure the controllers directory exists
        $controllersDir = 'app/Http/Controllers';
        if (!is_dir($controllersDir)) {
            mkdir($controllersDir, 0755, true);
        }

        // Generate the controller content
        $content = $this->generateControllerContent($name, $isResource, $isApi);

        // Write the file
        $filename = $controllersDir . '/' . $name . '.php';
        if (file_put_contents($filename, $content)) {
            $this->success("Controller created: {$filename}");
            
            if ($isResource) {
                $this->comment('Resource controller created with CRUD methods');
            } elseif ($isApi) {
                $this->comment('API controller created with JSON responses');
            }
            
            return 0;
        } else {
            $this->error("Failed to create controller: {$filename}");
            return 1;
        }
    }

    /**
     * Make model
     */
    protected function makeModel(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $options = [
            'migration' => $input->getOption('migration') || $input->getOption('all'),
            'factory' => $input->getOption('factory') || $input->getOption('all'),
            'seeder' => $input->getOption('seeder') || $input->getOption('all'),
            'controller' => $input->getOption('controller') || $input->getOption('all'),
            'resource' => $input->getOption('resource'),
            'api' => $input->getOption('api'),
        ];

        $this->title("Creating Model: {$name}");

        // Create the model
        if ($this->createModel($name, [], [], null, null, null)) {
            $this->success("Model [{$name}] created successfully.");
        } else {
            $this->error("Failed to create model [{$name}].");
            return 1;
        }

        // Create additional files if requested
        if ($options['migration']) {
            $this->createMigrationForModel($name);
        }

        if ($options['factory']) {
            $this->createFactoryForModel($name);
        }

        if ($options['seeder']) {
            $this->createSeederForModel($name);
        }

        if ($options['controller']) {
            $this->createControllerForModel($name, $options['resource'], $options['api']);
        }

        return 0;
    }

    /**
     * Make migration
     */
    protected function makeMigration(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $table = $input->getOption('table');
        $create = $input->getOption('create');
        $path = $input->getOption('path') ?? 'database/migrations';

        $this->title("Creating Migration: {$name}");

        // Generate migration file name
        $timestamp = $this->getTimestamp();
        $className = $this->studlyCase($name);
        $fileName = "{$timestamp}_{$name}.php";
        $fullPath = "{$path}/{$fileName}";

        // Generate migration content
        $content = $this->generateMigrationContent($className, $table, $create);

        // Ensure directory exists
        $this->ensureDirectoryExists($path);

        // Check if file already exists
        if ($this->fileExists($fullPath)) {
            $this->error("Migration [{$fileName}] already exists!");
            return 1;
        }

        // Write migration file
        if ($this->writeFile($fullPath, $content)) {
            $this->success("Migration [{$fileName}] created successfully.");
            return 0;
        } else {
            $this->error("Failed to create migration [{$fileName}].");
            return 1;
        }
    }

    /**
     * Make factory
     */
    protected function makeFactory(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $model = $input->getOption('model');
        $className = $this->studlyCase($name);

        $this->title("Creating Factory: {$className}");

        // If no model specified, try to guess from factory name
        if (!$model && str_ends_with($className, 'Factory')) {
            $model = substr($className, 0, -7); // Remove 'Factory' suffix
        }

        // Generate factory content
        $content = $this->generateFactoryContent($className, $model);

        // Ensure directory exists
        $this->ensureDirectoryExists('database/factories');

        $path = "database/factories/{$className}.php";

        // Check if file already exists
        if ($this->fileExists($path)) {
            if (!$this->confirm("Factory [{$className}] already exists. Overwrite?", false)) {
                return 1;
            }
        }

        // Write factory file
        if ($this->writeFile($path, $content)) {
            $this->success("Factory [{$className}] created successfully.");
            return 0;
        } else {
            $this->error("Failed to create factory [{$className}].");
            return 1;
        }
    }

    /**
     * Make seeder
     */
    protected function makeSeeder(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $className = $this->studlyCase($name);

        $this->title("Creating Seeder: {$className}");

        // Generate seeder content
        $content = $this->generateSeederContent($className);

        // Ensure directory exists
        $this->ensureDirectoryExists('database/seeders');

        $path = "database/seeders/{$className}.php";

        // Check if file already exists
        if ($this->fileExists($path)) {
            if (!$this->confirm("Seeder [{$className}] already exists. Overwrite?", false)) {
                return 1;
            }
        }

        // Write seeder file
        if ($this->writeFile($path, $content)) {
            $this->success("Seeder [{$className}] created successfully.");
            return 0;
        } else {
            $this->error("Failed to create seeder [{$className}].");
            return 1;
        }
    }

    /**
     * Make middleware
     */
    protected function makeMiddleware(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $className = $this->studlyCase($name);

        $this->title("Creating Middleware: {$className}");

        // Generate middleware content
        $content = $this->generateMiddlewareContent($className);

        // Ensure directory exists
        $this->ensureDirectoryExists('core/Middleware');

        $path = "core/Middleware/{$className}.php";

        // Check if file already exists
        if ($this->fileExists($path)) {
            if (!$this->confirm("Middleware [{$className}] already exists. Overwrite?", false)) {
                return 1;
            }
        }

        // Write middleware file
        if ($this->writeFile($path, $content)) {
            $this->success("Middleware [{$className}] created successfully.");
            $this->newLine();
            $this->info('Next steps:');
            $this->listing([
                'Register the middleware in your router or route groups',
                'Implement your middleware logic in the handle() method'
            ]);
            return 0;
        } else {
            $this->error("Failed to create middleware [{$className}].");
            return 1;
        }
    }

    /**
     * Make request
     */
    protected function makeRequest(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $className = $this->studlyCase($name);

        $this->title("Creating Form Request: {$className}");

        // Generate request content
        $content = $this->generateRequestContent($className);

        // Ensure directory exists
        $this->ensureDirectoryExists('app/Http/Requests');

        $path = "app/Http/Requests/{$className}.php";

        // Check if file already exists
        if ($this->fileExists($path)) {
            if (!$this->confirm("Form Request [{$className}] already exists. Overwrite?", false)) {
                return 1;
            }
        }

        // Write request file
        if ($this->writeFile($path, $content)) {
            $this->success("Form Request [{$className}] created successfully.");
            $this->newLine();
            $this->info('Next steps:');
            $this->listing([
                'Define validation rules in the rules() method',
                'Customize authorization logic in the authorize() method',
                'Add custom error messages in the messages() method (optional)'
            ]);
            return 0;
        } else {
            $this->error("Failed to create form request [{$className}].");
            return 1;
        }
    }

    /**
     * Create a complete scaffold for a resource
     */
    protected function makeScaffold(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $fields = $input->getOption('fields');
        $generateViews = $input->getOption('views');
        $generateRoutes = $input->getOption('routes');
        $isApi = $input->getOption('api');
        $relations = $input->getOption('relations');
        $fillable = $input->getOption('fillable');
        $hidden = $input->getOption('hidden');
        $casts = $input->getOption('casts');

        // Parse fillable, hidden, and casts if provided
        $parsedFillable = [];
        if ($fillable) {
            $parsedFillable = array_map('trim', explode(',', $fillable));
        }

        $parsedHidden = [];
        if ($hidden) {
            $parsedHidden = array_map('trim', explode(',', $hidden));
        }

        $parsedCasts = [];
        if ($casts) {
            $castPairs = explode(',', $casts);
            foreach ($castPairs as $pair) {
                $parts = explode(':', trim($pair));
                if (count($parts) >= 2) {
                    $parsedCasts[trim($parts[0])] = trim($parts[1]);
                }
            }
        }

        $this->title("Creating Scaffold for: {$name}");
        
        // Parse fields if provided
        $parsedFields = [];
        if ($fields) {
            $parsedFields = $this->parseFields($fields);
        }

        // Parse relations if provided
        $parsedRelations = [];
        if ($relations) {
            $parsedRelations = $this->parseRelations($relations);
        }

        $success = true;
        $created = [];

        // 1. Create Model
        $this->section("Creating Model: {$name}");
        if ($this->createModel($name, $parsedFields, $parsedRelations, $parsedFillable, $parsedHidden, $parsedCasts)) {
            $created[] = "Model: {$name}";
            $this->success("✓ Model created successfully");
        } else {
            $this->error("✗ Failed to create model");
            $success = false;
        }

        // 2. Create Migration
        $this->section("Creating Migration for: {$name}");
        $tableName = $this->getTableName($name);
        $migrationClassName = 'Create' . ucfirst($tableName) . 'Table';
        if ($this->createMigrationForScaffold($migrationClassName, $tableName, $parsedFields)) {
            $created[] = "Migration: create_{$tableName}_table";
            $this->success("✓ Migration created successfully");
        } else {
            $this->error("✗ Failed to create migration");
            $success = false;
        }

        // 3. Create Factory
        $this->section("Creating Factory for: {$name}");
        if ($this->createFactoryForModel($name)) {
            $created[] = "Factory: {$name}Factory";
            $this->success("✓ Factory created successfully");
        } else {
            $this->error("✗ Failed to create factory");
            $success = false;
        }

        // 4. Create Seeder
        $this->section("Creating Seeder for: {$name}");
        if ($this->createSeederForModel($name)) {
            $created[] = "Seeder: {$name}Seeder";
            $this->success("✓ Seeder created successfully");
        } else {
            $this->error("✗ Failed to create seeder");
            $success = false;
        }

        // 5. Create Controller
        $this->section("Creating Controller for: {$name}");
        if ($this->createControllerForModel($name, true, $isApi)) {
            $created[] = "Controller: {$name}Controller";
            $this->success("✓ Controller created successfully");
        } else {
            $this->error("✗ Failed to create controller");
            $success = false;
        }

        // 6. Create Views (if requested)
        if ($generateViews) {
            $this->section("Creating Views for: {$name}");
            if ($this->createViewsForScaffold($name, $parsedFields)) {
                $created[] = "Views: index, create, edit, show";
                $this->success("✓ Views created successfully");
            } else {
                $this->error("✗ Failed to create views");
                $success = false;
            }
        }

        // 7. Create Routes (if requested)
        if ($generateRoutes) {
            $this->section("Creating Routes for: {$name}");
            if ($this->createRoutesForScaffold($name)) {
                $created[] = "Routes: resource routes";
                $this->success("✓ Routes created successfully");
            } else {
                $this->error("✗ Failed to create routes");
                $success = false;
            }
        }

        // Summary
        $this->newLine();
        $this->section("Scaffold Summary");
        
        if ($success) {
            $this->success("🎉 Scaffold created successfully!");
            $this->info("Created components:");
            foreach ($created as $component) {
                $this->line("  ✓ {$component}");
            }
            
            $this->newLine();
            $this->comment("Next steps:");
            $this->line("  1. Run migration: php prism db migrate");
            $this->line("  2. Seed database: php prism db seed");
            $this->line("  3. Start server: php prism system:serve");
            
            return 0;
        } else {
            $this->error("❌ Scaffold creation failed!");
            $this->comment("Some components were created, but there were errors.");
            return 1;
        }
    }

    /**
     * Parse fields string into array
     */
    protected function parseFields(string $fields): array
    {
        $parsed = [];
        $fieldList = explode(',', $fields);
        
        foreach ($fieldList as $field) {
            $field = trim($field);
            if (empty($field)) continue;
            
            $parts = explode(':', $field);
            if (count($parts) >= 2) {
                $fieldName = trim($parts[0]);
                $fieldType = trim($parts[1]);
                $parsed[$fieldName] = $fieldType;
            }
        }
        
        return $parsed;
    }

    /**
     * Parse relations string into array
     */
    protected function parseRelations(string $relations): array
    {
        $parsed = [];
        $relationList = explode(',', $relations);
        
        foreach ($relationList as $relation) {
            $relation = trim($relation);
            if (empty($relation)) continue;
            
            $parts = explode(':', $relation);
            if (count($parts) >= 2) {
                $relationType = trim($parts[0]);
                $relationName = trim($parts[1]);
                $parsed[$relationType] = $relationName;
            }
        }
        
        return $parsed;
    }

    /**
     * Get table name from model name
     */
    protected function getTableName(string $modelName): string
    {
        // Convert PascalCase to snake_case and pluralize
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
        return $tableName . 's'; // Simple pluralization
    }

    /**
     * Create migration for scaffold
     */
    protected function createMigrationForScaffold(string $className, string $tableName, array $fields): bool
    {
        $migrationName = 'create_' . $tableName . '_table';
        $migrationContent = $this->generateScaffoldMigrationContent($className, $tableName, $fields);
        
        $migrationDir = 'database/migrations';
        $this->ensureDirectoryExists($migrationDir);
        
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . $migrationName . '.php';
        $filePath = $migrationDir . '/' . $fileName;
        
        return $this->writeFile($filePath, $migrationContent);
    }

    /**
     * Generate migration content for scaffold
     */
    protected function generateScaffoldMigrationContent(string $className, string $tableName, array $fields): string
    {
        $fieldsCode = '';
        
        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldType) {
                $phpType = $this->getPhpType($fieldType);
                $fieldsCode .= "            \$table->{$phpType}('{$fieldName}');\n";
            }
        } else {
            // Default fields if none specified
            $fieldsCode = "            \$table->string('name');\n";
            $fieldsCode .= "            \$table->text('description')->nullable();\n";
        }
        
        return "<?php

use Core\Database\Migration;
use Core\Database\Schema\Schema;
use Core\Database\Schema\Blueprint;

class {$className} extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$fieldsCode}            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
}";
    }

    /**
     * Get PHP type from field type
     */
    protected function getPhpType(string $fieldType): string
    {
        $typeMap = [
            'string' => 'string',
            'text' => 'text',
            'integer' => 'integer',
            'int' => 'integer',
            'bigint' => 'bigInteger',
            'boolean' => 'boolean',
            'bool' => 'boolean',
            'decimal' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'date' => 'date',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'json' => 'json',
        ];
        
        return $typeMap[$fieldType] ?? 'string';
    }

    /**
     * Create views for scaffold
     */
    protected function createViewsForScaffold(string $name, array $fields): bool
    {
        $viewsDir = 'resources/views/' . strtolower($name) . 's';
        $this->ensureDirectoryExists($viewsDir);
        
        $success = true;
        
        // Create index view
        $indexContent = $this->generateIndexView($name, $fields);
        if (!$this->writeFile($viewsDir . '/index.blade.php', $indexContent)) {
            $success = false;
        }
        
        // Create create view
        $createContent = $this->generateCreateView($name, $fields);
        if (!$this->writeFile($viewsDir . '/create.blade.php', $createContent)) {
            $success = false;
        }
        
        // Create edit view
        $editContent = $this->generateEditView($name, $fields);
        if (!$this->writeFile($viewsDir . '/edit.blade.php', $editContent)) {
            $success = false;
        }
        
        // Create show view
        $showContent = $this->generateShowView($name, $fields);
        if (!$this->writeFile($viewsDir . '/show.blade.php', $showContent)) {
            $success = false;
        }
        
        return $success;
    }

    /**
     * Generate index view
     */
    protected function generateIndexView(string $name, array $fields): string
    {
        $tableHeaders = '';
        $tableRows = '';
        
        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldType) {
                $tableHeaders .= "                    <th>{$fieldName}</th>\n";
                $tableRows .= "                        <td>{{ \${$name}->{$fieldName} }}</td>\n";
            }
        } else {
            $tableHeaders = "                    <th>Name</th>\n                    <th>Description</th>\n";
            $tableRows = "                        <td>{{ \${$name}->name }}</td>\n                        <td>{{ \${$name}->description }}</td>\n";
        }
        
        return "@extends('layouts.app')

@section('content')
<div class=\"container\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-12\">
            <div class=\"card\">
                <div class=\"card-header d-flex justify-content-between align-items-center\">
                    <h2>{$name}s</h2>
                    <a href=\"{{ route('{$name}s.create') }}\" class=\"btn btn-primary\">Create New {$name}</a>
                </div>
                <div class=\"card-body\">
                    @if(session('success'))
                        <div class=\"alert alert-success\">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <table class=\"table table-striped\">
                        <thead>
                            <tr>
                                <th>ID</th>
{$tableHeaders}                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\${$name}s as \${$name})
                            <tr>
                                <td>{{ \${$name}->id }}</td>
{$tableRows}                                <td>
                                    <a href=\"{{ route('{$name}s.show', \${$name}) }}\" class=\"btn btn-sm btn-info\">View</a>
                                    <a href=\"{{ route('{$name}s.edit', \${$name}) }}\" class=\"btn btn-sm btn-warning\">Edit</a>
                                    <form action=\"{{ route('{$name}s.destroy', \${$name}) }}\" method=\"POST\" class=\"d-inline\">
                                        @csrf
                                        @method('DELETE')
                                        <button type=\"submit\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Are you sure?')\">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection";
    }

    /**
     * Generate create view
     */
    protected function generateCreateView(string $name, array $fields): string
    {
        $formFields = '';
        
        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldType) {
                $inputType = $this->getInputType($fieldType);
                $formFields .= "                <div class=\"form-group\">
                    <label for=\"{$fieldName}\">{$fieldName}</label>
                    <input type=\"{$inputType}\" class=\"form-control @error('{$fieldName}') is-invalid @enderror\" id=\"{$fieldName}\" name=\"{$fieldName}\" value=\"{{ old('{$fieldName}') }}\">
                    @error('{$fieldName}')
                        <span class=\"invalid-feedback\">{{ \$message }}</span>
                    @enderror
                </div>\n";
            }
        } else {
            $formFields = "                <div class=\"form-group\">
                    <label for=\"name\">Name</label>
                    <input type=\"text\" class=\"form-control @error('name') is-invalid @enderror\" id=\"name\" name=\"name\" value=\"{{ old('name') }}\">
                    @error('name')
                        <span class=\"invalid-feedback\">{{ \$message }}</span>
                    @enderror
                </div>
                <div class=\"form-group\">
                    <label for=\"description\">Description</label>
                    <textarea class=\"form-control @error('description') is-invalid @enderror\" id=\"description\" name=\"description\">{{ old('description') }}</textarea>
                    @error('description')
                        <span class=\"invalid-feedback\">{{ \$message }}</span>
                    @enderror
                </div>";
        }
        
        return "@extends('layouts.app')

@section('content')
<div class=\"container\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-8\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h2>Create New {$name}</h2>
                </div>
                <div class=\"card-body\">
                    <form action=\"{{ route('{$name}s.store') }}\" method=\"POST\">
                        @csrf
{$formFields}
                        <div class=\"form-group mt-3\">
                            <button type=\"submit\" class=\"btn btn-primary\">Create {$name}</button>
                            <a href=\"{{ route('{$name}s.index') }}\" class=\"btn btn-secondary\">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection";
    }

    /**
     * Generate edit view
     */
    protected function generateEditView(string $name, array $fields): string
    {
        $formFields = '';
        
        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldType) {
                $inputType = $this->getInputType($fieldType);
                $formFields .= "                <div class=\"form-group\">
                    <label for=\"{$fieldName}\">{$fieldName}</label>
                    <input type=\"{$inputType}\" class=\"form-control @error('{$fieldName}') is-invalid @enderror\" id=\"{$fieldName}\" name=\"{$fieldName}\" value=\"{{ old('{$fieldName}', \${$name}->{$fieldName}) }}\">
                    @error('{$fieldName}')
                        <span class=\"invalid-feedback\">{{ \$message }}</span>
                    @enderror
                </div>\n";
            }
        } else {
            $formFields = "                <div class=\"form-group\">
                    <label for=\"name\">Name</label>
                    <input type=\"text\" class=\"form-control @error('name') is-invalid @enderror\" id=\"name\" name=\"name\" value=\"{{ old('name', \${$name}->name) }}\">
                    @error('name')
                        <span class=\"invalid-feedback\">{{ \$message }}</span>
                    @enderror
                </div>
                <div class=\"form-group\">
                    <label for=\"description\">Description</label>
                    <textarea class=\"form-control @error('description') is-invalid @enderror\" id=\"description\" name=\"description\">{{ old('description', \${$name}->description) }}</textarea>
                    @error('description')
                        <span class=\"invalid-feedback\">{{ \$message }}</span>
                    @enderror
                </div>";
        }
        
        return "@extends('layouts.app')

@section('content')
<div class=\"container\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-8\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h2>Edit {$name}</h2>
                </div>
                <div class=\"card-body\">
                    <form action=\"{{ route('{$name}s.update', \${$name}) }}\" method=\"POST\">
                        @csrf
                        @method('PUT')
{$formFields}
                        <div class=\"form-group mt-3\">
                            <button type=\"submit\" class=\"btn btn-primary\">Update {$name}</button>
                            <a href=\"{{ route('{$name}s.show', \${$name}) }}\" class=\"btn btn-secondary\">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection";
    }

    /**
     * Generate show view
     */
    protected function generateShowView(string $name, array $fields): string
    {
        $showFields = '';
        
        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldType) {
                $showFields .= "                <div class=\"row\">
                    <div class=\"col-md-3\"><strong>{$fieldName}:</strong></div>
                    <div class=\"col-md-9\">{{ \${$name}->{$fieldName} }}</div>
                </div>\n";
            }
        } else {
            $showFields = "                <div class=\"row\">
                    <div class=\"col-md-3\"><strong>Name:</strong></div>
                    <div class=\"col-md-9\">{{ \${$name}->name }}</div>
                </div>
                <div class=\"row\">
                    <div class=\"col-md-3\"><strong>Description:</strong></div>
                    <div class=\"col-md-9\">{{ \${$name}->description }}</div>
                </div>";
        }
        
        return "@extends('layouts.app')

@section('content')
<div class=\"container\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-8\">
            <div class=\"card\">
                <div class=\"card-header d-flex justify-content-between align-items-center\">
                    <h2>{$name} Details</h2>
                    <div>
                        <a href=\"{{ route('{$name}s.edit', \${$name}) }}\" class=\"btn btn-warning\">Edit</a>
                        <a href=\"{{ route('{$name}s.index') }}\" class=\"btn btn-secondary\">Back to List</a>
                    </div>
                </div>
                <div class=\"card-body\">
                    @if(session('success'))
                        <div class=\"alert alert-success\">
                            {{ session('success') }}
                        </div>
                    @endif
                    
{$showFields}
                    <div class=\"row\">
                        <div class=\"col-md-3\"><strong>Created:</strong></div>
                        <div class=\"col-md-9\">{{ \${$name}->created_at }}</div>
                    </div>
                    <div class=\"row\">
                        <div class=\"col-md-3\"><strong>Updated:</strong></div>
                        <div class=\"col-md-9\">{{ \${$name}->updated_at }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection";
    }

    /**
     * Get input type from field type
     */
    protected function getInputType(string $fieldType): string
    {
        $typeMap = [
            'string' => 'text',
            'text' => 'textarea',
            'integer' => 'number',
            'int' => 'number',
            'bigint' => 'number',
            'boolean' => 'checkbox',
            'bool' => 'checkbox',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'timestamp' => 'datetime-local',
            'json' => 'text',
        ];
        
        return $typeMap[$fieldType] ?? 'text';
    }

    /**
     * Create routes for scaffold
     */
    protected function createRoutesForScaffold(string $name): bool
    {
        $routesContent = "\n// {$name} routes\n";
        $routesContent .= "\$router->get('/" . strtolower($name) . "s', [\\App\\Http\\Controllers\\{$name}Controller::class, 'index']);\n";
        $routesContent .= "\$router->get('/" . strtolower($name) . "s/create', [\\App\\Http\\Controllers\\{$name}Controller::class, 'create']);\n";
        $routesContent .= "\$router->post('/" . strtolower($name) . "s', [\\App\\Http\\Controllers\\{$name}Controller::class, 'store']);\n";
        $routesContent .= "\$router->get('/" . strtolower($name) . "s/{id}', [\\App\\Http\\Controllers\\{$name}Controller::class, 'show']);\n";
        $routesContent .= "\$router->get('/" . strtolower($name) . "s/{id}/edit', [\\App\\Http\\Controllers\\{$name}Controller::class, 'edit']);\n";
        $routesContent .= "\$router->put('/" . strtolower($name) . "s/{id}', [\\App\\Http\\Controllers\\{$name}Controller::class, 'update']);\n";
        $routesContent .= "\$router->delete('/" . strtolower($name) . "s/{id}', [\\App\\Http\\Controllers\\{$name}Controller::class, 'destroy']);\n";
        
        $webRoutesFile = 'routes/web.php';
        
        if (!$this->fileExists($webRoutesFile)) {
            return false;
        }
        
        $currentContent = $this->readFile($webRoutesFile);
        
        // Add routes before the closing PHP tag or at the end
        if (strpos($currentContent, '// Scaffold routes') === false) {
            // Add scaffold routes section
            $insertPoint = strpos($currentContent, '// Add your routes here');
            if ($insertPoint !== false) {
                $newContent = substr($currentContent, 0, $insertPoint) . 
                             "// Scaffold routes\n" . $routesContent . "\n" .
                             substr($currentContent, $insertPoint);
            } else {
                // Add at the end before closing PHP tag
                $newContent = rtrim($currentContent, "\n?>") . "\n\n" . $routesContent . "\n";
            }
        } else {
            // Add to existing scaffold routes section
            $insertPoint = strpos($currentContent, '// Scaffold routes');
            $endPoint = strpos($currentContent, '// Add your routes here');
            if ($endPoint === false) {
                $endPoint = strlen($currentContent);
            }
            
            $newContent = substr($currentContent, 0, $endPoint) . $routesContent . "\n" . substr($currentContent, $endPoint);
        }
        
        return $this->writeFile($webRoutesFile, $newContent);
    }

    // ============ Helper Methods ============

    /**
     * Create model file
     */
    protected function createModel(string $name, array $fields, array $relations, ?array $fillable, ?array $hidden, ?array $casts): bool
    {
        $modelName = $this->studlyCase($name);
        $tableName = $this->snakeCase($this->plural($name));
        
        $content = $this->generateModelContent($modelName, $tableName, $fields, $relations, $fillable, $hidden, $casts);
        
        $this->ensureDirectoryExists('app/Models');
        $path = "app/Models/{$modelName}.php";
        
        if ($this->fileExists($path)) {
            if (!$this->confirm("Model [{$modelName}] already exists. Overwrite?", false)) {
                return false;
            }
        }
        
        return $this->writeFile($path, $content);
    }

    /**
     * Generate controller content
     */
    protected function generateControllerContent(string $name, bool $isResource, bool $isApi): string
    {
        $namespace = 'App\\Http\\Controllers';
        $baseClass = $isApi ? 'ApiController' : 'Controller';

        $content = "<?php\n\n";
        $content .= "namespace {$namespace};\n\n";
        $content .= "use Core\\Http\\Request;\n\n";
        $content .= "class {$name} extends {$baseClass}\n";
        $content .= "{\n";

        if ($isResource) {
            $content .= $this->generateResourceMethods($isApi, $name);
        } else {
            $content .= $this->generateBasicMethods($isApi, $name);
        }

        $content .= "}\n";

        return $content;
    }

    /**
     * Generate basic controller methods
     */
    protected function generateBasicMethods(bool $isApi, string $name = ''): string
    {
        $content = "    /**\n";
        $content .= "     * Display a listing of the resource.\n";
        $content .= "     */\n";
        $content .= "    public function index()\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Index method',\n";
            $content .= "            'data' => []\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        return \$this->view('" . ($name ? $name . '.' : '') . "index', [\n";
            $content .= "            'title' => 'Index Page'\n";
            $content .= "        ]);\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Store a newly created resource in storage.\n";
        $content .= "     */\n";
        $content .= "    public function store(Request \$request)\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        // Validate request\n";
            $content .= "        \$validated = \$request->validate([\n";
            $content .= "            'name' => 'required|string|max:255',\n";
            $content .= "        ]);\n\n";
            $content .= "        // Store the resource\n";
            $content .= "        // \$model = Model::create(\$validated);\n\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Resource created successfully',\n";
            $content .= "            'data' => \$validated\n";
            $content .= "        ], 201);\n";
        } else {
            $content .= "        // Validate request\n";
            $content .= "        \$validated = \$request->validate([\n";
            $content .= "            'name' => 'required|string|max:255',\n";
            $content .= "        ]);\n\n";
            $content .= "        // Store the resource\n";
            $content .= "        // \$model = Model::create(\$validated);\n\n";
            $content .= "        // Set flash message\n";
            $content .= "        \\Core\\Session::flash('success', 'Resource created successfully');\n";
            $content .= "        return \$this->redirect('/');\n";
        }
        
        $content .= "    }\n";

        return $content;
    }

    /**
     * Generate resource controller methods
     */
    protected function generateResourceMethods(bool $isApi, string $name = ''): string
    {
        $content = "    /**\n";
        $content .= "     * Display a listing of the resource.\n";
        $content .= "     */\n";
        $content .= "    public function index()\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        // \$items = Model::all();\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Resources retrieved successfully',\n";
            $content .= "            'data' => []\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        // \$items = Model::all();\n";
            $content .= "        return \$this->view('" . ($name ? $name . '.' : '') . "index', [\n";
            $content .= "            'title' => 'Resources List',\n";
            $content .= "            'items' => []\n";
            $content .= "        ]);\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Show the form for creating a new resource.\n";
        $content .= "     */\n";
        $content .= "    public function create()\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Create form'\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        return \$this->view('" . ($name ? $name . '.' : '') . "create', [\n";
            $content .= "            'title' => 'Create New Resource'\n";
            $content .= "        ]);\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Store a newly created resource in storage.\n";
        $content .= "     */\n";
        $content .= "    public function store(Request \$request)\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        \$validated = \$request->validate([\n";
            $content .= "            'name' => 'required|string|max:255',\n";
            $content .= "        ]);\n\n";
            $content .= "        // \$model = Model::create(\$validated);\n\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Resource created successfully',\n";
            $content .= "            'data' => \$validated\n";
            $content .= "        ], 201);\n";
        } else {
            $content .= "        \$validated = \$request->validate([\n";
            $content .= "            'name' => 'required|string|max:255',\n";
            $content .= "        ]);\n\n";
            $content .= "        // \$model = Model::create(\$validated);\n\n";
            $content .= "        // Set flash message\n";
            $content .= "        \\Core\\Session::flash('success', 'Resource created successfully');\n";
            $content .= "        return \$this->redirect('/');\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Display the specified resource.\n";
        $content .= "     */\n";
        $content .= "    public function show(\$id)\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        // \$item = Model::findOrFail(\$id);\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Resource retrieved successfully',\n";
            $content .= "            'data' => ['id' => \$id]\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        // \$item = Model::findOrFail(\$id);\n";
            $content .= "        return \$this->view('" . ($name ? $name . '.' : '') . "show', [\n";
            $content .= "            'title' => 'Resource Details',\n";
            $content .= "            'item' => ['id' => \$id]\n";
            $content .= "        ]);\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Show the form for editing the specified resource.\n";
        $content .= "     */\n";
        $content .= "    public function edit(\$id)\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        // \$item = Model::findOrFail(\$id);\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Edit form',\n";
            $content .= "            'data' => ['id' => \$id]\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        // \$item = Model::findOrFail(\$id);\n";
            $content .= "        return \$this->view('" . ($name ? $name . '.' : '') . "edit', [\n";
            $content .= "            'title' => 'Edit Resource',\n";
            $content .= "            'item' => ['id' => \$id]\n";
            $content .= "        ]);\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Update the specified resource in storage.\n";
        $content .= "     */\n";
        $content .= "    public function update(Request \$request, \$id)\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        \$validated = \$request->validate([\n";
            $content .= "            'name' => 'required|string|max:255',\n";
            $content .= "        ]);\n\n";
            $content .= "        // \$model = Model::findOrFail(\$id);\n";
            $content .= "        // \$model->update(\$validated);\n\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Resource updated successfully',\n";
            $content .= "            'data' => \$validated\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        \$validated = \$request->validate([\n";
            $content .= "            'name' => 'required|string|max:255',\n";
            $content .= "        ]);\n\n";
            $content .= "        // \$model = Model::findOrFail(\$id);\n";
            $content .= "        // \$model->update(\$validated);\n\n";
            $content .= "        // Set flash message\n";
            $content .= "        \\Core\\Session::flash('success', 'Resource updated successfully');\n";
            $content .= "        return \$this->redirect('/');\n";
        }
        
        $content .= "    }\n\n";

        $content .= "    /**\n";
        $content .= "     * Remove the specified resource from storage.\n";
        $content .= "     */\n";
        $content .= "    public function destroy(\$id)\n";
        $content .= "    {\n";
        
        if ($isApi) {
            $content .= "        // \$model = Model::findOrFail(\$id);\n";
            $content .= "        // \$model->delete();\n\n";
            $content .= "        return \$this->jsonResponse([\n";
            $content .= "            'message' => 'Resource deleted successfully'\n";
            $content .= "        ]);\n";
        } else {
            $content .= "        // \$model = Model::findOrFail(\$id);\n";
            $content .= "        // \$model->delete();\n\n";
            $content .= "        // Set flash message\n";
            $content .= "        \\Core\\Session::flash('success', 'Resource deleted successfully');\n";
            $content .= "        return \$this->redirect('/');\n";
        }
        
        $content .= "    }\n";

        return $content;
    }

    /**
     * Generate model content
     */
    protected function generateModelContent(string $modelName, string $tableName, array $fields, array $relations, ?array $fillable, ?array $hidden, ?array $casts): string
    {
        $relationsCode = '';
        if (!empty($relations)) {
            foreach ($relations as $relationType => $relationName) {
                $relationCode = $this->generateRelationCode($relationType, $relationName);
                $relationsCode .= $relationCode;
            }
        }

        $fillableCode = '';
        if (!empty($fillable)) {
            foreach ($fillable as $field) {
                $fillableCode .= "                '{$field}',\n";
            }
        }

        $hiddenCode = '';
        if (!empty($hidden)) {
            foreach ($hidden as $field) {
                $hiddenCode .= "                '{$field}',\n";
            }
        }

        $castsCode = '';
        if (!empty($casts)) {
            foreach ($casts as $field => $type) {
                $castsCode .= "                '{$field}' => '{$type}',\n";
            }
        }

        return "<?php

namespace App\\Models;

use Core\\Database\\Model;

class {$modelName} extends Model
{
    /**
     * The table associated with the model.
     */
    protected string \$table = '{$tableName}';

    /**
     * The attributes that are mass assignable.
     */
    protected array \$fillable = [\n{$fillableCode}    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected array \$hidden = [\n{$hiddenCode}    ];

    /**
     * The attributes that should be cast.
     */
    protected array \$casts = [\n{$castsCode}    ];

    /**
     * Define relationships.
     */
    protected function relations(): array
    {
        return [\n{$relationsCode}    ];
    }
}
";
    }

    /**
     * Generate relation code for model
     */
    protected function generateRelationCode(string $relationType, string $relationName): string
    {
        $relationName = $this->studlyCase($relationName);
        $relationModel = $this->studlyCase($relationName);

        switch ($relationType) {
            case 'hasMany':
                return "                '{$relationName}' => [{$relationModel}::class, 'hasMany'],\n";
            case 'belongsTo':
                return "                '{$relationName}' => [{$relationModel}::class, 'belongsTo'],\n";
            case 'hasOne':
                return "                '{$relationName}' => [{$relationModel}::class, 'hasOne'],\n";
            case 'belongsToMany':
                return "                '{$relationName}' => [{$relationModel}::class, 'belongsToMany'],\n";
            case 'morphMany':
                return "                '{$relationName}' => [{$relationModel}::class, 'morphMany'],\n";
            case 'morphOne':
                return "                '{$relationName}' => [{$relationModel}::class, 'morphOne'],\n";
            case 'morphToMany':
                return "                '{$relationName}' => [{$relationModel}::class, 'morphToMany'],\n";
            case 'morphTo':
                return "                '{$relationName}' => [{$relationModel}::class, 'morphTo'],\n";
            case 'morphedByMany':
                return "                '{$relationName}' => [{$relationModel}::class, 'morphedByMany'],\n";
            case 'hasManyThrough':
                return "                '{$relationName}' => [{$relationModel}::class, 'hasManyThrough'],\n";
            case 'hasOneThrough':
                return "                '{$relationName}' => [{$relationModel}::class, 'hasOneThrough'],\n";
            case 'belongsToThrough':
                return "                '{$relationName}' => [{$relationModel}::class, 'belongsToThrough'],\n";
            default:
                return "                // {$relationType} relationship with {$relationModel}\n";
        }
    }

    /**
     * Generate migration content
     */
    protected function generateMigrationContent(string $className, ?string $table, ?string $create): string
    {
        if ($create) {
            return $this->generateCreateMigration($className, $create);
        } elseif ($table) {
            return $this->generateTableMigration($className, $table);
        } else {
            return $this->generateBlankMigration($className);
        }
    }

    /**
     * Generate create table migration
     */
    protected function generateCreateMigration(string $className, string $tableName): string
    {
        return "<?php

use Core\\Database\\Migration;
use Core\\Database\\Schema\\Blueprint;
use Core\\Database\\Schema\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
";
    }

    /**
     * Generate table modification migration
     */
    protected function generateTableMigration(string $className, string $tableName): string
    {
        return "<?php

use Core\\Database\\Migration;
use Core\\Database\\Schema\\Blueprint;
use Core\\Database\\Schema\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('{$tableName}', function (Blueprint \$table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('{$tableName}', function (Blueprint \$table) {
            //
        });
    }
};
";
    }

    /**
     * Generate blank migration
     */
    protected function generateBlankMigration(string $className): string
    {
        return "<?php

use Core\\Database\\Migration;
use Core\\Database\\Schema\\Blueprint;
use Core\\Database\\Schema\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
";
    }

    /**
     * Generate factory content
     */
    protected function generateFactoryContent(string $className, ?string $model): string
    {
        $modelClass = $model ? "App\\Models\\{$model}" : 'App\\Models\\Model';
        $modelImport = $model ? "use {$modelClass};" : '';
        $modelReference = $model ? $model . '::class' : 'Model::class';

        return "<?php

namespace Database\\Factories;

use Core\\Database\\Factory;
{$modelImport}

class {$className} extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected \$model = {$modelReference};

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
";
    }

    /**
     * Generate seeder content
     */
    protected function generateSeederContent(string $className): string
    {
        return "<?php

namespace Database\\Seeders;

use Core\\Database\\Seeder;

class {$className} extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
";
    }

    /**
     * Generate middleware content
     */
    protected function generateMiddlewareContent(string $className): string
    {
        return "<?php

namespace Core\\Middleware;

use Core\\Http\\Request;
use Closure;

class {$className} implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request \$request, Closure \$next): mixed
    {
        // Perform action before the request is handled
        
        \$response = \$next(\$request);
        
        // Perform action after the request is handled
        
        return \$response;
    }
}
";
    }

    /**
     * Generate request content
     */
    protected function generateRequestContent(string $className): string
    {
        return "<?php

namespace App\\Http\\Requests;

use Core\\Http\\FormRequest;

class {$className} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            //
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            //
        ];
    }
}
";
    }

    /**
     * Create migration for model
     */
    protected function createMigrationForModel(string $name): void
    {
        $tableName = $this->snakeCase($this->plural($name));
        $migrationName = "create_{$tableName}_table";
        
        $this->info("Creating migration for model: {$name}");
        // Implementation would call the migration creation logic
    }

    /**
     * Create factory for model
     */
    protected function createFactoryForModel(string $name): bool
    {
        $factoryName = $name . 'Factory';
        $content = $this->generateFactoryContent($factoryName, $name);
        
        $this->ensureDirectoryExists('database/factories');
        $path = "database/factories/{$factoryName}.php";
        
        if ($this->fileExists($path)) {
            if (!$this->confirm("Factory [{$factoryName}] already exists. Overwrite?", false)) {
                return false;
            }
        }
        
        return $this->writeFile($path, $content);
    }

    /**
     * Create seeder for model
     */
    protected function createSeederForModel(string $name): bool
    {
        $seederName = $name . 'Seeder';
        $content = $this->generateSeederContent($seederName);
        
        $this->ensureDirectoryExists('database/seeders');
        $path = "database/seeders/{$seederName}.php";
        
        if ($this->fileExists($path)) {
            if (!$this->confirm("Seeder [{$seederName}] already exists. Overwrite?", false)) {
                return false;
            }
        }
        
        return $this->writeFile($path, $content);
    }

    /**
     * Create controller for model
     */
    protected function createControllerForModel(string $name, bool $resource, bool $api): bool
    {
        $controllerName = $name . 'Controller';
        $content = $this->generateControllerContent($controllerName, $resource, $api);
        
        $this->ensureDirectoryExists('app/Http/Controllers');
        $path = "app/Http/Controllers/{$controllerName}.php";
        
        if ($this->fileExists($path)) {
            if (!$this->confirm("Controller [{$controllerName}] already exists. Overwrite?", false)) {
                return false;
            }
        }
        
        return $this->writeFile($path, $content);
    }
}
