<?php
/**
 * Processamento de Cadastro de Consulta de Adestramento (Dashboard Master)
 * Pet360 - Sistema de Gerenciamento Master
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Iniciar sessão
startSecureSession();

// Verificar se o usuário está autenticado e é admin
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: ../../index.php');
    exit;
}

if ($_SESSION['usuario_nome'] !== 'admin' && $_SESSION['usuario_email'] !== 'admin@pet360.com.br') {
    header('Location: ../../dashboard.php');
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../dashboard_master.php');
    exit;
}

// Array para armazenar erros
$erros = [];

// Receber e sanitizar dados
$pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
$usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
$data_consulta = trim($_POST['data_consulta'] ?? '');
$hora_consulta = trim($_POST['hora_consulta'] ?? '');

// Validação
if ($pet_id <= 0) {
    $erros[] = 'Pet é obrigatório';
}

if ($usuario_id <= 0) {
    $erros[] = 'Tutor é obrigatório';
}

if (empty($data_consulta)) {
    $erros[] = 'Data é obrigatória';
}

if (empty($hora_consulta)) {
    $erros[] = 'Hora é obrigatória';
}

// Combinar data e hora
$data_hora = $data_consulta . ' ' . $hora_consulta . ':00';

// Validar data (não permitir datas passadas)
try {
    $data_consulta_obj = new DateTime($data_hora);
    $agora = new DateTime();
    
    if ($data_consulta_obj < $agora) {
        $erros[] = 'Não é possível agendar consultas no passado';
    }
} catch (Exception $e) {
    $erros[] = 'Data/hora inválida';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_consulta'] = $erros;
    header('Location: ../../dashboard_master.php?erro=consulta');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o pet existe e pertence ao tutor
    $stmt_check = $pdo->prepare("SELECT id, usuario_id FROM pets WHERE id = :pet_id AND usuario_id = :usuario_id");
    $stmt_check->execute(['pet_id' => $pet_id, 'usuario_id' => $usuario_id]);
    $pet = $stmt_check->fetch();
    
    if (!$pet) {
        $_SESSION['erros_consulta'] = ['Pet não encontrado ou não pertence ao tutor selecionado'];
        header('Location: ../../dashboard_master.php?erro=consulta');
        exit;
    }
    
    // Inserir consulta
    $stmt = $pdo->prepare("
        INSERT INTO consultas_adestramento (pet_id, usuario_id, data_consulta, data_criacao, data_atualizacao)
        VALUES (:pet_id, :usuario_id, :data_consulta, NOW(), NOW())
    ");
    
    $stmt->execute([
        'pet_id' => $pet_id,
        'usuario_id' => $usuario_id,
        'data_consulta' => $data_hora
    ]);
    
    $_SESSION['sucesso_consulta'] = 'Consulta agendada com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=consulta');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao agendar consulta: ' . $e->getMessage());
    $_SESSION['erros_consulta'] = ['Erro ao agendar consulta. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=consulta');
    exit;
}
