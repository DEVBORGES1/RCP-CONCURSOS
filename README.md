# 🎓 Sistema de Concursos - Plataforma Gamificada de Estudos

Uma plataforma completa para candidatos a concursos públicos que combina tecnologia avançada com gamificação para criar a experiência de estudo mais envolvente e eficiente.

## ✨ Funcionalidades Principais

### 🎮 Sistema de Gamificação (Estilo Duolingo)
- **Pontos e Níveis**: Ganhe pontos respondendo questões e suba de nível
- **Conquistas**: Desbloqueie medalhas e conquistas especiais
- **Ranking Mensal**: Compete com outros estudantes
- **Streak**: Mantenha uma sequência de dias estudando

### 📊 Dashboard Inteligente
- **Estatísticas Visuais**: Acompanhe seu progresso com gráficos
- **Métricas de Performance**: Taxa de acerto, questões respondidas, tempo de estudo
- **Progresso Detalhado**: Visualização clara da evolução

### 📚 Banco de Questões
- **Upload de Editais**: Envie PDFs de editais e provas anteriores
- **Questões Personalizadas**: Cadastre questões por disciplina
- **Prática Individual**: Responda questões com feedback imediato

### 📝 Simulados Inteligentes
- **Criação Personalizada**: Escolha quantidade e disciplinas
- **Timer Integrado**: Controle de tempo durante o simulado
- **Correção Automática**: Feedback instantâneo com pontuação
- **Histórico Completo**: Acompanhe todos os simulados realizados

### 📅 Cronograma de Estudos
- **Geração Automática**: Baseado no tempo disponível e peso das disciplinas
- **Acompanhamento**: Marque horas estudadas e progresso
- **Flexibilidade**: Adaptável às suas necessidades

## 🚀 Instalação

### Pré-requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)

### Passos de Instalação

1. **Clone o repositório**
```bash
git clone [url-do-repositorio]
cd RCP-CONCURSOPUBLICO-main
```

2. **Configure o banco de dados**
```bash
# Execute o arquivo banco_completo.sql no MySQL
mysql -u root -p < banco_completo.sql
```

3. **Configure a conexão** (Opcional)
```php
// Edite o arquivo classes/Database.php para configurar
// Por padrão usa: localhost, concursos, root, (sem senha)
```

4. **Configure permissões**
```bash
chmod 755 uploads/
chmod 644 *.php
```

5. **Acesse o sistema**
```
http://localhost/RCP-CONCURSOPUBLICO-main/
```

## 🎯 Como Usar

### 1. Cadastro e Login
- Acesse a página inicial
- Clique em "Criar Conta" para se cadastrar
- Faça login com suas credenciais

### 2. Upload de Editais
- Vá para "Upload Edital"
- Selecione um arquivo PDF do edital
- O sistema processará automaticamente

### 3. Cadastro de Questões
- Acesse "Banco de Questões"
- Adicione questões manualmente
- Organize por disciplinas

### 4. Criação de Simulados
- Vá para "Simulados"
- Escolha quantidade de questões
- Selecione disciplinas (opcional)
- Inicie o simulado

### 5. Acompanhamento
- Visualize seu progresso no Dashboard
- Acompanhe conquistas e ranking
- Monitore estatísticas de estudo

## 🏗️ Arquitetura do Sistema

### Versão 2.0 - POO (Orientado a Objetos)

O sistema foi completamente refatorado para **Programação Orientada a Objetos (POO)**, implementando padrões de design modernos e as melhores práticas de desenvolvimento.

