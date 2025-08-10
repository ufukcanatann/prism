<?php

namespace Core\Container\Interfaces;

interface ContainerInterface
{
    /**
     * Bind a service to the container
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void;

    /**
     * Bind a singleton to the container
     */
    public function singleton(string $abstract, $concrete = null): void;

    /**
     * Bind an instance to the container
     */
    public function instance(string $abstract, $instance): void;

    /**
     * Resolve a service from the container
     */
    public function make(string $abstract, array $parameters = []);

    /**
     * Check if service is bound
     */
    public function bound(string $abstract): bool;

    /**
     * Check if service is resolved
     */
    public function resolved(string $abstract): bool;

    /**
     * Call a method on a resolved instance
     */
    public function call($callback, array $parameters = []);

    /**
     * Tag a service
     */
    public function tag(string $abstract, array $tags): void;

    /**
     * Get tagged services
     */
    public function tagged(string $tag): array;
}
