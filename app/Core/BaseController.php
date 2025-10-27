<?php
/**
 * BaseController
 * 
 * Classe base para todos os controllers da aplicação
 * Fornece funcionalidades comuns para todos os controllers
 * 
 * @package App\Core
 */

namespace App\Core;

abstract class BaseController
{
    /**
     * Renderiza uma view
     * 
     * @param string $view Nome da view (sem extensão .php)
     * @param array $data Dados para passar para a view
     * @return string HTML renderizado
     */
    protected function view(string $view, array $data = []): string
    {
        // Extrair variáveis do array $data para escopo local
        extract($data);
        
        // Criar buffer de saída
        ob_start();
        
        // Caminho da view
        $viewPath = __DIR__ . '/../Views/pages/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '{$view}' não encontrada em {$viewPath}");
        }
        
        // Incluir a view
        require $viewPath;
        
        // Retornar conteúdo do buffer
        return ob_get_clean();
    }

    /**
     * Renderiza uma view com layout
     * 
     * @param string $view Nome da view
     * @param array $data Dados para a view
     * @param string $layout Layout a ser usado (padrão: 'default')
     * @return void
     */
    protected function renderWithLayout(string $view, array $data = [], string $layout = 'default'): void
    {
        // Extrair variáveis
        extract($data);
        
        // Conteúdo da view
        $content = $this->view($view, $data);
        
        // Carregar layout
        $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout '{$layout}' não encontrado em {$layoutPath}");
        }
        
        require $layoutPath;
    }

    /**
     * Retorna resposta JSON
     * 
     * @param mixed $data Dados para serializar
     * @param int $statusCode Código de status HTTP
     * @return void
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireciona para uma URL
     * 
     * @param string $url URL para redirecionamento
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Define flash message na sessão
     * 
     * @param string $type Tipo da mensagem (success, error, warning, info)
     * @param string $message Mensagem
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Verifica se o usuário está autenticado
     * 
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Obtém o ID do usuário autenticado
     * 
     * @return int|null
     */
    protected function getUserId(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Requer autenticação para acessar o método
     * 
     * @return void
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
}

