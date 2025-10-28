<?php
require 'conexao.php';
require 'classes/Gamificacao.php';

echo "<h2>=== CRIAÇÃO DE DADOS DE TESTE ===</h2>";

try {
    // 1. Verificar se existe um edital
    $stmt = $pdo->query("SELECT id FROM editais LIMIT 1");
    $edital_id = $stmt->fetchColumn();
    
    if (!$edital_id) {
        echo "<h3>1. Criando edital de teste...</h3>";
        
        // Criar um edital de teste
        $stmt = $pdo->prepare("INSERT INTO editais (usuario_id, nome_arquivo, texto_extraido) VALUES (?, ?, ?)");
        $stmt->execute([1, 'edital_teste.pdf', 'Edital de teste para questões']);
        $edital_id = $pdo->lastInsertId();
        
        echo "<p>✓ Edital criado com ID: $edital_id</p>";
    } else {
        echo "<p>✓ Usando edital existente ID: $edital_id</p>";
    }
    
    // 2. Verificar se existe uma disciplina
    $stmt = $pdo->prepare("SELECT id FROM disciplinas WHERE edital_id = ? LIMIT 1");
    $stmt->execute([$edital_id]);
    $disciplina_id = $stmt->fetchColumn();
    
    if (!$disciplina_id) {
        echo "<h3>2. Criando disciplina de teste...</h3>";
        
        $stmt = $pdo->prepare("INSERT INTO disciplinas (edital_id, nome_disciplina) VALUES (?, ?)");
        $stmt->execute([$edital_id, 'Matemática']);
        $disciplina_id = $pdo->lastInsertId();
        
        echo "<p>✓ Disciplina criada com ID: $disciplina_id</p>";
    } else {
        echo "<p>✓ Usando disciplina existente ID: $disciplina_id</p>";
    }
    
    // 3. Criar questões de teste
    echo "<h3>3. Criando questões de teste...</h3>";
    
    $questoes_teste = [
        [
            'enunciado' => 'Qual é o resultado de 2 + 2?',
            'alternativa_a' => '3',
            'alternativa_b' => '4',
            'alternativa_c' => '5',
            'alternativa_d' => '6',
            'alternativa_e' => '7',
            'alternativa_correta' => 'B'
        ],
        [
            'enunciado' => 'Qual é a raiz quadrada de 16?',
            'alternativa_a' => '2',
            'alternativa_b' => '3',
            'alternativa_c' => '4',
            'alternativa_d' => '5',
            'alternativa_e' => '6',
            'alternativa_correta' => 'C'
        ],
        [
            'enunciado' => 'Qual é o valor de 3 x 5?',
            'alternativa_a' => '12',
            'alternativa_b' => '15',
            'alternativa_c' => '18',
            'alternativa_d' => '20',
            'alternativa_e' => '25',
            'alternativa_correta' => 'B'
        ]
    ];
    
    $questoes_criadas = 0;
    foreach ($questoes_teste as $questao) {
        $stmt = $pdo->prepare("INSERT INTO questoes (edital_id, disciplina_id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e, alternativa_correta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
        $questoes_criadas++;
    }
    
    echo "<p>✓ $questoes_criadas questões criadas</p>";
    
    // 4. Simular respostas do usuário
    echo "<h3>4. Simulando respostas do usuário...</h3>";
    
    $gamificacao = new Gamificacao($pdo);
    $usuario_id = 1; // ID do usuário de teste
    
    // Obter questões criadas
    $stmt = $pdo->prepare("SELECT id, alternativa_correta FROM questoes WHERE edital_id = ? ORDER BY id DESC LIMIT 3");
    $stmt->execute([$edital_id]);
    $questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $respostas_corretas = 0;
    foreach ($questoes as $questao) {
        // Simular resposta (50% de chance de acertar)
        $resposta_usuario = rand(0, 1) ? $questao['alternativa_correta'] : chr(ord('A') + rand(0, 4));
        $correta = ($resposta_usuario === $questao['alternativa_correta']);
        
        if ($correta) {
            $respostas_corretas++;
        }
        
        // Inserir resposta
        $stmt = $pdo->prepare("INSERT INTO respostas_usuario (usuario_id, questao_id, resposta, correta, pontos_ganhos) VALUES (?, ?, ?, ?, ?)");
        $pontos = $correta ? 10 : 0;
        $stmt->execute([$usuario_id, $questao['id'], $resposta_usuario, $correta, $pontos]);
        
        // Adicionar pontos via gamificação
        if ($correta) {
            $gamificacao->adicionarPontos($usuario_id, $pontos, 'questao');
        }
        
        echo "<p>Questão {$questao['id']}: Resposta '$resposta_usuario' - " . ($correta ? 'Correta' : 'Incorreta') . "</p>";
    }
    
    echo "<p>✓ $respostas_corretas de " . count($questoes) . " questões corretas</p>";
    
    // 5. Verificar progresso final
    echo "<h3>5. Verificando progresso final...</h3>";
    
    $dados_final = $gamificacao->obterDadosUsuario($usuario_id);
    echo "<p>Nível: {$dados_final['nivel']}</p>";
    echo "<p>Pontos: {$dados_final['pontos_total']}</p>";
    echo "<p>Questões respondidas: {$dados_final['questoes_respondidas']}</p>";
    echo "<p>Questões corretas: {$dados_final['questoes_corretas']}</p>";
    
    $conquistas = $gamificacao->obterConquistasUsuario($usuario_id);
    $conquistas_desbloqueadas = 0;
    foreach ($conquistas as $conquista) {
        if ($conquista['data_conquista']) {
            $conquistas_desbloqueadas++;
        }
    }
    echo "<p>Conquistas desbloqueadas: $conquistas_desbloqueadas</p>";
    
    echo "<h3 style='color: green;'>✅ Dados de teste criados com sucesso!</h3>";
    echo "<p><a href='dashboard.php'>→ Ir para o Dashboard</a></p>";
    echo "<p><a href='testar_progresso.php'>→ Executar teste de progresso</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
