-- Script para adicionar campos de almoço na tabela horarios_config
-- Execute este script se a tabela já existe e precisa atualizar

-- Adicionar colunas de almoço se não existirem
ALTER TABLE horarios_config 
ADD COLUMN IF NOT EXISTS hora_inicio_almoco TIME DEFAULT NULL COMMENT 'Início do intervalo de almoço (ex: 12:00)',
ADD COLUMN IF NOT EXISTS hora_fim_almoco TIME DEFAULT NULL COMMENT 'Fim do intervalo de almoço (ex: 13:00)';

-- Atualizar intervalo padrão para 60 minutos (1 hora) se for 30
UPDATE horarios_config SET intervalo = 60 WHERE intervalo = 30;

-- Atualizar horários existentes para incluir almoço 12h-13h
UPDATE horarios_config 
SET hora_inicio_almoco = '12:00', 
    hora_fim_almoco = '13:00',
    intervalo = 60
WHERE hora_inicio_almoco IS NULL 
  AND hora_fim_almoco IS NULL 
  AND ativo = 1;

