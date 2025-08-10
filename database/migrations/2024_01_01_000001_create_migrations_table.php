<?php

use Core\Database\Migration;

class CreateMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        \Core\Database::getInstance()->execute($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Core\Database::getInstance()->execute("DROP TABLE IF EXISTS migrations");
    }
}
