<?php

namespace Core\Events;

use Core\Container\Container;
use Core\Events\Interfaces\EventDispatcherInterface;
use Core\Events\Interfaces\EventInterface;
use Core\Events\Interfaces\EventListenerInterface;
use Core\Events\Interfaces\EventSubscriberInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface, PsrEventDispatcherInterface
{
    /**
     * @var array
     */
    protected array $listeners = [];

    /**
     * @var array
     */
    protected array $subscribers = [];

    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var EventDispatcher|null
     */
    protected static ?EventDispatcher $instance = null;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(Container $container): EventDispatcher
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * Add an event listener
     */
    public function addListener(string $eventName, $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority
        ];

        // Sort by priority (higher priority first)
        usort($this->listeners[$eventName], function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }

    /**
     * Remove an event listener
     */
    public function removeListener(string $eventName, $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $key => $item) {
            if ($item['listener'] === $listener) {
                unset($this->listeners[$eventName][$key]);
                break;
            }
        }
    }

    /**
     * Add an event subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->subscribers[] = $subscriber;

        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
            } elseif (is_array($params)) {
                $this->addListener($eventName, [$subscriber, $params[0]], $params[1] ?? 0);
            }
        }
    }

    /**
     * Remove an event subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $key = array_search($subscriber, $this->subscribers, true);
        if ($key !== false) {
            unset($this->subscribers[$key]);
        }

        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->removeListener($eventName, [$subscriber, $params]);
            } elseif (is_array($params)) {
                $this->removeListener($eventName, [$subscriber, $params[0]]);
            }
        }
    }

    /**
     * Dispatch an event
     */
    public function dispatch(object $event): object
    {
        $eventName = $this->getEventName($event);

        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $item) {
                $listener = $item['listener'];

                // If listener is a string, resolve it from container
                if (is_string($listener)) {
                    $listener = $this->container->make($listener);
                }

                // If listener is an array, resolve the object
                if (is_array($listener) && is_string($listener[0])) {
                    $listener[0] = $this->container->make($listener[0]);
                }

                // Call the listener
                if (is_callable($listener)) {
                    $result = call_user_func($listener, $event);
                    
                    // If event is stoppable and propagation is stopped, break
                    if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                        break;
                    }
                }
            }
        }

        return $event;
    }

    /**
     * Get event name from event object
     */
    protected function getEventName(object $event): string
    {
        if ($event instanceof EventInterface) {
            return $event->getEventName();
        }

        return get_class($event);
    }

    /**
     * Check if event has listeners
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName]);
    }

    /**
     * Get all listeners for an event
     */
    public function getListeners(string $eventName): array
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        return array_column($this->listeners[$eventName], 'listener');
    }

    /**
     * Get all registered event names
     */
    public function getEventNames(): array
    {
        return array_keys($this->listeners);
    }

    /**
     * Clear all listeners
     */
    public function clearListeners(string $eventName = null): void
    {
        if ($eventName === null) {
            $this->listeners = [];
            $this->subscribers = [];
        } else {
            unset($this->listeners[$eventName]);
        }
    }

    /**
     * Get listener count for an event
     */
    public function getListenerCount(string $eventName): int
    {
        return isset($this->listeners[$eventName]) ? count($this->listeners[$eventName]) : 0;
    }

    /**
     * Add a wildcard listener
     */
    public function addWildcardListener(string $pattern, $listener, int $priority = 0): void
    {
        $this->addListener('*.' . $pattern, $listener, $priority);
    }

    /**
     * Dispatch event to wildcard listeners
     */
    protected function dispatchWildcardListeners(object $event): void
    {
        $eventName = $this->getEventName($event);

        foreach ($this->listeners as $pattern => $listeners) {
            if (strpos($pattern, '*') === 0) {
                $wildcardPattern = substr($pattern, 2); // Remove '*.' prefix
                if (fnmatch($wildcardPattern, $eventName)) {
                    foreach ($listeners as $item) {
                        $listener = $item['listener'];

                        if (is_string($listener)) {
                            $listener = $this->container->make($listener);
                        }

                        if (is_array($listener) && is_string($listener[0])) {
                            $listener[0] = $this->container->make($listener[0]);
                        }

                        if (is_callable($listener)) {
                            call_user_func($listener, $event);
                        }
                    }
                }
            }
        }
    }
}
