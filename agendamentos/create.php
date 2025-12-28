<?php
/**
 * Processamento de Compra/Agendamento de Serviço
 * Pet360 - Sistema de Gerenciamento de Serviços
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Iniciar sessão
startSecureSession();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: ../index.php');
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

// Receber e sanitizar dados
$servico_id = (int)($_POST['servico_id'] ?? 0);
$pet_id = (int)($_POST['pet_id'] ?? 0);
$data_agendamento = !empty($_POST['data_agendamento']) ? trim($_POST['data_agendamento']) : null;
$observacoes = !empty($_POST['observacoes']) ? trim($_POST['observacoes']) : null;

// Array para armazenar erros
$erros = [];

// Validação de dados
if (empty($servico_id)) {
    $erros[] = 'Serviço inválido';
}

if (empty($pet_id)) {
    $erros[] = 'Selecione um pet';
}

// Validar data se fornecida
if ($data_agendamento) {
    $data_obj = DateTime::createFromFormat('Y-m-d\TH:i', $data_agendamento);
    if (!$data_obj || $data_obj < new DateTime()) {
        $erros[] = 'Data e hora inválidas ou no passado';
    }
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_checkout'] = $erros;
    $_SESSION['checkout_servico_id'] = $servico_id;
    header('Location: ../dashboard.php?erro=checkout');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o serviço existe e está ativo
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM servicos WHERE id = :id AND ativo = 1");
    $stmt->execute(['id' => $servico_id]);
    $servico = $stmt->fetch();
    
    if (!$servico) {
        $_SESSION['erros_checkout'] = ['Serviço não encontrado ou indisponível'];
        $_SESSION['checkout_servico_id'] = $servico_id;
        header('Location: ../dashboard.php?erro=checkout');
        exit;
    }
    
    // Verificar se o pet pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM pets WHERE id = :id AND usuario_id = :usuario_id AND ativo = 1");
    $stmt->execute(['id' => $pet_id, 'usuario_id' => $_SESSION['usuario_id']]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        $_SESSION['erros_checkout'] = ['Pet não encontrado ou você não tem permissão para agendar serviços para este pet'];
        $_SESSION['checkout_servico_id'] = $servico_id;
        header('Location: ../dashboard.php?erro=checkout');
        exit;
    }
    
    // Converter data se fornecida
    $data_agendamento_formatada = null;
    if ($data_agendamento) {
        $data_obj = DateTime::createFromFormat('Y-m-d\TH:i', $data_agendamento);
        $data_agendamento_formatada = $data_obj->format('Y-m-d H:i:s');
    }
    
    // Inserir agendamento
    $stmt = $pdo->prepare("
        INSERT INTO agendamentos (usuario_id, pet_id, servico_id, data_agendamento, status, valor_pago, observacoes)
        VALUES (:usuario_id, :pet_id, :servico_id, :data_agendamento, 'pendente', :valor_pago, :observacoes)
    ");
    
    $stmt->execute([
        'usuario_id' => $_SESSION['usuario_id'],
        'pet_id' => $pet_id,
        'servico_id' => $servico_id,
        'data_agendamento' => $data_agendamento_formatada,
        'valor_pago' => $servico['preco'],
        'observacoes' => $observacoes
    ]);
    
    // Sucesso - redirecionar para dashboard
    header('Location: ../dashboard.php?sucesso=compra');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao criar agendamento: ' . $e->getMessage());
    $_SESSION['erros_checkout'] = ['Erro ao processar compra. Tente novamente mais tarde.'];
    $_SESSION['checkout_servico_id'] = $servico_id;
    header('Location: ../dashboard.php?erro=checkout');
    exit;
}
