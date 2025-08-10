<?php

namespace Core\View;

use Core\Container\Container;
use Core\View\Interfaces\ViewInterface;
use Core\View\Interfaces\ViewEngineInterface;
use Jenssegers\Blade\Blade;
use Core\View\Directives\BladeDirectives;
use Core\View\Helpers\BladeHelpers;

class AdvancedView implements ViewInterface
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var array
     */
    protected array $engines = [];

    /**
     * @var string
     */
    protected string $defaultEngine = 'blade';

    /**
     * @var array
     */
    protected array $shared = [];

    /**
     * @var array
     */
    protected array $composers = [];

    /**
     * @var array
     */
    protected array $creators = [];

    /**
     * @var array
     */
    protected array $components = [];

    /**
     * @var array
     */
    protected array $directives = [];

    /**
     * @var array
     */
    protected array $layouts = [];

    /**
     * @var BladeDirectives
     */
    protected BladeDirectives $bladeDirectives;

    /**
     * @var BladeHelpers
     */
    protected BladeHelpers $bladeHelpers;

    /**
     * @var AdvancedView|null
     */
    protected static ?AdvancedView $instance = null;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->bladeDirectives = new BladeDirectives($container);
        $this->bladeHelpers = new BladeHelpers($container);
        $this->registerDefaultEngines();
        $this->registerDefaultDirectives();
        $this->registerDefaultHelpers();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(Container $container): AdvancedView
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * Register default template engines
     */
    protected function registerDefaultEngines(): void
    {
        // Simple PHP template engine
        $this->registerEngine('php', new SimplePhpEngine());
        
        // Custom Blade template engine
        $customBlade = new \Core\View\CustomBladeEngine(
            $this->getViewPaths(),
            $this->getCachePath(),
            $this->container
        );
        $this->registerEngine('blade', $customBlade);
        
        // Set Blade as default engine
        $this->setDefaultEngine('blade');
    }

    /**
     * Register Blade directives
     */
    protected function registerBladeDirectives($blade): void
    {
        // Custom directives
        $blade->directive('auth', function() {
            return '<?php if (auth()->check()): ?>';
        });

        $blade->directive('endauth', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('guest', function() {
            return '<?php if (!auth()->check()): ?>';
        });

        $blade->directive('endguest', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('csrf', function() {
            return '<?php echo csrf_field(); ?>';
        });

        $blade->directive('method', function($expression) {
            return '<?php echo method_field(' . $expression . '); ?>';
        });

        $blade->directive('asset', function($expression) {
            return '<?php echo asset(' . $expression . '); ?>';
        });

        $blade->directive('url', function($expression) {
            return '<?php echo url(' . $expression . '); ?>';
        });

        $blade->directive('route', function($expression) {
            return '<?php echo route(' . $expression . '); ?>';
        });

        $blade->directive('old', function($expression) {
            return '<?php echo old(' . $expression . '); ?>';
        });

        $blade->directive('has_flash', function($expression) {
            return '<?php echo has_flash(' . $expression . ') ? "is-invalid" : ""; ?>';
        });

        $blade->directive('flash', function($expression) {
            return '<?php echo flash(' . $expression . '); ?>';
        });

        $blade->directive('config', function($expression) {
            return '<?php echo config(' . $expression . '); ?>';
        });

        $blade->directive('env', function($expression) {
            return '<?php echo env(' . $expression . '); ?>';
        });

        $blade->directive('date', function($expression) {
            return '<?php echo date(' . $expression . '); ?>';
        });

        $blade->directive('now', function($expression = '') {
            return '<?php echo now(' . $expression . '); ?>';
        });

        $blade->directive('can', function($expression) {
            return '<?php if (can(' . $expression . ')): ?>';
        });

        $blade->directive('endcan', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('cannot', function($expression) {
            return '<?php if (!can(' . $expression . ')): ?>';
        });

        $blade->directive('endcannot', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('role', function($expression) {
            return '<?php if (has_role(' . $expression . ')): ?>';
        });

        $blade->directive('endrole', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('permission', function($expression) {
            return '<?php if (has_permission(' . $expression . ')): ?>';
        });

        $blade->directive('endpermission', function() {
            return '<?php endif; ?>';
        });

        // Advanced directives
        $blade->directive('cache', function($expression) {
            return '<?php if (!cache_exists(' . $expression . ')): cache_start(' . $expression . '); ?>';
        });

        $blade->directive('endcache', function() {
            return '<?php cache_end(); endif; ?>';
        });

        $blade->directive('include', function($expression) {
            return '<?php echo view(' . $expression . '); ?>';
        });

        $blade->directive('component', function($expression) {
            return '<?php echo component(' . $expression . '); ?>';
        });

        $blade->directive('slot', function($expression) {
            return '<?php echo slot(' . $expression . '); ?>';
        });

        $blade->directive('endslot', function() {
            return '<?php end_slot(); ?>';
        });

        // Form directives
        $blade->directive('form', function($expression) {
            return '<?php echo form_open(' . $expression . '); ?>';
        });

        $blade->directive('endform', function() {
            return '<?php echo form_close(); ?>';
        });

        $blade->directive('input', function($expression) {
            return '<?php echo form_input(' . $expression . '); ?>';
        });

        $blade->directive('textarea', function($expression) {
            return '<?php echo form_textarea(' . $expression . '); ?>';
        });

        $blade->directive('select', function($expression) {
            return '<?php echo form_select(' . $expression . '); ?>';
        });

        $blade->directive('checkbox', function($expression) {
            return '<?php echo form_checkbox(' . $expression . '); ?>';
        });

        $blade->directive('radio', function($expression) {
            return '<?php echo form_radio(' . $expression . '); ?>';
        });

        $blade->directive('submit', function($expression) {
            return '<?php echo form_submit(' . $expression . '); ?>';
        });

        // Validation directives
        $blade->directive('error', function($expression) {
            return '<?php echo error(' . $expression . '); ?>';
        });

        $blade->directive('has_error', function($expression) {
            return '<?php echo has_error(' . $expression . ') ? "is-invalid" : ""; ?>';
        });

        // Session directives
        $blade->directive('session', function($expression) {
            return '<?php echo session(' . $expression . '); ?>';
        });

        $blade->directive('has_session', function($expression) {
            return '<?php echo has_session(' . $expression . '); ?>';
        });

        // Cookie directives
        $blade->directive('cookie', function($expression) {
            return '<?php echo cookie(' . $expression . '); ?>';
        });

        $blade->directive('has_cookie', function($expression) {
            return '<?php echo has_cookie(' . $expression . '); ?>';
        });

        // Request directives
        $blade->directive('request', function($expression) {
            return '<?php echo request(' . $expression . '); ?>';
        });

        $blade->directive('has_request', function($expression) {
            return '<?php echo has_request(' . $expression . '); ?>';
        });

        // Advanced conditional directives
        $blade->directive('if_auth', function($expression) {
            return '<?php if (auth()->check() && ' . $expression . '): ?>';
        });

        $blade->directive('if_guest', function($expression) {
            return '<?php if (!auth()->check() && ' . $expression . '): ?>';
        });

        $blade->directive('if_admin', function() {
            return '<?php if (auth()->check() && auth()->user()->is_admin): ?>';
        });

        $blade->directive('if_user', function($expression) {
            return '<?php if (auth()->check() && auth()->user()->id == ' . $expression . '): ?>';
        });

        // Loop directives
        $blade->directive('foreach', function($expression) {
            return '<?php foreach(' . $expression . '): ?>';
        });

        $blade->directive('endforeach', function() {
            return '<?php endforeach; ?>';
        });

        $blade->directive('for', function($expression) {
            return '<?php for(' . $expression . '): ?>';
        });

        $blade->directive('endfor', function() {
            return '<?php endfor; ?>';
        });

        $blade->directive('while', function($expression) {
            return '<?php while(' . $expression . '): ?>';
        });

        $blade->directive('endwhile', function() {
            return '<?php endwhile; ?>';
        });

        // Utility directives
        $blade->directive('dump', function($expression) {
            return '<?php dump(' . $expression . '); ?>';
        });

        $blade->directive('dd', function($expression) {
            return '<?php dd(' . $expression . '); ?>';
        });

        $blade->directive('var_dump', function($expression) {
            return '<?php var_dump(' . $expression . '); ?>';
        });

        $blade->directive('print_r', function($expression) {
            return '<?php print_r(' . $expression . '); ?>';
        });

        // Security directives
        $blade->directive('e', function($expression) {
            return '<?php echo e(' . $expression . '); ?>';
        });

        $blade->directive('escape', function($expression) {
            return '<?php echo htmlspecialchars(' . $expression . ', ENT_QUOTES, "UTF-8"); ?>';
        });

        $blade->directive('raw', function($expression) {
            return '<?php echo ' . $expression . '; ?>';
        });

        // JSON directives
        $blade->directive('json', function($expression) {
            return '<?php echo json_encode(' . $expression . '); ?>';
        });

        $blade->directive('json_decode', function($expression) {
            return '<?php echo json_decode(' . $expression . ', true); ?>';
        });

        // Array directives
        $blade->directive('count', function($expression) {
            return '<?php echo count(' . $expression . '); ?>';
        });

        $blade->directive('empty', function($expression) {
            return '<?php if (empty(' . $expression . ')): ?>';
        });

        $blade->directive('endempty', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('isset', function($expression) {
            return '<?php if (isset(' . $expression . ')): ?>';
        });

        $blade->directive('endisset', function() {
            return '<?php endif; ?>';
        });

        // String directives
        $blade->directive('strlen', function($expression) {
            return '<?php echo strlen(' . $expression . '); ?>';
        });

        $blade->directive('substr', function($expression) {
            return '<?php echo substr(' . $expression . '); ?>';
        });

        $blade->directive('strtolower', function($expression) {
            return '<?php echo strtolower(' . $expression . '); ?>';
        });

        $blade->directive('strtoupper', function($expression) {
            return '<?php echo strtoupper(' . $expression . '); ?>';
        });

        $blade->directive('ucfirst', function($expression) {
            return '<?php echo ucfirst(' . $expression . '); ?>';
        });

        $blade->directive('ucwords', function($expression) {
            return '<?php echo ucwords(' . $expression . '); ?>';
        });

        // Number directives
        $blade->directive('number_format', function($expression) {
            return '<?php echo number_format(' . $expression . '); ?>';
        });

        $blade->directive('round', function($expression) {
            return '<?php echo round(' . $expression . '); ?>';
        });

        $blade->directive('ceil', function($expression) {
            return '<?php echo ceil(' . $expression . '); ?>';
        });

        $blade->directive('floor', function($expression) {
            return '<?php echo floor(' . $expression . '); ?>';
        });

        // File directives
        $blade->directive('file_exists', function($expression) {
            return '<?php if (file_exists(' . $expression . ')): ?>';
        });

        $blade->directive('endfile_exists', function() {
            return '<?php endif; ?>';
        });

        $blade->directive('file_size', function($expression) {
            return '<?php echo filesize(' . $expression . '); ?>';
        });

        $blade->directive('file_modified', function($expression) {
            return '<?php echo filemtime(' . $expression . '); ?>';
        });

        // Debug directives
        $blade->directive('debug', function($expression) {
            return '<?php if (config("app.debug")): dump(' . $expression . '); endif; ?>';
        });

        $blade->directive('trace', function() {
            return '<?php if (config("app.debug")): echo "<pre>" . print_r(debug_backtrace(), true) . "</pre>"; endif; ?>';
        });

        // Performance directives
        $blade->directive('time', function($expression) {
            return '<?php $start_time = microtime(true); ' . $expression . '; $end_time = microtime(true); echo ($end_time - $start_time) * 1000 . " ms"; ?>';
        });

        $blade->directive('memory', function($expression) {
            return '<?php $start_memory = memory_get_usage(); ' . $expression . '; $end_memory = memory_get_usage(); echo ($end_memory - $start_memory) . " bytes"; ?>';
        });
    }

    /**
     * Register default directives
     */
    protected function registerDefaultDirectives(): void
    {
        // Custom directives will be registered here
    }

    /**
     * Register default helpers
     */
    protected function registerDefaultHelpers(): void
    {
        // Custom helpers will be registered here
    }

    /**
     * Register a template engine
     */
    public function registerEngine(string $name, $engine): self
    {
        $this->engines[$name] = $engine;
        return $this;
    }

    /**
     * Set the default template engine
     */
    public function setDefaultEngine(string $name): self
    {
        if (isset($this->engines[$name])) {
            $this->defaultEngine = $name;
        }
        return $this;
    }

    /**
     * Get a template engine
     */
    public function getEngine(string $name = null)
    {
        $name = $name ?: $this->defaultEngine;
        return $this->engines[$name] ?? null;
    }

    /**
     * Render a view
     */
    public function render(string $view, array $data = []): string
    {
        $engine = $this->getEngine();
        
        if (!$engine) {
            throw new \Exception("No template engine available");
        }

        // Merge shared data
        $data = array_merge($this->shared, $data);

        // Run composers
        $data = $this->runComposers($view, $data);

        // Run creators
        $data = $this->runCreators($view, $data);

        if ($engine instanceof \Jenssegers\Blade\Blade) {
            return $engine->render($view, $data);
        }

        if ($engine instanceof ViewEngineInterface) {
            return $engine->render($view, $data);
        }

        throw new \Exception("Unsupported template engine");
    }

    /**
     * Check if a view exists
     */
    public function exists(string $view): bool
    {
        $engine = $this->getEngine();
        
        if ($engine instanceof ViewEngineInterface) {
            return $engine->exists($view);
        }

        // For Blade engine, check if file exists
        if ($engine instanceof \Jenssegers\Blade\Blade) {
            $viewPath = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $view) . '.blade.php';
            return file_exists($viewPath);
        }

        return false;
    }

    /**
     * Share data with all views
     */
    public function share(string $key, $value): self
    {
        $this->shared[$key] = $value;
        return $this;
    }

    /**
     * Share data with all views
     */
    public function shareData(array $data): self
    {
        $this->shared = array_merge($this->shared, $data);
        return $this;
    }

    /**
     * Add a view composer
     */
    public function composer(string $view, callable $callback): self
    {
        $this->composers[$view] = $callback;
        return $this;
    }

    /**
     * Add a view creator
     */
    public function creator(string $view, callable $callback): self
    {
        $this->creators[$view] = $callback;
        return $this;
    }

    /**
     * Run view composers
     */
    public function runComposers(string $view, array $data = []): array
    {
        if (isset($this->composers[$view])) {
            $callback = $this->composers[$view];
            $composedData = $callback($data);
            
            if (is_array($composedData)) {
                $data = array_merge($data, $composedData);
            }
        }

        return $data;
    }

    /**
     * Run view creators
     */
    public function runCreators(string $view, array $data = []): array
    {
        if (isset($this->creators[$view])) {
            $callback = $this->creators[$view];
            $createdData = $callback($data);
            
            if (is_array($createdData)) {
                $data = array_merge($data, $createdData);
            }
        }

        return $data;
    }

    /**
     * Get the component manager
     */
    public function getComponentManager()
    {
        return $this;
    }

    /**
     * Get the directive manager
     */
    public function getDirectiveManager()
    {
        return $this;
    }

    /**
     * Get the layout manager
     */
    public function getLayoutManager()
    {
        return $this;
    }

    /**
     * Register a component
     */
    public function component(string $name, $component): self
    {
        $this->components[$name] = $component;
        return $this;
    }

    /**
     * Register a directive
     */
    public function directive(string $name, callable $callback): self
    {
        $this->directives[$name] = $callback;
        return $this;
    }

    /**
     * Set a layout
     */
    public function layout(string $name, string $content): self
    {
        $this->layouts[$name] = $content;
        return $this;
    }

    /**
     * Get shared data
     */
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * Clear shared data
     */
    public function clearShared(): self
    {
        $this->shared = [];
        return $this;
    }

    /**
     * Get all registered engines
     */
    public function getEngines(): array
    {
        return $this->engines;
    }

    /**
     * Get the default engine name
     */
    public function getDefaultEngine(): string
    {
        return $this->defaultEngine;
    }

    /**
     * Cache a view
     */
    public function cache(string $view, array $data = []): string
    {
        $cacheKey = $this->generateCacheKey($view, $data);
        
        // Simple file-based caching
        $cachePath = __DIR__ . '/../../storage/cache/views/' . $cacheKey . '.html';
        
        if (file_exists($cachePath)) {
            return file_get_contents($cachePath);
        }

        $content = $this->render($view, $data);
        
        // Ensure cache directory exists
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        file_put_contents($cachePath, $content);
        
        return $content;
    }

    /**
     * Generate cache key
     */
    public function generateCacheKey(string $view, array $data = []): string
    {
        return md5($view . serialize($data));
    }

    /**
     * Clear view cache
     */
    public function clearCache(): self
    {
        $cacheDir = __DIR__ . '/../../storage/cache/views';
        
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        return $this;
    }

    /**
     * Get view path
     */
    public function getViewPath(string $view): string
    {
        return __DIR__ . '/../../resources/views/' . str_replace('.', '/', $view) . '.blade.php';
    }

    /**
     * Get cache path
     */
    public function getCachePath(): string
    {
        return __DIR__ . '/../../storage/framework/cache/views';
    }

    /**
     * Get view paths
     */
    public function getViewPaths(): array
    {
        return [
            __DIR__ . '/../../resources/views'
        ];
    }
}
