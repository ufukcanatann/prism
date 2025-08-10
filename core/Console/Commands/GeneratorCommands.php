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
    ];

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('make')
            ->setDescription('Generate framework components')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of component to generate (controller, model, migration, factory, seeder, middleware, request)')
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
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The location where the migration file should be created');
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
        if ($this->createModel($name)) {
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

    // ============ Helper Methods ============

    /**
     * Create model file
     */
    protected function createModel(string $name): bool
    {
        $modelName = $this->studlyCase($name);
        $tableName = $this->snakeCase($this->plural($name));
        
        $content = $this->generateModelContent($modelName, $tableName);
        
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
            $content .= $this->generateResourceMethods($isApi);
        } else {
            $content .= $this->generateBasicMethods($isApi);
        }

        $content .= "}\n";

        return $content;
    }

    /**
     * Generate basic controller methods
     */
    protected function generateBasicMethods(bool $isApi): string
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
            $content .= "        return \$this->view('index', [\n";
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
            $content .= "        return \$this->redirect('/')->with('success', 'Resource created successfully');\n";
        }
        
        $content .= "    }\n";

        return $content;
    }

    /**
     * Generate resource controller methods
     */
    protected function generateResourceMethods(bool $isApi): string
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
            $content .= "        return \$this->view('index', [\n";
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
            $content .= "        return \$this->view('create', [\n";
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
            $content .= "        return \$this->redirect('/')->with('success', 'Resource created successfully');\n";
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
            $content .= "        return \$this->view('show', [\n";
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
            $content .= "        return \$this->view('edit', [\n";
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
            $content .= "        return \$this->redirect('/')->with('success', 'Resource updated successfully');\n";
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
            $content .= "        return \$this->redirect('/')->with('success', 'Resource deleted successfully');\n";
        }
        
        $content .= "    }\n";

        return $content;
    }

    /**
     * Generate model content
     */
    protected function generateModelContent(string $modelName, string $tableName): string
    {
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
    protected array \$fillable = [
        //
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected array \$hidden = [
        //
    ];

    /**
     * The attributes that should be cast.
     */
    protected array \$casts = [
        //
    ];
}
";
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
    protected function createFactoryForModel(string $name): void
    {
        $this->info("Creating factory for model: {$name}");
        // Implementation would call the factory creation logic
    }

    /**
     * Create seeder for model
     */
    protected function createSeederForModel(string $name): void
    {
        $this->info("Creating seeder for model: {$name}");
        // Implementation would call the seeder creation logic
    }

    /**
     * Create controller for model
     */
    protected function createControllerForModel(string $name, bool $resource, bool $api): void
    {
        $this->info("Creating controller for model: {$name}");
        // Implementation would call the controller creation logic
    }
}
