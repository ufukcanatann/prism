<?php

namespace Core\Events\Interfaces;

interface EventInterface
{
    /**
     * Get the event name
     */
    public function getEventName(): string;

    /**
     * Get event data
     */
    public function getData(): array;

    /**
     * Set event data
     */
    public function setData(array $data): void;
}
