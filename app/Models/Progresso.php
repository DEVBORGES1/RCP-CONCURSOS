<?php
/**
 * Progresso Model
 * 
 * Model responsável por operações com a tabela usuarios_progresso
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\BaseModel;

class Progresso extends BaseModel
{
    protected string $table = 'usuarios_progresso';

    /**
     * Inicializa ou obtém progresso do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array
     */
    public function obterOuCriar(int $usuarioId): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :usuario_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            
            $progresso = $stmt->fetch();
            
            if (!$progresso) {
                // Criar progresso inicial
                $this->create([
                    'usuario_id' => $usuarioId,
                    'nivel' => 1,
                    'pontos_total' => 0,
                    'streak_dias' => 0,
                    'ultimo_login' => date('Y-m-d')
                ]);
                
                return $this->obter($usuarioId);
            }
            
            return $progresso;
        } catch (\PDOException $e) {
            error_log("Erro ao obter ou criar progresso: " . $e->getMessage());
            
            // Retornar valores padrão
            return [
                'id' => 0,
                'usuario_id' => $usuarioId,
                'nivel' => 1,
                'pontos_total' => 0,
                'streak_dias' => 0,
                'ultimo_login' => null
            ];
        }
    }

    /**
     * Obtém progresso do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array|null
     */
    public function obter(int $usuarioId): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :usuario_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            
            return $stmt->fetch() ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao obter progresso: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Adiciona pontos ao usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param int $pontos Pontos para adicionar
     * @return bool
     */
    public function adicionarPontos(int $usuarioId, int $pontos): bool
    {
        $progresso = $this->obterOuCriar($usuarioId);
        
        $novosPontos = $progresso['pontos_total'] + $pontos;
        $novoNivel = $this->calcularNivel($novosPontos);
        
        return $this->update($progresso['id'], [
            'pontos_total' => $novosPontos,
            'nivel' => $novoNivel
        ]);
    }

    /**
     * Atualiza streak do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array Progresso atualizado
     */
    public function atualizarStreak(int $usuarioId): array
    {
        $progresso = $this->obterOuCriar($usuarioId);
        $hoje = date('Y-m-d');
        
        // Se o último login foi ontem ou hoje, incrementar streak
        if ($progresso['ultimo_login']) {
            $ultimoLogin = new \DateTime($progresso['ultimo_login']);
            $hojeObj = new \DateTime($hoje);
            $diferenca = $hojeObj->diff($ultimoLogin)->days;
            
            if ($diferenca == 0) {
                // Já atualizou hoje
                return $progresso;
            } elseif ($diferenca == 1) {
                // Streak continua
                $novoStreak = $progresso['streak_dias'] + 1;
            } else {
                // Streak quebrado
                $novoStreak = 1;
            }
        } else {
            // Primeiro acesso
            $novoStreak = 1;
        }
        
        $this->update($progresso['id'], [
            'streak_dias' => $novoStreak,
            'ultimo_login' => $hoje
        ]);
        
        return $this->obter($usuarioId);
    }

    /**
     * Calcula o nível baseado nos pontos
     * 
     * @param int $pontos Total de pontos
     * @return int Nível
     */
    public function calcularNivel(int $pontos): int
    {
        // Fórmula: nível = floor(sqrt(pontos / 100)) + 1
        return floor(sqrt($pontos / 100)) + 1;
    }
}

