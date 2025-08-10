<?php

namespace Core\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ClearCacheCommand extends Command
{
    protected static $defaultName = 'system:clear:cache';
    protected static $defaultDescription = 'Clear application cache';

    protected function configure(): void
    {
        $this
            ->setName('system:clear:cache')
            ->setDescription('Clear application cache')
            ->addOption('views', null, InputOption::VALUE_NONE, 'Clear view cache only')
            ->addOption('config', null, InputOption::VALUE_NONE, 'Clear config cache only')
            ->addOption('routes', null, InputOption::VALUE_NONE, 'Clear route cache only');
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $views = $input->getOption('views');
        $config = $input->getOption('config');
        $routes = $input->getOption('routes');

        $cleared = [];

        // If no specific cache specified, clear all
        if (!$views && !$config && !$routes) {
            $this->clearAllCache($cleared);
        } else {
            if ($views) $this->clearViewCache($cleared);
            if ($config) $this->clearConfigCache($cleared);
            if ($routes) $this->clearRouteCache($cleared);
        }

        if (empty($cleared)) {
            $this->warn('No cache to clear.');
            return self::SUCCESS;
        }

        $this->success('Cache cleared successfully!');
        foreach ($cleared as $cache) {
            $this->info("✓ {$cache} cache cleared");
        }

        return self::SUCCESS;
    }

    private function clearAllCache(array &$cleared): void
    {
        $this->clearViewCache($cleared);
        $this->clearConfigCache($cleared);
        $this->clearRouteCache($cleared);
        $this->clearApplicationCache($cleared);
    }

    private function clearViewCache(array &$cleared): void
    {
        $viewCachePath = $this->app->storagePath('framework/views');
        if (is_dir($viewCachePath)) {
            $this->clearDirectory($viewCachePath);
            $cleared[] = 'View';
        }
    }

    private function clearConfigCache(array &$cleared): void
    {
        $configCachePath = $this->app->storagePath('framework/cache/config.php');
        if (file_exists($configCachePath)) {
            unlink($configCachePath);
            $cleared[] = 'Config';
        }
    }

    private function clearRouteCache(array &$cleared): void
    {
        $routeCachePath = $this->app->storagePath('framework/cache/routes.php');
        if (file_exists($routeCachePath)) {
            unlink($routeCachePath);
            $cleared[] = 'Route';
        }
    }

    private function clearApplicationCache(array &$cleared): void
    {
        $appCachePath = $this->app->storagePath('cache');
        if (is_dir($appCachePath)) {
            $this->clearDirectory($appCachePath);
            $cleared[] = 'Application';
        }
    }

    private function clearDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            }
        }
    }
}
