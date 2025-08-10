<?php

/**
 * PRISM Framework Installation Script
 */

use Core\Database;
use Core\Config;

try {
    // Load configuration
    Config::load();
    
    $database = Database::getInstance();
    
    echo "Creating database tables...\n";
    
    // Create migrations table
    $sql = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_migration (migration),
        INDEX idx_batch (batch)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Migrations table created\n";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        email_verified_at TIMESTAMP NULL,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Users table created\n";
    
    // Create password_resets table
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Password resets table created\n";
    
    // Create sessions table
    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) NOT NULL PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload LONGTEXT NOT NULL,
        last_activity INT NOT NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_last_activity (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Sessions table created\n";
    
    // Create cache table
    $sql = "CREATE TABLE IF NOT EXISTS cache (
        key VARCHAR(255) NOT NULL PRIMARY KEY,
        value LONGTEXT NOT NULL,
        expiration INT NOT NULL,
        INDEX idx_expiration (expiration)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Cache table created\n";
    
    // Create failed_jobs table
    $sql = "CREATE TABLE IF NOT EXISTS failed_jobs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        uuid VARCHAR(255) NOT NULL UNIQUE,
        connection TEXT NOT NULL,
        queue TEXT NOT NULL,
        payload LONGTEXT NOT NULL,
        exception LONGTEXT NOT NULL,
        failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_uuid (uuid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Failed jobs table created\n";
    
    // Create personal_access_tokens table for API authentication
    $sql = "CREATE TABLE IF NOT EXISTS personal_access_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tokenable_type VARCHAR(255) NOT NULL,
        tokenable_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        abilities TEXT NULL,
        last_used_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tokenable (tokenable_type, tokenable_id),
        INDEX idx_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $database->execute($sql);
    echo "✓ Personal access tokens table created\n";
    
    echo "\nDatabase installation completed successfully!\n";
    
} catch (Exception $e) {
    echo "Database installation failed: " . $e->getMessage() . "\n";
    throw $e;
}