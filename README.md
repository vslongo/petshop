# 🐾 Petshop Premium - Sistema Web Completo

Sistema web completo em PHP para gerenciamento de petshop, incluindo produtos, serviços, agendamento online e painel administrativo.

## 📋 Características

- ✅ Página inicial com apresentação do petshop
- ✅ Catálogo de produtos com imagens, preços e descrições
- ✅ Listagem de serviços oferecidos
- ✅ Sistema de agendamento online com calendário interativo
- ✅ Painel administrativo completo
- ✅ Gerenciamento de produtos, serviços e agendamentos
- ✅ Configuração de horários de atendimento
- ✅ Dashboard com estatísticas
- ✅ Design responsivo com Bootstrap 5
- ✅ Interface moderna e intuitiva

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 7.4+ (puro, sem framework)
- **Banco de Dados:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Framework CSS:** Bootstrap 5.3
- **Ícones:** Bootstrap Icons

## 📦 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior (ou MariaDB 10.2+)
- Extensões PHP: mysqli, mbstring

> **Nota:** Não é necessário configurar Apache ou Nginx! Você pode usar opções mais simples abaixo.

## 🚀 Instalação

### 1. Clone ou baixe o projeto

```bash
git clone <url-do-repositorio>
cd petshop
```

### 2. Configure o banco de dados

1. Crie um banco de dados MySQL:
```sql
CREATE DATABASE petshop_db;
```

2. Execute o script SQL fornecido:
```bash
mysql -u root -p petshop_db < database.sql
```

Ou importe o arquivo `database.sql` pelo phpMyAdmin ou outra ferramenta de gerenciamento MySQL.

### 3. Configure a conexão com o banco

