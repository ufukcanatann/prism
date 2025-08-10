<?php

namespace Core\Routing\Interfaces;

interface RouteInterface
{
    /**
     * Get route URI
     */
    public function getUri(): string;

    /**
     * Get route methods
     */
    public function getMethods(): array;

    /**
     * Get route handler
     */
    public function getHandler();

    /**
     * Get route options
     */
    public function getOptions(): array;

    /**
     * Get route name
     */
    public function getName(): ?string;

    /**
     * Set route name
     */
    public function name(string $name): self;

    /**
     * Get route middleware
     */
    public function getMiddleware(): array;

    /**
     * Add middleware to route
     */
    public function middleware($middleware): self;

    /**
     * Get route constraints
     */
    public function getConstraints(): array;

    /**
     * Add constraint to route
     */
    public function where(string $parameter, string $pattern): self;
}
