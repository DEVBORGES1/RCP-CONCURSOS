# 🎓 RCP - Sistema de Concursos

Plataforma gamificada de estudos para concursos públicos com arquitetura MVC profissional.

---

## 🚀 Início Rápido

### Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx

### Instalação

1. **Configure o banco de dados**:
```bash
mysql -u root -p < banco.sql
```

2. **Configure a conexão**:
Edite `config/config.php` ou `conexao.php`

3. **Acesse o sistema**:
```
http://localhost/RCP-CONCURSOS/
```

---

## 📁 Estrutura do Projeto

```
├── app/              # Código MVC (Nova arquitetura)
├── old_code/         # Código antigo (em migração)
├── docs/             # Documentação técnica
├── config/           # Configurações
├── classes/          # Classes antigas
├── setup/            # Scripts de instalação
├── css/              # Estilos
├── uploads/          # Arquivos enviados
└── archive/          # Arquivos arquivados
```

**📚 [Ver documentação completa da estrutura](docs/ESTRUTURA_PROJETO.md)**

---

## 🎯 Funcionalidades

### ✅ Implementadas (MVC)
- 🏠 Homepage moderna
- 🔐 Sistema de login/registro
- 📊 Dashboard gamificado
- 📈 Sistema de progresso
- 🎮 Gamificação (pontos, níveis, streak)

### 🔄 Em Migração
- 📝 Banco de questões
- 📋 Simulados personalizados
- 📄 Upload de editais
- 🎥 Videoaulas
- 👤 Perfil do usuário

---

## 🏗️ Arquitetura

### Sistema MVC (Novo)
```
app/
├── Controllers/    # Lógica de controle
├── Models/         # Acesso a dados
├── Views/          # Apresentação
└── Core/           # Classes base
```

**📖 [Entender a arquitetura MVC](docs/ARQUITETURA_MVC.md)**

### Características
- ✅ Separação de responsabilidades
- ✅ Orientação a objetos (SOLID)
- ✅ PSR-4 Autoloading
- ✅ Sistema de rotas
- ✅ Segurança (Prepared Statements)

---

## 📚 Documentação

| Documento | Descrição |
|-----------|-----------|
| [ESTRUTURA_PROJETO.md](docs/ESTRUTURA_PROJETO.md) | Organização do projeto |
| [ARQUITETURA_MVC.md](docs/ARQUITETURA_MVC.md) | Arquitetura técnica |
| [README_MIGRACAO.md](docs/README_MIGRACAO.md) | Como migrar código |
| [GUIA_COMPLETO_MIGRACAO.md](docs/GUIA_COMPLETO_MIGRACAO.md) | Guia visual completo |
| [README_TECNICO.md](docs/README_TECNICO.md) | Referência técnica |

---

## 🎮 Como Usar

### Usuário Final
1. Cadastre-se em `/register`
2. Faça login em `/login`
3. Acesse o dashboard
4. Comece a estudar!

### Desenvolvedor

#### Usar Sistema Antigo
```bash
http://localhost/RCP-CONCURSOS/index.php
```

#### Usar Sistema MVC (Recomendado)
```bash
http://localhost/RCP-CONCURSOS/mvc_index.php
```

#### Criar Nova Funcionalidade
```php
// 1. Model
app/Models/MinhaEntidade.php

// 2. Controller
app/Controllers/MinhaEntidadeController.php

// 3. View
app/Views/pages/minha_entidade/index.php

// 4. Rota (app/Core/Router.php)
$this->get('/minha-entidade', 'MinhaEntidadeController@index');
```

**📖 [Ver guia completo](docs/GUIA_COMPLETO_MIGRACAO.md)**

---

## 🔧 Tecnologias

- **Backend**: PHP 7.4+, PDO
- **Banco**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Arquitetura**: MVC, SOLID, PSR-4

---

## 📊 Status do Projeto

### Versão Atual
- **Sistema Principal**: Funcional (código antigo)
- **Sistema MVC**: Parcialmente implementado
- **Migração**: Em andamento

### Próximas Etapas
1. Completar migração para MVC
2. Implementar funcionalidades restantes
3. Adicionar testes
4. Otimização de performance

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

---

## 📝 Licença

Este projeto está sob a licença MIT.

---

## 👥 Equipe

- **Desenvolvedor**: DEVBORGES1
- **Design**: DEVBORGES1 / FABIANO PIROLLI
- **Email**: Bstech.ti@gmail.com

---

## 🎉 Funcionalidades Principais

- 📚 **Banco de Questões**: Milhares de questões organizadas
- 📋 **Simulados Personalizados**: Crie simulados customizados
- 🎮 **Gamificação**: Pontos, níveis, conquistas
- 📈 **Dashboard Inteligente**: Acompanhe seu progresso
- 📄 **Upload de Editais**: Envie e analise editais
- 🎥 **Videoaulas**: Conteúdo estruturado

---

**Desenvolvido com ❤️ para candidatos a concursos públicos**

*Transforme seus estudos em uma jornada gamificada e eficiente!*
