# 🚗 AutosJulius

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

Sistema web de **revenda de veículos**: uma vitrine pública com o estoque e um painel administrativo para gerenciar carros e usuários.

Feito com **PHP puro (PDO)** e **PostgreSQL**, sem frameworks. O código do site está na pasta `autosjulius5`.

## Funcionalidades

**Público**
- Página de apresentação (`landing.php`)
- Estoque em cards, do veículo mais recente ao mais antigo
- Janela de detalhes: ano, km, combustível, câmbio, preço, peças/revisões e descrição

**Administrador (login obrigatório)**
- Cadastrar, editar e excluir veículos
- Cadastrar, editar e excluir usuários
- Validação dos dados no servidor e mensagens de sucesso/erro após cada ação

## Segurança

- Senhas protegidas com `password_hash` / `password_verify`
- Token CSRF em todos os formulários POST
- Consultas preparadas (PDO) contra SQL Injection
- Saída escapada com `htmlspecialchars` contra XSS
- `session_regenerate_id` no login
- O administrador não consegue excluir a própria conta

## Estrutura

```
autosjulius5/
├── index.php                # Estoque público
├── landing.php              # Apresentação
├── cadastrar_carros.php     # Novo veículo (admin)
├── editar_carro.php         # Editar veículo (admin)
├── database/
│   ├── connect.php          # Conexão PDO com o PostgreSQL
│   └── schema.sql           # Tabelas e dados iniciais
├── includes/                # header, footer, csrf e mensagens flash
└── login/
    ├── login.php            # Entrar
    ├── logout.php           # Sair
    ├── cadastrar.php        # Novo usuário / editar usuário
    ├── listar_logins.php    # Lista de usuários (admin)
    ├── deletar_login.php    # Excluir usuário (admin)
    └── gerenciar_carros.php # Painel de veículos (admin)
```

## Como rodar

Você precisa de **PHP 8+** com a extensão `pdo_pgsql` e de **PostgreSQL 12+**.

**1. Clone o repositório e entre na pasta do site**

```bash
git clone https://github.com/julio09-dot/projeto.git
cd projeto/autosjulius5
```

**2. Crie o banco e as tabelas**

```bash
createdb -U postgres autosjulius
psql -U postgres -d autosjulius -f database/schema.sql
```

Isso cria as tabelas `usuarios` e `veiculos`, o administrador inicial e 3 veículos de exemplo.

**3. Configure a conexão**

O arquivo `database/connect.php` lê estas variáveis de ambiente:

| Variável | Descrição | Padrão |
|---|---|---|
| `DB_HOST` | Servidor do banco | `192.168.10.31` |
| `DB_PORT` | Porta | `5432` |
| `DB_USER` | Usuário | `postgres` |
| `DB_PASS` | Senha | definida no arquivo |
| `DB_NAME` | Nome do banco | `autosjulius` |

```bash
export DB_HOST=localhost DB_USER=postgres DB_PASS=sua_senha DB_NAME=autosjulius
```

> ⚠️ Não publique senhas reais no GitHub. Use sempre variáveis de ambiente.

**4. Inicie o servidor**

Rode a partir da pasta que contém o `index.php` (os links usam caminhos como `/index.php`):

```bash
php -S localhost:8000
```

Acesse http://localhost:8000

## Acesso inicial

| Campo | Valor |
|---|---|
| E-mail | `admin@autosjulius.com` |
| Senha | `admin123` |

**Troque a senha no primeiro acesso** (menu *Visualizar Logins* → *Editar*).

## Banco de dados

**`usuarios`**: `id`, `nome`, `email` (único), `telefone`, `senha` (hash), `tipo` (`admin` ou `vendedor`), `data_cadastro`

**`veiculos`**: `id`, `usuario_id` (FK para `usuarios`), `marca`, `modelo`, `ano`, `preco`, `quilometros`, `combustivel`, `cambio`, `pecas`, `descricao`, `data_cadastro`

## Páginas

| Rota | Acesso | Função |
|---|---|---|
| `/landing.php` | Público | Apresentação |
| `/index.php` | Público | Estoque |
| `/login/login.php` | Público | Entrar |
| `/login/gerenciar_carros.php` | Admin | Painel de veículos |
| `/cadastrar_carros.php` | Admin | Novo veículo |
| `/editar_carro.php?id=` | Admin | Editar veículo |
| `/login/listar_logins.php` | Admin | Lista de usuários |

## Reportando problemas

Encontrou um erro? Abra uma [issue](https://github.com/julio09-dot/projeto/issues) descrevendo o que aconteceu e como reproduzir.

## Contribuindo

Sugestões são bem-vindas. Faça um fork, crie uma branch e envie um Pull Request para a branch `main`.

## Próximos passos

- [ ] Fotos dos veículos
- [ ] Busca e filtros no estoque
- [ ] Formulário de contato / interesse
- [ ] Pedido de avaliação de troca
- [ ] Status do veículo (disponível, reservado, vendido)

## Autor

**Júlio César** — [@julio09-dot](https://github.com/julio09-dot)
