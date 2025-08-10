<?php

namespace Core\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseCommands extends Command
{
    protected array $dbCommands = [
        'migrate' => 'runMigrations',
        'db:seed' => 'runSeeders',
    ];

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('db')
            ->setDescription('Database operations (migrate, seed)')
            ->addArgument('action', InputArgument::REQUIRED, 'Database action (migrate, seed)')
            ->addArgument('class', InputArgument::OPTIONAL, 'The class name of the root seeder', 'DatabaseSeeder')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files', 'database/migrations')
            ->addOption('step', null, InputOption::VALUE_OPTIONAL, 'Force the migrations to be run so they can be rolled back individually')
            ->addOption('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run')
            ->addOption('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production')
            ->addOption('class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder');
    }

    /**
     * Handle the command
     */
    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');

        if (!array_key_exists($action, $this->dbCommands)) {
            $this->error("Unknown database action: {$action}");
            $this->showAvailableActions();
            return 1;
        }

        $method = $this->dbCommands[$action];
        return $this->$method($input, $output);
    }

    /**
     * Show available database actions
     */
    protected function showAvailableActions(): void
    {
        $this->section('Available Database Actions');
        
        foreach (array_keys($this->dbCommands) as $action) {
            $this->text("  <info>{$action}</info>");
        }
        
        $this->newLine();
        $this->comment('Usage: php prism db <action>');
        $this->comment('Example: php prism db migrate');
    }

    /**
     * Run database migrations
     */
    protected function runMigrations(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getOption('path');
        $pretend = $input->getOption('pretend');
        $seed = $input->getOption('seed');
        $force = $input->getOption('force');
        $step = $input->getOption('step');

        $this->title('Database Migration');

        // Check if in production and not forced
        if ($this->app->environment() === 'production' && !$force) {
            if (!$this->confirm('Application In Production! Are you sure you want to run migrations?', false)) {
                $this->info('Migration cancelled.');
                return 0;
            }
        }

        try {
            // Create migrations table if not exists
            $this->createMigrationsTable();

            // Get migration files
            $migrationFiles = $this->getMigrationFiles($path);

            if (empty($migrationFiles)) {
                $this->info('No migrations found.');
                return 0;
            }

            // Get already run migrations
            $ranMigrations = $this->getRanMigrations();

            // Filter pending migrations
            $pendingMigrations = array_diff($migrationFiles, $ranMigrations);

            if (empty($pendingMigrations)) {
                $this->info('Nothing to migrate.');
                return 0;
            }

            $this->info('Running migrations...');
            $progressBar = $this->createProgressBar(count($pendingMigrations));

            foreach ($pendingMigrations as $migration) {
                if ($pretend) {
                    $this->info("Would run: {$migration}");
                } else {
                    $migrationPath = $this->app->basePath("{$path}/{$migration}.php");
                    $this->runMigration($migrationPath, $migration);
                    $this->recordMigration($migration);
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            if (!$pretend) {
                $this->success('Migrations completed successfully!');

                // Run seeders if requested
                if ($seed) {
                    $this->runSeedersAfterMigration();
                }
            } else {
                $this->info('Migration preview completed.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Run database seeders
     */
    protected function runSeeders(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getArgument('class') ?? $input->getOption('class') ?? 'DatabaseSeeder';
        $force = $input->getOption('force');

        $this->title('Database Seeding');

        // Check if in production and not forced
        if ($this->app->environment() === 'production' && !$force) {
            if (!$this->confirm('Application In Production! Are you sure you want to seed the database?', false)) {
                $this->info('Seeding cancelled.');
                return 0;
            }
        }

        try {
            $this->info("Seeding: {$class}");
            
            $seederClass = "Database\\Seeders\\{$class}";
            
            if (!class_exists($seederClass)) {
                // Try to load the seeder file
                $seederPath = $this->app->basePath("database/seeders/{$class}.php");
                if (file_exists($seederPath)) {
                    require_once $seederPath;
                }
                
                if (!class_exists($seederClass)) {
                    throw new \Exception("Seeder class [{$seederClass}] not found.");
                }
            }

            $seeder = new $seederClass();
            
            if (!method_exists($seeder, 'run')) {
                throw new \Exception("Seeder class [{$seederClass}] must have a run() method.");
            }

            $startTime = microtime(true);
            $seeder->run();
            $endTime = microtime(true);
            
            $duration = round(($endTime - $startTime) * 1000, 2);
            $this->success("Database seeding completed successfully! ({$duration}ms)");

            return 0;

        } catch (\Exception $e) {
            $this->error('Seeding failed: ' . $e->getMessage());
            return 1;
        }
    }

    // ============ Migration Helper Methods ============

    /**
     * Create migrations table
     */
    protected function createMigrationsTable(): void
    {
        $db = \Core\Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $db->execute($sql);
    }

    /**
     * Get migration files
     */
    protected function getMigrationFiles(string $path): array
    {
        $fullPath = $this->app->basePath($path);
        
        if (!is_dir($fullPath)) {
            return [];
        }

        $files = glob($fullPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get already run migrations
     */
    protected function getRanMigrations(): array
    {
        $db = \Core\Database::getInstance();
        
        try {
            $result = $db->executeQuery("SELECT migration FROM migrations ORDER BY migration");
            return array_column($result, 'migration');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Run a single migration
     */
    protected function runMigration(string $path, string $migration): void
    {
        $this->line("Running migration: {$migration}");
        
        // Include and run the migration
        require_once $path;
        
        // Get migration class name from filename
        $className = $this->getMigrationClassName($migration);
        
        if (class_exists($className)) {
            try {
                // Create migration instance using PRISM's Migration class
                $migrationInstance = new $className();
                
                if (method_exists($migrationInstance, 'up')) {
                    $migrationInstance->up();
                    $this->line("✓ Migrated: {$migration}");
                } else {
                    throw new \Exception("Migration method 'up' not found in {$className}");
                }
            } catch (\Exception $e) {
                $this->error("Migration failed: {$migration} - " . $e->getMessage());
                throw $e;
            }
        } else {
            throw new \Exception("Migration class not found: {$className}");
        }
    }
    
    /**
     * Get migration class name from filename
     */
    protected function getMigrationClassName(string $migration): string
    {
        // Remove timestamp and .php extension
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration);
        $name = str_replace('.php', '', $name);
        
        // Convert to PascalCase
        return $this->studlyCase($name);
    }

    /**
     * Convert string to StudlyCase
     */
    protected function studlyCase(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }

    /**
     * Record migration
     */
    protected function recordMigration(string $migration): void
    {
        $db = \Core\Database::getInstance();
        
        // Get next batch number
        $result = $db->executeQuery("SELECT MAX(batch) as max_batch FROM migrations");
        $batch = ($result[0]['max_batch'] ?? 0) + 1;
        
        $db->execute(
            "INSERT INTO migrations (migration, batch) VALUES (?, ?)",
            [$migration, $batch]
        );
    }

    /**
     * Run seeders after migration
     */
    protected function runSeedersAfterMigration(): void
    {
        $this->newLine();
        $this->info('Running seeders...');
        
        // Call the seed method directly
        $seedInput = clone $this->input;
        $seedInput->setArgument('action', 'seed');
        $this->runSeeders($seedInput, $this->output);
    }

    // ============ Additional Database Utilities ============

    /**
     * Get database status
     */
    protected function getDatabaseStatus(): array
    {
        $status = [];
        
        try {
            $db = \Core\Database::getInstance();
            
            // Check connection
            $status['connection'] = 'Connected';
            
            // Get database info
            $result = $db->executeQuery("SELECT DATABASE() as db_name");
            $status['database'] = $result[0]['db_name'] ?? 'Unknown';
            
            // Get tables
            $tables = $db->executeQuery("SHOW TABLES");
            $status['tables'] = count($tables);
            
            // Get migrations status
            $ranMigrations = $this->getRanMigrations();
            $status['migrations_run'] = count($ranMigrations);
            
            $allMigrations = $this->getMigrationFiles('database/migrations');
            $status['migrations_pending'] = count($allMigrations) - count($ranMigrations);
            
        } catch (\Exception $e) {
            $status['connection'] = 'Failed: ' . $e->getMessage();
        }
        
        return $status;
    }

    /**
     * Show database information
     */
    public function showDatabaseInfo(): void
    {
        $this->title('Database Information');
        
        $status = $this->getDatabaseStatus();
        
        $rows = [];
        foreach ($status as $key => $value) {
            $rows[] = [ucfirst(str_replace('_', ' ', $key)), $value];
        }
        
        $this->table(['Property', 'Value'], $rows);
    }

    /**
     * Reset database (rollback all migrations)
     */
    protected function resetDatabase(): int
    {
        $this->title('Database Reset');
        
        if (!$this->confirm('This will rollback ALL migrations. Are you sure?', false)) {
            $this->info('Reset cancelled.');
            return 0;
        }
        
        try {
            $db = \Core\Database::getInstance();
            
            // Get all ran migrations in reverse order
            $migrations = $db->executeQuery("SELECT migration FROM migrations ORDER BY batch DESC, migration DESC");
            
            if (empty($migrations)) {
                $this->info('No migrations to rollback.');
                return 0;
            }
            
            $this->info('Rolling back migrations...');
            $progressBar = $this->createProgressBar(count($migrations));
            
            foreach ($migrations as $migrationRow) {
                $migration = $migrationRow['migration'];
                $this->rollbackMigration($migration);
                
                // Remove from migrations table
                $db->execute("DELETE FROM migrations WHERE migration = ?", [$migration]);
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            $this->success('Database reset completed successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Database reset failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Rollback a migration
     */
    protected function rollbackMigration(string $migration): void
    {
        $migrationPath = $this->app->basePath("database/migrations/{$migration}.php");
        
        if (!file_exists($migrationPath)) {
            $this->warn("Migration file not found: {$migration}");
            return;
        }

        require_once $migrationPath;
        
        $className = $this->getMigrationClassName($migration);
        
        if (class_exists($className)) {
            $migrationInstance = new $className;
            
            if (method_exists($migrationInstance, 'down')) {
                $migrationInstance->down();
            }
        }

        $this->line("Rolled back: {$migration}");
    }

    /**
     * Fresh migration (reset + migrate)
     */
    protected function freshMigrate(InputInterface $input): int
    {
        $this->title('Fresh Migration');
        
        if (!$this->confirm('This will reset and re-run ALL migrations. Are you sure?', false)) {
            $this->info('Fresh migration cancelled.');
            return 0;
        }
        
        // Reset database
        $resetResult = $this->resetDatabase();
        if ($resetResult !== 0) {
            return $resetResult;
        }
        
        $this->newLine();
        
        // Run migrations again
        return $this->runMigrations($input, $this->output);
    }
}
