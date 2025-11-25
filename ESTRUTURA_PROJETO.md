# Estrutura do Projeto - Cão e Gato Princes

## 📁 Organização de Pastas

```
petshop/
├── admin/                    # Painel administrativo
│   ├── includes/              # Arquivos compartilhados do admin
│   ├── agenda.php            # Gerenciamento de agenda
│   ├── agendamentos.php      # Lista de agendamentos
│   ├── produtos.php          # Gerenciamento de produtos
│   ├── servicos.php          # Gerenciamento de serviços
│   └── login.php             # Login administrativo
│
├── assets/                    # Recursos estáticos
│   ├── css/                  # Estilos CSS
│   ├── images/               # Imagens
│   │   ├── banner/           # Imagens do carrossel (pet1.jpg, pet2.jpg, pet3.jpg)
│   │   └── produtos/         # Imagens dos produtos
│   └── js/                   # Scripts JavaScript
│
├── config/                   # Configurações
│   └── database.php          # Conexão com banco de dados
│
├── includes/                  # Arquivos compartilhados do site
│   ├── header.php            # Cabeçalho do site
│   └── footer.php            # Rodapé do site
│
├── sql/                      # Scripts SQL
│   ├── database.sql          # Script inicial do banco
│   ├── database_updates.sql  # Atualizações (preços por peso, taxi pet)
│   ├── fix_admin_user.sql    # Correção de usuário admin
│   └── update_horarios_almoco.sql
│
├── docs/                     # Documentação
│   ├── CORRECOES_AGENDAMENTO.md
│   └── INICIO_RAPIDO.md
│
├── scripts/                  # Scripts auxiliares
│   └── teste_agendamento.php
│
├── index.php                 # Página inicial
├── produtos.php              # Página de produtos
├── servicos.php              # Página de serviços
├── agendamento.php           # Página de agendamento
└── README.md                 # Documentação principal
```

## 🎨 Paleta de Cores

- **Cor Principal:** #4ECDC4 (Azul Turquesa) - Cor do logo
- **Cor Secundária:** #2C9A91 (Turquesa Escuro)
- **Cor Terciária:** #6EDDD6 (Turquesa Claro)
- **Cor Destaque:** #FFB84D (Laranja)
- **Cor Taxi Pet:** #F59E0B (Amarelo/Laranja)

## 🚀 Funcionalidades Implementadas

### 1. Banner/Carrossel
- Carrossel com imagens de pets na página inicial
- Imagens devem ser colocadas em `assets/images/banner/` (pet1.jpg, pet2.jpg, pet3.jpg)
- Recomendado: 1920x500px

### 2. Preços por Peso
- Sistema de preços diferentes conforme peso do animal
- Configurável por tipo (cão/gato) e faixa de peso
- Tabela: `servico_precos_peso`

### 3. Taxi Pet
- Serviço de transporte para pets
- Taxa base + taxa por km
- Configurável por serviço
- Campos na tabela `servicos`: `taxi_pet_disponivel`, `taxa_taxi_base`, `taxa_taxi_por_km`

### 4. Estrutura Organizada
- Pastas separadas para SQL, documentação e scripts
- Código mais limpo e organizado

## 📝 Próximos Passos

1. Adicionar imagens do banner em `assets/images/banner/`
2. Executar `sql/database_updates.sql` para adicionar novas funcionalidades
3. Configurar preços por peso no painel administrativo
4. Configurar taxi pet nos serviços desejados

