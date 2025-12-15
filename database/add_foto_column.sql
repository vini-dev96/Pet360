-- ============================================
-- Script SQL para adicionar campo foto na tabela pets
-- Pet360 - Sistema de Gerenciamento de Pets
-- ============================================
-- 
-- INSTRUÇÕES:
-- 1. Abra o phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Selecione o banco de dados 'pet360_db' no menu lateral esquerdo
-- 3. Clique na aba "SQL" no topo
-- 4. Cole este script
-- 5. Clique em "Executar" ou pressione Ctrl + Enter
-- ============================================

USE `pet360_db`;

-- Adicionar coluna foto (execute apenas se a coluna ainda não existir)
-- Se der erro dizendo que a coluna já existe, ignore o erro
ALTER TABLE `pets` 
ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL COMMENT 'Caminho da foto do pet' 
AFTER `tipo`;

-- Verificar se a coluna foi adicionada
SELECT 'Coluna foto adicionada com sucesso!' AS mensagem;
DESCRIBE `pets`;