Edite o arquivo `config/database.php` e ajuste as credenciais do banco de dados:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('DB_NAME', 'petshop_db');
```

### 4. Inicie o servidor web (escolha uma opção)

#### 🟢 Opção 1: PHP Built-in Server (MAIS FÁCIL - Recomendado)

O PHP já vem com um servidor web embutido. É a forma mais simples! Abra o terminal/prompt de comando na pasta do projeto e execute:

**Windows (PowerShell ou CMD):**
```bash
cd C:\petshop
php -S localhost:8000
```

**Linux/Mac:**
```bash
cd /caminho/para/petshop
php -S localhost:8000
```

Depois acesse: **http://localhost:8000**

> ⚠️ Mantenha o terminal aberto enquanto usar o sistema. Para parar, pressione `Ctrl + C`

#### 🟡 Opção 2: XAMPP (Windows/Mac/Linux)

Se você prefere usar XAMPP (que já inclui Apache + PHP + MySQL):

1. Baixe e instale o [XAMPP](https://www.apachefriends.org/)
2. Copie a pasta `petshop` para `C:\xampp\htdocs\` (Windows) ou `/Applications/XAMPP/htdocs/` (Mac)
3. Inicie o XAMPP e ative o Apache e MySQL
4. Acesse: **http://localhost/petshop**

**Vantagens:** Interface gráfica, fácil de usar, já vem com MySQL/phpMyAdmin  
**Desvantagens:** Precisa instalar e iniciar manualmente

#### 🟡 Opção 3: WAMP (Apenas Windows)

1. Baixe e instale o [WAMP Server](https://www.wampserver.com/)
2. Copie a pasta `petshop` para `C:\wamp64\www\`
3. Inicie o WAMP e ative os serviços
4. Acesse: **http://localhost/petshop**

#### 🔵 Opção 4: Apache Manual (Avançado - Opcional)

Se você já tem Apache configurado e quer usar, pode configurar um VirtualHost. Mas **não é necessário** para rodar o sistema!

### 5. Permissões de pastas (Linux/Mac)

```bash
chmod -R 755 assets/images
```

### 6. Acesse o sistema

**Se usou PHP Built-in Server (Opção 1):**
- **Site público:** http://localhost:8000
- **Painel administrativo:** http://localhost:8000/admin/

**Se usou XAMPP/WAMP (Opções 2 e 3):**
- **Site público:** http://localhost/petshop
- **Painel administrativo:** http://localhost/petshop/admin/

#### Credenciais padrão do admin:
- **E-mail:** admin@petshop.com
- **Senha:** admin123

⚠️ **IMPORTANTE:** Altere a senha do administrador após o primeiro login!

## 📁 Estrutura do Projeto

```
petshop/
├── admin/                 # Área administrativa
│   ├── includes/          # Arquivos de autenticação e templates
│   ├── agenda.php         # Configuração de horários
│   ├── agendamentos.php   # Gerenciar agendamentos
│   ├── index.php          # Dashboard
│   ├── login.php          # Página de login
│   ├── logout.php         # Logout
│   ├── produtos.php       # CRUD de produtos
│   └── servicos.php       # CRUD de serviços
├── assets/                # Recursos estáticos
│   ├── css/
│   │   └── style.css      # Estilos personalizados
│   ├── js/
│   │   ├── agendamento.js # Script do calendário
│   │   └── main.js        # JavaScript principal
│   └── images/
│       └── produtos/       # Imagens dos produtos
├── config/
│   └── database.php        # Configuração do banco
├── includes/              # Arquivos compartilhados
│   ├── header.php         # Cabeçalho do site
│   └── footer.php         # Rodapé do site
├── agendamento.php        # Página de agendamento
├── index.php              # Página inicial
├── produtos.php           # Catálogo de produtos
├── servicos.php           # Lista de serviços
├── database.sql           # Script de criação do banco
└── README.md              # Este arquivo
```

## 🎯 Funcionalidades Principais

### Área Pública

1. **Home** - Apresentação do petshop e destaques
2. **Produtos** - Catálogo com busca e detalhes
3. **Serviços** - Lista de serviços oferecidos
4. **Agendamento** - Sistema completo com calendário interativo
   - Seleção de data disponível
   - Escolha de horário livre
   - Prevenção de conflitos de horário
   - Formulário completo de dados

### Área Administrativa

1. **Dashboard** - Estatísticas e próximos agendamentos
2. **Produtos** - Cadastro, edição e exclusão
3. **Serviços** - Gerenciamento completo
4. **Agendamentos** - Visualizar e gerenciar (alterar status, excluir)
5. **Configurar Horários** - Definir dias e horários de atendimento

## 🔧 Configurações Adicionais

### Upload de Imagens

As imagens dos produtos devem ser colocadas na pasta `assets/images/produtos/`. No cadastro de produtos, informe apenas o nome do arquivo (ex: `produto1.jpg`).

**Exemplo:**
1. Faça upload da imagem para `assets/images/produtos/racao.jpg`
2. No cadastro, informe `racao.jpg` no campo "Imagem"

### Configuração de Horários

Configure os horários de atendimento em **Admin > Configurar Horários**:
- Defina dias da semana disponíveis
- Configure horário de início e fim
- Defina intervalo entre agendamentos (ex: 30 minutos)

### Personalização

- Edite `assets/css/style.css` para personalizar o visual
- Modifique os includes `header.php` e `footer.php` para ajustar o layout
- Adicione mais funcionalidades conforme necessário

## 📝 Notas Importantes

1. **Segurança:** Em produção, considere:
   - Usar HTTPS
   - Implementar proteção CSRF
   - Validar e sanitizar todas as entradas
   - Usar prepared statements (já implementado)
   - Hash de senhas (já implementado com password_hash)

2. **Performance:** Para sites com muito tráfego:
   - Implemente cache
   - Otimize consultas SQL
   - Use CDN para assets

3. **Extras Opcionais:**
   - Envio de e-mails de confirmação (usando PHPMailer)
   - Sistema de upload de imagens
   - Paginação de produtos
   - Área de cliente

## 🐛 Troubleshooting

### Erro de conexão com banco de dados
- Verifique as credenciais em `config/database.php`
- Confirme que o MySQL está rodando
- Verifique se o banco `petshop_db` existe

### Imagens não aparecem
- Verifique se as imagens estão em `assets/images/produtos/`
- Confirme permissões da pasta (755 ou 777)
- Verifique o nome do arquivo no cadastro

### Erro ao fazer agendamento
- Verifique se os horários estão configurados em Admin > Configurar Horários
- Confirme que há serviços cadastrados e ativos

## 📄 Licença

Este projeto é open-source e está disponível para uso pessoal e comercial.

## 👨‍💻 Desenvolvido com ❤️

Sistema desenvolvido para demonstrar um petshop completo com todas as funcionalidades solicitadas.

---

**Versão:** 1.0.0  
**Última atualização:** 2024

