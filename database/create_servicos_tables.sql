-- ============================================
-- Criar tabela de serviços
-- ============================================

USE pet360_db;

CREATE TABLE IF NOT EXISTS `servicos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do serviço',
    `descricao` TEXT DEFAULT NULL COMMENT 'Descrição detalhada do serviço',
    `preco` DECIMAL(10, 2) NOT NULL COMMENT 'Preço do serviço em R$',
    `duracao` INT(11) DEFAULT NULL COMMENT 'Duração estimada em minutos',
    `tipo` ENUM('banho_tosa', 'adestramento', 'passeios') NOT NULL COMMENT 'Tipo de serviço',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status do serviço (1=ativo, 0=inativo)',
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de cadastro',
    `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',
    PRIMARY KEY (`id`),
    KEY `idx_tipo` (`tipo`),
    KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Tabela de serviços disponíveis';

-- ============================================
-- Criar tabela de agendamentos/compras
-- ============================================

CREATE TABLE IF NOT EXISTS `agendamentos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NOT NULL COMMENT 'ID do usuário que comprou/agendou',
    `pet_id` INT(11) NOT NULL COMMENT 'ID do pet para o qual o serviço foi agendado',
    `servico_id` INT(11) NOT NULL COMMENT 'ID do serviço comprado',
    `data_agendamento` DATETIME DEFAULT NULL COMMENT 'Data e hora agendada para o serviço',
    `status` ENUM('pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente' COMMENT 'Status do agendamento',
    `valor_pago` DECIMAL(10, 2) NOT NULL COMMENT 'Valor pago pelo serviço',
    `observacoes` TEXT DEFAULT NULL COMMENT 'Observações do agendamento',
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da compra/agendamento',
    `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data e hora da última atualização',
    PRIMARY KEY (`id`),
    KEY `idx_usuario_id` (`usuario_id`),
    KEY `idx_pet_id` (`pet_id`),
    KEY `idx_servico_id` (`servico_id`),
    KEY `idx_status` (`status`),
    KEY `idx_data_agendamento` (`data_agendamento`),
    CONSTRAINT `fk_agendamentos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_agendamentos_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_agendamentos_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Tabela de agendamentos/compras de serviços';

-- ============================================
-- Inserir serviços padrão
-- ============================================

INSERT INTO `servicos` (`nome`, `descricao`, `preco`, `duracao`, `tipo`, `ativo`) VALUES
('Banho & Tosa Premium', 'Higiene completa, produtos hipoalergênicos e ambiente seguro para que o pet se sinta tranquilo. Inclui banho terapêutico, tosa higiênica, escovação, limpeza de ouvidos e corte de unhas.', 60.00, 120, 'banho_tosa', 1),
('Adestramento Positivo', 'Protocolos personalizados para cada fase da vida do pet, com reforço positivo e acompanhamento familiar. Inclui avaliação comportamental completa, sessões presenciais e atividades guiadas em casa.', 80.00, 60, 'adestramento', 1),
('Passeio Monitorado', 'Companhia carinhosa com passeios monitorados, alimentação conforme rotina do pet e muita diversão. Inclui monitoramento GPS, fotos em tempo real e relatório diário com check-ins.', 40.00, 45, 'passeios', 1);
