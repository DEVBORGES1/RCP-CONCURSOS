<?php
require_once 'conexao.php';

class GeradorCronograma {
    private $pdo;
    
    // Dias da semana em português
    private $dias_semana = [
        'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 
        'Sexta-feira', 'Sábado', 'Domingo'
    ];
    
    // Horários sugeridos por disciplina
    private $horarios_sugeridos = [
        'Português' => ['08:00', '14:00', '19:00'],
        'Matemática' => ['09:00', '15:00', '20:00'],
        'Raciocínio Lógico' => ['10:00', '16:00'],
        'Informática' => ['11:00', '17:00'],
        'Direito Constitucional' => ['08:30', '14:30'],
        'Direito Administrativo' => ['09:30', '15:30'],
        'Atualidades' => ['07:00', '18:00'],
        'História' => ['13:00', '19:30'],
        'Geografia' => ['12:00', '18:30']
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Gera cronograma personalizado baseado no edital e horas disponíveis
     */
    public function gerarCronograma($usuario_id, $edital_id, $horas_por_dia, $data_inicio, $duracao_semanas = 4) {
        try {
            $this->pdo->beginTransaction();
            
            // Obter disciplinas do edital
            $disciplinas = $this->obterDisciplinasEdital($edital_id);
            
            if (empty($disciplinas)) {
                throw new Exception("Nenhuma disciplina encontrada para este edital.");
            }
            
            // Calcular datas
            $data_fim = date('Y-m-d', strtotime($data_inicio . " +{$duracao_semanas} weeks"));
            
            // Criar cronograma principal
            $cronograma_id = $this->criarCronogramaPrincipal($usuario_id, $edital_id, $data_inicio, $data_fim, $horas_por_dia);
            
            // Gerar distribuição de disciplinas
            $distribuicao = $this->distribuirDisciplinas($disciplinas, $horas_por_dia, $duracao_semanas);
            
            // Criar cronograma detalhado
            $this->criarCronogramaDetalhado($cronograma_id, $distribuicao, $data_inicio, $duracao_semanas);
            
            $this->pdo->commit();
            
            return [
                'sucesso' => true,
                'cronograma_id' => $cronograma_id,
                'disciplinas' => $disciplinas,
                'distribuicao' => $distribuicao,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtém disciplinas do edital
     */
    private function obterDisciplinasEdital($edital_id) {
        $sql = "SELECT d.*, COUNT(q.id) as total_questoes 
                FROM disciplinas d 
                LEFT JOIN questoes q ON d.id = q.disciplina_id 
                WHERE d.edital_id = ? 
                GROUP BY d.id 
                ORDER BY d.nome_disciplina";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$edital_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Cria cronograma principal
     */
    private function criarCronogramaPrincipal($usuario_id, $edital_id, $data_inicio, $data_fim, $horas_por_dia) {
        $sql = "INSERT INTO cronogramas (usuario_id, edital_id, data_inicio, data_fim, horas_por_dia) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $edital_id, $data_inicio, $data_fim, $horas_por_dia]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Distribui disciplinas ao longo das semanas
     */
    private function distribuirDisciplinas($disciplinas, $horas_por_dia, $duracao_semanas) {
        $total_dias = $duracao_semanas * 7;
        $total_horas = $total_dias * $horas_por_dia;
        
        // Calcular peso de cada disciplina baseado no número de questões
        $pesos = [];
        $total_questoes = 0;
        
        foreach ($disciplinas as $disciplina) {
            $questoes = max(1, $disciplina['total_questoes']); // Mínimo 1 questão
            $pesos[$disciplina['id']] = $questoes;
            $total_questoes += $questoes;
        }
        
        // Distribuir horas proporcionalmente
        $distribuicao = [];
        foreach ($disciplinas as $disciplina) {
            $peso = $pesos[$disciplina['id']];
            $horas_disciplina = round(($peso / $total_questoes) * $total_horas);
            
            $distribuicao[$disciplina['id']] = [
                'disciplina' => $disciplina,
                'horas_total' => $horas_disciplina,
                'horas_por_sessao' => $this->calcularHorasPorSessao($horas_disciplina, $duracao_semanas),
                'sessoes_por_semana' => $this->calcularSessoesPorSemana($horas_disciplina, $duracao_semanas)
            ];
        }
        
        return $distribuicao;
    }
    
    /**
     * Calcula horas por sessão de estudo
     */
    private function calcularHorasPorSessao($horas_total, $semanas) {
        $sessoes_totais = $semanas * 5; // 5 dias úteis por semana
        return max(0.5, round($horas_total / $sessoes_totais, 1));
    }
    
    /**
     * Calcula sessões por semana
     */
    private function calcularSessoesPorSemana($horas_total, $semanas) {
        $horas_por_sessao = $this->calcularHorasPorSessao($horas_total, $semanas);
        return max(1, round($horas_total / ($semanas * $horas_por_sessao)));
    }
    
    /**
     * Cria cronograma detalhado dia a dia
     */
    private function criarCronogramaDetalhado($cronograma_id, $distribuicao, $data_inicio, $duracao_semanas) {
        $data_atual = $data_inicio;
        $dia_semana = 0;
        
        for ($semana = 0; $semana < $duracao_semanas; $semana++) {
            for ($dia = 0; $dia < 7; $dia++) {
                // Pular domingos ou ajustar conforme preferência
                if ($dia == 6) { // Domingo
                    $data_atual = date('Y-m-d', strtotime($data_atual . ' +1 day'));
                    continue;
                }
                
                // Distribuir disciplinas para este dia
                $disciplinas_dia = $this->selecionarDisciplinasParaDia($distribuicao, $dia_semana);
                
                foreach ($disciplinas_dia as $disciplina_id => $info) {
                    $sql = "INSERT INTO cronograma_detalhado 
                            (cronograma_id, disciplina_id, data_estudo, horas_previstas) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $cronograma_id, 
                        $disciplina_id, 
                        $data_atual, 
                        $info['horas']
                    ]);
                }
                
                $data_atual = date('Y-m-d', strtotime($data_atual . ' +1 day'));
                $dia_semana = ($dia_semana + 1) % 7;
            }
        }
    }
    
    /**
     * Seleciona disciplinas para um dia específico
     */
    private function selecionarDisciplinasParaDia($distribuicao, $dia_semana) {
        $disciplinas_dia = [];
        $horas_restantes = 4; // Máximo 4 horas por dia
        
        // Ordenar disciplinas por prioridade (mais questões = mais prioridade)
        uasort($distribuicao, function($a, $b) {
            return $b['disciplina']['total_questoes'] - $a['disciplina']['total_questoes'];
        });
        
        foreach ($distribuicao as $disciplina_id => $info) {
            if ($horas_restantes <= 0) break;
            
            $horas_disciplina = min($info['horas_por_sessao'], $horas_restantes);
            
            if ($horas_disciplina > 0) {
                $disciplinas_dia[$disciplina_id] = [
                    'horas' => $horas_disciplina,
                    'disciplina' => $info['disciplina']
                ];
                $horas_restantes -= $horas_disciplina;
            }
        }
        
        return $disciplinas_dia;
    }
    
    /**
     * Obtém cronograma detalhado para exibição
     */
    public function obterCronogramaDetalhado($cronograma_id) {
        $sql = "SELECT cd.*, d.nome_disciplina, c.data_inicio, c.data_fim, c.horas_por_dia
                FROM cronograma_detalhado cd
                JOIN disciplinas d ON cd.disciplina_id = d.id
                JOIN cronogramas c ON cd.cronograma_id = c.id
                WHERE cd.cronograma_id = ?
                ORDER BY cd.data_estudo, cd.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cronograma_id]);
        
        $cronograma = [];
        while ($row = $stmt->fetch()) {
            $data = $row['data_estudo'];
            if (!isset($cronograma[$data])) {
                $cronograma[$data] = [];
            }
            $cronograma[$data][] = $row;
        }
        
        return $cronograma;
    }
    
    /**
     * Gera dados para PDF do cronograma
     */
    public function gerarDadosPDF($cronograma_id) {
        $cronograma_detalhado = $this->obterCronogramaDetalhado($cronograma_id);
        
        $dados_pdf = [];
        
        foreach ($cronograma_detalhado as $data => $atividades) {
            $dia_semana = $this->dias_semana[date('N', strtotime($data)) - 1];
            $data_formatada = date('d/m/Y', strtotime($data));
            
            $dados_pdf[] = [
                'data' => $data,
                'data_formatada' => $data_formatada,
                'dia_semana' => $dia_semana,
                'atividades' => $atividades
            ];
        }
        
        return $dados_pdf;
    }
    
    /**
     * Obtém estatísticas do cronograma
     */
    public function obterEstatisticasCronograma($cronograma_id) {
        $sql = "SELECT 
                    COUNT(DISTINCT cd.data_estudo) as total_dias,
                    SUM(cd.horas_previstas) as total_horas,
                    COUNT(DISTINCT cd.disciplina_id) as total_disciplinas,
                    AVG(cd.horas_previstas) as media_horas_dia
                FROM cronograma_detalhado cd
                WHERE cd.cronograma_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cronograma_id]);
        
        return $stmt->fetch();
    }
}
?>