### Estrutura de Arquivos
```
├── classes/
│   ├── Database.php              # Singleton para conexão DB
│   ├── User.php                  # Gestão de usuários
│   ├── Questao.php               # Gestão de questões
│   ├── Simulado.php              # Gestão de simulados
│   ├── GamificacaoRefatorada.php # Sistema de gamificação (POO)
│   ├── Gamificacao.php           # Sistema antigo (legacy)
│   ├── AnalisadorEdital.php      # Análise de editais
│   └── GeradorCronograma.php     # Geração de cronogramas
├── css/
│   └── style.css               # Estilos modernos e responsivos
├── uploads/                    # Diretório para arquivos enviados
├── banco.sql                   # Estrutura do banco de dados
├── DOCUMENTACAO_POO.md         # Documentação completa POO
├── GUIA_MIGRACAO.md            # Guia de migração Procedural → POO
├── conexao.php                 # Configuração de conexão (legacy)
├── index.php                   # Página inicial
├── login.php                   # Sistema de login
├── register.php                # Sistema de cadastro
├── dashboard.php               # Dashboard principal
├── questoes.php               # Banco de questões
├── questao_individual.php      # Questão individual
├── simulados.php               # Gerenciamento de simulados
├── simulado.php                # Execução de simulados
├── upload_edital.php           # Upload de editais
├── gerar_cronograma.php        # Geração de cronogramas
└── logout.php                  # Logout do sistema
```

### Banco de Dados
- **usuarios**: Dados dos usuários
- **usuarios_progresso**: Progresso e gamificação
- **conquistas**: Sistema de conquistas
- **usuarios_conquistas**: Conquistas desbloqueadas
- **ranking_mensal**: Rankings mensais
- **editais**: Editais enviados
- **disciplinas**: Disciplinas por edital
- **questoes**: Banco de questões
- **respostas_usuario**: Respostas dos usuários
- **simulados**: Simulados criados
- **simulados_questoes**: Questões dos simulados
- **cronogramas**: Cronogramas de estudo
- **cronograma_detalhado**: Detalhes dos cronogramas

## 🎮 Sistema de Gamificação

### Pontuação
- **Questão Correta**: 10 pontos
- **Simulado Completo**: Pontos baseados na performance
- **Conquistas**: Pontos bônus especiais
- **Streak**: Pontos por dias consecutivos

### Níveis
- Fórmula: `nível = floor(sqrt(pontos / 100)) + 1`
- Cada nível requer mais pontos para avançar
- Desbloqueie novas funcionalidades

### Conquistas Disponíveis
- 🎯 Primeira Questão
- 🌟 Iniciante (10 questões)
- 📚 Estudioso (50 questões)
- 🏆 Expert (100 questões)
- 👑 Mestre (500 questões)
- 🔥 Streak 3, 7, 30 dias
- ⭐ Níveis 5, 10
- 📝 Simulador
- 💯 Perfeccionista

## 🔧 Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Design**: CSS Grid, Flexbox, Gradientes
- **Ícones**: Font Awesome 6.0
- **Segurança**: Prepared Statements, Password Hashing

## 🚀 Funcionalidades Futuras

### Próximas Implementações
- [ ] **Análise de Edital com IA**: Extração automática de disciplinas
- [ ] **Cronograma Inteligente**: Algoritmo baseado em peso das disciplinas
- [ ] **Web Crawler**: Busca automática de provas anteriores
- [ ] **Exportação**: Cronogramas em PDF/Google Calendar
- [ ] **Notificações**: Lembretes de estudo
- [ ] **Chat**: Comunidade de estudantes
- [ ] **Mobile App**: Aplicativo móvel

### Melhorias Planejadas
- [ ] **OCR Avançado**: Leitura de PDFs digitalizados
- [ ] **IA para Sugestões**: Recomendações personalizadas
- [ ] **Analytics Avançado**: Relatórios detalhados
- [ ] **Integração Social**: Compartilhamento de progresso

## 🤝 Contribuição

### Como Contribuir
1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

### Padrões de Código
- Use PSR-12 para PHP
- Comente funções complexas
- Mantenha consistência no CSS
- Teste todas as funcionalidades

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 👥 Equipe

- **Desenvolvedor Principal**: DEVBORGES1
- **Design**: DEVBORGES1 / FABIANO PIROLLI
- **Testes**: DEVBORGES1 / FABIANO PIROLLI

## 📞 Suporte

Para dúvidas, sugestões ou problemas:
- **Email**: Bstech.ti@gmail.com
---

**Desenvolvido com ❤️ para candidatos a concursos públicos**

*Transforme seus estudos em uma jornada gamificada e eficiente!*
