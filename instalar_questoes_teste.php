<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    echo "Usuário não logado!";
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

echo "🔧 Instalando questões de teste para o usuário $usuario_id...\n\n";

try {
    // 1. Verificar se já existe um edital
    $sql = "SELECT id FROM editais WHERE usuario_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $edital_id = $stmt->fetchColumn();
    
    if (!$edital_id) {
        // Criar edital de teste
        $sql = "INSERT INTO editais (usuario_id, nome_arquivo, texto_extraido) VALUES (?, 'Edital de Teste', 'Edital para questões de teste')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $edital_id = $pdo->lastInsertId();
        echo "✅ Edital de teste criado (ID: $edital_id)\n";
    } else {
        echo "✅ Usando edital existente (ID: $edital_id)\n";
    }
    
    // 2. Criar disciplinas se não existirem
    $disciplinas = [
        'Português' => 'Língua Portuguesa',
        'Matemática' => 'Matemática',
        'Raciocínio Lógico' => 'Raciocínio Lógico',
        'Informática' => 'Informática',
        'Direito' => 'Direito Administrativo e Constitucional',
        'Administração' => 'Administração Pública e Ética',
        'Atualidades' => 'Atualidades e Cidadania'
    ];
    
    $disciplina_ids = [];
    foreach ($disciplinas as $nome => $nome_completo) {
        $sql = "SELECT id FROM disciplinas WHERE nome_disciplina = ? AND edital_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_completo, $edital_id]);
        $disciplina_id = $stmt->fetchColumn();
        
        if (!$disciplina_id) {
            $sql = "INSERT INTO disciplinas (edital_id, nome_disciplina) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$edital_id, $nome_completo]);
            $disciplina_id = $pdo->lastInsertId();
            echo "✅ Disciplina '$nome_completo' criada (ID: $disciplina_id)\n";
        }
        
        $disciplina_ids[$nome] = $disciplina_id;
    }
    
    // 3. Criar questões de teste
    $questoes_teste = [
        // Português
        [
            'disciplina' => 'Português',
            'enunciado' => 'Qual é a função sintática da palavra "rapidamente" na frase "Ele correu rapidamente"?',
            'alternativas' => [
                'a' => 'Sujeito',
                'b' => 'Predicado',
                'c' => 'Adjunto adverbial',
                'd' => 'Complemento verbal',
                'e' => 'Adjunto adnominal'
            ],
            'correta' => 'C'
        ],
        [
            'disciplina' => 'Português',
            'enunciado' => 'Assinale a alternativa que apresenta erro de concordância:',
            'alternativas' => [
                'a' => 'Haviam muitos problemas na reunião.',
                'b' => 'Faz dois anos que não nos vemos.',
                'c' => 'É necessário que todos participem.',
                'd' => 'Os dados mostram que a situação melhorou.',
                'e' => 'A maioria dos alunos passou no exame.'
            ],
            'correta' => 'A'
        ],
        [
            'disciplina' => 'Português',
            'enunciado' => 'Qual é o tipo de sujeito na frase "Choveu muito ontem"?',
            'alternativas' => [
                'a' => 'Sujeito simples',
                'b' => 'Sujeito composto',
                'c' => 'Sujeito oculto',
                'd' => 'Sujeito indeterminado',
                'e' => 'Oração sem sujeito'
            ],
            'correta' => 'E'
        ],
        // Matemática
        [
            'disciplina' => 'Matemática',
            'enunciado' => 'Qual é o valor de x na equação 2x + 5 = 15?',
            'alternativas' => [
                'a' => 'x = 3',
                'b' => 'x = 5',
                'c' => 'x = 7',
                'd' => 'x = 10',
                'e' => 'x = 12'
            ],
            'correta' => 'B'
        ],
        [
            'disciplina' => 'Matemática',
            'enunciado' => 'Qual é a área de um retângulo com 8cm de comprimento e 5cm de largura?',
            'alternativas' => [
                'a' => '13 cm²',
                'b' => '26 cm²',
                'c' => '40 cm²',
                'd' => '45 cm²',
                'e' => '50 cm²'
            ],
            'correta' => 'C'
        ],
        [
            'disciplina' => 'Matemática',
            'enunciado' => 'Qual é o resultado de 3² + 4²?',
            'alternativas' => [
                'a' => '7',
                'b' => '12',
                'c' => '25',
                'd' => '49',
                'e' => '81'
            ],
            'correta' => 'C'
        ],
        // Raciocínio Lógico
        [
            'disciplina' => 'Raciocínio Lógico',
            'enunciado' => 'Se todos os gatos são animais e alguns animais são domésticos, então:',
            'alternativas' => [
                'a' => 'Todos os gatos são domésticos',
                'b' => 'Alguns gatos podem ser domésticos',
                'c' => 'Nenhum gato é doméstico',
                'd' => 'Todos os animais são gatos',
                'e' => 'Nenhuma das anteriores'
            ],
            'correta' => 'B'
        ],
        [
            'disciplina' => 'Raciocínio Lógico',
            'enunciado' => 'Qual é o próximo número na sequência: 2, 4, 8, 16, ?',
            'alternativas' => [
                'a' => '20',
                'b' => '24',
                'c' => '32',
                'd' => '36',
                'e' => '40'
            ],
            'correta' => 'C'
        ],
        // Informática
        [
            'disciplina' => 'Informática',
            'enunciado' => 'Qual é a função da tecla F5 no navegador?',
            'alternativas' => [
                'a' => 'Abrir nova aba',
                'b' => 'Fechar aba',
                'c' => 'Atualizar página',
                'd' => 'Voltar página',
                'e' => 'Avançar página'
            ],
            'correta' => 'C'
        ],
        [
            'disciplina' => 'Informática',
            'enunciado' => 'O que significa a sigla PDF?',
            'alternativas' => [
                'a' => 'Portable Document Format',
                'b' => 'Personal Data File',
                'c' => 'Public Document Format',
                'd' => 'Portable Data File',
                'e' => 'Personal Document Format'
            ],
            'correta' => 'A'
        ],
        // Direito
        [
            'disciplina' => 'Direito',
            'enunciado' => 'Qual é o princípio da administração pública que determina que o administrador deve agir com imparcialidade?',
            'alternativas' => [
                'a' => 'Legalidade',
                'b' => 'Impessoalidade',
                'c' => 'Moralidade',
                'd' => 'Publicidade',
                'e' => 'Eficiência'
            ],
            'correta' => 'B'
        ],
        [
            'disciplina' => 'Direito',
            'enunciado' => 'Qual é o prazo para interposição de recurso administrativo?',
            'alternativas' => [
                'a' => '5 dias',
                'b' => '10 dias',
                'c' => '15 dias',
                'd' => '30 dias',
                'e' => '60 dias'
            ],
            'correta' => 'B'
        ],
        // Administração
        [
            'disciplina' => 'Administração',
            'enunciado' => 'Qual é o conceito de eficiência na administração pública?',
            'alternativas' => [
                'a' => 'Fazer mais com menos recursos',
                'b' => 'Atingir os objetivos propostos',
                'c' => 'Seguir as normas legais',
                'd' => 'Manter a transparência',
                'e' => 'Satisfazer o cidadão'
            ],
            'correta' => 'A'
        ],
        // Atualidades
        [
            'disciplina' => 'Atualidades',
            'enunciado' => 'Qual é o objetivo principal dos Objetivos de Desenvolvimento Sustentável (ODS)?',
            'alternativas' => [
                'a' => 'Eliminar a pobreza mundial',
                'b' => 'Promover o desenvolvimento sustentável',
                'c' => 'Reduzir as desigualdades',
                'd' => 'Combater as mudanças climáticas',
                'e' => 'Todas as anteriores'
            ],
            'correta' => 'E'
        ]
    ];
    
    $questoes_criadas = 0;
    foreach ($questoes_teste as $questao) {
        $sql = "INSERT INTO questoes (edital_id, disciplina_id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e, alternativa_correta) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $edital_id,
            $disciplina_ids[$questao['disciplina']],
            $questao['enunciado'],
            $questao['alternativas']['a'],
            $questao['alternativas']['b'],
            $questao['alternativas']['c'],
            $questao['alternativas']['d'],
            $questao['alternativas']['e'],
            $questao['correta']
        ]);
        $questoes_criadas++;
    }
    
    echo "✅ $questoes_criadas questões de teste criadas com sucesso!\n\n";
    
    // 4. Verificar total de questões
    $sql = "SELECT COUNT(*) as total FROM questoes WHERE edital_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$edital_id]);
    $total_questoes = $stmt->fetchColumn();
    
    echo "📊 Total de questões disponíveis: $total_questoes\n";
    echo "🎉 Questões de teste instaladas com sucesso!\n";
    echo "💡 Agora você pode acessar os simulados pré-definidos.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
