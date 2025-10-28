# Funcionalidades do Sistema RCP Concursos

## Visão Geral

O **RCP - Sistema de Concursos** é uma plataforma gamificada completa para candidatos a concursos públicos, oferecendo desde upload de editais até geração inteligente de cronogramas de estudos.

---

## Módulos Principais

### 1. Sistema de Autenticação (Implementado MVC)

#### Login e Registro
- **Cadastro de usuários** com validação de email
- **Login seguro** com hash de senhas (bcrypt)
- **Sessões** gerenciadas automaticamente
- **Recuperação de senha** (a implementar)

**Status**: [OK] Totalmente funcional em MVC
**Arquivos**: `app/Controllers/AuthController.php`

---

### 2. Dashboard Gamificado (Implementado MVC)

#### Visão Geral do Usuário
- **Estatísticas personalizadas** (questões respondidas, taxa de acerto)
- **Nível e pontos** do usuário
- **Streak de dias** estudando
- **Conquistas desbloqueadas**
- **Ranking mensal** entre usuários
- **Histórico de atividades**

#### Gamificação Duolingo-Style
- **Sistema de Pontos**: Ganhe pontos respondendo questões
- **Sistema de Níveis**: Suba de nível conforme progride
- **Streak**: Mantenha sequência de dias estudando
- **Conquistas**: Desbloqueie medalhas e badges
- **Ranking**: Compita com outros estudantes mensalmente

**Status**: Totalmente funcional em MVC
**Arquivos**: `app/Controllers/DashboardController.php`

#### Conquistas Disponíveis
1.  **Primeira Questão** - Responda sua primeira questão
2.  **Iniciante** - Responda 10 questões (100 pontos)
3.  **Estudioso** - Responda 50 questões (500 pontos)
4.  **Expert** - Responda 100 questões (1000 pontos)
5.  **Mestre** - Responda 500 questões (5000 pontos)
6.  **Streak 3** - Estude 3 dias seguidos (50 pontos)
7.  **Streak 7** - Estude 7 dias seguidos (200 pontos)
8.  **Streak 30** - Estude 30 dias seguidos (1000 pontos)
9.  **Nível 5** - Alcance o nível 5 (250 pontos)
10. **Nível 10** - Alcance o nível 10 (750 pontos)
11.  **Simulador** - Complete seu primeiro simulado (100 pontos)
12.  **Perfeccionista** - Acerte 100% em um simulado (500 pontos)

**Fórmula de Nível**: `nível = floor(sqrt(pontos / 100)) + 1`

---

### 3.  Sistema de Editais ( Em Migração)

#### Upload de Editais
- **Upload de PDF** do edital
- **Extração de texto** do PDF
- **Armazenamento** seguro dos arquivos
- **Histórico** de editais enviados

#### Análise de Edital
- **Identificação automática** de disciplinas
- **Extração do conteúdo** programático
- **Organização** por disciplinas e tópicos
- **Geração de lista** estruturada

**Status**:  Sistema antigo funcional, aguardando migração para MVC
**Arquivos**: `old_code/upload_edital.php`, `classes/AnalisadorEdital.php`

---

### 4.  Banco de Questões ( Em Migração)

#### Gerenciamento de Questões
- **Cadastro manual** de questões
- **Organização** por edital e disciplina
- **Alternativas múltiplas** (A, B, C, D, E)
- **Marcação** de alternativa correta
- **Busca e filtragem** de questões

#### Prática de Questões
- **Questão individual** com feedback imediato
- **Pontos** por resposta correta
- **Histórico** de questões respondidas
- **Estatísticas** por disciplina
- **Questões aleatórias** personalizadas

**Status**: [EM MIGRAÇÃO] Sistema antigo funcional, aguardando migração
**Arquivos**: `old_code/questoes.php`, `old_code/questao_individual.php`

---

### 5.  Simulados Personalizados ([EM MIGRAÇÃO] Em Migração)

#### Criação de Simulado
- **Escolha de quantidade** de questões
- **Seleção de disciplinas** (opcional)
- **Seleção de edital** específico (opcional)
- **Questões aleatórias** baseadas nos critérios

#### Execução de Simulado
- **Timer integrado** com contagem regressiva
- **Interface amigável** para responder questões
- **Salvamento automático** de respostas
- **Finalização** manual ou por tempo

