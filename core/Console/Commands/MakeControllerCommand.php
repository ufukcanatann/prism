<?php

namespace Core\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeControllerCommand extends Command
{
    protected static $defaultName = 'make:controller';
    protected static $defaultDescription = 'Create a new controller class';

    protected function configure(): void
    {
        $this
            ->setName('make:controller')
            ->setDescription('Create a new controller class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller')
            ->addOption('resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller')
            ->addOption('api', null, InputOption::VALUE_NONE, 'Generate an API controller')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing controller');
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $resource = $input->getOption('resource');
        $api = $input->getOption('api');
        $force = $input->getOption('force');

        // Add 'Controller' suffix if not present
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $className = $this->studlyCase($name);
        $fileName = $className . '.php';
        $filePath = 'app/Http/Controllers/' . $fileName;

        // Check if file exists
        if ($this->fileExists($filePath) && !$force) {
            $this->error("Controller {$className} already exists!");
            return self::FAILURE;
        }

        // Generate controller content
        $content = $this->generateControllerContent($className, $resource, $api);

        // Create directory if it doesn't exist
        $this->ensureDirectoryExists(dirname($filePath));

        // Write file
        if ($this->writeFile($filePath, $content)) {
            $this->success("Controller {$className} created successfully!");
            $this->info("Location: app/Http/Controllers/{$fileName}");
            
            if ($resource) {
                $this->info('Resource controller created with CRUD methods.');
            } elseif ($api) {
                $this->info('API controller created without create/edit methods.');
            }
            
            return self::SUCCESS;
        }

        $this->error("Failed to create controller {$className}");
        return self::FAILURE;
    }

    private function generateControllerContent(string $className, bool $resource = false, bool $api = false): string
    {
        $extends = $api ? 'ApiController' : 'Controller';
        
        $content = "<?php\n\n";
        $content .= "namespace App\\Http\\Controllers;\n\n";
        $content .= "use App\\Http\\Controllers\\{$extends};\n";
        $content .= "use Core\\Http\\Request;\n\n";
        
        $content .= "class {$className} extends {$extends}\n{\n";

        if ($resource || $api) {
            $content .= $this->generateResourceMethods($api);
        } else {
            $content .= "    /**\n";
            $content .= "     * Display a listing of the resource.\n";
            $content .= "     */\n";
            $content .= "    public function index()\n";
            $content .= "    {\n";
            $content .= "        //\n";
            $content .= "    }\n";
        }

        $content .= "}\n";

        return $content;
    }

    private function generateResourceMethods(bool $api = false): string
    {
        $methods = [
            'index' => [
                'comment' => 'Display a listing of the resource.',
                'params' => '',
                'body' => "        //\n"
            ],
            'create' => [
                'comment' => 'Show the form for creating a new resource.',
                'params' => '',
                'body' => "        //\n"
            ],
            'store' => [
                'comment' => 'Store a newly created resource in storage.',
                'params' => 'Request $request',
                'body' => "        //\n"
            ],
            'show' => [
                'comment' => 'Display the specified resource.',
                'params' => '$id',
                'body' => "        //\n"
            ],
            'edit' => [
                'comment' => 'Show the form for editing the specified resource.',
                'params' => '$id',
                'body' => "        //\n"
            ],
            'update' => [
                'comment' => 'Update the specified resource in storage.',
                'params' => 'Request $request, $id',
                'body' => "        //\n"
            ],
            'destroy' => [
                'comment' => 'Remove the specified resource from storage.',
                'params' => '$id',
                'body' => "        //\n"
            ]
        ];

        // For API controllers, skip create and edit methods
        if ($api) {
            unset($methods['create'], $methods['edit']);
        }

        $content = '';
        foreach ($methods as $methodName => $method) {
            $content .= "    /**\n";
            $content .= "     * {$method['comment']}\n";
            $content .= "     */\n";
            $content .= "    public function {$methodName}({$method['params']})\n";
            $content .= "    {\n";
            $content .= $method['body'];
            $content .= "    }\n\n";
        }

        return rtrim($content, "\n");
    }
}
