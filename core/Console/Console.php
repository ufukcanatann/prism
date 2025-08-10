<?php

namespace Core\Console;

use Core\Application;
use Core\Console\Commands\Command;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var SymfonyConsole
     */
    protected SymfonyConsole $console;

    /**
     * @var array
     */
    protected array $commands = [];

    /**
     * Constructor
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->console = new SymfonyConsole('PRISM Framework', '3.0.0');
        
        $this->registerDefaultCommands();
    }

    /**
     * Register default commands
     */
    protected function registerDefaultCommands(): void
    {
        // Register all available commands
        $this->registerCommand(new \Core\Console\Commands\MakeControllerCommand($this->app));
        $this->registerCommand(new \Core\Console\Commands\ServeCommand($this->app));
        $this->registerCommand(new \Core\Console\Commands\ClearCacheCommand($this->app));
        $this->registerCommand(new \Core\Console\Commands\SystemCommands($this->app));
        $this->registerCommand(new \Core\Console\Commands\GeneratorCommands($this->app));
        $this->registerCommand(new \Core\Console\Commands\DatabaseCommands($this->app));
    }

    /**
     * Register commands
     */
    public function registerCommands(array $commands): void
    {
        foreach ($commands as $command) {
            $this->registerCommand(new $command($this->app));
        }
    }

    /**
     * Register a single command
     */
    public function registerCommand(Command $command): void
    {
        $this->console->add($command);
        $this->commands[] = $command;
    }

    /**
     * Run the console application
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        return $this->console->run($input, $output);
    }

    /**
     * Get all registered commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Get the Symfony console instance
     */
    public function getConsole(): SymfonyConsole
    {
        return $this->console;
    }
}
