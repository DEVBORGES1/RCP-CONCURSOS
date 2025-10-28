<?php
require 'conexao.php';

// Função para inserir disciplina se não existir
function inserirDisciplina($nome, $edital_id = 1)
{
    global $pdo;

    // Verificar se a disciplina já existe
    $sql = "SELECT id FROM disciplinas WHERE nome_disciplina = ? AND edital_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $edital_id]);

    if ($stmt->fetch()) {
        $sql = "SELECT id FROM disciplinas WHERE nome_disciplina = ? AND edital_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $edital_id]);
        return $stmt->fetch()['id'];
    }

    // Inserir nova disciplina
    $sql = "INSERT INTO disciplinas (edital_id, nome_disciplina) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$edital_id, $nome]);
    return $pdo->lastInsertId();
}

// Função para inserir questão
function inserirQuestao($disciplina_id, $enunciado, $alternativas, $correta)
{
    global $pdo;

    $sql = "INSERT INTO questoes (edital_id, disciplina_id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e, alternativa_correta) 
            VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $disciplina_id,
        $enunciado,
        $alternativas['a'],
        $alternativas['b'],
        $alternativas['c'],
        $alternativas['d'],
        $alternativas['e'] ?? '',
        $correta
    ]);

    return $pdo->lastInsertId();
}

