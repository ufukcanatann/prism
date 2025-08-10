<?php

namespace Core\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends Command
{
    protected static $defaultName = 'system:serve';
    protected static $defaultDescription = 'Serve the application on the PHP development server';

    protected function configure(): void
    {
        $this
            ->setName('system:serve')
            ->setDescription('Serve the application on the PHP development server')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', '8000')
            ->addOption('public', null, InputOption::VALUE_OPTIONAL, 'The path to the public directory', 'public');
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $public = $input->getOption('public');

        $this->info("PRISM Framework development server starting...");
        $this->info("Server: http://{$host}:{$port}");
        $this->info("Document root: {$public}");
        $this->info("Press Ctrl+C to quit.");

        $documentRoot = $this->app->basePath($public);
        $router = $documentRoot . '/router.php';

        // Check if router file exists
        if (!file_exists($router)) {
            $this->error("Router file not found: {$router}");
            return self::FAILURE;
        }

        // Build the command
        $command = sprintf(
            'php -S %s:%s -t %s %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($documentRoot),
            escapeshellarg($router)
        );

        // Execute the command
        $this->info("Starting server...");
        passthru($command, $exitCode);

        return $exitCode;
    }
}
