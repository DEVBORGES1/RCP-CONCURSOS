<?php
require_once 'conexao.php';

class SistemaProgressoAvancado {
    private $pdo;
    
    // Configurações do sistema
    private $config = [
        'pontos_por_questao_correta' => 10,
        'pontos_por_simulado_completo' => 50,
        'pontos_por_streak_dia' => 5,
        'pontos_bonus_conquista' => 25,
        'pontos_por_hora_estudo' => 2,
        'multiplicador_nivel' => 1.1
    ];
    
    // Tipos de métricas disponíveis
    private $tipos_metricas = [
        'questoes_respondidas',
        'questoes_corretas', 
        'taxa_acerto',
        'tempo_estudo',
        'simulados_completos',
        'streak_dias',
        'disciplinas_dominadas',
        'pontos_totais',
        'nivel_atual'
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obter dashboard completo de progresso
     */
    public function obterDashboardCompleto($usuario_id) {
        return [
            'resumo_geral' => $this->obterResumoGeral($usuario_id),
            'progresso_por_disciplina' => $this->obterProgressoPorDisciplina($usuario_id),
            'metas_e_objetivos' => $this->obterMetasUsuario($usuario_id),
            'conquistas_recentes' => $this->obterConquistasRecentes($usuario_id),
            'estatisticas_temporais' => $this->obterEstatisticasTemporais($usuario_id),
            'comparacao_ranking' => $this->obterComparacaoRanking($usuario_id),
            'insights_inteligentes' => $this->gerarInsightsInteligentes($usuario_id),
            'proximos_desafios' => $this->sugerirProximosDesafios($usuario_id)
        ];
    }
    
    /**
     * Obter resumo geral do progresso
     */
    public function obterResumoGeral($usuario_id) {
        // Primeiro, obter dados básicos do progresso
        $sql = "SELECT nivel, pontos_total, streak_dias, ultimo_login 
                FROM usuarios_progresso 
                WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $progresso_basico = $stmt->fetch();
        
        // Obter estatísticas de questões
        $sql = "SELECT 
                    COUNT(DISTINCT questao_id) as questoes_unicas_respondidas,
                    COUNT(id) as total_respostas,
                    SUM(CASE WHEN correta = 1 THEN 1 ELSE 0 END) as questoes_corretas,
                    AVG(CASE WHEN correta = 1 THEN 1 ELSE 0 END) * 100 as taxa_acerto
                FROM respostas_usuario 
                WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $estatisticas_questoes = $stmt->fetch();
        
        // Obter estatísticas de simulados
        $sql = "SELECT 
                    COUNT(id) as simulados_completos,
                    SUM(pontuacao_final) as pontos_simulados
                FROM simulados 
                WHERE usuario_id = ? AND questoes_corretas IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $estatisticas_simulados = $stmt->fetch();
        
        // Obter disciplinas estudadas
        $sql = "SELECT COUNT(DISTINCT d.id) as disciplinas_estudadas
                FROM disciplinas d
                INNER JOIN questoes q ON d.id = q.disciplina_id
                INNER JOIN respostas_usuario r ON q.id = r.questao_id
                WHERE r.usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $disciplinas_estudadas = $stmt->fetch();
        
        // Combinar todos os dados com valores padrão
        $dados = array_merge(
            $progresso_basico ?: ['nivel' => 1, 'pontos_total' => 0, 'streak_dias' => 0, 'ultimo_login' => null],
            $estatisticas_questoes ?: ['questoes_unicas_respondidas' => 0, 'total_respostas' => 0, 'questoes_corretas' => 0, 'taxa_acerto' => 0],
            $estatisticas_simulados ?: ['simulados_completos' => 0, 'pontos_simulados' => 0],
            $disciplinas_estudadas ?: ['disciplinas_estudadas' => 0]
        );
        
        // Calcular métricas avançadas
        $dados['pontos_para_proximo_nivel'] = $this->calcularPontosParaProximoNivel($dados['nivel'] ?? 1);
        $dados['progresso_nivel'] = $this->calcularProgressoNivel($dados['pontos_total'] ?? 0, $dados['nivel'] ?? 1);
        $dados['eficiencia_estudo'] = $this->calcularEficienciaEstudo($usuario_id);
        $dados['consistencia_estudo'] = $this->calcularConsistenciaEstudo($usuario_id);
        
        return $dados;
    }
    
    /**
     * Obter progresso detalhado por disciplina
     */
    public function obterProgressoPorDisciplina($usuario_id) {
        $sql = "SELECT 
                    d.nome_disciplina,
                    COUNT(r.id) as questoes_respondidas,
                    SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) as questoes_corretas,
                    AVG(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) * 100 as taxa_acerto,
                    COUNT(DISTINCT DATE(r.data_resposta)) as dias_estudados,
                    MAX(r.data_resposta) as ultimo_estudo,
                    SUM(r.pontos_ganhos) as pontos_disciplina,
                    AVG(TIMESTAMPDIFF(MINUTE, r.data_resposta, r.data_resposta)) as tempo_medio_por_questao
                FROM disciplinas d
                LEFT JOIN questoes q ON d.id = q.disciplina_id
                LEFT JOIN respostas_usuario r ON q.id = r.questao_id AND r.usuario_id = ?
                WHERE d.edital_id IN (
                    SELECT id FROM editais WHERE usuario_id = ?
                )
                GROUP BY d.id, d.nome_disciplina
                HAVING questoes_respondidas > 0
                ORDER BY pontos_disciplina DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id]);
        $disciplinas = $stmt->fetchAll();
        
        // Adicionar análise de domínio por disciplina
        foreach ($disciplinas as &$disciplina) {
            $disciplina['nivel_dominio'] = $this->calcularNivelDominio($disciplina['taxa_acerto'], $disciplina['questoes_respondidas']);
            $disciplina['pontos_necessarios_proximo_nivel'] = $this->calcularPontosProximoNivelDisciplina($disciplina['nivel_dominio']);
            $disciplina['recomendacoes'] = $this->gerarRecomendacoesDisciplina($disciplina);
        }
        
        return $disciplinas;
    }
    
    /**
     * Sistema de metas e objetivos personalizados
     */
    public function obterMetasUsuario($usuario_id) {
        // Metas automáticas baseadas no progresso
        $metas_automaticas = $this->gerarMetasAutomaticas($usuario_id);
        
        // Verificar se a tabela existe antes de consultar
        $metas_personalizadas = [];
        $metas_concluidas = [];
        
        try {
            // Metas personalizadas do usuário
            $sql = "SELECT * FROM metas_usuario WHERE usuario_id = ? AND ativa = 1 ORDER BY data_criacao DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $metas_personalizadas = $stmt->fetchAll();
            
            // Metas concluídas
            $metas_concluidas = $this->obterMetasConcluidas($usuario_id);
            
        } catch (PDOException $e) {
            // Se a tabela não existir, usar arrays vazios
            $metas_personalizadas = [];
            $metas_concluidas = [];
        }
        
        return [
            'automaticas' => $metas_automaticas,
            'personalizadas' => $metas_personalizadas,
            'metas_concluidas' => $metas_concluidas
        ];
    }
    
    /**
     * Gerar metas automáticas baseadas no progresso
     */
    private function gerarMetasAutomaticas($usuario_id) {
        $progresso = $this->obterResumoGeral($usuario_id);
        $metas = [];
        
        // Meta de questões
        $questoes_atual = $progresso['questoes_unicas_respondidas'];
        $proxima_meta_questoes = $this->calcularProximaMeta($questoes_atual, [10, 25, 50, 100, 250, 500, 1000]);
        if ($proxima_meta_questoes) {
            $metas[] = [
                'tipo' => 'questoes',
                'titulo' => "Responder {$proxima_meta_questoes} questões",
                'descricao' => "Você já respondeu {$questoes_atual} questões. Falta " . ($proxima_meta_questoes - $questoes_atual) . " para a próxima meta!",
                'progresso_atual' => $questoes_atual,
                'meta_final' => $proxima_meta_questoes,
                'pontos_recompensa' => $proxima_meta_questoes * 2,
                'prazo_sugerido' => $this->calcularPrazoMeta($questoes_atual, $proxima_meta_questoes, 'questoes')
            ];
        }
        
        // Meta de taxa de acerto
        $taxa_atual = $progresso['taxa_acerto'];
        if ($taxa_atual < 80) {
            $metas[] = [
                'tipo' => 'taxa_acerto',
                'titulo' => "Alcançar 80% de acerto",
                'descricao' => "Sua taxa atual é " . round($taxa_atual, 1) . "%. Melhore sua precisão!",
                'progresso_atual' => $taxa_atual,
                'meta_final' => 80,
                'pontos_recompensa' => 100,
                'prazo_sugerido' => '2 semanas'
            ];
        }
        
        // Meta de streak
        $streak_atual = $progresso['streak_dias'];
        $proxima_meta_streak = $this->calcularProximaMeta($streak_atual, [3, 7, 14, 30, 60, 100]);
        if ($proxima_meta_streak) {
            $metas[] = [
                'tipo' => 'streak',
                'titulo' => "Manter {$proxima_meta_streak} dias de streak",
                'descricao' => "Você está há {$streak_atual} dias estudando. Continue assim!",
                'progresso_atual' => $streak_atual,
                'meta_final' => $proxima_meta_streak,
                'pontos_recompensa' => $proxima_meta_streak * 10,
                'prazo_sugerido' => 'Contínuo'
            ];
        }
        
        return $metas;
    }
    
    /**
     * Obter conquistas recentes e próximas
     */
    public function obterConquistasRecentes($usuario_id) {
        $sql = "SELECT 
                    c.*, 
                    uc.data_conquista,
                    CASE WHEN uc.id IS NOT NULL THEN 1 ELSE 0 END as conquistada
                FROM conquistas c
                LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
                ORDER BY 
                    CASE WHEN uc.id IS NOT NULL THEN uc.data_conquista END DESC,
                    c.pontos_necessarios ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $conquistas = $stmt->fetchAll();
        
        // Separar conquistadas e próximas
        $conquistas_recentes = array_filter($conquistas, function($c) { return $c['conquistada']; });
        $proximas_conquistas = array_filter($conquistas, function($c) { return !$c['conquistada']; });
        
        return [
            'recentes' => array_slice($conquistas_recentes, 0, 5),
            'proximas' => array_slice($proximas_conquistas, 0, 5),
            'total_conquistadas' => count($conquistas_recentes),
            'total_disponiveis' => count($conquistas)
        ];
    }
    
    /**
     * Obter estatísticas temporais (últimos 30 dias)
     */
    public function obterEstatisticasTemporais($usuario_id) {
        $sql = "SELECT 
                    DATE(r.data_resposta) as data_estudo,
                    COUNT(r.id) as questoes_respondidas,
                    SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) as questoes_corretas,
                    AVG(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) * 100 as taxa_acerto_dia,
                    SUM(r.pontos_ganhos) as pontos_dia
                FROM respostas_usuario r
                WHERE r.usuario_id = ? 
                AND r.data_resposta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(r.data_resposta)
                ORDER BY data_estudo DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $dados_diarios = $stmt->fetchAll();
        
        // Calcular tendências
        $tendencias = $this->calcularTendencias($dados_diarios);
        
        return [
            'dados_diarios' => $dados_diarios,
            'tendencias' => $tendencias,
            'melhor_dia' => $this->encontrarMelhorDia($dados_diarios),
            'consistencia_semanal' => $this->calcularConsistenciaSemanal($dados_diarios)
        ];
    }
    
    /**
     * Gerar insights inteligentes baseados no progresso
     */
    public function gerarInsightsInteligentes($usuario_id) {
        $progresso = $this->obterResumoGeral($usuario_id);
        $disciplinas = $this->obterProgressoPorDisciplina($usuario_id);
        $temporais = $this->obterEstatisticasTemporais($usuario_id);
        
        $insights = [];
        
        // Insight sobre taxa de acerto
        if ($progresso['taxa_acerto'] > 85) {
            $insights[] = [
                'tipo' => 'sucesso',
                'titulo' => 'Excelente Performance!',
                'mensagem' => "Sua taxa de acerto de " . round($progresso['taxa_acerto'], 1) . "% está acima da média. Continue assim!",
                'icone' => '🎯',
                'acao_sugerida' => 'Tente questões mais difíceis para se desafiar'
            ];
        } elseif ($progresso['taxa_acerto'] < 60) {
            $insights[] = [
                'tipo' => 'atencao',
                'titulo' => 'Melhore sua Precisão',
                'mensagem' => "Sua taxa de acerto está em " . round($progresso['taxa_acerto'], 1) . "%. Foque em qualidade, não quantidade.",
                'icone' => '📚',
                'acao_sugerida' => 'Revise os conteúdos antes de responder'
            ];
        }
        
        // Insight sobre disciplina mais forte
        if (!empty($disciplinas)) {
            $disciplina_forte = $disciplinas[0];
            $insights[] = [
                'tipo' => 'info',
                'titulo' => 'Sua Disciplina Forte',
                'mensagem' => "Você domina {$disciplina_forte['nome_disciplina']} com " . round($disciplina_forte['taxa_acerto'], 1) . "% de acerto.",
                'icone' => '🏆',
                'acao_sugerida' => 'Use essa força para ajudar em outras disciplinas'
            ];
        }
        
        // Insight sobre consistência
        if ($progresso['streak_dias'] >= 7) {
            $insights[] = [
                'tipo' => 'sucesso',
                'titulo' => 'Hábito Estabelecido!',
                'mensagem' => "Você está há {$progresso['streak_dias']} dias estudando. O hábito está formado!",
                'icone' => '🔥',
                'acao_sugerida' => 'Mantenha a consistência e aumente gradualmente'
            ];
        }
        
        return $insights;
    }
    
    /**
     * Sugerir próximos desafios personalizados
     */
    public function sugerirProximosDesafios($usuario_id) {
        $progresso = $this->obterResumoGeral($usuario_id);
        $disciplinas = $this->obterProgressoPorDisciplina($usuario_id);
        
        $desafios = [];
        
        // Desafio de simulado
        if ($progresso['simulados_completos'] < 5) {
            $desafios[] = [
                'tipo' => 'simulado',
                'titulo' => 'Complete seu primeiro simulado',
                'descricao' => 'Teste seus conhecimentos com um simulado completo',
                'dificuldade' => 'média',
                'pontos_recompensa' => 100,
                'tempo_estimado' => '60 minutos'
            ];
        }
        
        // Desafio de disciplina fraca
        if (!empty($disciplinas)) {
            $disciplina_fraca = end($disciplinas); // Última da lista (menor pontuação)
            if ($disciplina_fraca['taxa_acerto'] < 70) {
                $desafios[] = [
                    'tipo' => 'disciplina',
                    'titulo' => "Melhore em {$disciplina_fraca['nome_disciplina']}",
                    'descricao' => "Foque em estudar mais {$disciplina_fraca['nome_disciplina']} para melhorar sua performance",
                    'dificuldade' => 'alta',
                    'pontos_recompensa' => 150,
                    'tempo_estimado' => '2 semanas'
                ];
            }
        }
        
        return $desafios;
    }
    
    // Métodos auxiliares
    private function calcularPontosParaProximoNivel($nivel_atual) {
        $pontos_nivel_atual = pow($nivel_atual - 1, 2) * 100;
        $pontos_proximo_nivel = pow($nivel_atual, 2) * 100;
        return $pontos_proximo_nivel - $pontos_nivel_atual;
    }
    
    private function calcularProgressoNivel($pontos_total, $nivel_atual) {
        $pontos_nivel_atual = pow($nivel_atual - 1, 2) * 100;
        $pontos_proximo_nivel = pow($nivel_atual, 2) * 100;
        $pontos_necessarios = $pontos_proximo_nivel - $pontos_nivel_atual;
        $pontos_progresso = $pontos_total - $pontos_nivel_atual;
        
        return min(100, max(0, ($pontos_progresso / $pontos_necessarios) * 100));
    }
    
    private function calcularEficienciaEstudo($usuario_id) {
        // Implementar cálculo de eficiência baseado em tempo vs pontos
        return 85; // Placeholder
    }
    
    private function calcularConsistenciaEstudo($usuario_id) {
        // Implementar cálculo de consistência baseado em frequência
        return 78; // Placeholder
    }
    
    private function calcularNivelDominio($taxa_acerto, $questoes_respondidas) {
        if ($questoes_respondidas < 10) return 1;
        if ($taxa_acerto >= 90) return 5;
        if ($taxa_acerto >= 80) return 4;
        if ($taxa_acerto >= 70) return 3;
        if ($taxa_acerto >= 60) return 2;
        return 1;
    }
    
    private function calcularProximaMeta($valor_atual, $metas_possiveis) {
        foreach ($metas_possiveis as $meta) {
            if ($valor_atual < $meta) {
                return $meta;
            }
        }
        return null;
    }
    
    private function calcularTendencias($dados_diarios) {
        // Implementar cálculo de tendências
        return [
            'questoes' => 'crescendo',
            'taxa_acerto' => 'estável',
            'pontos' => 'crescendo'
        ];
    }
    
    private function encontrarMelhorDia($dados_diarios) {
        if (empty($dados_diarios)) return null;
        
        $melhor_dia = $dados_diarios[0];
        foreach ($dados_diarios as $dia) {
            if ($dia['pontos_dia'] > $melhor_dia['pontos_dia']) {
                $melhor_dia = $dia;
            }
        }
        
        return $melhor_dia;
    }
    
    private function calcularConsistenciaSemanal($dados_diarios) {
        // Implementar cálculo de consistência semanal
        return 82; // Placeholder
    }
    
    /**
     * Calcular pontos necessários para próximo nível de disciplina
     */
    private function calcularPontosProximoNivelDisciplina($nivel_atual) {
        $pontos_por_nivel = [
            1 => 50,   // Nível 1 -> 2: 50 pontos
            2 => 100,  // Nível 2 -> 3: 100 pontos
            3 => 200,  // Nível 3 -> 4: 200 pontos
            4 => 400,  // Nível 4 -> 5: 400 pontos
            5 => 0     // Nível máximo
        ];
        
        return $pontos_por_nivel[$nivel_atual] ?? 0;
    }
    
    /**
     * Gerar recomendações para disciplina
     */
    private function gerarRecomendacoesDisciplina($disciplina) {
        $recomendacoes = [];
        
        if ($disciplina['taxa_acerto'] < 60) {
            $recomendacoes[] = "Revise os conceitos básicos antes de responder questões";
        } elseif ($disciplina['taxa_acerto'] < 80) {
            $recomendacoes[] = "Continue praticando para melhorar sua precisão";
        } else {
            $recomendacoes[] = "Excelente performance! Tente questões mais difíceis";
        }
        
        if ($disciplina['questoes_respondidas'] < 10) {
            $recomendacoes[] = "Pratique mais questões para consolidar o conhecimento";
        }
        
        if ($disciplina['dias_estudados'] < 3) {
            $recomendacoes[] = "Estude esta disciplina com mais frequência";
        }
        
        return $recomendacoes;
    }
    
    /**
     * Obter metas concluídas do usuário
     */
    private function obterMetasConcluidas($usuario_id) {
        try {
            $sql = "SELECT * FROM metas_usuario 
                    WHERE usuario_id = ? AND concluida = 1 
                    ORDER BY data_conclusao DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Se a tabela não existir, retornar array vazio
            return [];
        }
    }
    
    /**
     * Calcular prazo para meta
     */
    private function calcularPrazoMeta($valor_atual, $meta_final, $tipo) {
        $diferenca = $meta_final - $valor_atual;
        
        switch ($tipo) {
            case 'questoes':
                $questoes_por_dia = 5; // Estimativa
                $dias = ceil($diferenca / $questoes_por_dia);
                return $dias <= 7 ? "1 semana" : ($dias <= 30 ? "1 mês" : "2 meses");
                
            case 'taxa_acerto':
                return "2 semanas";
                
            case 'streak':
                return "Contínuo";
                
            default:
                return "1 mês";
        }
    }
    
    /**
     * Obter comparação de ranking do usuário
     */
    public function obterComparacaoRanking($usuario_id) {
        try {
            // Obter dados básicos do usuário
            $sql = "SELECT pontos_total, nivel FROM usuarios_progresso WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $dados_usuario = $stmt->fetch();
            
            if (!$dados_usuario) {
                return [
                    'ranking_geral' => ['posicao' => 0, 'total' => 0, 'percentil' => 0],
                    'ranking_nivel' => ['posicao' => 0, 'total' => 0, 'percentil' => 0],
                    'ranking_mensal' => ['posicao' => 0, 'total' => 0, 'percentil' => 0]
                ];
            }
            
            // Ranking geral por pontos
            $sql = "SELECT 
                        COUNT(*) + 1 as posicao_geral,
                        (SELECT COUNT(*) FROM usuarios_progresso) as total_geral
                    FROM usuarios_progresso 
                    WHERE pontos_total > ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$dados_usuario['pontos_total']]);
            $ranking_geral = $stmt->fetch();
            
            // Ranking por nível
            $sql = "SELECT 
                        COUNT(*) + 1 as posicao_nivel,
                        (SELECT COUNT(*) FROM usuarios_progresso WHERE nivel = ?) as total_nivel
                    FROM usuarios_progresso 
                    WHERE nivel = ? AND pontos_total > ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$dados_usuario['nivel'], $dados_usuario['nivel'], $dados_usuario['pontos_total']]);
            $ranking_nivel = $stmt->fetch();
            
            // Ranking mensal
            $mes_ano = date('Y-m');
            $sql = "SELECT 
                        COUNT(*) + 1 as posicao_mensal,
                        (SELECT COUNT(*) FROM ranking_mensal WHERE mes_ano = ?) as total_mensal
                    FROM ranking_mensal 
                    WHERE mes_ano = ? AND pontos_mes > (
                        SELECT pontos_mes FROM ranking_mensal 
                        WHERE usuario_id = ? AND mes_ano = ?
                    )";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$mes_ano, $mes_ano, $usuario_id, $mes_ano]);
            $ranking_mensal = $stmt->fetch();
            
            // Calcular percentis
            $percentil_geral = $ranking_geral['total_geral'] > 0 ? 
                round((($ranking_geral['total_geral'] - $ranking_geral['posicao_geral'] + 1) / $ranking_geral['total_geral']) * 100, 1) : 0;
            
            $percentil_nivel = $ranking_nivel['total_nivel'] > 0 ? 
                round((($ranking_nivel['total_nivel'] - $ranking_nivel['posicao_nivel'] + 1) / $ranking_nivel['total_nivel']) * 100, 1) : 0;
            
            $percentil_mensal = $ranking_mensal['total_mensal'] > 0 ? 
                round((($ranking_mensal['total_mensal'] - $ranking_mensal['posicao_mensal'] + 1) / $ranking_mensal['total_mensal']) * 100, 1) : 0;
            
            return [
                'ranking_geral' => [
                    'posicao' => $ranking_geral['posicao_geral'],
                    'total' => $ranking_geral['total_geral'],
                    'percentil' => $percentil_geral
                ],
                'ranking_nivel' => [
                    'posicao' => $ranking_nivel['posicao_nivel'],
                    'total' => $ranking_nivel['total_nivel'],
                    'percentil' => $percentil_nivel
                ],
                'ranking_mensal' => [
                    'posicao' => $ranking_mensal['posicao_mensal'],
                    'total' => $ranking_mensal['total_mensal'],
                    'percentil' => $percentil_mensal
                ]
            ];
            
        } catch (PDOException $e) {
            // Se houver erro, retornar dados padrão
            return [
                'ranking_geral' => ['posicao' => 0, 'total' => 0, 'percentil' => 0],
                'ranking_nivel' => ['posicao' => 0, 'total' => 0, 'percentil' => 0],
                'ranking_mensal' => ['posicao' => 0, 'total' => 0, 'percentil' => 0]
            ];
        }
    }
    
    /**
     * Obter conquistas recentes e próximas
     */
    public function obterConquistasRecentes($usuario_id) {
        try {
            $sql = "SELECT 
                        c.*, 
                        uc.data_conquista,
                        CASE WHEN uc.id IS NOT NULL THEN 1 ELSE 0 END as conquistada
                    FROM conquistas c
                    LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
                    ORDER BY 
                        CASE WHEN uc.id IS NOT NULL THEN uc.data_conquista END DESC,
                        c.pontos_necessarios ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $conquistas = $stmt->fetchAll();
            
            // Separar conquistadas e próximas
            $conquistas_recentes = array_filter($conquistas, function($c) { return $c['conquistada']; });
            $proximas_conquistas = array_filter($conquistas, function($c) { return !$c['conquistada']; });
            
            return [
                'recentes' => array_slice($conquistas_recentes, 0, 5),
                'proximas' => array_slice($proximas_conquistas, 0, 5),
                'total_conquistadas' => count($conquistas_recentes),
                'total_disponiveis' => count($conquistas)
            ];
            
        } catch (PDOException $e) {
            // Se a tabela não existir, retornar dados padrão
            return [
                'recentes' => [],
                'proximas' => [],
                'total_conquistadas' => 0,
                'total_disponiveis' => 0
            ];
        }
    }
    
    /**
     * Obter estatísticas temporais (últimos 30 dias)
     */
    public function obterEstatisticasTemporais($usuario_id) {
        try {
            $sql = "SELECT 
                        DATE(r.data_resposta) as data_estudo,
                        COUNT(r.id) as questoes_respondidas,
                        SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) as questoes_corretas,
                        AVG(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) * 100 as taxa_acerto_dia,
                        SUM(r.pontos_ganhos) as pontos_dia
                    FROM respostas_usuario r
                    WHERE r.usuario_id = ? 
                    AND r.data_resposta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(r.data_resposta)
                    ORDER BY data_estudo DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $dados_diarios = $stmt->fetchAll();
            
            // Calcular tendências
            $tendencias = $this->calcularTendencias($dados_diarios);
            
            return [
                'dados_diarios' => $dados_diarios,
                'tendencias' => $tendencias,
                'melhor_dia' => $this->encontrarMelhorDia($dados_diarios),
                'consistencia_semanal' => $this->calcularConsistenciaSemanal($dados_diarios)
            ];
            
        } catch (PDOException $e) {
            return [
                'dados_diarios' => [],
                'tendencias' => ['questoes' => 'estável', 'taxa_acerto' => 'estável', 'pontos' => 'estável'],
                'melhor_dia' => null,
                'consistencia_semanal' => 0
            ];
        }
    }
    
    /**
     * Gerar insights inteligentes baseados no progresso
     */
    public function gerarInsightsInteligentes($usuario_id) {
        try {
            $progresso = $this->obterResumoGeral($usuario_id);
            $disciplinas = $this->obterProgressoPorDisciplina($usuario_id);
            
            $insights = [];
            
            // Insight sobre taxa de acerto
            if ($progresso['taxa_acerto'] > 85) {
                $insights[] = [
                    'tipo' => 'sucesso',
                    'titulo' => 'Excelente Performance!',
                    'mensagem' => "Sua taxa de acerto de " . round($progresso['taxa_acerto'], 1) . "% está acima da média. Continue assim!",
                    'icone' => '🎯',
                    'acao_sugerida' => 'Tente questões mais difíceis para se desafiar'
                ];
            } elseif ($progresso['taxa_acerto'] < 60) {
                $insights[] = [
                    'tipo' => 'atencao',
                    'titulo' => 'Melhore sua Precisão',
                    'mensagem' => "Sua taxa de acerto está em " . round($progresso['taxa_acerto'], 1) . "%. Foque em qualidade, não quantidade.",
                    'icone' => '📚',
                    'acao_sugerida' => 'Revise os conteúdos antes de responder'
                ];
            }
            
            // Insight sobre disciplina mais forte
            if (!empty($disciplinas)) {
                $disciplina_forte = $disciplinas[0];
                $insights[] = [
                    'tipo' => 'info',
                    'titulo' => 'Sua Disciplina Forte',
                    'mensagem' => "Você domina {$disciplina_forte['nome_disciplina']} com " . round($disciplina_forte['taxa_acerto'], 1) . "% de acerto.",
                    'icone' => '🏆',
                    'acao_sugerida' => 'Use essa força para ajudar em outras disciplinas'
                ];
            }
            
            // Insight sobre consistência
            if ($progresso['streak_dias'] >= 7) {
                $insights[] = [
                    'tipo' => 'sucesso',
                    'titulo' => 'Hábito Estabelecido!',
                    'mensagem' => "Você está há {$progresso['streak_dias']} dias estudando. O hábito está formado!",
                    'icone' => '🔥',
                    'acao_sugerida' => 'Mantenha a consistência e aumente gradualmente'
                ];
            }
            
            return $insights;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Sugerir próximos desafios personalizados
     */
    public function sugerirProximosDesafios($usuario_id) {
        try {
            $progresso = $this->obterResumoGeral($usuario_id);
            $disciplinas = $this->obterProgressoPorDisciplina($usuario_id);
            
            $desafios = [];
            
            // Desafio de simulado
            if ($progresso['simulados_completos'] < 5) {
                $desafios[] = [
                    'tipo' => 'simulado',
                    'titulo' => 'Complete seu primeiro simulado',
                    'descricao' => 'Teste seus conhecimentos com um simulado completo',
                    'dificuldade' => 'média',
                    'pontos_recompensa' => 100,
                    'tempo_estimado' => '60 minutos'
                ];
            }
            
            // Desafio de disciplina fraca
            if (!empty($disciplinas)) {
                $disciplina_fraca = end($disciplinas); // Última da lista (menor pontuação)
                if ($disciplina_fraca['taxa_acerto'] < 70) {
                    $desafios[] = [
                        'tipo' => 'disciplina',
                        'titulo' => "Melhore em {$disciplina_fraca['nome_disciplina']}",
                        'descricao' => "Foque em estudar mais {$disciplina_fraca['nome_disciplina']} para melhorar sua performance",
                        'dificuldade' => 'alta',
                        'pontos_recompensa' => 150,
                        'tempo_estimado' => '2 semanas'
                    ];
                }
            }
            
            return $desafios;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
