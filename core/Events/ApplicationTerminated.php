<?php

namespace Core\Events;

use Core\Events\Interfaces\EventInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTerminated implements EventInterface
{
    public Request $request;
    public Response $response;
    
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
    
    public function getEventName(): string
    {
        return 'application.terminated';
    }
    
    public function getData(): array
    {
        return [
            'request' => $this->request,
            'response' => $this->response
        ];
    }
    
    public function setData(array $data): void
    {
        if (isset($data['request'])) {
            $this->request = $data['request'];
        }
        if (isset($data['response'])) {
            $this->response = $data['response'];
        }
    }
}
