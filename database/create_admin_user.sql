-- ============================================
-- Script SQL para criar usuário administrador
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

-- Inserir usuário administrador
-- Nome: admin
-- Senha: admin (hash gerado com password_hash PHP)
-- O hash abaixo corresponde à senha "admin" gerado com password_hash('admin', PASSWORD_DEFAULT)

INSERT INTO `usuarios` (`nome`, `email`, `telefone`, `senha`, `data_criacao`, `data_atualizacao`, `ativo`)
VALUES (
    'admin',
    'admin@pet360.com.br',
    '11999999999',
    '$2y$10$KW8TLy6u/NU.CK.WbPlTnOcoe8bzE3eFA3XPeMs7phtQuzuSzuUI2', -- hash da senha "admin"
    NOW(),
    NOW(),
    1
)
ON DUPLICATE KEY UPDATE `nome` = `nome`; -- Evita duplicar se já existir

-- Verificar se o usuário foi criado
-- SELECT id, nome, email FROM usuarios WHERE nome = 'admin';
