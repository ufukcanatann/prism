<?php

namespace Core\View;

class SimplePhpEngine
{
    protected string $viewPath;
    protected string $cachePath;

    public function __construct()
    {
        $this->viewPath = __DIR__ . '/../../resources/views';
        $this->cachePath = __DIR__ . '/../../storage/cache/views';
    }

    public function render(string $view, array $data = []): string
    {
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View not found: {$view}");
        }

        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        include $viewFile;

        // Get the content and clean the buffer
        $content = ob_get_clean();

        return $content;
    }

    public function exists(string $view): bool
    {
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        return file_exists($viewFile);
    }
}
