<?php
require_once 'conexao.php';

class AnalisadorEdital {
    private $pdo;
    
    // Lista de disciplinas comuns em concursos
    private $disciplinas_comuns = [
        'Português', 'Matemática', 'Raciocínio Lógico', 'Informática', 'Direito Constitucional',
        'Direito Administrativo', 'Direito Penal', 'Direito Civil', 'Direito Processual',
        'Direito Tributário', 'Direito do Trabalho', 'Direito Previdenciário', 'Direito Empresarial',
        'Administração Pública', 'Contabilidade', 'Auditoria', 'Economia', 'Finanças Públicas',
        'Estatística', 'História do Brasil', 'Geografia', 'Atualidades', 'Legislação',
        'Ética na Administração Pública', 'Regime Jurídico Único', 'Licitações',
        'Controle Interno e Externo', 'Orçamento Público', 'Gestão de Pessoas',
        'Gestão de Projetos', 'Gestão de Qualidade', 'Comunicação Social',
        'Arquitetura', 'Engenharia Civil', 'Engenharia Elétrica', 'Engenharia Mecânica',
        'Medicina', 'Enfermagem', 'Fisioterapia', 'Psicologia', 'Assistência Social',
        'Pedagogia', 'Letras', 'História', 'Filosofia', 'Sociologia', 'Antropologia'
    ];
    
    // Padrões para identificar disciplinas no texto
    private $padroes_disciplinas = [
        '/\b(?:disciplina|matéria|área|conhecimento)s?\s*:?\s*([^\.\n]+)/i',
        '/\b(?:conteúdo|programa|matriz)\s+(?:curricular|programático)\s*:?\s*([^\.\n]+)/i',
        '/\b(?:prova|exame)\s+(?:de|da|do)\s+([^\.\n]+)/i',
        '/\b(?:área|conhecimento)\s+([^\.\n]+?)(?:\s+com\s+|\s+e\s+|\s+ou\s+)/i'
    ];
    
