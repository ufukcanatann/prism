<?php

namespace Core;

use Core\Container\Container;
use Core\Events\EventDispatcher;
use Core\Exceptions\Handler;
use Core\Routing\AdvancedRouter;
use Core\View\AdvancedView;
use Core\Providers\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Core\Config;

class Application implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var EventDispatcher
     */
    protected EventDispatcher $events;

    /**
     * @var Handler
     */
    protected Handler $exceptions;

    /**
     * @var AdvancedRouter
     */
    protected AdvancedRouter $router;

    /**
     * @var AdvancedView
     */
    protected AdvancedView $view;

    /**
     * @var array
     */
    protected array $serviceProviders = [];

    /**
     * @var bool
     */
    protected bool $booted = false;

    /**
     * @var Application|null
     */
    protected static ?Application $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->container = Container::getInstance();
        
        // Register core bindings first
        $this->registerCoreBindings();
        
        // Register service providers before creating instances
        $this->registerCoreServiceProviders();
        
        // Now create instances after services are registered
        $this->events = EventDispatcher::getInstance($this->container);
        $this->exceptions = Handler::getInstance($this->container);
        $this->router = AdvancedRouter::getInstance($this->container);
        $this->view = AdvancedView::getInstance($this->container);
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register core bindings
     */
    protected function registerCoreBindings(): void
    {
        $this->container->singleton(Application::class, $this);
        $this->container->singleton(Container::class, $this->container);
        $this->container->singleton('auth', \Core\Auth\Auth::class);
    }

    /**
     * Register core service providers
     */
    protected function registerCoreServiceProviders(): void
    {
        // Register service providers
        $this->register(new \Core\Providers\ConfigServiceProvider($this->container));
        $this->register(new \Core\Providers\EventServiceProvider($this->container));
        $this->register(new \Core\Providers\RouteServiceProvider($this->container));
        $this->register(new \Core\Providers\ViewServiceProvider($this->container));
        $this->register(new \Core\Providers\DatabaseServiceProvider($this->container));
        $this->register(new \Core\Providers\SessionServiceProvider($this->container));
        $this->register(new \Core\Providers\CacheServiceProvider($this->container));
        $this->register(new \Core\Providers\LogServiceProvider($this->container));
        
        // Boot the container to register all services
        $this->container->boot();
    }

    /**
     * Register a service provider
     */
    public function register(ServiceProvider $provider): void
    {
        $this->container->register($provider);
        $this->serviceProviders[] = $provider;
    }

    /**
     * Boot the application
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Boot service providers
        $this->container->boot();

        // Set up global exception handlers
        $this->exceptions->setupGlobalHandlers();

        // Load routes
        $this->loadRoutes();

        $this->booted = true;
    }

    /**
     * Load application routes
     */
    protected function loadRoutes(): void
    {
        $router = $this->router;
        
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            require __DIR__ . '/../routes/web.php';
        }

        if (file_exists(__DIR__ . '/../routes/api.php')) {
            require __DIR__ . '/../routes/api.php';
        }
    }

    /**
     * Handle an incoming HTTP request
     */
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        try {
            $this->boot();

            // Dispatch request events
            $this->events->dispatch(new \Core\Events\RequestReceived($request));

            // Handle the request
            $response = $this->router->dispatch($request);

            // Dispatch response events
            $this->events->dispatch(new \Core\Events\ResponseSent($request, $response));

            return $response;

        } catch (\Throwable $e) {
            if (!$catch) {
                throw $e;
            }

            return $this->exceptions->render($request, $e);
        }
    }

    /**
     * Terminate the application
     */
    public function terminate(Request $request, Response $response): void
    {
        // Dispatch terminate events
        $this->events->dispatch(new \Core\Events\ApplicationTerminated($request, $response));
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    /**
     * Get the container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the event dispatcher
     */
    public function getEvents(): EventDispatcher
    {
        return $this->events;
    }

    /**
     * Get the exception handler
     */
    public function getExceptions(): Handler
    {
        return $this->exceptions;
    }

    /**
     * Get the router
     */
    public function getRouter(): AdvancedRouter
    {
        return $this->router;
    }

    /**
     * Get the view system
     */
    public function getView(): AdvancedView
    {
        return $this->view;
    }

    /**
     * Check if application is booted
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Get all service providers
     */
    public function getServiceProviders(): array
    {
        return $this->serviceProviders;
    }

    /**
     * Get service provider by class
     */
    public function getServiceProvider(string $class): ?ServiceProvider
    {
        foreach ($this->serviceProviders as $provider) {
            if (get_class($provider) === $class) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Check if service provider is registered
     */
    public function hasServiceProvider(string $class): bool
    {
        return $this->getServiceProvider($class) !== null;
    }

    /**
     * Get the application version
     */
    public function version(): string
    {
        return '2.0.0';
    }

    /**
     * Get the application environment
     */
    public function environment(): string
    {
        return Config::get('app.env', 'production');
    }

    /**
     * Check if the application is in debug mode
     */
    public function isDebug(): bool
    {
        return Config::get('app.debug', false);
    }

    /**
     * Check if the application is down for maintenance
     */
    public function isDownForMaintenance(): bool
    {
        return file_exists($this->storagePath('framework/down'));
    }

    /**
     * Get the base path
     */
    public function basePath(string $path = ''): string
    {
        // CLI'den çalıştırıldığında mevcut çalışma dizinini kullan
        if ($this->runningInConsole()) {
            return getcwd() . '/' . ltrim($path, '/');
        }
        
        // Web'den çalıştırıldığında public klasöründen bir seviye yukarı
        return __DIR__ . '/../' . ltrim($path, '/');
    }

    /**
     * Get the config path
     */
    public function configPath(string $path = ''): string
    {
        return $this->basePath('config/' . ltrim($path, '/'));
    }

    /**
     * Get the database path
     */
    public function databasePath(string $path = ''): string
    {
        return $this->basePath('database/' . ltrim($path, '/'));
    }

    /**
     * Get the storage path
     */
    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage/' . ltrim($path, '/'));
    }

    /**
     * Get the resources path
     */
    public function resourcePath(string $path = ''): string
    {
        return $this->basePath('resources/' . ltrim($path, '/'));
    }

    /**
     * Get the public path
     */
    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public/' . ltrim($path, '/'));
    }

    /**
     * Get the app path
     */
    public function appPath(string $path = ''): string
    {
        return $this->basePath('app/' . ltrim($path, '/'));
    }

    /**
     * Get the core path
     */
    public function corePath(string $path = ''): string
    {
        return $this->basePath('core/' . ltrim($path, '/'));
    }

    /**
     * Get the routes path
     */
    public function routesPath(string $path = ''): string
    {
        return $this->basePath('routes/' . ltrim($path, '/'));
    }

    /**
     * Check if the application is running in console
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * Check if the application is running unit tests
     */
    public function runningUnitTests(): bool
    {
        return $this->environment() === 'testing';
    }

    /**
     * Get the application locale
     */
    public function getLocale(): string
    {
        return Config::get('app.locale', 'tr');
    }

    /**
     * Set the application locale
     */
    public function setLocale(string $locale): void
    {
        Config::set('app.locale', $locale);
        setlocale(LC_ALL, $locale);
    }

    /**
     * Get the application timezone
     */
    public function getTimezone(): string
    {
        return Config::get('app.timezone', 'Europe/Istanbul');
    }

    /**
     * Set the application timezone
     */
    public function setTimezone(string $timezone): void
    {
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);
    }

    /**
     * Get the application name
     */
    public function getName(): string
    {
        return Config::get('app.name', 'Kozuva Framework');
    }

    /**
     * Get the application URL
     */
    public function getUrl(): string
    {
        return Config::get('app.url', 'http://127.0.0.1');
    }

    /**
     * Check if the application is in maintenance mode
     */
    public function isInMaintenanceMode(): bool
    {
        return $this->isDownForMaintenance();
    }

    /**
     * Put the application into maintenance mode
     */
    public function down(string $message = null): void
    {
        $data = [
            'time' => time(),
            'message' => $message ?? 'Application is in maintenance mode.',
            'retry' => 60
        ];
        
        file_put_contents($this->storagePath('framework/down'), json_encode($data));
    }

    /**
     * Bring the application out of maintenance mode
     */
    public function up(): void
    {
        if (file_exists($this->storagePath('framework/down'))) {
            unlink($this->storagePath('framework/down'));
        }
    }
}
