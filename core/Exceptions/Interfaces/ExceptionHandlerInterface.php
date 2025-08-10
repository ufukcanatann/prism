<?php

namespace Core\Exceptions\Interfaces;

use Psr\Log\LoggerInterface;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * Report or log an exception
     */
    public function report(Throwable $exception): void;

    /**
     * Render an exception into an HTTP response
     */
    public function render($request, Throwable $exception);

    /**
     * Render an exception to the console
     */
    public function renderForConsole($output, Throwable $exception): void;

    /**
     * Determine if the exception should be reported
     */
    public function shouldReport(Throwable $exception): bool;

    /**
     * Add exception types that should not be reported
     */
    public function dontReport(array $exceptions): void;

    /**
     * Register a custom exception handler
     */
    public function register(string $exceptionClass, callable $handler): void;
}
