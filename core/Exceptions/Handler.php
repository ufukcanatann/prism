<?php

namespace Core\Exceptions;

use Core\Container\Container;
use Core\Exceptions\Interfaces\ExceptionHandlerInterface;
use Core\Exceptions\Interfaces\ReportableExceptionInterface;
use Core\Exceptions\Interfaces\RenderableExceptionInterface;
use Core\View\AdvancedView;
use Psr\Log\LoggerInterface;
use Throwable;

class Handler implements ExceptionHandlerInterface
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var array
     */
    protected array $dontReport = [];

    /**
     * @var array
     */
    protected array $dontFlash = [];

    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var Handler|null
     */
    protected static ?Handler $instance = null;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->make(LoggerInterface::class);
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(Container $container): Handler
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * Report or log an exception
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        if ($exception instanceof ReportableExceptionInterface) {
            $exception->report($this->logger);
            return;
        }

        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Render an exception into an HTTP response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof RenderableExceptionInterface) {
            return $exception->render($request);
        }

        // Check for custom handlers
        $handler = $this->getHandler($exception);
        if ($handler) {
            return $handler->handle($request, $exception);
        }

        // Default rendering
        return $this->renderException($request, $exception);
    }

    /**
     * Render an exception to the console
     */
    public function renderForConsole($output, Throwable $exception): void
    {
        if ($exception instanceof RenderableExceptionInterface) {
            $exception->renderForConsole($output);
            return;
        }

        $output->writeln("<error>{$exception->getMessage()}</error>");
        $output->writeln("<comment>File: {$exception->getFile()}:{$exception->getLine()}</comment>");
        $output->writeln("<comment>Trace:</comment>");
        $output->writeln($exception->getTraceAsString());
    }

    /**
     * Determine if the exception should be reported
     */
    public function shouldReport(Throwable $exception): bool
    {
        return !$this->shouldntReport($exception);
    }

    /**
     * Determine if the exception should not be reported
     */
    public function shouldntReport(Throwable $exception): bool
    {
        foreach ($this->dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add exception types that should not be reported
     */
    public function dontReport(array $exceptions): void
    {
        $this->dontReport = array_merge($this->dontReport, $exceptions);
    }

    /**
     * Add exception types that should not be flashed to the session
     */
    public function dontFlash(array $exceptions): void
    {
        $this->dontFlash = array_merge($this->dontFlash, $exceptions);
    }

    /**
     * Register a custom exception handler
     */
    public function register(string $exceptionClass, callable $handler): void
    {
        $this->handlers[$exceptionClass] = $handler;
    }

    /**
     * Get handler for exception
     */
    protected function getHandler(Throwable $exception): ?callable
    {
        $exceptionClass = get_class($exception);

        // Check for exact match
        if (isset($this->handlers[$exceptionClass])) {
            return $this->handlers[$exceptionClass];
        }

        // Check for parent class matches
        foreach ($this->handlers as $class => $handler) {
            if ($exception instanceof $class) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Default exception rendering
     */
    protected function renderException($request, Throwable $exception)
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);

        if ($request->headers->get('Accept') === 'application/json' || 
            $request->headers->get('Content-Type') === 'application/json') {
            return new \Symfony\Component\HttpFoundation\JsonResponse([
                'error' => $message,
                'code' => $statusCode
            ], $statusCode);
        }

        if ($this->container->make('config')->get('app.debug', false)) {
            return $this->renderDebugException($request, $exception);
        }

        return $this->renderProductionException($request, $exception);
    }

    /**
     * Render exception in debug mode
     */
    protected function renderDebugException($request, Throwable $exception)
    {
        // Debug modunda Whoops kullan
        if (class_exists('\Whoops\Run')) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            
            // Whoops'un çıktısını yakala
            ob_start();
            $whoops->handleException($exception);
            $output = ob_get_clean();
            
            return new \Symfony\Component\HttpFoundation\Response($output, 500);
        }
        
        // Whoops yoksa basit HTML
        $data = [
            'exception' => $exception,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'traceAsString' => $exception->getTraceAsString()
        ];

        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Error - Debug Mode</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; }
        .trace { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin-top: 20px; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Error in Debug Mode</h1>
    <div class="error">
        <h2>' . htmlspecialchars($data['message']) . '</h2>
        <p><strong>File:</strong> ' . htmlspecialchars($data['file']) . '</p>
        <p><strong>Line:</strong> ' . $data['line'] . '</p>
    </div>
    <div class="trace">
        <h3>Stack Trace:</h3>
        <pre>' . htmlspecialchars($data['traceAsString']) . '</pre>
    </div>
</body>
</html>';

        return new \Symfony\Component\HttpFoundation\Response($html, 500);
    }

    /**
     * Render exception in production mode
     */
    protected function renderProductionException($request, Throwable $exception)
    {
        $statusCode = $this->getStatusCode($exception);
        $view = $this->getErrorView($statusCode);

        if ($this->container->make(AdvancedView::class)->exists($view)) {
            return new \Symfony\Component\HttpFoundation\Response(
                $this->container->make(AdvancedView::class)->render($view, ['exception' => $exception])
            );
        }

        return new \Symfony\Component\HttpFoundation\Response($this->getDefaultErrorMessage($statusCode), $statusCode);
    }

    /**
     * Get HTTP status code for exception
     */
    protected function getStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        if (method_exists($exception, 'getCode')) {
            $code = $exception->getCode();
            if ($code >= 400 && $code < 600) {
                return $code;
            }
        }

        return 500;
    }

    /**
     * Get message for exception
     */
    protected function getMessage(Throwable $exception): string
    {
        if (method_exists($exception, 'getMessage')) {
            return $exception->getMessage();
        }

        return 'An error occurred';
    }

    /**
     * Get error view for status code
     */
    protected function getErrorView(int $statusCode): string
    {
        return "errors.{$statusCode}";
    }

    /**
     * Get default error message for status code
     */
    protected function getDefaultErrorMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];

        return $messages[$statusCode] ?? 'Server Error';
    }

    /**
     * Handle uncaught exceptions
     */
    public function handleUncaughtException(Throwable $exception): void
    {
        $this->report($exception);

        if (php_sapi_name() === 'cli') {
            $this->renderForConsole(new \Symfony\Component\Console\Output\ConsoleOutput(), $exception);
        } else {
            $this->render(request(), $exception);
        }
    }

    /**
     * Set up global exception handlers
     */
    public function setupGlobalHandlers(): void
    {
        set_exception_handler([$this, 'handleUncaughtException']);
        set_error_handler([$this, 'handleError']);
    }

    /**
     * Handle PHP errors
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        return true;
    }
}
