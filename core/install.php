<?php

/**
 * PRISM Framework Kurulum Script'i
 * Bu dosya composer create-project sonrası otomatik olarak çalışır
 */

echo "PRISM Framework Kurulumu Başlatılıyor...\n\n";

// 1. Gerekli dizinleri oluştur
echo "Gerekli dizinler oluşturuluyor...\n";
$directories = [
    'storage/logs',
    'storage/cache',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/uploads/documents',
    'storage/uploads/evidence',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "{$dir} oluşturuldu\n";
    }
}

// 2. .env dosyası oluştur
echo "\n.env dosyası oluşturuluyor...\n";
if (!file_exists('.env')) {
    copy('.env.example', '.env');
    echo ".env dosyası oluşturuldu\n";
} else {
    echo "ℹ.env dosyası zaten mevcut\n";
}

// 3. Application key oluştur
echo "\nApplication key oluşturuluyor...\n";
$envContent = file_get_contents('.env');
if (strpos($envContent, 'APP_KEY=') !== false && strpos($envContent, 'APP_KEY=base64:') === false) {
    $key = 'base64:' . base64_encode(random_bytes(32));
    $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $key, $envContent);
    file_put_contents('.env', $envContent);
    echo "Application key oluşturuldu\n";
} else {
    echo "ℹApplication key zaten mevcut\n";
}

// 4. Veritabanı tabloları oluştur
echo "\nVeritabanı tabloları oluşturuluyor...\n";
try {
    // .env dosyasını manuel olarak parse et
    $envLines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $dbConfig = [];
    
    foreach ($envLines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $dbConfig[trim($key)] = trim($value);
        }
    }
    
    $dbHost = $dbConfig['DB_HOST'] ?? 'localhost';
    $dbPort = $dbConfig['DB_PORT'] ?? '3306';
    $dbName = $dbConfig['DB_DATABASE'] ?? 'prism_framework';
    $dbUser = $dbConfig['DB_USERNAME'] ?? 'root';
    $dbPass = $dbConfig['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName}", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Temel tabloları oluştur
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        'migrations' => "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        )",
        'sessions' => "CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY,
            user_id INT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload TEXT NOT NULL,
            last_activity INT NOT NULL
        )"
    ];
    
    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        echo "{$tableName} tablosu oluşturuldu\n";
    }
    
} catch (PDOException $e) {
    echo "Veritabanı bağlantısı kurulamadı: " . $e->getMessage() . "\n";
    echo "ℹVeritabanı ayarlarını .env dosyasında kontrol edin\n";
}

// 5. Dosya izinlerini ayarla
echo "\n Dosya izinleri ayarlanıyor...\n";
$writableDirs = [
    'storage',
    'bootstrap/cache'
];

foreach ($writableDirs as $dir) {
    if (is_dir($dir)) {
        chmod($dir, 0755);
        echo "{$dir} izinleri ayarlandı\n";
    }
}

// 6. Kurulum tamamlandı
echo "\nPRISM Framework Kurulumu Tamamlandı!\n\n";
echo "Sonraki adımlar:\n";
echo "1. .env dosyasında veritabanı ayarlarını yapın\n";
echo "2. Veritabanını oluşturun\n";
echo "3. Sunucuyu başlatın: php prism system serve\n";
echo "4. Tarayıcıda http://localhost:8000 adresine gidin\n\n";
echo "Başarılı kurulum!\n";