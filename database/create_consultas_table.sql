-- ============================================
-- Script SQL para criar tabela de consultas de adestramento
-- Pet360 - Dashboard Master
-- ============================================
-- 
-- INSTRUÇÕES:
-- 1. Abra o phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Selecione o banco 'pet360_db'
-- 3. Selecione a aba "SQL"
-- 4. Cole este script completo
-- 5. Clique em "Executar"
-- ============================================

USE `pet360_db`;

-- Criar a tabela de consultas de adestramento
CREATE TABLE IF NOT EXISTS `consultas_adestramento` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pet_id` INT(11) NOT NULL COMMENT 'ID do pet',
    `usuario_id` INT(11) NOT NULL COMMENT 'ID do tutor (usuário)',
    `data_consulta` DATETIME NOT NULL COMMENT 'Data e hora da consulta',
    `observacoes` TEXT DEFAULT NULL COMMENT 'Observações da consulta (timeline de evolução)',
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação do registro',
    `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',
    PRIMARY KEY (`id`),
    KEY `idx_pet_id` (`pet_id`),
    KEY `idx_usuario_id` (`usuario_id`),
    KEY `idx_data_consulta` (`data_consulta`),
    CONSTRAINT `fk_consultas_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_consultas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Tabela de consultas de adestramento com observações para timeline de evolução';

-- ============================================
-- Estrutura da tabela 'consultas_adestramento':
-- 
-- id              - Identificador único (auto-incremento)
-- pet_id          - ID do pet (chave estrangeira)
-- usuario_id      - ID do tutor (chave estrangeira)
-- data_consulta   - Data e hora da consulta
-- observacoes     - Observações da consulta (texto longo para timeline)
-- data_criacao    - Timestamp de criação (automático)
-- data_atualizacao- Timestamp de atualização (automático)
-- 
-- Índices:
-- - PRIMARY KEY em 'id' (chave primária)
-- - INDEX em 'pet_id' (otimiza buscas por pet)
-- - INDEX em 'usuario_id' (otimiza buscas por tutor)
-- - INDEX em 'data_consulta' (otimiza ordenação por data)
-- - FOREIGN KEY em 'pet_id' (garante integridade referencial)
-- - FOREIGN KEY em 'usuario_id' (garante integridade referencial)
-- ============================================

-- Verificar se a tabela foi criada corretamente
-- SELECT * FROM consultas_adestramento LIMIT 0;
