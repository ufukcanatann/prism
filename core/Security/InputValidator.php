<?php

namespace Core\Security;

class InputValidator
{
    /**
     * Validation rules
     */
    private array $rules = [];
    
    /**
     * Custom error messages
     */
    private array $messages = [];
    
    /**
     * Validation errors
     */
    private array $errors = [];
    
    /**
     * Custom validation rules
     */
    private static array $customRules = [];
    
    /**
     * Constructor
     */
    public function __construct(array $rules = [], array $messages = [])
    {
        $this->rules = $rules;
        $this->messages = $messages;
    }
    
    /**
     * Validate data against rules
     */
    public function validate(array $data): bool
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rulesString) {
            $rules = $this->parseRules($rulesString);
            $value = $data[$field] ?? null;
            
            foreach ($rules as $rule) {
                if (!$this->validateRule($field, $value, $rule, $data)) {
                    break; // Stop on first error for this field
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Static validation method
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        $validator = new self($rules, $messages);
        $validator->validate($data);
        return $validator;
    }
    
    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }
    
    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Parse rule string into array
     */
    private function parseRules(string $rulesString): array
    {
        return explode('|', $rulesString);
    }
    
    /**
     * Validate single rule
     */
    private function validateRule(string $field, $value, string $rule, array $data): bool
    {
        [$ruleName, $ruleValue] = $this->parseRule($rule);
        
        switch ($ruleName) {
            case 'required':
                return $this->validateRequired($field, $value);
                
            case 'string':
                return $this->validateString($field, $value);
                
            case 'numeric':
                return $this->validateNumeric($field, $value);
                
            case 'integer':
                return $this->validateInteger($field, $value);
                
            case 'email':
                return $this->validateEmail($field, $value);
                
            case 'url':
                return $this->validateUrl($field, $value);
                
            case 'min':
                return $this->validateMin($field, $value, $ruleValue);
                
            case 'max':
                return $this->validateMax($field, $value, $ruleValue);
                
            case 'between':
                return $this->validateBetween($field, $value, $ruleValue);
                
            case 'in':
                return $this->validateIn($field, $value, $ruleValue);
                
            case 'not_in':
                return $this->validateNotIn($field, $value, $ruleValue);
                
            case 'regex':
                return $this->validateRegex($field, $value, $ruleValue);
                
            case 'confirmed':
                return $this->validateConfirmed($field, $value, $data);
                
            case 'unique':
                return $this->validateUnique($field, $value, $ruleValue);
                
            case 'exists':
                return $this->validateExists($field, $value, $ruleValue);
                
            case 'date':
                return $this->validateDate($field, $value);
                
            case 'before':
                return $this->validateBefore($field, $value, $ruleValue);
                
            case 'after':
                return $this->validateAfter($field, $value, $ruleValue);
                
            case 'alpha':
                return $this->validateAlpha($field, $value);
                
            case 'alpha_num':
                return $this->validateAlphaNum($field, $value);
                
            case 'alpha_dash':
                return $this->validateAlphaDash($field, $value);
                
            case 'json':
                return $this->validateJson($field, $value);
                
            case 'ip':
                return $this->validateIp($field, $value);
                
            case 'file':
                return $this->validateFile($field, $value);
                
            case 'image':
                return $this->validateImage($field, $value);
                
            default:
                // Check custom rules
                if (isset(self::$customRules[$ruleName])) {
                    return $this->validateCustom($field, $value, $ruleName, $ruleValue, $data);
                }
                
                return true; // Unknown rule passes
        }
    }
    
    /**
     * Parse individual rule
     */
    private function parseRule(string $rule): array
    {
        if (strpos($rule, ':') !== false) {
            return explode(':', $rule, 2);
        }
        
        return [$rule, null];
    }
    
    /**
     * Add validation error
     */
    private function addError(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->getErrorMessage($field, $rule, $parameters);
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get error message
     */
    private function getErrorMessage(string $field, string $rule, array $parameters = []): string
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }
        
        $defaultMessages = [
            'required' => "The {$field} field is required.",
            'string' => "The {$field} must be a string.",
            'numeric' => "The {$field} must be a number.",
            'integer' => "The {$field} must be an integer.",
            'email' => "The {$field} must be a valid email address.",
            'url' => "The {$field} must be a valid URL.",
            'min' => "The {$field} must be at least {$parameters[0]}.",
            'max' => "The {$field} may not be greater than {$parameters[0]}.",
            'between' => "The {$field} must be between {$parameters[0]} and {$parameters[1]}.",
            'in' => "The selected {$field} is invalid.",
            'not_in' => "The selected {$field} is invalid.",
            'regex' => "The {$field} format is invalid.",
            'confirmed' => "The {$field} confirmation does not match.",
            'unique' => "The {$field} has already been taken.",
            'exists' => "The selected {$field} is invalid.",
            'date' => "The {$field} is not a valid date.",
            'before' => "The {$field} must be a date before {$parameters[0]}.",
            'after' => "The {$field} must be a date after {$parameters[0]}.",
            'alpha' => "The {$field} may only contain letters.",
            'alpha_num' => "The {$field} may only contain letters and numbers.",
            'alpha_dash' => "The {$field} may only contain letters, numbers, dashes and underscores.",
            'json' => "The {$field} must be a valid JSON string.",
            'ip' => "The {$field} must be a valid IP address.",
            'file' => "The {$field} must be a file.",
            'image' => "The {$field} must be an image."
        ];
        
        return $defaultMessages[$rule] ?? "The {$field} is invalid.";
    }
    
    // Validation rule methods
    private function validateRequired(string $field, $value): bool
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
            return false;
        }
        return true;
    }
    
    private function validateString(string $field, $value): bool
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, 'string');
            return false;
        }
        return true;
    }
    
    private function validateNumeric(string $field, $value): bool
    {
        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, 'numeric');
            return false;
        }
        return true;
    }
    
    private function validateInteger(string $field, $value): bool
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'integer');
            return false;
        }
        return true;
    }
    
    private function validateEmail(string $field, $value): bool
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
            return false;
        }
        return true;
    }
    
    private function validateUrl(string $field, $value): bool
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
            return false;
        }
        return true;
    }
    
    private function validateMin(string $field, $value, $min): bool
    {
        if ($value === null) return true;
        
        $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : count($value));
        
        if ($length < $min) {
            $this->addError($field, 'min', [$min]);
            return false;
        }
        return true;
    }
    
    private function validateMax(string $field, $value, $max): bool
    {
        if ($value === null) return true;
        
        $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : count($value));
        
        if ($length > $max) {
            $this->addError($field, 'max', [$max]);
            return false;
        }
        return true;
    }
    
    private function validateBetween(string $field, $value, $range): bool
    {
        if ($value === null) return true;
        
        [$min, $max] = explode(',', $range);
        $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : count($value));
        
        if ($length < $min || $length > $max) {
            $this->addError($field, 'between', [$min, $max]);
            return false;
        }
        return true;
    }
    
    private function validateIn(string $field, $value, $options): bool
    {
        if ($value === null) return true;
        
        $validOptions = explode(',', $options);
        
        if (!in_array($value, $validOptions)) {
            $this->addError($field, 'in');
            return false;
        }
        return true;
    }
    
    private function validateNotIn(string $field, $value, $options): bool
    {
        if ($value === null) return true;
        
        $invalidOptions = explode(',', $options);
        
        if (in_array($value, $invalidOptions)) {
            $this->addError($field, 'not_in');
            return false;
        }
        return true;
    }
    
    private function validateRegex(string $field, $value, $pattern): bool
    {
        if ($value === null) return true;
        
        if (!preg_match($pattern, $value)) {
            $this->addError($field, 'regex');
            return false;
        }
        return true;
    }
    
    private function validateConfirmed(string $field, $value, array $data): bool
    {
        $confirmationField = $field . '_confirmation';
        
        if (!isset($data[$confirmationField]) || $data[$confirmationField] !== $value) {
            $this->addError($field, 'confirmed');
            return false;
        }
        return true;
    }
    
    private function validateUnique(string $field, $value, $table): bool
    {
        if ($value === null) return true;
        
        try {
            $result = \Core\Database::fetch(
                "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?",
                [$value]
            );
            
            if ($result && $result['count'] > 0) {
                $this->addError($field, 'unique');
                return false;
            }
        } catch (\Exception $e) {
            // Database error, assume valid
        }
        
        return true;
    }
    
    private function validateExists(string $field, $value, $table): bool
    {
        if ($value === null) return true;
        
        try {
            $result = \Core\Database::fetch(
                "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?",
                [$value]
            );
            
            if (!$result || $result['count'] == 0) {
                $this->addError($field, 'exists');
                return false;
            }
        } catch (\Exception $e) {
            $this->addError($field, 'exists');
            return false;
        }
        
        return true;
    }
    
    private function validateDate(string $field, $value): bool
    {
        if ($value === null) return true;
        
        if (!strtotime($value)) {
            $this->addError($field, 'date');
            return false;
        }
        return true;
    }
    
    private function validateBefore(string $field, $value, $beforeDate): bool
    {
        if ($value === null) return true;
        
        if (strtotime($value) >= strtotime($beforeDate)) {
            $this->addError($field, 'before', [$beforeDate]);
            return false;
        }
        return true;
    }
    
    private function validateAfter(string $field, $value, $afterDate): bool
    {
        if ($value === null) return true;
        
        if (strtotime($value) <= strtotime($afterDate)) {
            $this->addError($field, 'after', [$afterDate]);
            return false;
        }
        return true;
    }
    
    private function validateAlpha(string $field, $value): bool
    {
        if ($value !== null && !preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, 'alpha');
            return false;
        }
        return true;
    }
    
    private function validateAlphaNum(string $field, $value): bool
    {
        if ($value !== null && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, 'alpha_num');
            return false;
        }
        return true;
    }
    
    private function validateAlphaDash(string $field, $value): bool
    {
        if ($value !== null && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $this->addError($field, 'alpha_dash');
            return false;
        }
        return true;
    }
    
    private function validateJson(string $field, $value): bool
    {
        if ($value !== null) {
            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($field, 'json');
                return false;
            }
        }
        return true;
    }
    
    private function validateIp(string $field, $value): bool
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, 'ip');
            return false;
        }
        return true;
    }
    
    private function validateFile(string $field, $value): bool
    {
        if ($value !== null && (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK)) {
            $this->addError($field, 'file');
            return false;
        }
        return true;
    }
    
    private function validateImage(string $field, $value): bool
    {
        if ($value !== null) {
            if (!$this->validateFile($field, $value)) {
                return false;
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES[$field]['type'] ?? '';
            
            if (!in_array($fileType, $allowedTypes)) {
                $this->addError($field, 'image');
                return false;
            }
        }
        return true;
    }
    
    private function validateCustom(string $field, $value, string $ruleName, $ruleValue, array $data): bool
    {
        $callback = self::$customRules[$ruleName];
        
        if (is_callable($callback)) {
            $result = $callback($field, $value, $ruleValue, $data);
            
            if ($result !== true) {
                $message = is_string($result) ? $result : "The {$field} is invalid.";
                $this->errors[$field][] = $message;
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Add custom validation rule
     */
    public static function addRule(string $name, callable $callback): void
    {
        self::$customRules[$name] = $callback;
    }
    
    /**
     * Get first error for field
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Check if field has errors
     */
    public function has(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}
