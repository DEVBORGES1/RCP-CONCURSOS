CREATE DATABASE concursos;
USE concursos;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha_hash VARCHAR(255)
);

CREATE TABLE editais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nome_arquivo VARCHAR(255),
    texto_extraido LONGTEXT,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edital_id INT,
    nome_disciplina VARCHAR(150),
    FOREIGN KEY (edital_id) REFERENCES editais(id)
);

CREATE TABLE cronogramas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    edital_id INT,
    data_inicio DATE,
    data_fim DATE,
    horas_por_dia INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (edital_id) REFERENCES editais(id)
);

CREATE TABLE questoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edital_id INT,
    disciplina_id INT,
    enunciado TEXT,
    alternativa_a VARCHAR(255),
    alternativa_b VARCHAR(255),
    alternativa_c VARCHAR(255),
    alternativa_d VARCHAR(255),
    alternativa_e VARCHAR(255),
    alternativa_correta CHAR(1),
    FOREIGN KEY (edital_id) REFERENCES editais(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
);

CREATE TABLE respostas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    questao_id INT,
    resposta CHAR(1),
    correta BOOLEAN,
    pontos_ganhos INT DEFAULT 0,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (questao_id) REFERENCES questoes(id)
);

-- Sistema de Gamificação
CREATE TABLE usuarios_progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nivel INT DEFAULT 1,
    pontos_total INT DEFAULT 0,
    streak_dias INT DEFAULT 0,
    ultimo_login DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE conquistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    descricao TEXT,
    icone VARCHAR(50),
    pontos_necessarios INT,
    tipo VARCHAR(50) -- 'questoes', 'streak', 'nivel', 'simulado'
);

CREATE TABLE usuarios_conquistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    conquista_id INT,
    data_conquista TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (conquista_id) REFERENCES conquistas(id)
);

-- Sistema de Ranking
CREATE TABLE ranking_mensal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    mes_ano VARCHAR(7), -- formato: 2024-01
    pontos_mes INT DEFAULT 0,
    posicao INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Cronograma melhorado
CREATE TABLE cronograma_detalhado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cronograma_id INT,
    disciplina_id INT,
    data_estudo DATE,
    horas_previstas DECIMAL(3,1),
    horas_realizadas DECIMAL(3,1) DEFAULT 0,
    concluido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (cronograma_id) REFERENCES cronogramas(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
);

-- Simulados personalizados
CREATE TABLE simulados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nome VARCHAR(100),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    questoes_total INT,
    questoes_corretas INT DEFAULT 0,
    pontuacao_final INT DEFAULT 0,
    tempo_gasto INT, -- em minutos
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE simulados_questoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    simulado_id INT,
    questao_id INT,
    resposta_usuario CHAR(1),
    correta BOOLEAN,
    FOREIGN KEY (simulado_id) REFERENCES simulados(id),
    FOREIGN KEY (questao_id) REFERENCES questoes(id)
);

-- Inserir conquistas padrão
INSERT INTO conquistas (nome, descricao, icone, pontos_necessarios, tipo) VALUES
('Primeira Questão', 'Responda sua primeira questão', '🎯', 10, 'questoes'),
('Iniciante', 'Responda 10 questões', '🌟', 100, 'questoes'),
('Estudioso', 'Responda 50 questões', '📚', 500, 'questoes'),
('Expert', 'Responda 100 questões', '🏆', 1000, 'questoes'),
('Mestre', 'Responda 500 questões', '👑', 5000, 'questoes'),
('Streak 3', 'Estude 3 dias seguidos', '🔥', 50, 'streak'),
('Streak 7', 'Estude 7 dias seguidos', '🔥🔥', 200, 'streak'),
('Streak 30', 'Estude 30 dias seguidos', '🔥🔥🔥', 1000, 'streak'),
('Nível 5', 'Alcance o nível 5', '⭐', 250, 'nivel'),
('Nível 10', 'Alcance o nível 10', '⭐⭐', 750, 'nivel'),
('Simulador', 'Complete seu primeiro simulado', '📝', 100, 'simulado'),
('Perfeccionista', 'Acerte 100% em um simulado', '💯', 500, 'simulado');