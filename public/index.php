<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application;
use Core\Config;

if (Config::get('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (php_sapi_name() !== 'cli') {
        $whoops = new \Whoops\Run;

        // APP_KEY eksik hatası için custom handler'ı önce ekle
        $whoops->pushHandler(new \Core\Exceptions\AppKeyMissingHandler);

        // Normal PrettyPageHandler'ı sonra ekle
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);

        $whoops->register();
    }
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

require_once __DIR__ . '/../core/helpers.php';

/**
 * APP_KEY eksik hatası için HTML sayfası oluştur
 */
function generateAppKeyMissingHtml(): string
{
    return '
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>APP_KEY Eksik - PRISM Framework</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />
        <style>
            :root {
                --primary-color: #0d4073;
                --primary-dark: #0a2f5a;
                --primary-light: #1158a3;
                --secondary-color: #1b1b1d;
                --bg-primary: #0d1117;
                --bg-secondary: #1b1f23;
                --bg-card: rgba(22, 27, 34, 0.8);
                --text-primary: #f0f6fc;
                --text-secondary: #8b949e;
                --text-muted: #6e7681;
                --border-primary: rgba(13, 64, 115, 0.3);
                --border-light: rgba(240, 246, 252, 0.1);
                --shadow-primary: 0 10px 25px rgba(13, 64, 115, 0.2);
                --shadow-lg: 0 20px 25px rgba(0, 0, 0, 0.1);
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background: var(--bg-primary);
                color: var(--text-primary);
                line-height: 1.6;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .error-container {
                background: var(--bg-secondary);
                border: 1px solid var(--border-primary);
                border-radius: 12px;
                box-shadow: var(--shadow-lg);
                max-width: 800px;
                width: 100%;
                overflow: hidden;
            }
            
            .error-header {
                background: var(--primary-color);
                padding: 24px 32px;
                border-bottom: 1px solid var(--border-primary);
                display: flex;
                align-items: center;
                gap: 16px;
            }
            
            .prism-logo {
                width: 48px;
                height: 48px;
                background: var(--text-primary);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--primary-color);
                font-size: 20px;
                font-weight: bold;
                font-family: "Courier New", monospace;
            }
            
            .error-title {
                color: var(--text-primary);
                font-size: 24px;
                font-weight: 600;
            }
            
            .error-content {
                padding: 32px;
            }
            
            .error-description {
                color: var(--text-secondary);
                font-size: 16px;
                margin-bottom: 24px;
                line-height: 1.6;
            }
            
            .error-description strong {
                color: var(--text-primary);
                font-weight: 600;
            }
            
            .solution-section {
                background: var(--bg-card);
                border: 1px solid var(--border-light);
                border-radius: 8px;
                padding: 24px;
                margin-bottom: 24px;
            }
            
            .solution-title {
                color: var(--text-primary);
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .solution-text {
                color: var(--text-secondary);
                margin-bottom: 20px;
                font-size: 14px;
            }
            
            .generate-btn {
                background: var(--primary-color);
                color: var(--text-primary);
                border: none;
                padding: 12px 24px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .generate-btn:hover {
                background: var(--primary-light);
                transform: translateY(-1px);
                box-shadow: var(--shadow-primary);
            }
            
            .generate-btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }
            
            .debug-section {
                background: var(--bg-card);
                border: 1px solid var(--border-light);
                border-radius: 8px;
                padding: 20px;
                margin-top: 24px;
            }
            
            .debug-title {
                color: var(--text-primary);
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .debug-info {
                background: var(--bg-primary);
                border: 1px solid var(--border-light);
                border-radius: 6px;
                padding: 16px;
                font-family: "Courier New", monospace;
                font-size: 13px;
                color: var(--text-secondary);
                overflow-x: auto;
            }
            
            .debug-info .key {
                color: var(--primary-light);
            }
            
            .debug-info .value {
                color: var(--text-primary);
            }
            
            .debug-info .error {
                color: #f56565;
            }
            
            .status {
                margin-top: 20px;
                padding: 16px;
                border-radius: 6px;
                font-weight: 500;
                font-size: 14px;
            }
            
            .status.success {
                background: rgba(34, 197, 94, 0.1);
                color: #22c55e;
                border: 1px solid rgba(34, 197, 94, 0.2);
            }
            
            .status.error {
                background: rgba(239, 68, 68, 0.1);
                color: #ef4444;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }
            
            .loading {
                display: none;
                margin-top: 20px;
                text-align: center;
                color: var(--text-secondary);
            }
            
            .spinner {
                border: 2px solid var(--border-light);
                border-top: 2px solid var(--primary-color);
                border-radius: 50%;
                width: 20px;
                height: 20px;
                animation: spin 1s linear infinite;
                margin: 0 auto 12px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .footer-links {
                margin-top: 24px;
                padding-top: 24px;
                border-top: 1px solid var(--border-light);
                display: flex;
                gap: 24px;
                flex-wrap: wrap;
            }
            
            .footer-link {
                color: var(--text-secondary);
                text-decoration: none;
                font-size: 14px;
                transition: color 0.2s ease;
            }
            
            .footer-link:hover {
                color: var(--primary-light);
            }
            
            @media (max-width: 768px) {
                .error-container {
                    margin: 10px;
                }
                
                .error-header {
                    padding: 20px 24px;
                }
                
                .error-content {
                    padding: 24px 20px;
                }
                
                .footer-links {
                    flex-direction: column;
                    gap: 16px;
                }
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-header">
                <h1 class="error-title">MissingAppKeyException</h1>
            </div>
            
            <div class="error-content">
                <div class="error-description">
                    <p>No application encryption key has been specified.</p>
                    <p>Your app key is missing. Generate your application encryption key using the button below.</p>
                </div>
                
                <div class="solution-section">
                    <h2 class="solution-title">Solution</h2>
                    <p class="solution-text">Click the button below to automatically generate and save your APP_KEY to the .env file.</p>
                    
                    <button class="generate-btn" onclick="generateAppKey()" id="generateBtn">
                        <span><i class="fa-solid fa-key"></i></span>
                        Generate app key
                    </button>
                    
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Generating APP_KEY...</p>
                    </div>
                    
                    <div id="status"></div>
                </div>
                
                <div class="debug-section">
                    <h3 class="debug-title">Debug Information</h3>
                    <div class="debug-info">
                        <div><span class="key">Exception:</span> <span class="value">MissingAppKeyException</span></div>
                        <div><span class="key">Message:</span> <span class="value">No application encryption key has been specified</span></div>
                        <div><span class="key">File:</span> <span class="value">' . __FILE__ . '</span></div>
                        <div><span class="key">Line:</span> <span class="value">' . __LINE__ . '</span></div>
                        <div><span class="key">Time:</span> <span class="value">' . date('Y-m-d H:i:s') . '</span></div>
                        <div><span class="key">Framework:</span> <span class="value">PRISM v3.0.0</span></div>
                        <div><span class="key">PHP Version:</span> <span class="value">' . PHP_VERSION . '</span></div>
                        <div><span class="key">Environment:</span> <span class="value">' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '</span></div>
                    </div>
                </div>
                
                <div class="footer-links">
                    <a href="https://github.com/prism-framework" class="footer-link">GitHub</a>
                    <a href="https://docs.prism-framework.com" class="footer-link">Documentation</a>
                    <a href="https://prism-framework.com" class="footer-link">Website</a>
                </div>
            </div>
        </div>

        <script>
            async function generateAppKey() {
                const btn = document.getElementById("generateBtn");
                const loading = document.getElementById("loading");
                const status = document.getElementById("status");
                
                btn.disabled = true;
                loading.style.display = "block";
                status.innerHTML = "";
                
                try {
                    const response = await fetch("/generate-app-key", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        status.innerHTML = `
                            <div class="status success">
                                ${result.message}<br>
                                <small>New APP_KEY: ${result.key.substring(0, 20)}...</small>
                            </div>
                        `;
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                        
                    } else {
                        status.innerHTML = `
                            <div class="status error">
                                ${result.message}
                            </div>
                        `;
                    }
                    
                } catch (error) {
                    status.innerHTML = `
                        <div class="status error">
                            Connection error: ${error.message}
                        </div>
                    `;
                } finally {
                    loading.style.display = "none";
                    btn.disabled = false;
                }
            }
        </script>
    </body>
    </html>
    ';
}

if (php_sapi_name() !== 'cli') {
    // Allow the generate-app-key route to bypass APP_KEY check
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $isGenerateKeyRequest = strpos($requestUri, '/generate-app-key') !== false;

    if (!$isGenerateKeyRequest && (!file_exists(__DIR__ . '/../.env') ||
        \Core\Helpers\AppKeyGenerator::isAppKeyEmpty())) {

        // Whoops yerine kendi hata sayfamızı göster
        $html = generateAppKeyMissingHtml();
        echo $html;
        exit;
    }
}

$app = Application::getInstance();
$app->run();
