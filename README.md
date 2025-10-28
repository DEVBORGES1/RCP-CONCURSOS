# RCP - Sistema de Concursos

Plataforma gamificada de estudos para concursos públicos com arquitetura MVC profissional.

## Início Rápido

### Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Composer (para autoloading)

### Instalação

1. **Configure o banco de dados**:
```bash
mysql -u root -p < banco.sql
```

2. **Configure a conexão**:
Edite `config/config.php` com suas credenciais:
```php
'database' => [
    'host' => 'localhost',
    'name' => 'concursos',
    'user' => 'seu_usuario',
    'password' => 'sua_senha',
]
```

3. **Instale as dependências** (opcional):
```bash
composer install
```

4. **Acesse o sistema**:
```
http://localhost/RCP-CONCURSOS/
```


## Funcionalidades

- [x] Homepage moderna e responsiva
- [x] Sistema de autenticação (login/registro)
- [x] Dashboard gamificado com estatísticas
- [x] Sistema de progresso e níveis
- [x] Gamificação completa (pontos, conquistas, ranking)
- [ ] Banco de questões
- [ ] Simulados personalizados
- [ ] Upload e análise de editais
- [ ] Videoaulas
- [ ] Perfil do usuário
- [ ] Cronograma de estudos

## Arquitetura



**Entender a arquitetura**: [docs/ARQUITETURA_MVC.md](docs/ARQUITETURA_MVC.md)

### Características
- Separação de responsabilidades (MVC)
- Orientação a objetos (SOLID)
- PSR-4 Autoloading
- Sistema de rotas
- Segurança (Prepared Statements, hash de senhas)

## Documentação

| Documento | Descrição |
|-----------|-----------|
| [ESTRUTURA_PROJETO.md](docs/ESTRUTURA_PROJETO.md) | Organização do projeto |
| [ARQUITETURA_MVC.md](docs/ARQUITETURA_MVC.md) | Arquitetura técnica completa |
| [README_MIGRACAO.md](docs/README_MIGRACAO.md) | Guia de migração e uso |
| [README_TECNICO.md](docs/README_TECNICO.md) | Referência técnica |
| [FUNCIONALIDADES.md](docs/FUNCIONALIDADES.md) | Lista completa de funcionalidades |
| [STATUS_MIGRACAO.md](docs/STATUS_MIGRACAO.md) | Status da migração MVC |

## Como Usar

### Para Usuários Finais

1. Cadastre-se em `/register`
2. Faça login em `/login`
3. Acesse o dashboard
4. Comece a estudar!

### Para Desenvolvedores

#### Usar Sistema Antigo (Completo)
```bash
http://localhost/RCP-CONCURSOS/index.php
```

#### Usar Sistema MVC (Novo)
```bash
http://localhost/RCP-CONCURSOS/mvc_index.php
```

#### Ativar Sistema MVC Permanente

```bash
# Backup
cp index.php index_old.php

# Ativar MVC
cp mvc_index.php index.php
```

#### Criar Nova Funcionalidade

1. **Criar Model**:
```php
// app/Models/MinhaEntidade.php
namespace App\Models;
use App\Core\BaseModel;

class MinhaEntidade extends BaseModel
{
    protected string $table = 'minha_tabela';
}
```

2. **Criar Controller**:
```php
// app/Controllers/MinhaEntidadeController.php
namespace App\Controllers;
use App\Core\BaseController;
use App\Models\MinhaEntidade;

class MinhaEntidadeController extends BaseController
{
    public function index(): void
    {
        echo $this->view('minha_entidade/index', $data);
    }
}
```

3. **Criar View**:
```php
// app/Views/pages/minha_entidade/index.php
<h1>Minha Entidade</h1>
```

4. **Adicionar Rota**:
```php
// app/Core/Router.php - método defineRoutes()
$this->get('/minha-entidade', 'MinhaEntidadeController@index');
```

Ver guia completo: [docs/README_MIGRACAO.md](docs/README_MIGRACAO.md)

## Tecnologias

- **Backend**: PHP 7.4+, PDO
- **Banco**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Arquitetura**: MVC Pattern, SOLID Principles, PSR-4
- **Autoloading**: Composer PSR-4

## Status do Projeto

### Versão Atual: 2.0.0

- **Sistema Principal**: Funcional (código antigo em `old_code/`)
- **Sistema MVC**: Parcialmente implementado (app/)
- **Migração**: Em andamento
- **Documentação**: Completa

### Próximas Etapas
- [ ] Completar migração para MVC
- [ ] Implementar funcionalidades restantes
- [ ] Adicionar testes automatizados
- [ ] Otimização de performance

Ver status detalhado: [docs/STATUS_MIGRACAO.md](docs/STATUS_MIGRACAO.md)

## Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT.

## Equipe

- **Desenvolvedor**: DEVBORGES1
- **Design**: DEVBORGES1 / FABIANO PIROLLI
- **Email**: Bstech.ti@gmail.com

## Funcionalidades Principais

- **Banco de Questões**: Milhares de questões organizadas por disciplina
- **Simulados Personalizados**: Crie simulados customizados
- **Gamificação**: Sistema completo de pontos, níveis e conquistas
- **Dashboard Inteligente**: Acompanhe seu progresso detalhadamente
- **Upload de Editais**: Envie e analise editais automaticamente
- **Videoaulas**: Conteúdo estruturado por categoria
- **Cronograma Inteligente**: Geração automática de planos de estudo
- **Ranking Mensal**: Compita com outros estudantes

Ver todas as funcionalidades: [docs/FUNCIONALIDADES.md](docs/FUNCIONALIDADES.md)

---

Desenvolvido para candidatos a concursos públicos.

Transforme seus estudos em uma jornada gamificada e eficiente!
