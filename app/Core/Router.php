<?php
/**
 * Router
 * 
 * Sistema de rotas simples para a aplicação MVC
 * 
 * @package App\Core
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $routeParams = [];

    /**
     * Adiciona uma rota GET
     * 
     * @param string $path Caminho da rota
     * @param string $controller Método do controller (ex: 'HomeController@index')
     * @return self
     */
    public function get(string $path, string $controller): self
    {
        $this->addRoute('GET', $path, $controller);
        return $this;
    }

    /**
     * Adiciona uma rota POST
     * 
     * @param string $path Caminho da rota
     * @param string $controller Método do controller
     * @return self
     */
    public function post(string $path, string $controller): self
    {
        $this->addRoute('POST', $path, $controller);
        return $this;
    }

    /**
     * Adiciona uma rota (método interno)
     * 
     * @param string $method Método HTTP
     * @param string $path Caminho da rota
     * @param string $controller Método do controller
     * @return void
     */
    private function addRoute(string $method, string $path, string $controller): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
        ];
    }

    /**
     * Resolve a requisição atual
     * 
     * @return void
     */
    public function resolve(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Remove query string da URI
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove barra inicial se existir
        $path = ltrim($path, '/');
        
        // Remove base path (RCP-CONCURSOS)
        $path = str_replace('RCP-CONCURSOS/', '', $path);
        $path = str_replace('RCP-CONCURSOS', '', $path);

        // Se estiver vazio, assume index
        if (empty($path) || $path === 'mvc_index.php') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($this->matchRoute($route['path'], $path) && $route['method'] === $requestMethod) {
                $this->executeRoute($route['controller']);
                return;
            }
        }

        // Rota não encontrada - exibir debug
        $this->handle404($path);
    }

    /**
     * Verifica se a rota corresponde ao path
     * 
     * @param string $route Rota definida
     * @param string $path Path atual
     * @return bool
     */
    private function matchRoute(string $route, string $path): bool
    {
        // Conversão simples: 'user/:id' vira regex
        $pattern = preg_replace('/:(\w+)/', '([^/]+)', $route);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remove primeiro match (string completa)
            $this->routeParams = $matches;
            return true;
        }

        return $route === $path;
    }

    /**
     * Executa o controller correspondente à rota
     * 
     * @param string $controller Método do controller (ex: 'HomeController@index')
     * @return void
     */
    private function executeRoute(string $controller): void
    {
        list($controllerClass, $method) = explode('@', $controller);

        $controllerFullClass = "App\\Controllers\\{$controllerClass}";

        if (!class_exists($controllerFullClass)) {
            $this->handle404();
            return;
        }

        $controllerInstance = new $controllerFullClass();

        if (!method_exists($controllerInstance, $method)) {
            $this->handle404();
            return;
        }

        // Passar parâmetros da rota como argumentos
        $controllerInstance->$method(...$this->routeParams);
    }

    /**
     * Manipula erro 404
     * 
     * @param string $path Path que não foi encontrado
     * @return void
     */
    private function handle404(string $path = ''): void
    {
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        echo "<p>A página '{$path}' que você procura não existe.</p>";
        echo "<p><strong>Debug:</strong></p>";
        echo "<pre>";
        echo "Path recebido: " . htmlspecialchars($path) . "\n";
        echo "Rotas disponíveis:\n";
        foreach ($this->routes as $route) {
            echo "  - {$route['method']} {$route['path']}\n";
        }
        echo "</pre>";
    }

    /**
     * Define todas as rotas da aplicação
     * 
     * @return void
     */
    public function defineRoutes(): void
    {
        // Rotas públicas
        $this->get('/', 'HomeController@index');
        $this->get('/login', 'AuthController@login');
        $this->get('/register', 'AuthController@register');
        
        $this->post('/login', 'AuthController@processarLogin');
        $this->post('/register', 'AuthController@processarRegistro');
        
        $this->get('/logout', 'AuthController@logout');

        // Rotas autenticadas
        $this->get('/dashboard', 'DashboardController@index');
    }
}

