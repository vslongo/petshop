-- Versão simplificada do script de atualização
-- Se der erro de "coluna já existe", ignore e continue

USE petshop_db;

-- Adicionar campos de taxi pet (ignore erro se já existir)
ALTER TABLE servicos ADD COLUMN taxi_pet_disponivel TINYINT(1) DEFAULT 0 COMMENT 'Se o serviço oferece taxi pet';
ALTER TABLE servicos ADD COLUMN taxa_taxi_base DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Taxa base do taxi pet';
ALTER TABLE servicos ADD COLUMN taxa_taxi_por_km DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Taxa adicional por km';

-- Criar tabela de preços por peso
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

-- Adicionar campos nos agendamentos (ignore erro se já existir)
ALTER TABLE agendamentos ADD COLUMN pet_peso DECIMAL(5, 2) DEFAULT NULL COMMENT 'Peso do pet em kg';
ALTER TABLE agendamentos ADD COLUMN usar_taxi_pet TINYINT(1) DEFAULT 0 COMMENT 'Se o cliente quer usar taxi pet';
ALTER TABLE agendamentos ADD COLUMN distancia_km DECIMAL(5, 2) DEFAULT NULL COMMENT 'Distância em km para cálculo do taxi';
ALTER TABLE agendamentos ADD COLUMN valor_taxi DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Valor calculado do taxi pet';

-- Configurar taxi pet para alguns serviços
UPDATE servicos SET 
    taxi_pet_disponivel = 1,
    taxa_taxi_base = 15.00,
    taxa_taxi_por_km = 2.50
WHERE nome IN ('Banho', 'Tosa', 'Consulta Veterinária')
AND (taxi_pet_disponivel IS NULL OR taxi_pet_disponivel = 0);

-- Preços por peso para Banho (apenas se não existir)
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 0.00, 5.00, 40.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 5.01, 10.00, 50.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 10.01, 20.00, 65.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 20.01, 999.99, 80.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'gato', 0.00, 3.00, 35.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'gato', 3.01, 5.00, 45.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'gato', 5.01, 999.99, 55.00 FROM servicos WHERE nome = 'Banho' LIMIT 1;

-- Preços por peso para Tosa (apenas se não existir)
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 0.00, 5.00, 60.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 5.01, 10.00, 80.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 10.01, 20.00, 100.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'cao', 20.01, 999.99, 120.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'gato', 0.00, 3.00, 50.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'gato', 3.01, 5.00, 65.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;
INSERT IGNORE INTO servico_precos_peso (servico_id, tipo_animal, peso_min, peso_max, preco) 
SELECT id, 'gato', 5.01, 999.99, 80.00 FROM servicos WHERE nome = 'Tosa' LIMIT 1;




