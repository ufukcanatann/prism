<?php

namespace Core\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Core\Security\XssProtection;

class SystemCommands extends Command
{
    protected array $systemCommands = [
        'serve' => 'serveApplication',
        'install' => 'installFramework',
        'key:generate' => 'generateKey',
        'clear:cache' => 'clearCache',
        'optimize' => 'optimizeApplication',
        'up' => 'bringUp',
        'down' => 'bringDown',
        'route:list' => 'listRoutes',
        'inspect' => 'inspectApplication',
        'env' => 'manageEnvironment',
        'security:scan' => 'scanSecurity',
        'security:setup' => 'setupSecurity',
        'list' => 'listCommands',
    ];

    /**
     * Get system commands
     */
    public function getSystemCommands(): array
    {
        return $this->systemCommands;
    }

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('system')
            ->setDescription('System management commands')
            ->addArgument('action', InputArgument::REQUIRED, 'System action to perform')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name or identifier for the action')
            ->addArgument('value', InputArgument::OPTIONAL, 'Value for the action')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', '8000')
            ->addOption('public', null, InputOption::VALUE_OPTIONAL, 'The public directory to serve', 'public')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation')
            ->addOption('seed', 's', InputOption::VALUE_NONE, 'Seed the database with sample data')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format (table, json, yaml)', 'table')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter results')
            ->addOption('export', null, InputOption::VALUE_OPTIONAL, 'Export to file')
            ->addOption('method', null, InputOption::VALUE_OPTIONAL, 'Filter routes by HTTP method')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Filter routes by path pattern')
            ->addOption('retry', null, InputOption::VALUE_OPTIONAL, 'The number of seconds after which the request may be retried', 60)
            ->addOption('message', null, InputOption::VALUE_OPTIONAL, 'The message for the maintenance mode')
            ->addOption('allow', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'IP or networks allowed to access the application while in maintenance mode', [])
            ->addOption('secret', null, InputOption::VALUE_OPTIONAL, 'The secret phrase that may be used to bypass maintenance mode')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'The status code that should be returned for maintenance mode requests', 503)
            ->addOption('template', null, InputOption::VALUE_OPTIONAL, 'The template that should be rendered for maintenance mode requests')
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, 'Environment file path', '.env')
            ->addOption('backup', 'b', InputOption::VALUE_OPTIONAL, 'Backup file path')
            ->addOption('fix', null, InputOption::VALUE_NONE, 'Automatically fix detected issues')
            ->addOption('report', null, InputOption::VALUE_OPTIONAL, 'Generate security report file')
            ->addOption('level', null, InputOption::VALUE_OPTIONAL, 'Security scan level (basic, full)', 'basic')
            ->addOption('csrf', null, InputOption::VALUE_NONE, 'Setup CSRF protection')
            ->addOption('headers', null, InputOption::VALUE_NONE, 'Setup security headers')
            ->addOption('rate-limit', null, InputOption::VALUE_NONE, 'Setup rate limiting')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Setup all security features');
    }

    /**
     * Handle the command
     */
    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');

        if (!array_key_exists($action, $this->systemCommands)) {
            $this->error("Unknown system action: {$action}");
            $this->showAvailableActions();
            return 1;
        }

        $method = $this->systemCommands[$action];
        return $this->$method($input, $output);
    }

    /**
     * Show available system actions
     */
    protected function showAvailableActions(): void
    {
        $this->section('Available System Actions');
        
        $categories = [
            'Server Management' => ['serve', 'install', 'up', 'down'],
            'Utilities' => ['clear:cache', 'optimize', 'key:generate', 'env'],
            'Information' => ['route:list', 'inspect', 'list'],
            'Security' => ['security:scan', 'security:setup'],
        ];

        foreach ($categories as $category => $commands) {
            $this->text("<info>{$category}:</info>");
            foreach ($commands as $command) {
                $this->text("  {$command}");
            }
            $this->newLine();
        }
        
        $this->comment('Usage: php prism system <action> [options]');
        $this->comment('Example: php prism system serve --port=8080');
    }

    // ============ Server Management ============

    /**
     * Serve the application
     */
    protected function serveApplication(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $public = $input->getOption('public');

        $this->info('PRISM Framework Development Server');
        $this->line('==================================');
        $this->line("Starting server on http://{$host}:{$port}");
        $this->line("Document root: {$public}");
        $this->line('');
        $this->comment('Press Ctrl+C to stop the server');
        $this->line('');

        // Check if public directory exists
        if (!is_dir($public)) {
            $this->error("Public directory '{$public}' does not exist!");
            return 1;
        }

        // Build the command
        $command = sprintf(
            'php -S %s:%s -t %s %s/router.php',
            $host,
            $port,
            escapeshellarg($public),
            escapeshellarg($public)
        );

        // Execute the command
        $this->line("Executing: {$command}");
        $this->line('');

        passthru($command, $returnCode);

        return $returnCode;
    }

    /**
     * Install framework
     */
    protected function installFramework(InputInterface $input, OutputInterface $output): int
    {
        $this->info('PRISM Framework Installation');
        $this->line('============================');

        // Check if already installed
        if (!$input->getOption('force') && $this->isInstalled()) {
            $this->warn('PRISM Framework is already installed!');
            $this->comment('Use --force to reinstall.');
            return 0;
        }

        try {
            // Create directories
            $this->createDirectories();
            
            // Create .env file
            $this->createEnvFile();
            
            // Test database connection and create database
            $this->testDatabaseConnection();
            
            // Create database tables
            $this->createDatabaseTables();
            
            // Seed database if requested
            if ($input->getOption('seed')) {
                $this->seedDatabase();
            }
            
            // Generate application key
            $this->generateApplicationKey();
            
            $this->info('PRISM Framework installed successfully!');
            $this->line('');
            $this->comment('Next steps:');
            $this->line('1. Database created and connected successfully');
            $this->line('2. Run: php prism system serve');
            $this->line('3. Visit: http://127.0.0.1:8000');
            $this->line('4. Create models with: php prism make scaffold ModelName --fields "field1:type,field2:type" --views --routes');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Installation failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Bring application up
     */
    protected function bringUp(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Bringing Application Online');

        $maintenanceFile = 'storage/framework/down';

        if (!$this->fileExists($maintenanceFile)) {
            $this->info('Application is already up.');
            return 0;
        }

        try {
            $fullPath = $this->app->basePath($maintenanceFile);
            
            if (unlink($fullPath)) {
                $this->success('Application is now live.');
                return 0;
            } else {
                $this->error('Failed to bring application online.');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Failed to disable maintenance mode: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Bring application down (maintenance mode)
     */
    protected function bringDown(InputInterface $input, OutputInterface $output): int
    {
        $retry = (int) $input->getOption('retry');
        $message = $input->getOption('message') ?? 'Application is in maintenance mode.';
        $allow = $input->getOption('allow');
        $secret = $input->getOption('secret');
        $status = (int) $input->getOption('status');
        $template = $input->getOption('template');

        $this->title('Application Maintenance Mode');

        try {
            // Create maintenance mode file
            $data = [
                'time' => time(),
                'message' => $message,
                'retry' => $retry,
                'allowed' => $allow,
                'secret' => $secret,
                'status' => $status,
                'template' => $template
            ];

            $this->ensureDirectoryExists('storage/framework');
            $maintenanceFile = 'storage/framework/down';

            if ($this->writeFile($maintenanceFile, json_encode($data, JSON_PRETTY_PRINT))) {
                $this->success('Application is now in maintenance mode.');
                
                $this->newLine();
                $this->info('Maintenance mode settings:');
                $this->listing([
                    "Message: {$message}",
                    "Retry after: {$retry} seconds",
                    "Status code: {$status}",
                    $secret ? "Secret: {$secret}" : 'No secret set',
                    !empty($allow) ? 'Allowed IPs: ' . implode(', ', $allow) : 'No IPs allowed',
                    $template ? "Template: {$template}" : 'Using default template'
                ]);

                if ($secret) {
                    $this->newLine();
                    $this->note("You can bypass maintenance mode by visiting: " . $this->app->getUrl() . "?secret={$secret}");
                }

                return 0;
            } else {
                $this->error('Failed to put application into maintenance mode.');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Failed to enable maintenance mode: ' . $e->getMessage());
            return 1;
        }
    }

    // ============ Utilities ============

    /**
     * Clear application cache
     */
    protected function clearCache(InputInterface $input, OutputInterface $output): int
    {
        $this->info('Clearing application cache...');

        $cacheDirs = [
            'storage/cache',
            'storage/cache/views',
            'storage/framework/cache',
        ];

        $cleared = 0;

        foreach ($cacheDirs as $dir) {
            $fullPath = $this->getProjectPath($dir);
            if (is_dir($fullPath)) {
                if ($this->clearDirectory($fullPath)) {
                    $this->line("✓ Cleared: {$dir}");
                    $cleared++;
                } else {
                    $this->warn("⚠ Could not clear: {$dir}");
                }
            } else {
                $this->line("✓ Skipped: {$dir} (not found)");
            }
        }

        if ($cleared > 0) {
            $this->info("Cache cleared successfully! ({$cleared} directories)");
        } else {
            $this->comment('No cache directories found to clear.');
        }

        return 0;
    }

    /**
     * Generate application key
     */
    protected function generateKey(InputInterface $input, OutputInterface $output): int
    {
        $this->info('Generating application key...');

        try {
            // AppKeyGenerator sınıfını kullan
            $result = \Core\Helpers\AppKeyGenerator::generateAndSave();
            
            if ($result['success']) {
                $this->line('✓ Application key generated and saved to .env file');
                $this->comment("Key: " . $result['key']);
                return 0;
            } else {
                $this->error('Failed to generate application key: ' . $result['message']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error generating application key: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Optimize application
     */
    protected function optimizeApplication(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Optimizing Application');

        $optimizations = [
            'Config Cache' => [$this, 'optimizeConfig'],
            'Route Cache' => [$this, 'optimizeRoutes'],
            'View Cache' => [$this, 'optimizeViews'],
            'Autoloader' => [$this, 'optimizeAutoloader']
        ];

        $successful = 0;
        $total = count($optimizations);

        foreach ($optimizations as $name => $callback) {
            $this->section("Optimizing {$name}");
            
            try {
                if (call_user_func($callback)) {
                    $this->success("{$name} optimization completed.");
                    $successful++;
                } else {
                    $this->warn("{$name} optimization failed.");
                }
            } catch (\Exception $e) {
                $this->error("{$name} optimization failed: " . $e->getMessage());
            }
        }

        $this->newLine();
        if ($successful === $total) {
            $this->success("All optimizations completed successfully! ({$successful}/{$total})");
        } else {
            $this->warn("Optimization completed with some issues. ({$successful}/{$total} successful)");
        }

        $this->newLine();
        $this->info('Performance tips:');
        $this->listing([
            'Use OPcache in production for better PHP performance',
            'Enable gzip compression on your web server',
            'Use a CDN for static assets',
            'Consider using Redis or Memcached for session storage',
            'Optimize your database queries and use indexes'
        ]);

        return $successful === $total ? 0 : 1;
    }

    // ============ Information Commands ============

    /**
     * List all routes
     */
    protected function listRoutes(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Route List');

        $methodFilter = $input->getOption('method');
        $nameFilter = $input->getArgument('name');
        $pathFilter = $input->getOption('path');

        try {
            // Get router instance
            $router = $this->app->getRouter();
            $routes = $this->getRoutes($router);

            if (empty($routes)) {
                $this->info('No routes found.');
                return 0;
            }

            // Apply filters
            if ($methodFilter) {
                $routes = array_filter($routes, function($route) use ($methodFilter) {
                    return in_array(strtoupper($methodFilter), $route['methods']);
                });
            }

            if ($nameFilter) {
                $routes = array_filter($routes, function($route) use ($nameFilter) {
                    return stripos($route['name'] ?? '', $nameFilter) !== false;
                });
            }

            if ($pathFilter) {
                $routes = array_filter($routes, function($route) use ($pathFilter) {
                    return stripos($route['uri'], $pathFilter) !== false;
                });
            }

            if (empty($routes)) {
                $this->info('No routes match the given filters.');
                return 0;
            }

            // Prepare table data
            $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware'];
            $rows = [];

            foreach ($routes as $route) {
                $rows[] = [
                    implode('|', $route['methods']),
                    $route['uri'],
                    $route['name'] ?? '',
                    $route['action'],
                    implode(', ', $route['middleware'])
                ];
            }

            // Display table
            $this->table($headers, $rows);
            $this->newLine();
            $this->info('Total routes: ' . count($routes));

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to list routes: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Inspect application
     */
    protected function inspectApplication(InputInterface $input, OutputInterface $output): int
    {
        $component = $input->getArgument('name');
        $format = $input->getOption('format');
        $filter = $input->getOption('filter');
        $export = $input->getOption('export');

        $this->title('Application Inspector');

        if (!$component) {
            return $this->showInspectorMenu();
        }

        switch ($component) {
            case 'routes':
                return $this->inspectRoutes($format, $filter, $export);
            case 'config':
                return $this->inspectConfig($format, $filter, $export);
            case 'database':
                return $this->inspectDatabase($format, $filter, $export);
            case 'cache':
                return $this->inspectCache($format, $filter, $export);
            case 'container':
                return $this->inspectContainer($format, $filter, $export);
            case 'env':
                return $this->inspectEnvironment($format, $filter, $export);
            default:
                $this->error("Unknown component: {$component}");
                return $this->showInspectorMenu();
        }
    }

    /**
     * List all commands
     */
    protected function listCommands(InputInterface $input, OutputInterface $output): int
    {
        $this->info('PRISM Framework Available Commands');
        $this->line('==================================');
        $this->line('');

        $commands = [
            // Installation & Setup
            'system install' => 'Install PRISM Framework',
            'system key:generate' => 'Generate application key',
            
            // Generators
            'make controller <name>' => 'Create a new controller class',
            'make model <name>' => 'Create a new model class',
            'make migration <name>' => 'Create a new migration file',
            'make seeder <name>' => 'Create a new seeder class',
            'make factory <name>' => 'Create a new factory class',
            'make middleware <name>' => 'Create a new middleware class',
            'make request <name>' => 'Create a new form request class',
            
            // Database
            'db migrate' => 'Run database migrations',
            'db seed' => 'Seed the database with data',
            
            // Server & Maintenance
            'system serve' => 'Serve the application on development server',
            'system down' => 'Put the application into maintenance mode',
            'system up' => 'Bring the application out of maintenance mode',
            
            // Utilities
            'system clear:cache' => 'Clear application cache',
            'system optimize' => 'Cache framework bootstrap files for better performance',
            'system route:list' => 'List all registered routes',
            'system inspect <component>' => 'Inspect and debug application components',
            'system env <action>' => 'Manage environment variables',
            
            // Security
            'system security:scan' => 'Scan application for security vulnerabilities',
            'system security:setup' => 'Setup security features for the application',
            
            'system list' => 'List all available commands',
        ];

        foreach ($commands as $command => $description) {
            $this->line("  <info>{$command}</info>");
            $this->line("    {$description}");
            $this->line('');
        }

        $this->comment('For more information about a command, use:');
        $this->line('  php prism help <command>');

        return 0;
    }

    // ============ Environment Management ============

    /**
     * Manage environment variables
     */
    protected function manageEnvironment(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('name');
        $key = $input->getArgument('value');
        $value = $input->getOption('value');
        $file = $input->getOption('file');
        $backup = $input->getOption('backup');
        $force = $input->getOption('force');

        $this->title('Environment Manager');

        if (!$action) {
            return $this->showEnvMenu();
        }

        switch ($action) {
            case 'get':
                return $this->getEnvValue($key, $file);
            case 'set':
                return $this->setEnvValue($key, $value, $file, $force);
            case 'unset':
                return $this->unsetEnvValue($key, $file);
            case 'list':
                return $this->listEnvValues($file);
            case 'backup':
                return $this->backupEnv($file, $backup);
            case 'restore':
                return $this->restoreEnv($backup, $file);
            case 'generate':
                return $this->generateEnv($file);
            case 'validate':
                return $this->validateEnv($file);
            default:
                $this->error("Unknown action: {$action}");
                return $this->showEnvMenu();
        }
    }

    // ============ Security Commands ============

    /**
     * Scan for security vulnerabilities
     */
    protected function scanSecurity(InputInterface $input, OutputInterface $output): int
    {
        $fix = $input->getOption('fix');
        $report = $input->getOption('report');
        $level = $input->getOption('level');

        $this->title('Security Scanner');

        $vulnerabilities = [];
        $fixedIssues = 0;

        // 1. Check file permissions
        $this->section('Checking File Permissions');
        $permissionIssues = $this->checkFilePermissions();
        if (!empty($permissionIssues)) {
            $vulnerabilities['file_permissions'] = $permissionIssues;
            if ($fix) {
                $fixedIssues += $this->fixFilePermissions($permissionIssues);
            }
        } else {
            $this->success('File permissions are secure.');
        }

        // 2. Check configuration security
        $this->section('Checking Configuration Security');
        $configIssues = $this->checkConfigurationSecurity();
        if (!empty($configIssues)) {
            $vulnerabilities['configuration'] = $configIssues;
            if ($fix) {
                $fixedIssues += $this->fixConfigurationIssues($configIssues);
            }
        } else {
            $this->success('Configuration security is good.');
        }

        // 3. Check for exposed sensitive files
        $this->section('Checking for Exposed Files');
        $exposedFiles = $this->checkExposedFiles();
        if (!empty($exposedFiles)) {
            $vulnerabilities['exposed_files'] = $exposedFiles;
            if ($fix) {
                $fixedIssues += $this->fixExposedFiles($exposedFiles);
            }
        } else {
            $this->success('No exposed sensitive files found.');
        }

        // Summary
        $this->newLine();
        $totalIssues = array_sum(array_map('count', $vulnerabilities));
        
        if ($totalIssues === 0) {
            $this->success('Security scan completed successfully! No vulnerabilities found.');
        } else {
            $this->warn("Security scan found {$totalIssues} potential issue(s).");
            
            if ($fix && $fixedIssues > 0) {
                $this->success("Automatically fixed {$fixedIssues} issue(s).");
            }
        }

        // Generate report if requested
        if ($report) {
            $this->generateSecurityReport($vulnerabilities, $report);
        }

        return $totalIssues > 0 ? 1 : 0;
    }

    /**
     * Setup security features
     */
    protected function setupSecurity(InputInterface $input, OutputInterface $output): int
    {
        $csrf = $input->getOption('csrf');
        $headers = $input->getOption('headers');
        $rateLimit = $input->getOption('rate-limit');
        $all = $input->getOption('all');

        $this->title('Security Setup Wizard');

        if (!$csrf && !$headers && !$rateLimit && !$all) {
            return $this->interactiveSecuritySetup();
        }

        $success = true;

        if ($all || $csrf) {
            $success &= $this->setupCsrfProtection();
        }

        if ($all || $headers) {
            $success &= $this->setupSecurityHeaders();
        }

        if ($all || $rateLimit) {
            $success &= $this->setupRateLimiting();
        }

        if ($success) {
            $this->success('Security setup completed successfully!');
            $this->displaySecurityInfo();
            return 0;
        } else {
            $this->error('Some security features could not be set up properly.');
            return 1;
        }
    }

    // ============ Helper Methods ============

    /**
     * Check if framework is installed
     */
    protected function isInstalled(): bool
    {
        return file_exists('.env') && file_exists('storage/framework');
    }

    /**
     * Create necessary directories
     */
    protected function createDirectories(): void
    {
        $this->info('Creating directories...');
        
        $directories = [
            'storage',
            'storage/cache',
            'storage/cache/views',
            'storage/logs',
            'storage/framework',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'public/uploads',
            'tests',
            'tests/Unit',
            'tests/Feature',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (mkdir($dir, 0755, true)) {
                    $this->line("✓ Created: {$dir}");
                } else {
                    throw new \Exception("Failed to create directory: {$dir}");
                }
            } else {
                $this->line("✓ Exists: {$dir}");
            }
        }
    }

    /**
     * Create .env file
     */
    protected function createEnvFile(): void
    {
        $this->info('Creating .env file...');
        
        if (!file_exists('.env') && file_exists('env.example')) {
            if (copy('env.example', '.env')) {
                $this->line('✓ .env file created');
            } else {
                throw new \Exception('Failed to create .env file');
            }
        } else {
            $this->line('✓ .env file exists');
        }
    }

    /**
     * Test database connection
     */
    protected function testDatabaseConnection(): void
    {
        $this->info('Testing database connection...');
        
        // Load config
        \Core\Config::load();
        
        $host = \Core\Config::get('database.host');
        $port = \Core\Config::get('database.port');
        $database = \Core\Config::get('database.database');
        $username = \Core\Config::get('database.username');
        $password = \Core\Config::get('database.password');
        
        $this->line("Host: {$host}:{$port}");
        $this->line("Database: {$database}");
        $this->line("Username: {$username}");
        
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        
        try {
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            
            $this->line('✓ Database connection successful');
            
            // Create database if not exists
            $this->line("Creating database '{$database}' if not exists...");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->line("✓ Database '{$database}' ready");
            
            // Test connection to the specific database
            $pdo = new \PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            
            $this->line("✓ Successfully connected to database '{$database}'");
            
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Create database tables
     */
    protected function createDatabaseTables(): void
    {
        $this->info('Creating database tables...');
        
        // Run the install script
        include __DIR__ . '/../../install.php';
    }

    /**
     * Seed the database
     */
    protected function seedDatabase(): void
    {
        $this->info('Seeding database...');
        
        // This will be implemented when we have the seeder system ready
        $this->line('✓ Database seeded');
    }

    /**
     * Generate application key
     */
    protected function generateApplicationKey(): void
    {
        $this->info('Generating application key...');
        
        $key = base64_encode(random_bytes(32));
        
        if (file_exists('.env')) {
            $env = file_get_contents('.env');
            $env = preg_replace('/APP_KEY=.*/', "APP_KEY={$key}", $env);
            file_put_contents('.env', $env);
            $this->line('✓ Application key generated');
        }
    }

    /**
     * Clear directory recursively
     */
    protected function clearDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }
        
        $files = glob($dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (!unlink($file)) {
                    return false;
                }
            } elseif (is_dir($file)) {
                if (!$this->clearDirectory($file)) {
                    return false;
                }
                if (!rmdir($file)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    // ============ Optimization Methods ============

    /**
     * Optimize config files
     */
    protected function optimizeConfig(): bool
    {
        try {
            $this->ensureDirectoryExists('storage/framework/cache');
            
            // Cache configuration
            $configFiles = glob($this->app->configPath('*.php'));
            $cachedConfig = [];
            
            foreach ($configFiles as $file) {
                $key = basename($file, '.php');
                $cachedConfig[$key] = require $file;
            }
            
            $cacheFile = 'storage/framework/cache/config.php';
            $content = "<?php\n\nreturn " . var_export($cachedConfig, true) . ";\n";
            
            return $this->writeFile($cacheFile, $content);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Optimize routes
     */
    protected function optimizeRoutes(): bool
    {
        try {
            $this->ensureDirectoryExists('storage/framework/cache');
            
            // Get router and extract routes
            $router = $this->app->getRouter();
            $reflection = new \ReflectionClass($router);
            
            if ($reflection->hasProperty('routes')) {
                $routesProperty = $reflection->getProperty('routes');
                $routesProperty->setAccessible(true);
                $routes = $routesProperty->getValue($router);
                
                $cacheFile = 'storage/framework/cache/routes.php';
                $content = "<?php\n\nreturn " . var_export($routes, true) . ";\n";
                
                return $this->writeFile($cacheFile, $content);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Optimize views
     */
    protected function optimizeViews(): bool
    {
        try {
            // Clear and warm up view cache
            $viewCacheDir = 'storage/framework/views';
            $this->ensureDirectoryExists($viewCacheDir);
            
            // Clear existing cache
            $this->clearDirectoryContents($viewCacheDir);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Optimize autoloader
     */
    protected function optimizeAutoloader(): bool
    {
        try {
            // Run composer dump-autoload with optimization
            $command = 'composer dump-autoload --optimize --classmap-authoritative --no-dev';
            $this->exec($command);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear directory contents
     */
    protected function clearDirectoryContents(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }
        
        $files = glob($dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectoryContents($file);
                rmdir($file);
            }
        }
        
        return true;
    }

    // ============ Route Inspection Methods ============

    /**
     * Get routes from router
     */
    protected function getRoutes($router): array
    {
        $routes = [];
        
        // Get routes through reflection since router might not have a public method
        try {
            $reflection = new \ReflectionClass($router);
            $routesProperty = $reflection->getProperty('routes');
            $routesProperty->setAccessible(true);
            $routeCollection = $routesProperty->getValue($router);

            foreach ($routeCollection as $route) {
                $routes[] = [
                    'methods' => $route['methods'] ?? ['GET'],
                    'uri' => $route['uri'] ?? $route['path'] ?? '/',
                    'name' => $route['name'] ?? null,
                    'action' => $this->formatAction($route['action'] ?? $route['handler'] ?? 'Closure'),
                    'middleware' => $route['middleware'] ?? []
                ];
            }
        } catch (\Exception $e) {
            // Fallback: try to get routes from common router methods
            if (method_exists($router, 'getRoutes')) {
                $routeCollection = $router->getRoutes();
                foreach ($routeCollection as $route) {
                    $routes[] = [
                        'methods' => $route['methods'] ?? ['GET'],
                        'uri' => $route['uri'] ?? '/',
                        'name' => $route['name'] ?? null,
                        'action' => $this->formatAction($route['action'] ?? 'Closure'),
                        'middleware' => $route['middleware'] ?? []
                    ];
                }
            }
        }

        return $routes;
    }

    /**
     * Format action for display
     */
    protected function formatAction($action): string
    {
        if (is_string($action)) {
            return $action;
        }

        if (is_array($action) && count($action) >= 2) {
            $controller = $action[0];
            $method = $action[1];
            
            // Shorten controller name
            if (is_string($controller)) {
                $controller = class_basename($controller);
            }
            
            return "{$controller}@{$method}";
        }

        if (is_callable($action)) {
            return 'Closure';
        }

        return 'Unknown';
    }

    // ============ Inspector Methods ============

    /**
     * Show inspector menu
     */
    protected function showInspectorMenu(): int
    {
        $this->section('Available Components');
        
        $components = [
            'routes' => 'Inspect application routes',
            'config' => 'Inspect configuration values',
            'database' => 'Inspect database connection and tables',
            'cache' => 'Inspect cache status and keys',
            'container' => 'Inspect dependency injection container',
            'env' => 'Inspect environment variables'
        ];

        foreach ($components as $name => $description) {
            $this->text("  <info>{$name}</info> - {$description}");
        }

        $this->newLine();
        $this->comment('Usage: php prism system inspect <component>');
        $this->comment('Example: php prism system inspect routes --format=json');

        return 0;
    }

    /**
     * Inspect routes
     */
    protected function inspectRoutes(string $format, ?string $filter, ?string $export): int
    {
        $this->section('Route Inspector');

        try {
            $router = $this->app->getRouter();
            $routes = $this->getRoutesData($router);

            if ($filter) {
                $routes = array_filter($routes, function($route) use ($filter) {
                    return stripos($route['uri'], $filter) !== false ||
                           stripos($route['action'], $filter) !== false ||
                           stripos($route['name'] ?? '', $filter) !== false;
                });
            }

            $this->outputData($routes, $format, $export, 'routes');
            $this->info('Total routes: ' . count($routes));

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to inspect routes: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Inspect configuration
     */
    protected function inspectConfig(string $format, ?string $filter, ?string $export): int
    {
        $this->section('Configuration Inspector');

        try {
            $config = $this->getConfigData();

            if ($filter) {
                $config = $this->filterConfig($config, $filter);
            }

            $this->outputData($config, $format, $export, 'config');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to inspect config: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Inspect database
     */
    protected function inspectDatabase(string $format, ?string $filter, ?string $export): int
    {
        $this->section('Database Inspector');

        try {
            $dbInfo = $this->getDatabaseInfo();
            $this->outputData($dbInfo, $format, $export, 'database');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to inspect database: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Inspect cache
     */
    protected function inspectCache(string $format, ?string $filter, ?string $export): int
    {
        $this->section('Cache Inspector');

        try {
            $cacheInfo = $this->getCacheInfo();
            $this->outputData($cacheInfo, $format, $export, 'cache');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to inspect cache: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Inspect container
     */
    protected function inspectContainer(string $format, ?string $filter, ?string $export): int
    {
        $this->section('Container Inspector');

        try {
            $container = $this->app->getContainer();
            $bindings = $this->getContainerBindings($container);

            if ($filter) {
                $bindings = array_filter($bindings, function($binding) use ($filter) {
                    return stripos($binding['abstract'], $filter) !== false ||
                           stripos($binding['concrete'], $filter) !== false;
                });
            }

            $this->outputData($bindings, $format, $export, 'container');
            $this->info('Total bindings: ' . count($bindings));

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to inspect container: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Inspect environment
     */
    protected function inspectEnvironment(string $format, ?string $filter, ?string $export): int
    {
        $this->section('Environment Inspector');

        try {
            $env = $_ENV;

            if ($filter) {
                $env = array_filter($env, function($key) use ($filter) {
                    return stripos($key, $filter) !== false;
                }, ARRAY_FILTER_USE_KEY);
            }

            // Hide sensitive data
            $sensitiveKeys = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN'];
            foreach ($env as $key => $value) {
                foreach ($sensitiveKeys as $sensitive) {
                    if (stripos($key, $sensitive) !== false) {
                        $env[$key] = str_repeat('*', strlen($value));
                        break;
                    }
                }
            }

            $envData = [];
            foreach ($env as $key => $value) {
                $envData[] = ['key' => $key, 'value' => $value];
            }

            $this->outputData($envData, $format, $export, 'environment');
            $this->info('Total environment variables: ' . count($envData));

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to inspect environment: ' . $e->getMessage());
            return 1;
        }
    }

    // ============ Helper Methods for Inspector ============

    /**
     * Output data in requested format
     */
    protected function outputData(array $data, string $format, ?string $export, string $type): void
    {
        if ($export) {
            $this->exportData($data, $export, $format);
            $this->success("Data exported to: {$export}");
            return;
        }

        switch ($format) {
            case 'json':
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
                break;
            case 'yaml':
                $this->warn('YAML format not supported, using JSON instead.');
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
                break;
            case 'table':
            default:
                if (!empty($data)) {
                    $headers = array_keys($data[0]);
                    $this->table($headers, $data);
                } else {
                    $this->info('No data found.');
                }
                break;
        }
    }

    /**
     * Export data to file
     */
    protected function exportData(array $data, string $filename, string $format): void
    {
        $content = '';
        
        switch ($format) {
            case 'json':
                $content = json_encode($data, JSON_PRETTY_PRINT);
                break;
            case 'yaml':
                $content = json_encode($data, JSON_PRETTY_PRINT);
                break;
            default:
                $content = json_encode($data, JSON_PRETTY_PRINT);
                break;
        }

        file_put_contents($filename, $content);
    }

    /**
     * Get routes data
     */
    protected function getRoutesData($router): array
    {
        $routes = [];
        
        try {
            $reflection = new \ReflectionClass($router);
            $routesProperty = $reflection->getProperty('routes');
            $routesProperty->setAccessible(true);
            $routeCollection = $routesProperty->getValue($router);

            foreach ($routeCollection as $route) {
                $routes[] = [
                    'method' => implode('|', $route['methods'] ?? ['GET']),
                    'uri' => $route['uri'] ?? '/',
                    'name' => $route['name'] ?? '',
                    'action' => $this->formatRouteAction($route['action'] ?? 'Closure'),
                    'middleware' => implode(', ', $route['middleware'] ?? [])
                ];
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return $routes;
    }

    /**
     * Format route action
     */
    protected function formatRouteAction($action): string
    {
        if (is_string($action)) {
            return $action;
        }

        if (is_array($action) && count($action) >= 2) {
            $controller = is_string($action[0]) ? class_basename($action[0]) : 'Controller';
            return "{$controller}@{$action[1]}";
        }

        return 'Closure';
    }

    /**
     * Get configuration data
     */
    protected function getConfigData(): array
    {
        $configData = [];
        
        // Try to get config files
        $configPath = $this->app->configPath();
        
        if (is_dir($configPath)) {
            $files = glob($configPath . '/*.php');
            
            foreach ($files as $file) {
                $key = basename($file, '.php');
                try {
                    $config = require $file;
                    $this->flattenConfig($configData, $config, $key);
                } catch (\Exception $e) {
                    $configData[] = ['key' => $key, 'value' => 'Error loading config'];
                }
            }
        }

        return $configData;
    }

    /**
     * Flatten config array
     */
    protected function flattenConfig(array &$result, array $config, string $prefix = ''): void
    {
        foreach ($config as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (is_array($value)) {
                $this->flattenConfig($result, $value, $fullKey);
            } else {
                $result[] = [
                    'key' => $fullKey,
                    'value' => is_bool($value) ? ($value ? 'true' : 'false') : (string)$value,
                    'type' => gettype($value)
                ];
            }
        }
    }

    /**
     * Filter config data
     */
    protected function filterConfig(array $config, string $filter): array
    {
        return array_filter($config, function($item) use ($filter) {
            return stripos($item['key'], $filter) !== false;
        });
    }

    /**
     * Get database info
     */
    protected function getDatabaseInfo(): array
    {
        $info = [];
        
        try {
            $db = \Core\Database::getInstance();
            
            // Basic connection info
            $info[] = ['property' => 'Status', 'value' => 'Connected'];
            
            // Get tables
            $tables = $db->executeQuery("SHOW TABLES");
            $info[] = ['property' => 'Total Tables', 'value' => count($tables)];
            
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                $info[] = ['property' => 'Table', 'value' => $tableName];
            }
            
        } catch (\Exception $e) {
            $info[] = ['property' => 'Status', 'value' => 'Error: ' . $e->getMessage()];
        }

        return $info;
    }

    /**
     * Get cache info
     */
    protected function getCacheInfo(): array
    {
        $info = [];
        
        // Check cache directories
        $cacheDirs = [
            'storage/cache',
            'storage/framework/cache',
            'storage/framework/views'
        ];

        foreach ($cacheDirs as $dir) {
            $fullPath = $this->app->basePath($dir);
            $exists = is_dir($fullPath);
            $fileCount = $exists ? count(glob($fullPath . '/*')) : 0;
            
            $info[] = [
                'directory' => $dir,
                'exists' => $exists ? 'Yes' : 'No',
                'files' => $fileCount
            ];
        }

        return $info;
    }

    /**
     * Get container bindings
     */
    protected function getContainerBindings($container): array
    {
        $bindings = [];
        
        try {
            $reflection = new \ReflectionClass($container);
            
            if ($reflection->hasProperty('bindings')) {
                $bindingsProperty = $reflection->getProperty('bindings');
                $bindingsProperty->setAccessible(true);
                $containerBindings = $bindingsProperty->getValue($container);
                
                foreach ($containerBindings as $abstract => $concrete) {
                    $bindings[] = [
                        'abstract' => $abstract,
                        'concrete' => is_string($concrete) ? $concrete : gettype($concrete),
                        'singleton' => $this->isSingleton($container, $abstract) ? 'Yes' : 'No'
                    ];
                }
            }
        } catch (\Exception $e) {
            $bindings[] = [
                'abstract' => 'Error',
                'concrete' => $e->getMessage(),
                'singleton' => 'Unknown'
            ];
        }

        return $bindings;
    }

    /**
     * Check if binding is singleton
     */
    protected function isSingleton($container, string $abstract): bool
    {
        try {
            $reflection = new \ReflectionClass($container);
            
            if ($reflection->hasProperty('instances')) {
                $instancesProperty = $reflection->getProperty('instances');
                $instancesProperty->setAccessible(true);
                $instances = $instancesProperty->getValue($container);
                
                return array_key_exists($abstract, $instances);
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return false;
    }

    // ============ Environment Methods ============

    /**
     * Show environment menu
     */
    protected function showEnvMenu(): int
    {
        $this->section('Available Actions');
        
        $actions = [
            'get <key>' => 'Get an environment variable value',
            'set <key> <value>' => 'Set an environment variable',
            'unset <key>' => 'Remove an environment variable',
            'list' => 'List all environment variables',
            'backup [file]' => 'Backup environment file',
            'restore <file>' => 'Restore environment from backup',
            'generate' => 'Generate a new environment file from example',
            'validate' => 'Validate environment file syntax'
        ];

        foreach ($actions as $command => $description) {
            $this->text("  <info>{$command}</info> - {$description}");
        }

        $this->newLine();
        $this->comment('Usage: php prism system env <action> [arguments] [options]');

        return 0;
    }

    /**
     * Get environment value
     */
    protected function getEnvValue(?string $key, string $file): int
    {
        if (!$key) {
            $this->error('Key is required for get action.');
            return 1;
        }

        $envPath = $this->app->basePath($file);
        
        if (!file_exists($envPath)) {
            $this->error("Environment file not found: {$file}");
            return 1;
        }

        $env = $this->parseEnvFile($envPath);
        
        if (array_key_exists($key, $env)) {
            $this->info("Value for {$key}:");
            $this->line($env[$key]);
            return 0;
        } else {
            $this->warn("Environment variable '{$key}' not found.");
            return 1;
        }
    }

    /**
     * Set environment value
     */
    protected function setEnvValue(?string $key, ?string $value, string $file, bool $force): int
    {
        if (!$key) {
            $this->error('Key is required for set action.');
            return 1;
        }

        if ($value === null) {
            $value = $this->ask("Enter value for {$key}");
        }

        $envPath = $this->app->basePath($file);
        
        if (!file_exists($envPath)) {
            $this->error("Environment file not found: {$file}");
            return 1;
        }

        $env = $this->parseEnvFile($envPath);
        
        if (array_key_exists($key, $env) && !$force) {
            if (!$this->confirm("Key '{$key}' already exists. Overwrite?", false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $env[$key] = $value;
        
        if ($this->writeEnvFile($envPath, $env)) {
            $this->success("Environment variable '{$key}' has been set.");
            return 0;
        } else {
            $this->error("Failed to update environment file.");
            return 1;
        }
    }

    /**
     * Unset environment value
     */
    protected function unsetEnvValue(?string $key, string $file): int
    {
        if (!$key) {
            $this->error('Key is required for unset action.');
            return 1;
        }

        $envPath = $this->app->basePath($file);
        
        if (!file_exists($envPath)) {
            $this->error("Environment file not found: {$file}");
            return 1;
        }

        $env = $this->parseEnvFile($envPath);
        
        if (!array_key_exists($key, $env)) {
            $this->warn("Environment variable '{$key}' not found.");
            return 1;
        }

        if (!$this->confirm("Are you sure you want to remove '{$key}'?", false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        unset($env[$key]);
        
        if ($this->writeEnvFile($envPath, $env)) {
            $this->success("Environment variable '{$key}' has been removed.");
            return 0;
        } else {
            $this->error("Failed to update environment file.");
            return 1;
        }
    }

    /**
     * List environment values
     */
    protected function listEnvValues(string $file): int
    {
        $envPath = $this->app->basePath($file);
        
        if (!file_exists($envPath)) {
            $this->error("Environment file not found: {$file}");
            return 1;
        }

        $env = $this->parseEnvFile($envPath);
        
        if (empty($env)) {
            $this->info('No environment variables found.');
            return 0;
        }

        $rows = [];
        $sensitiveKeys = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN'];
        
        foreach ($env as $key => $value) {
            $displayValue = $value;
            
            // Hide sensitive values
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    $displayValue = str_repeat('*', min(strlen($value), 20));
                    break;
                }
            }
            
            $rows[] = [$key, $displayValue];
        }

        $this->table(['Key', 'Value'], $rows);
        $this->info('Total variables: ' . count($env));

        return 0;
    }

    /**
     * Backup environment file
     */
    protected function backupEnv(string $file, ?string $backup): int
    {
        $envPath = $this->app->basePath($file);
        
        if (!file_exists($envPath)) {
            $this->error("Environment file not found: {$file}");
            return 1;
        }

        $backupPath = $backup ?? $file . '.backup.' . date('Y-m-d-H-i-s');
        $fullBackupPath = $this->app->basePath($backupPath);

        if (copy($envPath, $fullBackupPath)) {
            $this->success("Environment backed up to: {$backupPath}");
            return 0;
        } else {
            $this->error("Failed to create backup.");
            return 1;
        }
    }

    /**
     * Restore environment file
     */
    protected function restoreEnv(?string $backup, string $file): int
    {
        if (!$backup) {
            $this->error('Backup file path is required for restore action.');
            return 1;
        }

        $backupPath = $this->app->basePath($backup);
        
        if (!file_exists($backupPath)) {
            $this->error("Backup file not found: {$backup}");
            return 1;
        }

        $envPath = $this->app->basePath($file);

        if (file_exists($envPath) && !$this->confirm("This will overwrite the current environment file. Continue?", false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        if (copy($backupPath, $envPath)) {
            $this->success("Environment restored from: {$backup}");
            return 0;
        } else {
            $this->error("Failed to restore environment file.");
            return 1;
        }
    }

    /**
     * Generate environment file
     */
    protected function generateEnv(string $file): int
    {
        $examplePath = $this->app->basePath('env.example');
        $envPath = $this->app->basePath($file);

        if (!file_exists($examplePath)) {
            $this->error('env.example file not found.');
            return 1;
        }

        if (file_exists($envPath) && !$this->confirm("Environment file already exists. Overwrite?", false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        if (copy($examplePath, $envPath)) {
            $this->success("Environment file generated from example.");
            
            // Generate app key if needed
            $env = $this->parseEnvFile($envPath);
            if (array_key_exists('APP_KEY', $env) && empty($env['APP_KEY'])) {
                $env['APP_KEY'] = base64_encode(random_bytes(32));
                $this->writeEnvFile($envPath, $env);
                $this->info('Application key generated automatically.');
            }
            
            return 0;
        } else {
            $this->error("Failed to generate environment file.");
            return 1;
        }
    }

    /**
     * Validate environment file
     */
    protected function validateEnv(string $file): int
    {
        $envPath = $this->app->basePath($file);
        
        if (!file_exists($envPath)) {
            $this->error("Environment file not found: {$file}");
            return 1;
        }

        $this->section('Validating Environment File');

        $errors = [];
        $warnings = [];
        
        try {
            $env = $this->parseEnvFile($envPath);
            
            // Check required variables
            $required = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_URL'];
            foreach ($required as $key) {
                if (!array_key_exists($key, $env) || empty($env[$key])) {
                    $errors[] = "Missing required variable: {$key}";
                }
            }

            // Check app key format
            if (array_key_exists('APP_KEY', $env) && !empty($env['APP_KEY'])) {
                $key = $env['APP_KEY'];
                if (strlen($key) < 32) {
                    $warnings[] = "APP_KEY seems too short (recommended: 32+ characters)";
                }
            }

            // Check database configuration
            if (array_key_exists('DB_CONNECTION', $env) && $env['DB_CONNECTION'] === 'mysql') {
                $dbVars = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
                foreach ($dbVars as $var) {
                    if (!array_key_exists($var, $env) || empty($env[$var])) {
                        $warnings[] = "Database variable not set: {$var}";
                    }
                }
            }

            // Display results
            if (empty($errors) && empty($warnings)) {
                $this->success('Environment file validation passed!');
                return 0;
            }

            if (!empty($errors)) {
                $this->error('Validation errors found:');
                $this->listing($errors);
            }

            if (!empty($warnings)) {
                $this->warn('Validation warnings:');
                $this->listing($warnings);
            }

            return empty($errors) ? 0 : 1;

        } catch (\Exception $e) {
            $this->error('Failed to parse environment file: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Parse environment file
     */
    protected function parseEnvFile(string $path): array
    {
        $env = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments
            if (strpos($line, '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                $env[$key] = $value;
            }
        }

        return $env;
    }

    /**
     * Write environment file
     */
    protected function writeEnvFile(string $path, array $env): bool
    {
        $lines = [];
        
        foreach ($env as $key => $value) {
            // Add quotes if value contains spaces
            if (strpos($value, ' ') !== false || strpos($value, '#') !== false) {
                $value = '"' . $value . '"';
            }
            
            $lines[] = "{$key}={$value}";
        }

        return file_put_contents($path, implode("\n", $lines) . "\n") !== false;
    }

    // ============ Security Methods ============

    /**
     * Check file permissions
     */
    protected function checkFilePermissions(): array
    {
        $issues = [];
        $criticalFiles = [
            '.env' => 0600,
            'config/' => 0644,
            'storage/' => 0755,
        ];

        foreach ($criticalFiles as $file => $expectedPerms) {
            $fullPath = $this->app->basePath($file);
            
            if (file_exists($fullPath)) {
                $currentPerms = fileperms($fullPath) & 0777;
                
                if ($currentPerms !== $expectedPerms) {
                    $issues[] = [
                        'file' => $file,
                        'current' => decoct($currentPerms),
                        'expected' => decoct($expectedPerms),
                        'severity' => 'high'
                    ];
                    $this->warn("Insecure permissions on {$file}: " . decoct($currentPerms) . " (should be " . decoct($expectedPerms) . ")");
                }
            }
        }

        return $issues;
    }

    /**
     * Check configuration security
     */
    protected function checkConfigurationSecurity(): array
    {
        $issues = [];

        // Check if debug mode is enabled in production
        if ($this->app->environment() === 'production' && $this->app->isDebug()) {
            $issues[] = [
                'type' => 'debug_mode',
                'message' => 'Debug mode is enabled in production',
                'severity' => 'high'
            ];
            $this->error('Debug mode is enabled in production environment!');
        }

        // Check if default app key is being used
        $envPath = $this->app->basePath('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=""') !== false) {
                $issues[] = [
                    'type' => 'app_key',
                    'message' => 'Application key is not set',
                    'severity' => 'high'
                ];
                $this->error('Application key is not set!');
            }
        }

        return $issues;
    }

    /**
     * Check for exposed sensitive files
     */
    protected function checkExposedFiles(): array
    {
        $issues = [];
        $sensitiveFiles = [
            '.env',
            '.env.example',
            'composer.json',
            'composer.lock',
            'config/',
            'storage/',
            'vendor/',
            '.git/',
            '.gitignore'
        ];

        $publicPath = $this->app->publicPath();
        
        foreach ($sensitiveFiles as $file) {
            $publicFile = $publicPath . '/' . $file;
            if (file_exists($publicFile)) {
                $issues[] = [
                    'file' => $file,
                    'location' => 'public directory',
                    'severity' => 'critical'
                ];
                $this->error("Sensitive file exposed in public directory: {$file}");
            }
        }

        return $issues;
    }

    /**
     * Fix file permissions
     */
    protected function fixFilePermissions(array $issues): int
    {
        $fixed = 0;
        
        foreach ($issues as $issue) {
            $fullPath = $this->app->basePath($issue['file']);
            $expectedPerms = octdec($issue['expected']);
            
            if (chmod($fullPath, $expectedPerms)) {
                $this->info("Fixed permissions for {$issue['file']}");
                $fixed++;
            } else {
                $this->error("Failed to fix permissions for {$issue['file']}");
            }
        }

        return $fixed;
    }

    /**
     * Fix configuration issues
     */
    protected function fixConfigurationIssues(array $issues): int
    {
        $fixed = 0;
        
        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'app_key':
                    if ($this->generateAppKey()) {
                        $this->info('Generated new application key');
                        $fixed++;
                    }
                    break;
                    
                case 'debug_mode':
                    if ($this->disableDebugMode()) {
                        $this->info('Disabled debug mode');
                        $fixed++;
                    }
                    break;
            }
        }

        return $fixed;
    }

    /**
     * Fix exposed files
     */
    protected function fixExposedFiles(array $issues): int
    {
        $fixed = 0;
        
        foreach ($issues as $issue) {
            $publicFile = $this->app->publicPath($issue['file']);
            
            if (unlink($publicFile)) {
                $this->info("Removed exposed file: {$issue['file']}");
                $fixed++;
            } else {
                $this->error("Failed to remove exposed file: {$issue['file']}");
            }
        }

        return $fixed;
    }

    /**
     * Generate security report
     */
    protected function generateSecurityReport(array $vulnerabilities, string $filename): void
    {
        $report = [
            'scan_date' => date('Y-m-d H:i:s'),
            'framework_version' => $this->app->version(),
            'environment' => $this->app->environment(),
            'total_issues' => array_sum(array_map('count', $vulnerabilities)),
            'vulnerabilities' => $vulnerabilities,
            'recommendations' => [
                'Update to latest framework version',
                'Enable CSRF protection on all forms',
                'Use HTTPS in production',
                'Regularly update dependencies',
                'Implement Content Security Policy'
            ]
        ];

        if ($this->writeFile($filename, json_encode($report, JSON_PRETTY_PRINT))) {
            $this->success("Security report generated: {$filename}");
        } else {
            $this->error("Failed to generate security report");
        }
    }

    /**
     * Generate application key for security
     */
    protected function generateAppKey(): bool
    {
        $key = base64_encode(random_bytes(32));
        
        if (file_exists('.env')) {
            $env = file_get_contents('.env');
            
            if (strpos($env, 'APP_KEY=') !== false) {
                $env = preg_replace('/APP_KEY=.*/', "APP_KEY={$key}", $env);
            } else {
                $env .= "\nAPP_KEY={$key}";
            }
            
            return file_put_contents('.env', $env) !== false;
        }

        return false;
    }

    /**
     * Disable debug mode
     */
    protected function disableDebugMode(): bool
    {
        $envPath = $this->app->basePath('.env');
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $envContent = preg_replace('/APP_DEBUG=true/', 'APP_DEBUG=false', $envContent);
            
            return file_put_contents($envPath, $envContent) !== false;
        }

        return false;
    }

    /**
     * Interactive security setup
     */
    protected function interactiveSecuritySetup(): int
    {
        $this->section('Choose security features to setup:');

        $choices = [
            'csrf' => 'CSRF Protection',
            'headers' => 'Security Headers',
            'rate-limit' => 'Rate Limiting',
            'all' => 'All security features'
        ];

        $selected = $this->choice(
            'Which security features would you like to setup?',
            array_values($choices),
            'All security features'
        );

        $success = true;

        if ($selected === 'All security features' || $selected === 'CSRF Protection') {
            $success &= $this->setupCsrfProtection();
        }

        if ($selected === 'All security features' || $selected === 'Security Headers') {
            $success &= $this->setupSecurityHeaders();
        }

        if ($selected === 'All security features' || $selected === 'Rate Limiting') {
            $success &= $this->setupRateLimiting();
        }

        if ($success) {
            $this->success('Security setup completed successfully!');
            $this->displaySecurityInfo();
            return 0;
        } else {
            $this->error('Some security features could not be set up properly.');
            return 1;
        }
    }

    /**
     * Setup CSRF protection
     */
    protected function setupCsrfProtection(): bool
    {
        $this->section('Setting up CSRF Protection');

        try {
            // Create helper functions file
            $helpersContent = $this->generateCsrfHelpers();
            $helpersPath = 'core/Helpers/SecurityHelpers.php';
            
            if ($this->writeFile($helpersPath, $helpersContent)) {
                $this->info('✓ CSRF helper functions created');
            }

            // Create example middleware integration
            $routeExample = $this->generateRouteExample();
            $examplePath = 'examples/security/csrf_example.php';
            
            if ($this->writeFile($examplePath, $routeExample)) {
                $this->info('✓ CSRF usage example created');
            }

            $this->success('CSRF Protection setup completed!');
            
            $this->newLine();
            $this->info('To use CSRF protection:');
            $this->listing([
                'Add CsrfMiddleware to your routes',
                'Use csrf_field() in your forms',
                'Include CSRF meta tag in your layouts',
                'Check the example file for implementation details'
            ]);

            return true;

        } catch (\Exception $e) {
            $this->error('Failed to setup CSRF protection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup security headers
     */
    protected function setupSecurityHeaders(): bool
    {
        $this->section('Setting up Security Headers');

        try {
            // Create configuration file for security headers
            $configContent = $this->generateSecurityHeadersConfig();
            $configPath = 'config/security.php';
            
            if ($this->writeFile($configPath, $configContent)) {
                $this->info('✓ Security headers configuration created');
            }

            // Create .htaccess example for additional security
            $htaccessContent = $this->generateHtaccessSecurity();
            $htaccessPath = 'examples/security/.htaccess_security';
            
            if ($this->writeFile($htaccessPath, $htaccessContent)) {
                $this->info('✓ Apache security configuration example created');
            }

            $this->success('Security Headers setup completed!');
            
            $this->newLine();
            $this->info('Security headers that will be applied:');
            $this->listing([
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: DENY',
                'X-XSS-Protection: 1; mode=block',
                'Content-Security-Policy (configurable)',
                'Strict-Transport-Security (HTTPS only)',
                'Referrer-Policy: strict-origin-when-cross-origin'
            ]);

            return true;

        } catch (\Exception $e) {
            $this->error('Failed to setup security headers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup rate limiting
     */
    protected function setupRateLimiting(): bool
    {
        $this->section('Setting up Rate Limiting');

        try {
            // Create rate limiting configuration
            $rateLimitConfig = $this->generateRateLimitConfig();
            $configPath = 'config/rate_limit.php';
            
            if ($this->writeFile($configPath, $rateLimitConfig)) {
                $this->info('✓ Rate limiting configuration created');
            }

            // Create example usage
            $exampleContent = $this->generateRateLimitExample();
            $examplePath = 'examples/security/rate_limit_example.php';
            
            if ($this->writeFile($examplePath, $exampleContent)) {
                $this->info('✓ Rate limiting usage example created');
            }

            $this->success('Rate Limiting setup completed!');
            
            $this->newLine();
            $this->info('Rate limiting features:');
            $this->listing([
                'IP-based rate limiting',
                'Route-specific limits',
                'Configurable time windows',
                'Automatic cleanup of expired entries',
                'Custom rate limit responses'
            ]);

            return true;

        } catch (\Exception $e) {
            $this->error('Failed to setup rate limiting: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display security information
     */
    protected function displaySecurityInfo(): void
    {
        $this->newLine();
        $this->section('Security Best Practices');
        
        $this->info('Additional security recommendations:');
        $this->listing([
            'Always use HTTPS in production',
            'Keep your framework and dependencies up to date',
            'Use strong, unique passwords',
            'Implement proper input validation',
            'Log security events for monitoring',
            'Regular security audits and scans',
            'Backup your data regularly',
            'Use environment variables for sensitive data'
        ]);

        $this->newLine();
        $this->info('Useful commands:');
        $this->listing([
            'php prism system security:scan - Scan for vulnerabilities',
            'php prism system env validate - Validate environment configuration',
            'php prism system optimize - Optimize for production'
        ]);
    }

    /**
     * Generate CSRF helpers
     */
    protected function generateCsrfHelpers(): string
    {
        return '<?php

if (!function_exists(\'csrf_token\')) {
    /**
     * Get CSRF token
     */
    function csrf_token(): string
    {
        return \Core\Security\CsrfProtection::token();
    }
}

if (!function_exists(\'csrf_field\')) {
    /**
     * Generate CSRF hidden input field
     */
    function csrf_field(): string
    {
        return \Core\Security\CsrfProtection::field();
    }
}

if (!function_exists(\'csrf_meta\')) {
    /**
     * Generate CSRF meta tag
     */
    function csrf_meta(): string
    {
        return \Core\Security\CsrfProtection::metaTag();
    }
}

if (!function_exists(\'xss_clean\')) {
    /**
     * Clean input from XSS
     */
    function xss_clean(string $input, bool $allowHtml = false): string
    {
        return \Core\Security\XssProtection::clean($input, $allowHtml);
    }
}

if (!function_exists(\'e\')) {
    /**
     * Escape HTML entities
     */
    function e(string $value): string
    {
        return \Core\Security\XssProtection::escape($value);
    }
}
';
    }

    /**
     * Generate route example
     */
    protected function generateRouteExample(): string
    {
        return '<?php

/**
 * CSRF Protection Example
 * 
 * This file shows how to implement CSRF protection in your application.
 */

// Example route with CSRF protection
$router->group([\'middleware\' => \'csrf\'], function() use ($router) {
    
    // Form routes that need CSRF protection
    $router->post(\'/contact\', \'ContactController@store\');
    $router->post(\'/users\', \'UserController@store\');
    $router->put(\'/users/{id}\', \'UserController@update\');
    $router->delete(\'/users/{id}\', \'UserController@destroy\');
    
});

// Example form in Blade template:
/*
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    {{ csrf_meta() }}
</head>
<body>
    <form method="POST" action="/contact">
        {{ csrf_field() }}
        
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="message">Message:</label>
        <textarea id="message" name="message" required></textarea>
        
        <button type="submit">Send Message</button>
    </form>
</body>
</html>
*/

// Example AJAX with CSRF token:
/*
<script>
// Get CSRF token from meta tag
const token = document.querySelector(\'meta[name="csrf-token"]\').getAttribute(\'content\');

// Include in AJAX requests
fetch(\'/api/data\', {
    method: \'POST\',
    headers: {
        \'Content-Type\': \'application/json\',
        \'X-CSRF-TOKEN\': token
    },
    body: JSON.stringify(data)
});
</script>
*/
';
    }

    /**
     * Generate security headers config
     */
    protected function generateSecurityHeadersConfig(): string
    {
        return '<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    */

    \'headers\' => [
        \'X-Content-Type-Options\' => \'nosniff\',
        \'X-Frame-Options\' => \'DENY\',
        \'X-XSS-Protection\' => \'1; mode=block\',
        \'Referrer-Policy\' => \'strict-origin-when-cross-origin\',
        \'Permissions-Policy\' => \'geolocation=(), microphone=(), camera=()\',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */

    \'csp\' => [
        \'default-src\' => "\'self\'",
        \'script-src\' => "\'self\' \'unsafe-inline\'",
        \'style-src\' => "\'self\' \'unsafe-inline\'",
        \'img-src\' => "\'self\' data: https:",
        \'connect-src\' => "\'self\'",
        \'font-src\' => "\'self\'",
        \'object-src\' => "\'none\'",
        \'media-src\' => "\'self\'",
        \'frame-src\' => "\'none\'",
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security
    |--------------------------------------------------------------------------
    */

    \'hsts\' => [
        \'max-age\' => 31536000, // 1 year
        \'include-subdomains\' => true,
        \'preload\' => false,
    ],
];
';
    }

    /**
     * Generate .htaccess security example
     */
    protected function generateHtaccessSecurity(): string
    {
        return '# Security Headers for Apache
# Add these to your .htaccess file in the public directory

# Prevent access to sensitive files
<FilesMatch "\.(env|log|md|txt|yml|yaml|xml)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Hide server information
ServerTokens Prod
Header unset Server
Header unset X-Powered-By

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# HSTS (if using HTTPS)
# Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Content Security Policy
# Header always set Content-Security-Policy "default-src \'self\'"

# Disable server signature
ServerSignature Off

# Prevent access to hidden files
RedirectMatch 404 /\..*$
';
    }

    /**
     * Generate rate limit config
     */
    protected function generateRateLimitConfig(): string
    {
        return '<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    \'default\' => [
        \'max_attempts\' => 60,
        \'window_minutes\' => 1,
    ],

    \'api\' => [
        \'max_attempts\' => 100,
        \'window_minutes\' => 1,
    ],

    \'login\' => [
        \'max_attempts\' => 5,
        \'window_minutes\' => 15,
    ],

    \'register\' => [
        \'max_attempts\' => 3,
        \'window_minutes\' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Storage
    |--------------------------------------------------------------------------
    | Options: session, redis, memcached, database
    */

    \'storage\' => \'session\',

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Response
    |--------------------------------------------------------------------------
    */

    \'response\' => [
        \'status_code\' => 429,
        \'message\' => \'Too Many Requests\',
    ],
];
';
    }

    /**
     * Generate rate limit example
     */
    protected function generateRateLimitExample(): string
    {
        return '<?php

/**
 * Rate Limiting Example
 * 
 * This file shows how to implement rate limiting in your application.
 */

// Example 1: Basic rate limiting for all routes
$router->group([\'middleware\' => \'rate-limit:60,1\'], function() use ($router) {
    $router->get(\'/api/data\', \'ApiController@data\');
});

// Example 2: Strict rate limiting for authentication routes
$router->group([\'middleware\' => \'rate-limit:5,15\'], function() use ($router) {
    $router->post(\'/login\', \'AuthController@login\');
    $router->post(\'/register\', \'AuthController@register\');
    $router->post(\'/forgot-password\', \'AuthController@forgotPassword\');
});

// Example 3: Custom rate limiter in controller
/*
class ApiController extends Controller
{
    public function data(Request $request)
    {
        // Custom rate limiting logic
        $rateLimiter = new \Core\Middleware\RateLimitMiddleware(100, 1); // 100 requests per minute
        
        // Apply rate limiting
        $response = $rateLimiter->handle($request, function($req) {
            return $this->getData();
        });
        
        if ($response->getStatusCode() === 429) {
            return $response; // Rate limit exceeded
        }
        
        return $this->jsonResponse([
            \'data\' => $this->getData()
        ]);
    }
}
*/

// Example 4: Rate limiting with Redis (when available)
/*
// In your middleware configuration
$app->singleton(\'rate-limiter\', function() {
    return new \Core\RateLimit\RedisRateLimiter(
        new Redis([\'host\' => \'127.0.0.1\', \'port\' => 6379])
    );
});
*/
';
    }
}
