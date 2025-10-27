USE concursos;

-- Tabela de categorias de videoaulas
CREATE TABLE IF NOT EXISTS videoaulas_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    icone VARCHAR(50) DEFAULT 'fas fa-video',
    cor VARCHAR(20) DEFAULT '#667eea',
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de videoaulas
CREATE TABLE IF NOT EXISTS videoaulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    url VARCHAR(500),
    duracao INT DEFAULT 0 COMMENT 'Duração em minutos',
    nivel VARCHAR(20) DEFAULT 'iniciante' COMMENT 'iniciante, intermediario, avancado',
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES videoaulas_categorias(id) ON DELETE CASCADE
);

-- Tabela de progresso de videoaulas por usuário
CREATE TABLE IF NOT EXISTS videoaulas_progresso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    videoaula_id INT NOT NULL,
    tempo_assistido INT DEFAULT 0 COMMENT 'Tempo em segundos',
    concluida BOOLEAN DEFAULT 0,
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    data_ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (videoaula_id) REFERENCES videoaulas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_videoaula (usuario_id, videoaula_id)
);

-- Inserir categorias de exemplo
INSERT INTO videoaulas_categorias (nome, descricao, icone, cor, ordem) VALUES
('Português', 'Videoaulas de Português para concursos', 'fas fa-book', '#3498db', 1),
('Matemática', 'Videoaulas de Matemática para concursos', 'fas fa-calculator', '#e74c3c', 2),
('Direito Constitucional', 'Videoaulas de Direito Constitucional', 'fas fa-gavel', '#9b59b6', 3),
('Informática', 'Videoaulas de Informática para concursos', 'fas fa-laptop', '#16a085', 4),
('Raciocínio Lógico', 'Videoaulas de Raciocínio Lógico', 'fas fa-brain', '#f39c12', 5);

-- Inserir videoaulas de exemplo (você pode adicionar URLs reais depois)
INSERT INTO videoaulas (categoria_id, titulo, descricao, duracao, nivel, ordem) VALUES
(1, 'Concordância Verbal', 'Aprendendo as regras de concordância verbal', 30, 'iniciante', 1),
(1, 'Concordância Nominal', 'Regras de concordância nominal em detalhes', 25, 'intermediario', 2),
(1, 'Regência Verbal', 'Entendendo regência verbal e nominal', 35, 'avancado', 3),
(2, 'Álgebra Básica', 'Conceitos fundamentais de álgebra', 40, 'iniciante', 1),
(2, 'Geometria Plana', 'Aspectos importantes da geometria plana', 50, 'intermediario', 2),
(3, 'Constituição Federal - Artigos Iniciais', 'Princípios fundamentais da CF', 45, 'iniciante', 1),
(3, 'Direitos e Garantias Fundamentais', 'Direitos fundamentais detalhados', 60, 'intermediario', 2),
(4, 'Windows e Linux', 'Sistemas operacionais básicos', 20, 'iniciante', 1),
(4, 'Excel Avançado', 'Funções avançadas do Excel', 35, 'avancado', 2),
(5, 'Lógica Proposicional', 'Proposições e conectivos lógicos', 30, 'iniciante', 1),
(5, 'Tabelas Verdade', 'Construindo e analisando tabelas verdade', 25, 'intermediario', 2);

