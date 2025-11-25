-- Atualizações do Banco de Dados
-- Execute este script para adicionar funcionalidades de preço por peso e taxi pet

USE petshop_db;

-- Adicionar campo de taxi pet na tabela de serviços
ALTER TABLE servicos 
ADD COLUMN IF NOT EXISTS taxi_pet_disponivel TINYINT(1) DEFAULT 0 COMMENT 'Se o serviço oferece taxi pet',
ADD COLUMN IF NOT EXISTS taxa_taxi_base DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Taxa base do taxi pet',
ADD COLUMN IF NOT EXISTS taxa_taxi_por_km DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Taxa adicional por km';

-- Criar tabela de preços por peso para serviços
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

-- Adicionar campo de peso do pet nos agendamentos
ALTER TABLE agendamentos 
ADD COLUMN IF NOT EXISTS pet_peso DECIMAL(5, 2) DEFAULT NULL COMMENT 'Peso do pet em kg',
ADD COLUMN IF NOT EXISTS usar_taxi_pet TINYINT(1) DEFAULT 0 COMMENT 'Se o cliente quer usar taxi pet',
ADD COLUMN IF NOT EXISTS distancia_km DECIMAL(5, 2) DEFAULT NULL COMMENT 'Distância em km para cálculo do taxi',
ADD COLUMN IF NOT EXISTS valor_taxi DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Valor calculado do taxi pet';

-- Exemplo: Configurar taxi pet para alguns serviços
UPDATE servicos SET 
    taxi_pet_disponivel = 1,
    taxa_taxi_base = 15.00,
    taxa_taxi_por_km = 2.50
WHERE nome IN ('Banho', 'Tosa', 'Consulta Veterinária');

-- Exemplo: Preços por peso para Banho
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

-- Exemplo: Preços por peso para Tosa
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

