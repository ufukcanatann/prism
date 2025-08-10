<?php

namespace Core\Helpers;

class AppKeyGenerator
{
    /**
     * .env dosyasından APP_KEY'i oku
     */
    public static function getCurrentAppKey(): ?string
    {
        $envFile = self::getEnvFilePath();
        if (!file_exists($envFile)) {
            return null;
        }
        
        $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($envLines as $line) {
            if (strpos($line, 'APP_KEY=') === 0 && !str_starts_with(trim($line), '#')) {
                $appKey = trim(substr($line, 8)); // APP_KEY= kısmını çıkar
                return empty($appKey) ? null : $appKey;
            }
        }
        
        return null;
    }
    
    /**
     * APP_KEY'in boş olup olmadığını kontrol et
     */
    public static function isAppKeyEmpty(): bool
    {
        $appKey = self::getCurrentAppKey();
        return empty($appKey) || trim($appKey) === '';
    }
    
    /**
     * Yeni APP_KEY generate et ve .env dosyasına yaz
     */
    public static function generateAndSave(): array
    {
        try {
            $envFile = self::getEnvFilePath();
            if (!file_exists($envFile)) {
                return ['success' => false, 'message' => '.env dosyası bulunamadı'];
            }
            
            $envContent = file_get_contents($envFile);
            
            // Yeni APP_KEY generate et
            $newKey = 'base64:' . base64_encode(random_bytes(32));
            
            // APP_KEY'i güncelle
            if (strpos($envContent, 'APP_KEY=') !== false) {
                $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $newKey, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $newKey;
            }
            
            // .env dosyasına yaz
            if (file_put_contents($envFile, $envContent)) {
                return [
                    'success' => true, 
                    'message' => 'APP_KEY başarıyla generate edildi', 
                    'key' => $newKey
                ];
            } else {
                return ['success' => false, 'message' => 'APP_KEY yazılamadı'];
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Hata: ' . $e->getMessage()];
        }
    }
    
    /**
     * .env dosyasının tam yolunu al
     */
    private static function getEnvFilePath(): string
    {
        return __DIR__ . '/../../.env';
    }
    
    /**
     * APP_KEY generate işlemini CLI'dan çalıştır
     */
    public static function generateFromCli(): void
    {
        echo "APP_KEY generate ediliyor...\n";
        
        $result = self::generateAndSave();
        
        if ($result['success']) {
            echo "✅ " . $result['message'] . "\n";
            echo "Yeni APP_KEY: " . $result['key'] . "\n";
        } else {
            echo "❌ " . $result['message'] . "\n";
        }
    }
}