#### Correção e Resultados
- **Correção automática** ao finalizar
- **Pontuação detalhada** por disciplina
- **Feedback** de questões erradas/corretas
- **Estatísticas** de performance
- **Gamificação**: Pontos por simulado completo

**Status**:  Sistema antigo funcional, aguardando migração
**Arquivos**: `old_code/simulados.php`, `old_code/simulado.php`

#### Tabelas do Banco
- `simulados` - Dados do simulado
- `simulados_questoes` - Questões e respostas do simulado

---

### 6.  Cronograma de Estudos Inteligente ( Em Migração)

#### Geração Automática
- **Upload do edital** como base
- **Informações do usuário** (tempo disponível, data da prova)
- **Análise de disciplinas** e pesos
- **Distribuição inteligente** de horas
- **Cronograma personalizado** por dia

#### Acompanhamento
- **Checklist** de dias de estudo
- **Marcação de conclusão**
- **Horas realizadas** vs previstas
- **Progresso visual** por disciplina
- **Notificações** (a implementar)

**Status**:  Sistema antigo funcional
**Arquivos**: `old_code/gerar_cronograma.php`, `classes/GeradorCronograma.php`, `classes/GeradorPDFCronograma.php`

#### Recursos Técnicos
- **Algoritmo de distribuição** por peso de disciplina
- **Geração de PDF** do cronograma
- **Cronograma detalhado** com horários
- **Export para Google Calendar** (a implementar)

---

### 7.  Sistema de Videoaulas ([EM MIGRAÇÃO] Em Migração)

#### Organização
- **Categorização** por disciplina e tópico
- **Upload de vídeos** (YouTube, Vimeo, upload local)
- **Descrição** e metadados
- **Duração** e dificuldade

#### Visualização
- **Player integrado**
- **Histórico de visualização**
- **Progresso** por videoaula
- **Comentários** (a implementar)

**Status**: [EM MIGRAÇÃO] Sistema antigo funcional
**Arquivos**: `old_code/videoaulas.php`, `old_code/videoaula_individual.php`, `old_code/videoaulas_categoria.php`

---

### 8.  Perfil do Usuário ([EM MIGRAÇÃO] Em Migração)

#### Informações Pessoais
- **Dados cadastrais**
- **Edição de perfil**
- **Alteração de senha**

#### Estatísticas Detalhadas
- **Histórico completo** de atividades
- **Gráficos** de progresso
- **Performance** por disciplina
- **Timeline** de conquistas

**Status**: [EM MIGRAÇÃO] Sistema antigo funcional
**Arquivos**: `old_code/perfil.php`

---

### 9.  Sistema de Gamificação Completo ([OK] Funcional)

#### Implementado
- [OK] **Classe Gamificacao** (`classes/Gamificacao.php`)
- [OK] **Pontos por questão** correta (10 pontos)
- [OK] **Pontos por simulado** (baseado na performance)
- [OK] **Sistema de níveis** (fórmula exponencial)
- [OK] **Streak de dias** estudando
- [OK] **Conquistas automáticas**
- [OK] **Ranking mensal**
- [OK] **Atualização automática** de progresso

#### Funcionalidades da Classe Gamificacao
```php
- adicionarPontos($usuario_id, $pontos, $tipo)
- calcularNivel($usuario_id)
- atualizarStreak($usuario_id)
- verificarConquistas($usuario_id, $tipo)
- obterRankingMensal($limite)
- obterConquistasUsuario($usuario_id)
```

**Status**:  Totalmente funcional
**Arquivo**: `classes/Gamificacao.php`

---

##  Tabelas do Banco de Dados

### Core
- `usuarios` - Usuários do sistema
- `usuarios_progresso` - Gamificação e níveis

### Conteúdo
- `editais` - Editais enviados
- `disciplinas` - Disciplinas por edital
- `questoes` - Banco de questões
- `respostas_usuario` - Histórico de respostas

### Estudos
- `simulados` - Simulados criados
- `simulados_questoes` - Questões de simulados
- `cronogramas` - Cronogramas de estudo
- `cronograma_detalhado` - Detalhes diários

### Gamificação
- `conquistas` - Conquistas disponíveis (12 tipos)
- `usuarios_conquistas` - Conquistas desbloqueadas
- `ranking_mensal` - Rankings mensais

