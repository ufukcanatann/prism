<?php

namespace Core\Events\Interfaces;

interface EventDispatcherInterface
{
    /**
     * Add an event listener
     */
    public function addListener(string $eventName, $listener, int $priority = 0): void;

    /**
     * Remove an event listener
     */
    public function removeListener(string $eventName, $listener): void;

    /**
     * Add an event subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void;

    /**
     * Remove an event subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void;

    /**
     * Dispatch an event
     */
    public function dispatch(object $event): object;

    /**
     * Check if event has listeners
     */
    public function hasListeners(string $eventName): bool;

    /**
     * Get all listeners for an event
     */
    public function getListeners(string $eventName): array;

    /**
     * Get all registered event names
     */
    public function getEventNames(): array;

    /**
     * Clear all listeners
     */
    public function clearListeners(string $eventName = null): void;

    /**
     * Get listener count for an event
     */
    public function getListenerCount(string $eventName): int;
}
