<?php

namespace Core\Exceptions;

use Whoops\Handler\Handler;
use Whoops\Exception\Frame;

class AppKeyMissingHandler extends Handler
{
    /**
     * Handle the exception
     */
    public function handle()
    {
        $exception = $this->getException();
        
        // Debug: Hangi exception'ın geldiğini logla
        error_log("AppKeyMissingHandler çalıştı. Exception: " . $exception->getMessage());
        
        // Sadece APP_KEY eksik hatasını yakala
        if (strpos($exception->getMessage(), 'APP_KEY eksik') === false) {
            error_log("APP_KEY hatası değil, Handler::DONE döndürülüyor");
            return Handler::DONE;
        }
        
        error_log("APP_KEY hatası yakalandı, custom HTML gösteriliyor");
        
        // HTML çıktısını oluştur
        $html = $this->generateHtml($exception);
        
        echo $html;
        
        return Handler::QUIT;
    }
    
    /**
     * HTML çıktısını oluştur
     */
    private function generateHtml(\Throwable $exception): string
    {
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>APP_KEY Eksik - PRISM Framework</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #333;
                }
                
                .container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    padding: 40px;
                    max-width: 600px;
                    width: 90%;
                    text-align: center;
                }
                
                .logo {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    border-radius: 50%;
                    margin: 0 auto 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 24px;
                    font-weight: bold;
                }
                
                h1 {
                    color: #e74c3c;
                    margin-bottom: 20px;
                    font-size: 28px;
                }
                
                .description {
                    color: #666;
                    margin-bottom: 30px;
                    line-height: 1.6;
                }
                
                .error-details {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 10px;
                    padding: 20px;
                    margin: 20px 0;
                    text-align: left;
                    font-family: monospace;
                    font-size: 14px;
                    color: #495057;
                }
                
                .generate-btn {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    border-radius: 50px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    margin: 20px 10px;
                }
                
                .generate-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
                }
                
                .generate-btn:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    transform: none;
                }
                
                .cli-btn {
                    background: linear-gradient(135deg, #28a745, #20c997);
                }
                
                .status {
                    margin-top: 20px;
                    padding: 15px;
                    border-radius: 10px;
                    font-weight: 500;
                }
                
                .status.success {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                
                .status.error {
                    background: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
                
                .loading {
                    display: none;
                    margin-top: 20px;
                }
                
                .spinner {
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #667eea;
                    border-radius: 50%;
                    width: 30px;
                    height: 30px;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 10px;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    color: #999;
                    font-size: 14px;
                }
                
                .buttons {
                    display: flex;
                    justify-content: center;
                    flex-wrap: wrap;
                    gap: 10px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="logo">P</div>
                
                <h1>⚠️ APP_KEY Eksik</h1>
                
                <div class="description">
                    <p>Uygulama çalışması için gerekli olan <strong>APP_KEY</strong> parametresi henüz tanımlanmamış.</p>
                    <p>Bu güvenlik anahtarı, uygulamanızın güvenli çalışması için gereklidir.</p>
                </div>
                
                <div class="error-details">
                    <strong>Hata:</strong> ' . htmlspecialchars($exception->getMessage()) . '<br>
                    <strong>Dosya:</strong> ' . htmlspecialchars($exception->getFile()) . '<br>
                    <strong>Satır:</strong> ' . $exception->getLine() . '
                </div>
                
                <div class="buttons">
                    <button class="generate-btn" onclick="generateAppKey()" id="generateBtn">
                        🔑 KEY GENERATE
                    </button>
                    
                    <button class="generate-btn cli-btn" onclick="showCliCommand()">
                        💻 CLI Komutu
                    </button>
                </div>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>APP_KEY generate ediliyor...</p>
                </div>
                
                <div id="status"></div>
                
                <div class="footer">
                    <p>PRISM Framework v3.0.0</p>
                    <p>Bu hata sayfası Whoops tarafından otomatik oluşturuldu</p>
                </div>
            </div>

            <script>
                async function generateAppKey() {
                    const btn = document.getElementById("generateBtn");
                    const loading = document.getElementById("loading");
                    const status = document.getElementById("status");
                    
                    // Butonu devre dışı bırak ve loading göster
                    btn.disabled = true;
                    loading.style.display = "block";
                    status.innerHTML = "";
                    
                    try {
                        // AJAX ile APP_KEY generate et
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
                                    ✅ ${result.message}<br>
                                    <small>Yeni APP_KEY: ${result.key.substring(0, 20)}...</small>
                                </div>
                            `;
                            
                            // 3 saniye sonra sayfayı yenile
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                            
                        } else {
                            status.innerHTML = `
                                <div class="status error">
                                    ❌ ${result.message}
                                </div>
                            `;
                        }
                        
                    } catch (error) {
                        status.innerHTML = `
                            <div class="status error">
                                ❌ Bağlantı hatası: ${error.message}<br>
                                <small>Lütfen CLI komutunu kullanın: <code>php prism app:key:generate</code></small>
                            </div>
                        `;
                    } finally {
                        // Loading\'i gizle ve butonu tekrar aktif et
                        loading.style.display = "none";
                        btn.disabled = false;
                    }
                }
                
                function showCliCommand() {
                    const status = document.getElementById("status");
                    status.innerHTML = `
                        <div class="status success">
                            💻 CLI komutunu kullanın:<br>
                            <code style="background: #f8f9fa; padding: 10px; border-radius: 5px; display: block; margin: 10px 0;">
                                php prism app:key:generate
                            </code>
                            <p>Bu komutu terminal/komut satırında çalıştırın.</p>
                        </div>
                    `;
                }
            </script>
        </body>
        </html>
        ';
    }
}
