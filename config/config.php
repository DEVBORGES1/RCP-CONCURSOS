<?php
/**
 * Configuração Principal do Sistema
 * 
 * Arquivo centralizado de configurações para facilitar
 * manutenção e deploy em diferentes ambientes
 */

return [
    // Configurações do Banco de Dados
    'database' => [
        'host' => 'localhost',
        'name' => 'concursos',
        'user' => 'root',
        'password' => '1234',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // Configurações da Aplicação
    'app' => [
        'name' => 'RCP - Sistema de Concursos',
        'version' => '2.0.0',
        'debug' => true,
        'environment' => 'development', // development, production
        'timezone' => 'America/Sao_Paulo',
        'session_lifetime' => 3600 * 24, // 24 horas
    ],

    // Configurações de Upload
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['pdf', 'doc', 'docx'],
        'upload_path' => __DIR__ . '/../uploads/',
    ],

    // Configurações de Gamificação
    'gamification' => [
        'points_per_question' => 10,
        'points_per_simulado' => 50,
        'base_level_points' => 100,
    ],

    // Paths
    'paths' => [
        'root' => __DIR__ . '/../',
        'app' => __DIR__ . '/../app/',
        'views' => __DIR__ . '/../app/Views/',
        'public' => __DIR__ . '/../public/',
        'uploads' => __DIR__ . '/../uploads/',
    ],
];

