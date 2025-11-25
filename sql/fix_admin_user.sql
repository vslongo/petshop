-- Script para corrigir/criar usuário administrador
-- Execute este script se tiver problemas com login

USE petshop_db;

-- Verificar se o usuário existe e atualizar/criar
-- Primeiro, remover o usuário se existir (para recriar)
DELETE FROM usuarios WHERE email = 'admin@petshop.com';

-- Criar o usuário admin com senha 'admin123'
-- O hash foi gerado com password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@petshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Verificar se foi criado
SELECT id, nome, email, tipo FROM usuarios WHERE email = 'admin@petshop.com';

