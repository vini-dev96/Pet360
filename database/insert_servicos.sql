-- ============================================
-- Inserir Serviços Padrão
-- Pet360 - Sistema de Gerenciamento de Serviços
-- ============================================
-- 
-- Este script insere os três serviços principais:
-- 1. Banho & Tosa Premium
-- 2. Adestramento Positivo
-- 3. Passeio Monitorado
--
-- IMPORTANTE: Execute este script apenas se a tabela `servicos` já existir
-- mas os serviços ainda não foram inseridos.
-- 
-- Se a tabela não existir, execute primeiro: create_servicos_tables.sql
-- ============================================

USE pet360_db;

-- Verificar se os serviços já existem antes de inserir
-- Se já existirem, este script não fará nada (INSERT IGNORE)

INSERT IGNORE INTO `servicos` (`nome`, `descricao`, `preco`, `duracao`, `tipo`, `ativo`) VALUES
('Banho & Tosa Premium', 'Higiene completa, produtos hipoalergênicos e ambiente seguro para que o pet se sinta tranquilo. Inclui banho terapêutico, tosa higiênica, escovação, limpeza de ouvidos e corte de unhas. Produtos de alta qualidade e profissionais especializados garantem o melhor cuidado para seu pet.', 60.00, 120, 'banho_tosa', 1),
('Adestramento Positivo', 'Protocolos personalizados para cada fase da vida do pet, com reforço positivo e acompanhamento familiar. Inclui avaliação comportamental completa, sessões presenciais e atividades guiadas em casa. Relatórios com vídeos e metas semanais para acompanhar o progresso do seu pet.', 80.00, 60, 'adestramento', 1),
('Passeio Monitorado', 'Companhia carinhosa com passeios monitorados, alimentação conforme rotina do pet e muita diversão. Inclui monitoramento GPS, fotos em tempo real e relatório diário com check-ins. Ideal para pets que precisam de exercício e socialização.', 40.00, 45, 'passeios', 1);

-- Verificar se os serviços foram inseridos
SELECT 'Serviços inseridos com sucesso!' as Status;
SELECT id, nome, preco, tipo, ativo FROM servicos ORDER BY tipo, nome;
