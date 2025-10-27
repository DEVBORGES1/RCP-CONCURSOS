<?php
/**
 * Usuario Model
 * 
 * Model responsável por operações com a tabela usuarios
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Usuario extends BaseModel
{
    protected string $table = 'usuarios';

    /**
     * Busca usuário por email
     * 
     * @param string $email Email do usuário
     * @return array|null Dados do usuário ou null
     */
    public function findByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar usuário por email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria um novo usuário
     * 
     * @param string $nome Nome do usuário
     * @param string $email Email do usuário
     * @param string $senha Senha em texto plano
     * @return int|false ID do usuário criado ou false
     */
    public function criar(string $nome, string $email, string $senha)
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        return $this->create([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => $senhaHash
        ]);
    }

    /**
     * Verifica se as credenciais são válidas
     * 
     * @param string $email Email do usuário
     * @param string $senha Senha em texto plano
     * @return array|null Dados do usuário ou null se inválido
     */
    public function verificarCredenciais(string $email, string $senha): ?array
    {
        $usuario = $this->findByEmail($email);
        
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            return $usuario;
        }
        
        return null;
    }

    /**
     * Atualiza a senha do usuário
     * 
     * @param int $id ID do usuário
     * @param string $novaSenha Nova senha em texto plano
     * @return bool Sucesso da operação
     */
    public function atualizarSenha(int $id, string $novaSenha): bool
    {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        return $this->update($id, [
            'senha_hash' => $senhaHash
        ]);
    }

    /**
     * Obtém dados completos do usuário incluindo progresso
     * 
     * @param int $id ID do usuário
     * @return array|null Dados completos
     */
    public function obterDadosCompletos(int $id): ?array
    {
        try {
            $sql = "
                SELECT 
                    u.*,
                    up.nivel,
                    up.pontos_total,
                    up.streak_dias,
                    up.ultimo_login,
                    (
                        SELECT COUNT(*) 
                        FROM respostas_usuario 
                        WHERE usuario_id = u.id
                    ) as questoes_respondidas,
                    (
                        SELECT COUNT(*) 
                        FROM respostas_usuario 
                        WHERE usuario_id = u.id AND correta = 1
                    ) as questoes_corretas,
                    (
                        SELECT COUNT(*) 
                        FROM simulados 
                        WHERE usuario_id = u.id
                    ) as total_simulados
                FROM usuarios u
                LEFT JOIN usuarios_progresso up ON up.usuario_id = u.id
                WHERE u.id = :id
                LIMIT 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch() ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao obter dados completos: " . $e->getMessage());
            return null;
        }
    }
}

