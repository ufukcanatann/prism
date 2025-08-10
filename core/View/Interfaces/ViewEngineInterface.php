<?php

namespace Core\View\Interfaces;

interface ViewEngineInterface
{
    /**
     * Render a view
     */
    public function render(string $view, array $data = []): string;

    /**
     * Check if a view exists
     */
    public function exists(string $view): bool;

    /**
     * Get the view path
     */
    public function getViewPath(string $view): string;

    /**
     * Get view paths
     */
    public function getViewPaths(): array;
}
