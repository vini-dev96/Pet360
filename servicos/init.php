<?php
/**
 * Inicializar Serviços no Banco de Dados
 * Pet360 - Sistema de Gerenciamento de Serviços
 * 
 * Este arquivo pode ser executado via navegador para inserir os serviços
 * caso eles não existam no banco de dados.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

startSecureSession();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    die('Você precisa estar logado para executar esta ação.');
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'servicos'");
    if ($stmt->rowCount() == 0) {
        die('A tabela "servicos" não existe. Execute primeiro o script create_servicos_tables.sql no phpMyAdmin.');
    }
    
    // Verificar quantos serviços já existem
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM servicos");
    $result = $stmt->fetch();
    $totalServicos = $result['total'];
    
    if ($totalServicos >= 3) {
        // Serviços já existem, redirecionar
        $_SESSION['servicos_inseridos'] = 0;
        header('Location: ../dashboard.php?servicos_inseridos=0');
        exit;
    }
    
    // Inserir serviços
    $servicos = [
        [
            'nome' => 'Banho & Tosa Premium',
            'descricao' => 'Higiene completa, produtos hipoalergênicos e ambiente seguro para que o pet se sinta tranquilo. Inclui banho terapêutico, tosa higiênica, escovação, limpeza de ouvidos e corte de unhas. Produtos de alta qualidade e profissionais especializados garantem o melhor cuidado para seu pet.',
            'preco' => 60.00,
            'duracao' => 120,
            'tipo' => 'banho_tosa',
            'ativo' => 1
        ],
        [
            'nome' => 'Adestramento Positivo',
            'descricao' => 'Protocolos personalizados para cada fase da vida do pet, com reforço positivo e acompanhamento familiar. Inclui avaliação comportamental completa, sessões presenciais e atividades guiadas em casa. Relatórios com vídeos e metas semanais para acompanhar o progresso do seu pet.',
            'preco' => 80.00,
            'duracao' => 60,
            'tipo' => 'adestramento',
            'ativo' => 1
        ],
        [
            'nome' => 'Passeio Monitorado',
            'descricao' => 'Companhia carinhosa com passeios monitorados, alimentação conforme rotina do pet e muita diversão. Inclui monitoramento GPS, fotos em tempo real e relatório diário com check-ins. Ideal para pets que precisam de exercício e socialização.',
            'preco' => 40.00,
            'duracao' => 45,
            'tipo' => 'passeios',
            'ativo' => 1
        ]
    ];
    
    $inseridos = 0;
    $stmt = $pdo->prepare("
        INSERT INTO servicos (nome, descricao, preco, duracao, tipo, ativo) 
        VALUES (:nome, :descricao, :preco, :duracao, :tipo, :ativo)
    ");
    
    foreach ($servicos as $servico) {
        // Verificar se já existe
        $check = $pdo->prepare("SELECT id FROM servicos WHERE nome = :nome AND tipo = :tipo");
        $check->execute(['nome' => $servico['nome'], 'tipo' => $servico['tipo']]);
        
        if ($check->rowCount() == 0) {
            $stmt->execute($servico);
            $inseridos++;
        }
    }
    
    // Redirecionar para dashboard com mensagem de sucesso
    $_SESSION['servicos_inseridos'] = $inseridos;
    header('Location: ../dashboard.php?servicos_inseridos=1');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['erro_servicos'] = $e->getMessage();
    header('Location: ../dashboard.php?erro=servicos');
    exit;
}
