<?php

namespace Core\Database;

abstract class Migration
{
    /**
     * Run the migrations
     */
    abstract public function up(): void;
    
    /**
     * Reverse the migrations
     */
    abstract public function down(): void;
}
