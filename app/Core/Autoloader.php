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
            // Namespace base da aplicação
            $baseDir = __DIR__ . '/../../';
            
            // Converter namespace para caminho de arquivo
            // App\Controllers\AuthController => app/Controllers/AuthController.php
            $path = str_replace('\\', '/', $class);
            
            // Remover prefixo 'App' e 'Config'
            $path = str_replace(['App/', 'Config/'], '', $path);
            
            // Adicionar extensão .php
            $file = $baseDir . strtolower($path) . '.php';
            
            // Se arquivo existe, incluir
            if (file_exists($file)) {
                require_once $file;
                return;
            }
            
            // Tentar caminho absoluto
            $parts = explode('\\', $class);
            $className = array_pop($parts);
            $namespace = implode('\\', $parts);
            
            // App\Controller\HomeController
            // -> app/Controllers/HomeController.php
            if ($namespace === 'App\Controllers') {
                $file = $baseDir . 'app/Controllers/' . $className . '.php';
            } elseif ($namespace === 'App\Models') {
                $file = $baseDir . 'app/Models/' . $className . '.php';
            } elseif ($namespace === 'App\Services') {
                $file = $baseDir . 'app/Services/' . $className . '.php';
            } elseif ($namespace === 'App\Core') {
                $file = $baseDir . 'app/Core/' . $className . '.php';
            } elseif ($namespace === 'Config') {
                $file = $baseDir . 'config/' . $className . '.php';
            }
            
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}

