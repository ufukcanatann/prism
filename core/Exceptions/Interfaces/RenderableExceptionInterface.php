<?php

namespace Core\Exceptions\Interfaces;

use Throwable;

interface RenderableExceptionInterface extends Throwable
{
    /**
     * Render the exception into an HTTP response
     */
    public function render($request);

    /**
     * Render the exception to the console
     */
    public function renderForConsole($output): void;
}
