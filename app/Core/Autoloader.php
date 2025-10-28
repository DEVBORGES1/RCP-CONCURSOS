<?php
/**
 * Autoloader
 * 
 * Sistema de autoloading personalizado para a aplicação
 * Compatível com PSR-4
 * 
 * @package App\Core
 */

namespace App\Core;

class Autoloader
{
    /**
     * Registra o autoloader
     * 
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            // Base directory
            $baseDir = __DIR__ . '/../../';
            
            // Convert App\Controllers\HomeController to app/Controllers/HomeController.php
            $file = str_replace('App\\', 'app/', $class);
            $file = str_replace('Config\\', 'config/', $file);
            $file = str_replace('\\', '/', $file);
            $file = $baseDir . $file . '.php';
            
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}