---

##  Fluxo do Usuário

### 1 Primeiro Acesso
```
Cadastro → Login → Dashboard → Tutorial (a implementar)
```

### 2 Configuração Inicial
```
Upload Edital → Identificação de Disciplinas → Geração de Cronograma
```

### 3 Estudos
```
Questão Individual → Ganhar Pontos → Subir de Nível → Desbloquear Conquistas
OU
Criar Simulado → Responder → Ganhar Pontos → Ver Ranking
```

### 4 Acompanhamento
```
Dashboard → Ver Estatísticas → Ver Conquistas → Ver Ranking → Ajustar Estudos
```

---

##  Status de Migração para MVC

| Funcionalidade | Status Atual | Status MVC | Arquivos |
|----------------|--------------|------------|----------|
| Homepage |  Funcional |  Implementado | `app/Views/pages/home/` |
| Login/Registro | Funcional |  Implementado | `app/Controllers/AuthController.php` |
| Dashboard |  Funcional |  Implementado | `app/Controllers/DashboardController.php` |
| Gamificação |  Funcional |  Aguardando | `classes/Gamificacao.php` |
| Questões |  Funcional |  Pendente | `old_code/questoes.php` |
| Simulados |  Funcional |  Pendente | `old_code/simulados.php` |
| Editais |  Funcional |  Pendente | `old_code/editais.php` |
| Cronograma |  Funcional |  Pendente | `old_code/gerar_cronograma.php` |
| Videoaulas | Funcional |  Pendente | `old_code/videoaulas.php` |
| Perfil |  Funcional | [EM MIGRAÇÃO] Pendente | `old_code/perfil.php` |

---

## Funcionalidades em Desenvolvimento

### Curto Prazo
- [ ] API REST para integração
- [ ] Notificações push
- [ ] Modo offline (PWA)
- [ ] Chat entre usuários
- [ ] Fórum de discussão

### Médio Prazo
- [ ] Exportação de cronograma (PDF/Excel)
- [ ] Sincronização com Google Calendar
- [ ] App mobile (React Native)
- [ ] Integração com YouTube/Vimeo
- [ ] Sistema de reviews de videoaulas

### Longo Prazo
- [ ] IA para análise de edital
- [ ] Recomendação inteligente de questões
- [ ] Análise preditiva de aprovação
- [ ] Machine Learning para personalização
- [ ] Integração com bibliotecas digitais

---

## Tecnologias Utilizadas

### Backend
- **PHP 7.4+** com OOP
- **MySQL 5.7+** como banco de dados
- **PDO** para queries seguras
- **Sessions** para autenticação

### Frontend
- **HTML5/CSS3**
- **JavaScript** (Vanilla)
- **Font Awesome** para ícones
- **Google Fonts** (Inter)

### Arquitetura
- **MVC Pattern** (em migração)
- **SOLID Principles**
- **PSR-4** Autoloading
- **Singleton Pattern**

---

##  Métricas e Estatísticas

### Pontuação
- **Questão correta**: 10 pontos
- **Simulado completo**: 50+ pontos (baseado na performance)
- **Conquista**: Pontos bônus conforme conquista

### Níveis
- **Fórmula**: `nível = floor(sqrt(pontos / 100)) + 1`
- **Raro**: Níveis 1-5
- **Comum**: Níveis 6-15
- **Epic**: Níveis 16-25
- **Legendary**: Níveis 26+

### Conquistas por Categoria
- **Questões**: 5 conquistas
- **Streak**: 3 conquistas
- **Nível**: 2 conquistas
- **Simulado**: 2 conquistas

---

##  Diferenciais do Sistema

1. ** Gamificação Completa** - Sistema estilo Duolingo
2. ** Análise de Dados** - Dashboard com estatísticas avançadas
3. ** Cronograma Inteligente** - Geração automática personalizada
4. ** Automação** - Upload de edital e extração automática
5. ** Foco no Progresso** - Acompanhamento detalhado
6. ** Competição Saudável** - Ranking mensal motivacional
7. ** Responsivo** - Funciona em mobile, tablet e desktop
8. ** Seguro** - Prepared statements e hash de senhas

---

**Sistema completo para transformar estudos em uma jornada gamificada e eficiente!**

