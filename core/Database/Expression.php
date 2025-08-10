<?php

namespace Core\Database;

class Expression
{
    /**
     * Raw SQL expression
     */
    private string $expression;
    
    /**
     * Constructor
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }
    
    /**
     * Get expression value
     */
    public function getValue(): string
    {
        return $this->expression;
    }
    
    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->expression;
    }
}
