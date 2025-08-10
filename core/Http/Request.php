<?php

namespace Core\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
    /**
     * Validate the request data
     */
    public function validate(array $rules): array
    {
        $data = $this->all();
        $validated = [];
        
        foreach ($rules as $field => $rule) {
            if (isset($data[$field])) {
                $validated[$field] = $data[$field];
            }
        }
        
        return $validated;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return array_merge($this->query->all(), $this->request->all());
    }

    /**
     * Get input by key
     */
    public function input(string $key, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * Check if request has file
     */
    public function hasFile(string $key): bool
    {
        return $this->files->has($key);
    }

    /**
     * Get file by key
     */
    public function file(string $key)
    {
        return $this->files->get($key);
    }
}
