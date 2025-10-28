<?php
/**
 * Bootstrap da Aplicação
 * 
 * Arquivo principal que inicializa toda a aplicação
 * Configura ambiente, autoloading, sessão e inclui dependências
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Ativar exibição de erros em desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Carregar autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Autoloader manual PSR-4
    require_once __DIR__ . '/app/Core/Autoloader.php';
    \App\Core\Autoloader::register();
}

// Carregar configurações
$config = require __DIR__ . '/config/config.php';

// Inicializar Router
use App\Core\Router;

$router = new Router();
$router->defineRoutes();
$router->resolve();

