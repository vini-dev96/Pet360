<?php
/**
 * Processamento de Criação de Observação (Dashboard Master)
 * Cria ou atualiza consulta com observação
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
$observacoes = trim($_POST['observacoes'] ?? '');

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

if (empty($observacoes)) {
    $erros[] = 'Observações são obrigatórias';
}

// Combinar data e hora
$data_hora = $data_consulta . ' ' . $hora_consulta . ':00';

// Validar data
try {
    $data_consulta_obj = new DateTime($data_hora);
} catch (Exception $e) {
    $erros[] = 'Data/hora inválida';
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros_observacao'] = $erros;
    header('Location: ../../dashboard_master.php?erro=observacao');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Verificar se o pet existe e pertence ao tutor
    $stmt_check = $pdo->prepare("SELECT id, usuario_id FROM pets WHERE id = :pet_id AND usuario_id = :usuario_id");
    $stmt_check->execute(['pet_id' => $pet_id, 'usuario_id' => $usuario_id]);
    $pet = $stmt_check->fetch();
    
    if (!$pet) {
        $_SESSION['erros_observacao'] = ['Pet não encontrado ou não pertence ao tutor selecionado'];
        header('Location: ../../dashboard_master.php?erro=observacao');
        exit;
    }
    
    // Verificar se já existe uma consulta para esse pet nessa data/hora
    $stmt_consulta = $pdo->prepare("SELECT id, observacoes FROM consultas_adestramento WHERE pet_id = :pet_id AND data_consulta = :data_consulta LIMIT 1");
    $stmt_consulta->execute(['pet_id' => $pet_id, 'data_consulta' => $data_hora]);
    $consulta_existente = $stmt_consulta->fetch();
    
    if ($consulta_existente) {
        // Atualizar consulta existente com nova observação
        $observacoes_finais = $observacoes;
        if (!empty($consulta_existente['observacoes'])) {
            $observacoes_finais = $consulta_existente['observacoes'] . "\n\n--- " . date('d/m/Y H:i') . " ---\n" . $observacoes;
        }
        
        $stmt = $pdo->prepare("
            UPDATE consultas_adestramento 
            SET observacoes = :observacoes, data_atualizacao = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            'id' => $consulta_existente['id'],
            'observacoes' => $observacoes_finais
        ]);
    } else {
        // Criar nova consulta com observação
        $stmt = $pdo->prepare("
            INSERT INTO consultas_adestramento (pet_id, usuario_id, data_consulta, observacoes, data_criacao, data_atualizacao)
            VALUES (:pet_id, :usuario_id, :data_consulta, :observacoes, NOW(), NOW())
        ");
        
        $stmt->execute([
            'pet_id' => $pet_id,
            'usuario_id' => $usuario_id,
            'data_consulta' => $data_hora,
            'observacoes' => $observacoes
        ]);
    }
    
    $_SESSION['sucesso_observacao'] = 'Observação salva com sucesso!';
    header('Location: ../../dashboard_master.php?sucesso=observacao');
    exit;
    
} catch (PDOException $e) {
    error_log('Erro ao salvar observação: ' . $e->getMessage());
    $_SESSION['erros_observacao'] = ['Erro ao salvar observação. Tente novamente.'];
    header('Location: ../../dashboard_master.php?erro=observacao');
    exit;
}
