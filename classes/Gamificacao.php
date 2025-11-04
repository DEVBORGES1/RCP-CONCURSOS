<?php
/**
 * Wrapper de compatibilidade - MIGRAR PARA POO
 * 
 * Este arquivo mantém compatibilidade com código antigo.
 * Use GamificacaoRefatorada em novos códigos.
 * 
 * @deprecated Use GamificacaoRefatorada
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/GamificacaoRefatorada.php';

class Gamificacao {
    private $gamificacao;
    
    public function __construct($pdo = null) {
        // Ignorar $pdo, usar Database singleton
        $this->gamificacao = new GamificacaoRefatorada();
    }
    
    public function adicionarPontos($usuario_id, $pontos, $tipo = 'questao') {
        return $this->gamificacao->adicionarPontos($usuario_id, $pontos, $tipo);
    }
    
    public function garantirProgressoUsuario($usuario_id) {
        return $this->gamificacao->garantirProgressoUsuario($usuario_id);
    }
    
    public function atualizarStreak($usuario_id) {
        return $this->gamificacao->atualizarStreak($usuario_id);
    }
    
    public function verificarTodasConquistas($usuario_id) {
        return $this->gamificacao->verificarTodasConquistas($usuario_id);
    }
    
    public function obterDadosUsuario($usuario_id) {
        return $this->gamificacao->obterDadosUsuario($usuario_id);
    }
    
    public function obterConquistasUsuario($usuario_id) {
        return $this->gamificacao->obterConquistasUsuario($usuario_id);
    }
    
    public function obterRankingMensal($limite = 10) {
        return $this->gamificacao->obterRankingMensal($limite);
    }
    
    public function obterPosicaoUsuario($usuario_id) {
        return $this->gamificacao->obterPosicaoUsuario($usuario_id);
    }
}