    // Padrões para identificar tópicos específicos
    private $padroes_topicos = [
        '/\b(?:tema|tópico|assunto|item)s?\s*:?\s*([^\.\n]+)/i',
        '/\b(?:conteúdo|programa)\s*:?\s*([^\.\n]+)/i',
        '/\b(?:abordar|incluir|tratar)\s+([^\.\n]+)/i'
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Analisa o texto do edital e extrai disciplinas automaticamente
     */
    public function analisarEdital($edital_id, $texto_edital) {
        try {
            $this->pdo->beginTransaction();
            
            // Limpar disciplinas existentes do edital
            $this->limparDisciplinasEdital($edital_id);
            
            // Extrair disciplinas do texto
            $disciplinas_encontradas = $this->extrairDisciplinas($texto_edital);
            
            // Salvar disciplinas no banco
            foreach ($disciplinas_encontradas as $disciplina) {
                $this->salvarDisciplina($edital_id, $disciplina);
            }
            
            // Gerar questões automáticas para cada disciplina
            foreach ($disciplinas_encontradas as $disciplina) {
                $this->gerarQuestoesAutomaticas($edital_id, $disciplina);
            }
            
            $this->pdo->commit();
            return [
                'sucesso' => true,
                'disciplinas_encontradas' => count($disciplinas_encontradas),
                'disciplinas' => $disciplinas_encontradas
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
     * Extrai disciplinas do texto do edital
     */
    private function extrairDisciplinas($texto) {
        $disciplinas_encontradas = [];
        $texto_lower = strtolower($texto);
        
        // Buscar disciplinas usando padrões regex
        foreach ($this->padroes_disciplinas as $padrao) {
            preg_match_all($padrao, $texto, $matches);
            foreach ($matches[1] as $match) {
                $disciplinas_candidatas = $this->processarTextoDisciplina($match);
                $disciplinas_encontradas = array_merge($disciplinas_encontradas, $disciplinas_candidatas);
            }
        }
        
        // Buscar disciplinas conhecidas no texto
        foreach ($this->disciplinas_comuns as $disciplina) {
            if (stripos($texto, $disciplina) !== false) {
                $disciplinas_encontradas[] = $disciplina;
            }
        }
        
        // Remover duplicatas e limpar
        $disciplinas_encontradas = array_unique($disciplinas_encontradas);
        $disciplinas_encontradas = array_filter($disciplinas_encontradas, function($d) {
            return strlen(trim($d)) > 2 && strlen(trim($d)) < 100;
        });
        
        return array_values($disciplinas_encontradas);
    }
    
    /**
     * Processa texto extraído para identificar disciplinas
     */
    private function processarTextoDisciplina($texto) {
        $disciplinas = [];
        $texto = trim($texto);
        
        // Dividir por vírgulas, pontos e vírgulas, "e", "ou"
        $partes = preg_split('/[,;]\s*|\s+e\s+|\s+ou\s+/i', $texto);
        
        foreach ($partes as $parte) {
            $parte = trim($parte);
            if (strlen($parte) > 2 && strlen($parte) < 100) {
                // Verificar se contém alguma disciplina conhecida
                foreach ($this->disciplinas_comuns as $disciplina_conhecida) {
                    if (stripos($parte, $disciplina_conhecida) !== false) {
                        $disciplinas[] = $disciplina_conhecida;
                    }
                }
                
                // Se não encontrou disciplina conhecida, usar o texto como está
                if (empty($disciplinas) || !in_array($parte, $disciplinas)) {
                    $disciplinas[] = ucwords(strtolower($parte));
                }
            }
        }
        
        return $disciplinas;
    }
    
    /**
     * Salva disciplina no banco de dados
     */
    private function salvarDisciplina($edital_id, $nome_disciplina) {
        // Verificar se já existe
        $sql = "SELECT id FROM disciplinas WHERE edital_id = ? AND nome_disciplina = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$edital_id, $nome_disciplina]);
        
        if ($stmt->fetchColumn()) {
            return; // Já existe
        }
        
        $sql = "INSERT INTO disciplinas (edital_id, nome_disciplina) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$edital_id, $nome_disciplina]);
    }
    
    /**
     * Limpa disciplinas existentes do edital
     */
    private function limparDisciplinasEdital($edital_id) {
        $sql = "DELETE FROM disciplinas WHERE edital_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$edital_id]);
    }
    
    /**
     * Gera questões automáticas para uma disciplina
     */
    private function gerarQuestoesAutomaticas($edital_id, $disciplina) {
        // Obter ID da disciplina
        $sql = "SELECT id FROM disciplinas WHERE edital_id = ? AND nome_disciplina = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$edital_id, $disciplina]);
        $disciplina_id = $stmt->fetchColumn();
        
        if (!$disciplina_id) {
            return;
        }
        
        // Gerar questões baseadas no tipo de disciplina
        $questoes = $this->gerarQuestoesPorDisciplina($disciplina);
        
        foreach ($questoes as $questao) {
            $this->salvarQuestao($edital_id, $disciplina_id, $questao);
        }
    }
    
    /**
     * Gera questões específicas para cada tipo de disciplina
     */
    private function gerarQuestoesPorDisciplina($disciplina) {
        $questoes = [];
        
        switch (strtolower($disciplina)) {
            case 'português':
                $questoes = $this->gerarQuestoesPortugues();
                break;
            case 'matemática':
                $questoes = $this->gerarQuestoesMatematica();
                break;
            case 'raciocínio lógico':
                $questoes = $this->gerarQuestoesRaciocinioLogico();
                break;
            case 'informática':
                $questoes = $this->gerarQuestoesInformatica();
                break;
            case 'direito constitucional':
                $questoes = $this->gerarQuestoesDireitoConstitucional();
                break;
            case 'direito administrativo':
                $questoes = $this->gerarQuestoesDireitoAdministrativo();
                break;
            default:
                $questoes = $this->gerarQuestoesGenericas($disciplina);
        }
        
        return $questoes;
    }
    
    /**
     * Gera questões de Português
     */
    private function gerarQuestoesPortugues() {
        return [
            [
                'enunciado' => 'Qual é a função sintática da palavra destacada na frase: "O aluno estudou MUITO para a prova"?',
                'alternativa_a' => 'Adjunto adverbial',
                'alternativa_b' => 'Complemento nominal',
                'alternativa_c' => 'Predicativo do sujeito',
                'alternativa_d' => 'Objeto direto',
                'alternativa_e' => 'Adjunto adnominal',
                'alternativa_correta' => 'A'
            ],
            [
                'enunciado' => 'Assinale a alternativa que apresenta erro de concordância:',
                'alternativa_a' => 'Haviam muitas pessoas na reunião.',
                'alternativa_b' => 'Fazem dois anos que não nos vemos.',
                'alternativa_c' => 'Devem existir várias soluções.',
                'alternativa_d' => 'Há muitas pessoas interessadas.',
                'alternativa_e' => 'Existem várias possibilidades.',
                'alternativa_correta' => 'A'
            ],
            [
                'enunciado' => 'Qual das palavras abaixo é um advérbio?',
                'alternativa_a' => 'Rapidamente',
                'alternativa_b' => 'Rápido',
                'alternativa_c' => 'Rapidez',
                'alternativa_d' => 'Rapidinho',
                'alternativa_e' => 'Rapidaço',
                'alternativa_correta' => 'A'
            ]
        ];
    }
    
    /**
     * Gera questões de Matemática
     */
    private function gerarQuestoesMatematica() {
        return [
            [
                'enunciado' => 'Se um retângulo tem comprimento 8 cm e largura 5 cm, qual é sua área?',
                'alternativa_a' => '13 cm²',
                'alternativa_b' => '26 cm²',
                'alternativa_c' => '40 cm²',
                'alternativa_d' => '65 cm²',
                'alternativa_e' => '80 cm²',
                'alternativa_correta' => 'C'
            ],
            [
                'enunciado' => 'Qual é o valor de x na equação 2x + 5 = 13?',
                'alternativa_a' => '2',
                'alternativa_b' => '3',
                'alternativa_c' => '4',
                'alternativa_d' => '5',
                'alternativa_e' => '6',
                'alternativa_correta' => 'C'
            ],
            [
                'enunciado' => 'Se 30% de um número é 45, qual é esse número?',
                'alternativa_a' => '120',
                'alternativa_b' => '135',
                'alternativa_c' => '150',
                'alternativa_d' => '165',
                'alternativa_e' => '180',
                'alternativa_correta' => 'C'
            ]
        ];
    }
    
    /**
     * Gera questões de Raciocínio Lógico
     */
    private function gerarQuestoesRaciocinioLogico() {
        return [
            [
                'enunciado' => 'Se todos os gatos são mamíferos e todos os mamíferos são animais, então:',
                'alternativa_a' => 'Todos os animais são gatos',
                'alternativa_b' => 'Todos os gatos são animais',
                'alternativa_c' => 'Alguns gatos não são animais',
                'alternativa_d' => 'Nenhum gato é animal',
                'alternativa_e' => 'Todos os animais são mamíferos',
                'alternativa_correta' => 'B'
            ],
            [
                'enunciado' => 'Qual é o próximo número na sequência: 2, 4, 8, 16, ?',
                'alternativa_a' => '20',
                'alternativa_b' => '24',
                'alternativa_c' => '32',
                'alternativa_d' => '36',
                'alternativa_e' => '40',
                'alternativa_correta' => 'C'
            ]
        ];
    }
    
    /**
     * Gera questões de Informática
     */
    private function gerarQuestoesInformatica() {
        return [
            [
                'enunciado' => 'Qual é a função da tecla F5 em um navegador web?',
                'alternativa_a' => 'Salvar página',
                'alternativa_b' => 'Atualizar página',
                'alternativa_c' => 'Fechar aba',
                'alternativa_d' => 'Abrir nova aba',
                'alternativa_e' => 'Imprimir página',
                'alternativa_correta' => 'B'
            ],
            [
                'enunciado' => 'O que significa a sigla PDF?',
                'alternativa_a' => 'Portable Document Format',
                'alternativa_b' => 'Personal Data File',
                'alternativa_c' => 'Program Data Format',
                'alternativa_d' => 'Portable Data Format',
                'alternativa_e' => 'Personal Document File',
                'alternativa_correta' => 'A'
            ]
        ];
    }
    
    /**
     * Gera questões de Direito Constitucional
     */
    private function gerarQuestoesDireitoConstitucional() {
        return [
            [
                'enunciado' => 'Qual é o fundamento da República Federativa do Brasil?',
                'alternativa_a' => 'A soberania, a cidadania, a dignidade da pessoa humana',
                'alternativa_b' => 'Os valores sociais do trabalho e da livre iniciativa',
                'alternativa_c' => 'O pluralismo político',
                'alternativa_d' => 'Todas as alternativas anteriores',
                'alternativa_e' => 'Apenas a soberania',
                'alternativa_correta' => 'D'
            ],
            [
                'enunciado' => 'Qual é o prazo para promulgação de uma lei aprovada pelo Congresso Nacional?',
                'alternativa_a' => '15 dias',
                'alternativa_b' => '30 dias',
                'alternativa_c' => '45 dias',
                'alternativa_d' => '60 dias',
                'alternativa_e' => '90 dias',
                'alternativa_correta' => 'A'
            ]
        ];
    }
    
    /**
     * Gera questões de Direito Administrativo
     */
    private function gerarQuestoesDireitoAdministrativo() {
        return [
            [
                'enunciado' => 'Qual é o princípio que determina que a Administração Pública deve agir com transparência?',
                'alternativa_a' => 'Princípio da legalidade',
                'alternativa_b' => 'Princípio da publicidade',
                'alternativa_c' => 'Princípio da impessoalidade',
                'alternativa_d' => 'Princípio da moralidade',
                'alternativa_e' => 'Princípio da eficiência',
                'alternativa_correta' => 'B'
            ]
        ];
    }
    
    /**
     * Gera questões genéricas para disciplinas não específicas
     */
    private function gerarQuestoesGenericas($disciplina) {
        return [
            [
                'enunciado' => "Sobre {$disciplina}, assinale a alternativa correta:",
                'alternativa_a' => 'É uma área de conhecimento importante',
                'alternativa_b' => 'Não possui aplicação prática',
                'alternativa_c' => 'É estudada apenas teoricamente',
                'alternativa_d' => 'Não possui relevância atual',
                'alternativa_e' => 'É uma disciplina obsoleta',
                'alternativa_correta' => 'A'
            ],
            [
                'enunciado' => "Qual é a principal característica de {$disciplina}?",
                'alternativa_a' => 'Sua complexidade teórica',
                'alternativa_b' => 'Sua aplicação prática',
                'alternativa_c' => 'Sua simplicidade conceitual',
                'alternativa_d' => 'Sua irrelevância atual',
                'alternativa_e' => 'Sua obsolescência',
                'alternativa_correta' => 'B'
            ]
        ];
    }
    
    /**
     * Salva questão no banco de dados
     */
    private function salvarQuestao($edital_id, $disciplina_id, $questao) {
        $sql = "INSERT INTO questoes (edital_id, disciplina_id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e, alternativa_correta) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $edital_id,
            $disciplina_id,
            $questao['enunciado'],
            $questao['alternativa_a'],
            $questao['alternativa_b'],
            $questao['alternativa_c'],
            $questao['alternativa_d'],
            $questao['alternativa_e'],
            $questao['alternativa_correta']
        ]);
    }
    
    /**
     * Obtém estatísticas da análise
     */
    public function obterEstatisticasAnalise($edital_id) {
        $sql = "SELECT 
                    COUNT(DISTINCT d.id) as total_disciplinas,
                    COUNT(q.id) as total_questoes
                FROM disciplinas d 
                LEFT JOIN questoes q ON d.id = q.disciplina_id 
                WHERE d.edital_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$edital_id]);
        
        return $stmt->fetch();
    }
}
?>
