<?php

namespace Core\Events;

use Core\Events\Interfaces\EventInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestReceived implements EventInterface
{
    public Request $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function getEventName(): string
    {
        return 'request.received';
    }
    
    public function getData(): array
    {
        return ['request' => $this->request];
    }
    
    public function setData(array $data): void
    {
        if (isset($data['request'])) {
            $this->request = $data['request'];
        }
    }
}
