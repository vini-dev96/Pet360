-- ============================================
-- Script SQL para criação do banco de dados Pet360
-- Sistema de Autenticação
-- ============================================
-- 
-- INSTRUÇÕES:
-- 1. Abra o phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Selecione a aba "SQL"
-- 3. Cole este script completo
-- 4. Clique em "Executar"
-- 
-- OU
-- 
-- Execute apenas a parte do CREATE DATABASE primeiro,
-- depois selecione o banco 'pet360_db' e execute o CREATE TABLE
-- ============================================

-- Criar o banco de dados (se não existir)
CREATE DATABASE IF NOT EXISTS `pet360_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

-- Selecionar o banco de dados
USE `pet360_db`;

-- Criar a tabela de usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome completo do usuário',
    `email` VARCHAR(255) NOT NULL COMMENT 'Email único do usuário (usado para login)',
    `telefone` VARCHAR(20) DEFAULT NULL COMMENT 'Telefone de contato (opcional)',
    `senha` VARCHAR(255) NOT NULL COMMENT 'Hash da senha (usando password_hash)',
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação da conta',
    `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status da conta (1=ativo, 0=inativo)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email_unique` (`email`),
    KEY `idx_email` (`email`),
    KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Tabela de usuários do sistema Pet360';

-- ============================================
-- Estrutura da tabela 'usuarios':
-- 
-- id              - Identificador único (auto-incremento)
-- nome            - Nome completo do usuário
-- email           - Email único (usado para login)
-- telefone        - Telefone (opcional)
-- senha           - Hash da senha (nunca armazenar senha em texto plano!)
-- data_criacao    - Timestamp de criação (automático)
-- data_atualizacao- Timestamp de atualização (automático)
-- ativo           - Status da conta (1=ativo, 0=inativo)
-- 
-- Índices:
-- - PRIMARY KEY em 'id' (chave primária)
-- - UNIQUE em 'email' (garante emails únicos)
-- - INDEX em 'email' (otimiza buscas por email)
-- - INDEX em 'ativo' (otimiza filtros por status)
-- ============================================

-- ============================================
-- Criar a tabela de pets
-- ============================================

CREATE TABLE IF NOT EXISTS `pets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NOT NULL COMMENT 'ID do usuário dono do pet',
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do pet',
    `idade` INT(3) DEFAULT NULL COMMENT 'Idade do pet em anos',
    `raca` VARCHAR(255) DEFAULT NULL COMMENT 'Raça do pet',
    `tipo` ENUM('cachorro', 'gato', 'outro') DEFAULT 'cachorro' COMMENT 'Tipo de animal',
    `foto` VARCHAR(255) DEFAULT NULL COMMENT 'Caminho da foto do pet',
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de cadastro',
    `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status do pet (1=ativo, 0=inativo)',
    PRIMARY KEY (`id`),
    KEY `idx_usuario_id` (`usuario_id`),
    KEY `idx_ativo` (`ativo`),
    CONSTRAINT `fk_pets_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Tabela de pets cadastrados pelos usuários';

-- ============================================
-- Estrutura da tabela 'pets':
-- 
-- id              - Identificador único (auto-incremento)
-- usuario_id      - ID do usuário dono do pet (chave estrangeira)
-- nome            - Nome do pet
-- idade           - Idade do pet em anos
-- raca            - Raça do pet
-- tipo            - Tipo de animal (cachorro, gato, outro)
-- foto            - Caminho da foto do pet (opcional)
-- data_criacao    - Timestamp de cadastro (automático)
-- data_atualizacao- Timestamp de atualização (automático)
-- ativo           - Status do pet (1=ativo, 0=inativo)
-- 
-- Índices:
-- - PRIMARY KEY em 'id' (chave primária)
-- - INDEX em 'usuario_id' (otimiza buscas por usuário)
-- - INDEX em 'ativo' (otimiza filtros por status)
-- - FOREIGN KEY em 'usuario_id' (garante integridade referencial)
-- ============================================

-- Verificar se a tabela foi criada corretamente
-- Descomente a linha abaixo para testar:
-- SELECT * FROM usuarios LIMIT 0;
-- SELECT * FROM pets LIMIT 0;
