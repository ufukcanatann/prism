<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application;
use Core\Config;

// Hata ayıklama
if (Config::get('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Whoops'u sadece web ortamında yükle
    if (php_sapi_name() !== 'cli') {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Helpers'ı yükle
require_once __DIR__ . '/../core/helpers.php';

// Uygulamayı başlat
$app = Application::getInstance();

// Uygulamayı çalıştır
$app->run();
