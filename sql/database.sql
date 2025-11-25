-- Banco de Dados para Petshop
-- Execute este script no MySQL para criar o banco e as tabelas

CREATE DATABASE IF NOT EXISTS petshop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE petshop_db;

-- Tabela de Usuários (para área administrativa)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'funcionario') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir usuário administrador padrão (senha: admin123)
-- ALTERE A SENHA APÓS O PRIMEIRO LOGIN!
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@petshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Senha padrão: admin123

-- Tabela de Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    descricao_curta VARCHAR(255),
    preco DECIMAL(10, 2) NOT NULL,
    imagem VARCHAR(255) DEFAULT 'default.jpg',
    categoria VARCHAR(100),
    estoque INT DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    duracao INT NOT NULL COMMENT 'Duração em minutos',
    taxi_pet_disponivel TINYINT(1) DEFAULT 0 COMMENT 'Se o serviço oferece taxi pet',
    taxa_taxi_base DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Taxa base do taxi pet',
    taxa_taxi_por_km DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Taxa adicional por km',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Configuração de Horários
CREATE TABLE IF NOT EXISTS horarios_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana INT NOT NULL COMMENT '0=Dom, 1=Seg, ..., 6=Sáb',
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    hora_inicio_almoco TIME DEFAULT NULL COMMENT 'Início do intervalo de almoço (ex: 12:00)',
    hora_fim_almoco TIME DEFAULT NULL COMMENT 'Fim do intervalo de almoço (ex: 13:00)',
    intervalo INT DEFAULT 60 COMMENT 'Intervalo entre agendamentos em minutos',
    ativo TINYINT(1) DEFAULT 1
);

-- Tabela de Preços por Peso para Serviços
CREATE TABLE IF NOT EXISTS servico_precos_peso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servico_id INT NOT NULL,
    tipo_animal ENUM('cao', 'gato') NOT NULL,
    peso_min DECIMAL(5, 2) NOT NULL COMMENT 'Peso mínimo em kg',
    peso_max DECIMAL(5, 2) NOT NULL COMMENT 'Peso máximo em kg',
    preco DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    INDEX idx_servico_tipo (servico_id, tipo_animal),
    INDEX idx_peso (peso_min, peso_max)
);

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nome VARCHAR(200) NOT NULL,
    cliente_telefone VARCHAR(20) NOT NULL,
    cliente_email VARCHAR(100),
    pet_nome VARCHAR(100) NOT NULL,
    pet_tipo VARCHAR(50),
    pet_peso DECIMAL(5, 2) DEFAULT NULL COMMENT 'Peso do pet em kg',
    servico_id INT NOT NULL,
    data DATE NOT NULL,
    horario TIME NOT NULL,
    usar_taxi_pet TINYINT(1) DEFAULT 0 COMMENT 'Se o cliente quer usar taxi pet',
    distancia_km DECIMAL(5, 2) DEFAULT NULL COMMENT 'Distância em km para cálculo do taxi',
    valor_taxi DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Valor calculado do taxi pet',
    observacoes TEXT,
    status ENUM('pendente', 'confirmado', 'cancelado', 'concluido') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    INDEX idx_data_horario (data, horario),
    INDEX idx_status (status)
);

-- Inserir alguns dados de exemplo
INSERT INTO servicos (nome, descricao, preco, duracao, taxi_pet_disponivel, taxa_taxi_base, taxa_taxi_por_km) VALUES
('Banho', 'Banho completo com produtos de qualidade', 50.00, 60, 1, 15.00, 2.50),
('Tosa', 'Tosa higiênica ou completa conforme raça', 80.00, 90, 1, 15.00, 2.50),
('Consulta Veterinária', 'Consulta com veterinário especializado', 150.00, 60, 1, 15.00, 2.50),
('Vacinação', 'Aplicação de vacinas atualizadas', 80.00, 30, 0, 0.00, 0.00),
('Passeio', 'Passeio recreativo com cuidador', 40.00, 60, 0, 0.00, 0.00);

-- Inserir preços por peso para Banho
INSERT INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) VALUES
-- Banho para Cães
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'cao', 0.00, 5.00, 40.00),   -- Até 5kg
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'cao', 5.01, 10.00, 50.00), -- 5.1 a 10kg
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'cao', 10.01, 20.00, 65.00), -- 10.1 a 20kg
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'cao', 20.01, 999.99, 80.00), -- Acima de 20kg
-- Banho para Gatos
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'gato', 0.00, 3.00, 35.00),  -- Até 3kg
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'gato', 3.01, 5.00, 45.00),  -- 3.1 a 5kg
((SELECT id FROM servicos WHERE nome = 'Banho' LIMIT 1), 'gato', 5.01, 999.99, 55.00); -- Acima de 5kg

-- Inserir preços por peso para Tosa
INSERT INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) VALUES
-- Tosa para Cães
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'cao', 0.00, 5.00, 60.00),   -- Até 5kg
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'cao', 5.01, 10.00, 80.00),  -- 5.1 a 10kg
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'cao', 10.01, 20.00, 100.00), -- 10.1 a 20kg
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'cao', 20.01, 999.99, 120.00), -- Acima de 20kg
-- Tosa para Gatos
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'gato', 0.00, 3.00, 50.00),  -- Até 3kg
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'gato', 3.01, 5.00, 65.00),   -- 3.1 a 5kg
((SELECT id FROM servicos WHERE nome = 'Tosa' LIMIT 1), 'gato', 5.01, 999.99, 80.00); -- Acima de 5kg

INSERT INTO produtos (nome, descricao_curta, descricao, preco, categoria, estoque) VALUES
('Ração Premium para Cães', 'Ração de alta qualidade para cães adultos', 'Ração completa e balanceada com proteínas de alta qualidade, ideal para cães adultos de todas as raças.', 89.90, 'Alimentação', 50),
('Brinquedo para Gatos', 'Brinquedo interativo para entreter seu gato', 'Brinquedo com catnip natural que estimula o instinto de caça dos felinos.', 25.00, 'Brinquedos', 30),
('Coleira Antipulgas', 'Coleira com proteção contra pulgas e carrapatos', 'Coleira com eficácia de até 8 meses contra pulgas e carrapatos.', 45.00, 'Acessórios', 40),
('Shampoo para Banho', 'Shampoo neutro para banho de cães e gatos', 'Shampoo hipoalergênico com pH balanceado, adequado para uso frequente.', 32.50, 'Higiene', 25);

-- Configurar horários padrão (Segunda a Sexta: 9h às 18h, almoço 12h-13h, intervalo de 1 hora)
INSERT INTO horarios_config (dia_semana, hora_inicio, hora_fim, hora_inicio_almoco, hora_fim_almoco, intervalo) VALUES
(1, '09:00', '18:00', '12:00', '13:00', 60), -- Segunda
(2, '09:00', '18:00', '12:00', '13:00', 60), -- Terça
(3, '09:00', '18:00', '12:00', '13:00', 60), -- Quarta
(4, '09:00', '18:00', '12:00', '13:00', 60), -- Quinta
(5, '09:00', '18:00', '12:00', '13:00', 60); -- Sexta

