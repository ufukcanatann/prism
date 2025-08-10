<?php

namespace Core\View\Interfaces;

interface ViewInterface
{
    /**
     * Register a template engine
     */
    public function registerEngine(string $name, $engine): self;

    /**
     * Set the default template engine
     */
    public function setDefaultEngine(string $name): self;

    /**
     * Get a template engine
     */
    public function getEngine(string $name = null);

    /**
     * Render a view
     */
    public function render(string $view, array $data = []): string;

    /**
     * Check if a view exists
     */
    public function exists(string $view): bool;

    /**
     * Share data with all views
     */
    public function share(string $key, $value): self;

    /**
     * Share data with all views
     */
    public function shareData(array $data): self;

    /**
     * Add a view composer
     */
    public function composer(string $view, callable $callback): self;

    /**
     * Add a view creator
     */
    public function creator(string $view, callable $callback): self;

    /**
     * Run view composers
     */
    public function runComposers(string $view, array $data = []): array;

    /**
     * Run view creators
     */
    public function runCreators(string $view, array $data = []): array;

    /**
     * Get the component manager
     */
    public function getComponentManager();

    /**
     * Get the directive manager
     */
    public function getDirectiveManager();

    /**
     * Get the layout manager
     */
    public function getLayoutManager();

    /**
     * Register a component
     */
    public function component(string $name, $component): self;

    /**
     * Register a directive
     */
    public function directive(string $name, callable $callback): self;

    /**
     * Set a layout
     */
    public function layout(string $name, string $content): self;

    /**
     * Get shared data
     */
    public function getShared(): array;

    /**
     * Clear shared data
     */
    public function clearShared(): self;

    /**
     * Get all registered engines
     */
    public function getEngines(): array;

    /**
     * Get the default engine name
     */
    public function getDefaultEngine(): string;

    /**
     * Cache a view
     */
    public function cache(string $view, array $data = []): string;

    /**
     * Generate cache key
     */
    public function generateCacheKey(string $view, array $data = []): string;

    /**
     * Clear view cache
     */
    public function clearCache(): self;

    /**
     * Get view path
     */
    public function getViewPath(string $view): string;

    /**
     * Get view paths
     */
    public function getViewPaths(): array;
}