try {
    echo "Iniciando inserção de exercícios...\n";

    // Verificar se existe um edital padrão, se não, criar um
    $sql = "SELECT id FROM editais LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $edital_existente = $stmt->fetch();

    if (!$edital_existente) {
        // Verificar se existe um usuário, se não, criar um padrão
        $sql = "SELECT id FROM usuarios LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $usuario_existente = $stmt->fetch();

        if (!$usuario_existente) {
            echo "Criando usuário padrão...\n";
            $sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES ('Usuário Padrão', 'admin@sistema.com', ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([password_hash('123456', PASSWORD_DEFAULT)]);
            $usuario_id = $pdo->lastInsertId();
            echo "Usuário padrão criado com ID: $usuario_id\n";
        } else {
            $usuario_id = $usuario_existente['id'];
        }

        echo "Criando edital padrão...\n";
        $sql = "INSERT INTO editais (usuario_id, nome_arquivo, texto_extraido) VALUES (?, 'Edital Padrão', 'Edital criado automaticamente para exercícios do sistema')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $edital_id = $pdo->lastInsertId();
        echo "Edital padrão criado com ID: $edital_id\n";
    } else {
        $edital_id = $edital_existente['id'];
        echo "Usando edital existente com ID: $edital_id\n";
    }

    // Inserir disciplinas
    $disciplinas = [
        'Língua Portuguesa' => inserirDisciplina('Língua Portuguesa', $edital_id),
        'Matemática' => inserirDisciplina('Matemática', $edital_id),
        'Raciocínio Lógico' => inserirDisciplina('Raciocínio Lógico', $edital_id),
        'Informática' => inserirDisciplina('Informática', $edital_id),
        'Direito Administrativo e Constitucional' => inserirDisciplina('Direito Administrativo e Constitucional', $edital_id),
        'Atualidades e Cidadania' => inserirDisciplina('Atualidades e Cidadania', $edital_id),
        'Administração Pública e Ética' => inserirDisciplina('Administração Pública e Ética', $edital_id)
    ];

    echo "Disciplinas inseridas/verificadas.\n";

    // Exercícios de Língua Portuguesa
    $portugues = [
        [
            'enunciado' => 'Assinale a alternativa em que a concordância verbal está correta:',
            'alternativas' => [
                'a' => 'Fazem dois anos que ele viajou.',
                'b' => 'Existem muitas pessoas feliz.',
                'c' => 'Havia muitas crianças no parque.',
                'd' => 'Devem haver muitas dúvidas.'
            ],
            'correta' => 'c'
        ],
        [
            'enunciado' => 'Indique o termo que exerce função de adjunto adnominal: "O amor de mãe é incondicional."',
            'alternativas' => [
                'a' => 'amor',
                'b' => 'mãe',
                'c' => 'de mãe',
                'd' => 'é incondicional'
            ],
            'correta' => 'c'
        ],
        [
            'enunciado' => 'Assinale a alternativa em que há erro de regência verbal:',
            'alternativas' => [
                'a' => 'Assisti ao filme.',
                'b' => 'Prefiro café a chá.',
                'c' => 'Obedeci as regras.',
                'd' => 'Cheguei à escola.'
            ],
            'correta' => 'c'
        ],
        [
            'enunciado' => 'A palavra "exceção" tem acento gráfico porque:',
            'alternativas' => [
                'a' => 'É oxítona terminada em "o".',
                'b' => 'É proparoxítona.',
                'c' => 'É paroxítona terminada em "ão".',
                'd' => 'É oxítona terminada em "ão".'
            ],
            'correta' => 'd'
        ],
        [
            'enunciado' => 'Indique a frase em que há crase obrigatória:',
            'alternativas' => [
                'a' => 'Vou a escola.',
                'b' => 'Entreguei a carta a ela.',
                'c' => 'Referi-me à professora.',
                'd' => 'Cheguei a pé.'
            ],
            'correta' => 'c'
        ]
    ];

    // Exercícios de Matemática
    $matematica = [
        [
            'enunciado' => 'Qual é o resultado de 8 × (3 + 2)²?',
            'alternativas' => [
                'a' => '80',
                'b' => '100',
                'c' => '200',
                'd' => '160'
            ],
            'correta' => 'c'
        ],
        [
            'enunciado' => 'Um produto custava R$ 200 e teve um desconto de 25%. Qual o novo valor?',
            'alternativas' => [
                'a' => 'R$ 150',
                'b' => 'R$ 160',
                'c' => 'R$ 175',
                'd' => 'R$ 180'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'Qual é a área de um triângulo de base 8 cm e altura 5 cm?',
            'alternativas' => [
                'a' => '20 cm²',
                'b' => '25 cm²',
                'c' => '30 cm²',
                'd' => '40 cm²'
            ],
            'correta' => 'a'
        ],
        [
            'enunciado' => 'O número 0,25 equivale a:',
            'alternativas' => [
                'a' => '1/2',
                'b' => '1/3',
                'c' => '1/4',
                'd' => '1/5'
            ],
            'correta' => 'c'
        ],
        [
            'enunciado' => 'Em uma razão 3:12, o valor da fração equivalente a 1:4 é:',
            'alternativas' => [
                'a' => '3/12',
                'b' => '1/4',
                'c' => '2/8',
                'd' => 'Todas as alternativas acima'
            ],
            'correta' => 'd'
        ]
    ];

    // Exercícios de Raciocínio Lógico
    $logico = [
        [
            'enunciado' => 'Se todo A é B e nenhum B é C, então:',
            'alternativas' => [
                'a' => 'Todo C é A.',
                'b' => 'Nenhum A é C.',
                'c' => 'Algum A é C.',
                'd' => 'Todo B é A.'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'Qual número completa a sequência: 2, 4, 8, 16, __ ?',
            'alternativas' => [
                'a' => '18',
                'b' => '20',
                'c' => '24',
                'd' => '32'
            ],
            'correta' => 'd'
        ],
        [
            'enunciado' => 'Se João é mais velho que Maria, e Maria é mais velha que Ana, então:',
            'alternativas' => [
                'a' => 'João é mais novo que Ana.',
                'b' => 'João é mais velho que Ana.',
                'c' => 'Ana é mais velha que João.',
                'd' => 'Maria é mais nova que João.'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'Negação correta da frase "Todos os alunos estudam":',
            'alternativas' => [
                'a' => 'Nenhum aluno estuda.',
                'b' => 'Alguns alunos não estudam.',
                'c' => 'Alguns alunos estudam.',
                'd' => 'Todos os alunos não estudam.'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'Se 5 máquinas produzem 100 peças em 10 horas, quantas peças produzem 10 máquinas no mesmo tempo?',
            'alternativas' => [
                'a' => '200',
                'b' => '150',
                'c' => '100',
                'd' => '250'
            ],
            'correta' => 'a'
        ]
    ];

    // Exercícios de Informática
    $informatica = [
        [
            'enunciado' => 'No Windows, a combinação Ctrl + C serve para:',
            'alternativas' => [
                'a' => 'Copiar',
                'b' => 'Colar',
                'c' => 'Cortar',
                'd' => 'Fechar'
            ],
            'correta' => 'a'
        ],
        [
            'enunciado' => 'O que é um navegador de internet?',
            'alternativas' => [
                'a' => 'Programa para edição de texto',
                'b' => 'Programa para acessar sites',
                'c' => 'Programa de antivírus',
                'd' => 'Sistema operacional'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'Qual extensão indica um arquivo do Microsoft Word?',
            'alternativas' => [
                'a' => '.xls',
                'b' => '.docx',
                'c' => '.pptx',
                'd' => '.txt'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'O que é um "phishing"?',
            'alternativas' => [
                'a' => 'Vírus de computador',
                'b' => 'Técnica de fraude para obter dados pessoais',
                'c' => 'Programa de segurança',
                'd' => 'Software de backup'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'A função principal do antivírus é:',
            'alternativas' => [
                'a' => 'Criar senhas',
                'b' => 'Reproduzir vídeos',
                'c' => 'Detectar e remover ameaças',
                'd' => 'Editar textos'
            ],
            'correta' => 'c'
        ]
    ];

    // Exercícios de Direito
    $direito = [
        [
            'enunciado' => 'O princípio da legalidade significa que:',
            'alternativas' => [
                'a' => 'O administrador público pode fazer o que quiser.',
                'b' => 'Só pode agir conforme a lei.',
                'c' => 'A lei não se aplica ao servidor.',
                'd' => 'Nenhuma das anteriores.'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'O Brasil é uma República:',
            'alternativas' => [
                'a' => 'Oligárquica',
                'b' => 'Federativa',
                'c' => 'Absolutista',
                'd' => 'Monárquica'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'O direito à liberdade de expressão está previsto na:',
            'alternativas' => [
                'a' => 'Constituição Federal',
                'b' => 'CLT',
                'c' => 'Código Penal',
                'd' => 'Lei 8.666/93'
            ],
            'correta' => 'a'
        ],
        [
            'enunciado' => 'A licitação tem por objetivo:',
            'alternativas' => [
                'a' => 'Privilegiar empresas locais.',
                'b' => 'Garantir a isonomia entre os concorrentes.',
                'c' => 'Escolher sempre o menor preço.',
                'd' => 'Dispensar formalidades.'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'O chefe do Poder Executivo Federal é:',
            'alternativas' => [
                'a' => 'O Presidente da Câmara',
                'b' => 'O Presidente da República',
                'c' => 'O Ministro da Justiça',
                'd' => 'O Senado Federal'
            ],
            'correta' => 'b'
        ]
    ];

    // Exercícios de Atualidades
    $atualidades = [
        [
            'enunciado' => 'O aquecimento global está relacionado principalmente a:',
            'alternativas' => [
                'a' => 'Aumento de áreas verdes',
                'b' => 'Emissão de gases poluentes',
                'c' => 'Diminuição de poluição',
                'd' => 'Rotação da Terra'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'A ONU tem como principal objetivo:',
            'alternativas' => [
                'a' => 'Incentivar guerras',
                'b' => 'Promover a paz e a cooperação internacional',
                'c' => 'Regular o comércio internacional',
                'd' => 'Defender apenas países ricos'
            ],
            'correta' => 'b'
        ],
        [
            'enunciado' => 'O termo "fake news" se refere a:',
            'alternativas' => [
                'a' => 'Notícias falsas divulgadas como verdadeiras',
                'b' => 'Notícias antigas',
                'c' => 'Notícias de esportes',
                'd' => 'Notícias científicas'
            ],
            'correta' => 'a'
        ]
    ];

    // Exercícios de Administração e Ética
    $administracao = [
        [
            'enunciado' => 'A impessoalidade na Administração Pública significa:',
            'alternativas' => [
                'a' => 'O servidor deve agir de acordo com seus interesses.',
                'b' => 'O ato administrativo deve beneficiar o administrador.',
                'c' => 'O servidor deve agir com foco no interesse público.',
                'd' => 'O agente pode favorecer parentes.'
            ],
            'correta' => 'c'
        ],
        [
            'enunciado' => 'O servidor público deve pautar sua conduta pela:',
            'alternativas' => [
                'a' => 'Boa-fé, honestidade e transparência',
                'b' => 'Desconfiança e sigilo',
                'c' => 'Hierarquia apenas',
                'd' => 'Política partidária'
            ],
            'correta' => 'a'
        ]
    ];

    // Inserir todas as questões
    $todas_questoes = [
        'Língua Portuguesa' => $portugues,
        'Matemática' => $matematica,
        'Raciocínio Lógico' => $logico,
        'Informática' => $informatica,
        'Direito Administrativo e Constitucional' => $direito,
        'Atualidades e Cidadania' => $atualidades,
        'Administração Pública e Ética' => $administracao
    ];

    $total_inseridas = 0;

    foreach ($todas_questoes as $disciplina_nome => $questoes) {
        $disciplina_id = $disciplinas[$disciplina_nome];
        echo "Inserindo questões de $disciplina_nome...\n";

        foreach ($questoes as $questao) {
            inserirQuestao(
                $disciplina_id,
                $questao['enunciado'],
                $questao['alternativas'],
                $questao['correta']
            );
            $total_inseridas++;
        }
    }

    echo "\n✅ Processo concluído!\n";
    echo "Total de questões inseridas: $total_inseridas\n";
    echo "Disciplinas criadas: " . count($disciplinas) . "\n";

    // Mostrar estatísticas
    foreach ($disciplinas as $nome => $id) {
        $sql = "SELECT COUNT(*) as total FROM questoes WHERE disciplina_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $count = $stmt->fetch()['total'];
        echo "- $nome: $count questões\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
