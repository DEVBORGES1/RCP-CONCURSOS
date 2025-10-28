<?php
/**
 * Teste do Sistema MVC
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste do Sistema MVC</h1>";

// Testar autoloader
echo "<h2>Testando Autoloader</h2>";

try {
    require_once __DIR__ . '/app/Core/Autoloader.php';
    App\Core\Autoloader::register();
    echo "<p style='color: green;'>✓ Autoloader registrado</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro no autoloader: " . $e->getMessage() . "</p>";
}

// Testar Database
echo "<h2>Testando Database</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Config\Database::getInstance();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✓ Conexão com banco OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na conexão: " . $e->getMessage() . "</p>";
}

// Testar Controllers
echo "<h2>Testando Controllers</h2>";
try {
    $controllers = [
        'App\Core\BaseController',
        'App\Controllers\AuthController',
        'App\Controllers\DashboardController',
        'App\Controllers\HomeController',
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "<p style='color: green;'>✓ {$controller}</p>";
        } else {
            echo "<p style='color: orange;'>⚠ {$controller} não encontrado</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}

// Testar Models
echo "<h2>Testando Models</h2>";
try {
    $models = [
        'App\Models\Usuario',
        'App\Models\Questao',
        'App\Models\Simulado',
        'App\Models\Edital',
        'App\Models\Progresso',
    ];
    
    foreach ($models as $model) {
        if (class_exists($model)) {
            echo "<p style='color: green;'>✓ {$model}</p>";
        } else {
            echo "<p style='color: orange;'>⚠ {$model} não encontrado</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}

// Testar Router
echo "<h2>Testando Router</h2>";
try {
    $router = new App\Core\Router();
    echo "<p style='color: green;'>✓ Router criado</p>";
    
    $router->defineRoutes();
    echo "<p style='color: green;'>✓ Rotas definidas</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}

// Testar Views
echo "<h2>Testando Views</h2>";
$views = [
    'app/Views/layouts/default.php',
    'app/Views/pages/home/index.php',
    'app/Views/pages/auth/login.php',
    'app/Views/pages/auth/register.php',
    'app/Views/pages/dashboard/index.php',
];

foreach ($views as $view) {
    if (file_exists(__DIR__ . '/' . $view)) {
        echo "<p style='color: green;'>✓ {$view}</p>";
    } else {
        echo "<p style='color: red;'>✗ {$view} não encontrado</p>";
    }
}

echo "<hr><p><strong>Teste concluído!</strong></p>";
echo "<p><a href='mvc_index.php'>Tentar acessar mvc_index.php</a></p>";


