<?php

namespace Core\Database;

abstract class Seeder
{
    /**
     * Run the database seeds.
     */
    abstract public function run(): void;

    /**
     * Call another seeder
     */
    protected function call(string|array $seederClasses): void
    {
        if (is_string($seederClasses)) {
            $seederClasses = [$seederClasses];
        }
        
        foreach ($seederClasses as $seederClass) {
            $seeder = new $seederClass();
            $seeder->run();
        }
    }

    /**
     * Get current timestamp
     */
    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
