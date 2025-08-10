<?php

namespace Core\Events\Interfaces;

interface EventSubscriberInterface
{
    /**
     * Get the events that this subscriber listens to
     */
    public function getSubscribedEvents(): array;
}
