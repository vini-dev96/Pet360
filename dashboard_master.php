<?php
// dashboard_master.php — Dashboard Master (Administrador)
require_once __DIR__ . '/config/session.php';
startSecureSession();

// Verificar se o usuário está autenticado e é admin
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: index.php');
    exit;
}

// Verificar se é admin (por nome ou email)
if ($_SESSION['usuario_nome'] !== 'admin' && $_SESSION['usuario_email'] !== 'admin@pet360.com.br') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

// Buscar dados do banco
$tutores = [];
$pets = [];
$consultas = [];
$timeline_por_pet = [];

try {
    $pdo = getDatabaseConnection();
    
    // Buscar todos os tutores (exceto admin)
    try {
        $stmtTutores = $pdo->prepare("SELECT id, nome, telefone, data_criacao FROM usuarios WHERE nome != 'admin' AND email != 'admin@pet360.com.br' ORDER BY nome");
        $stmtTutores->execute();
        $tutores = $stmtTutores->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao buscar tutores: ' . $e->getMessage());
        $tutores = [];
    }
    
    // Buscar todos os pets com informações do tutor
    try {
        $stmtPets = $pdo->prepare("
            SELECT p.id, p.nome, p.idade, p.raca, p.tipo, p.foto, p.usuario_id, u.nome as tutor_nome
            FROM pets p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.ativo = 1
            ORDER BY p.data_criacao DESC
        ");
        $stmtPets->execute();
        $pets = $stmtPets->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao buscar pets: ' . $e->getMessage());
        $pets = [];
    }
    
    // Buscar todas as consultas com informações do pet e tutor
    try {
        $stmtConsultas = $pdo->prepare("
            SELECT c.id, c.pet_id, c.usuario_id, c.data_consulta, c.observacoes, c.data_criacao, c.data_atualizacao,
                   p.nome as pet_nome, u.nome as tutor_nome
            FROM consultas_adestramento c
            INNER JOIN pets p ON c.pet_id = p.id
            INNER JOIN usuarios u ON c.usuario_id = u.id
            ORDER BY c.data_consulta DESC
        ");
        $stmtConsultas->execute();
        $consultas = $stmtConsultas->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao buscar consultas: ' . $e->getMessage());
        $consultas = [];
    }
    
    // Organizar timeline por pet
    foreach ($consultas as $consulta) {
        if (!isset($timeline_por_pet[$consulta['pet_id']])) {
            $timeline_por_pet[$consulta['pet_id']] = [];
        }
        $timeline_por_pet[$consulta['pet_id']][] = $consulta;
    }
    
} catch (PDOException $e) {
    error_log('Erro ao buscar dados: ' . $e->getMessage());
}

function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Formatar telefone
function formatarTelefone($telefone) {
    $numeros = preg_replace('/\D/', '', $telefone);
    if (strlen($numeros) == 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $numeros);
    } elseif (strlen($numeros) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $numeros);
    }
    return $telefone;
}

$business = [
    'name' => 'Pet360',
    'logo' => './img/logo-pet360-2.png',
];
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Dashboard Master — <?= esc($business['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="./img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --emerald-700: #047857;
            --emerald-900: #064e3b;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }
        body { font-family: 'Inter', sans-serif; }
        .btn-primary {
            background: var(--emerald-500);
            color: white;
            border-color: var(--emerald-500);
        }
        .btn-primary:hover {
            background: var(--emerald-600);
            border-color: var(--emerald-600);
        }
        .btn-secondary {
            background: white;
            color: var(--slate-700);
            border-color: var(--slate-300);
        }
        .btn-secondary:hover {
            background: var(--slate-50);
            border-color: var(--slate-400);
        }
        .card-outline {
            border-color: var(--slate-200);
        }
        .card {
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: 1rem;
        }
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
        .modal-backdrop.show {
            opacity: 1;
        }
        .modal-content {
            transform: translateY(20px) scale(0.95);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .modal-backdrop.show .modal-content {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            padding-bottom: 2rem;
        }
        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 2rem;
            bottom: -2rem;
            width: 2px;
            background: var(--slate-200);
        }
        .timeline-dot {
            position: absolute;
            left: 0;
            top: 0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--emerald-500);
            border: 2px solid white;
            box-shadow: 0 0 0 2px var(--emerald-500);
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="flex items-center gap-2">
                        <img src="<?= esc($business['logo']) ?>" alt="<?= esc($business['name']) ?>" class="h-8 w-auto">
                        <span class="text-xl font-extrabold text-emerald-600"><?= esc($business['name']) ?> Master</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-3">
                        <span class="text-sm text-slate-600">Olá, <strong>Admin</strong></span>
                    </div>
                    <a href="auth/logout.php" class="rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-secondary hover:shadow-md">
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 mb-2">
                Dashboard Master 👑
            </h1>
            <p class="text-slate-600">Gerencie tutores, pets e consultas de adestramento</p>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Tutores</h2>
                </div>
                <p class="text-3xl font-extrabold text-emerald-600"><?= count($tutores) ?></p>
            </div>
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-purple-100 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Pets</h2>
                </div>
                <p class="text-3xl font-extrabold text-emerald-600"><?= count($pets) ?></p>
            </div>
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-green-100 rounded-xl">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Consultas</h2>
                </div>
                <p class="text-3xl font-extrabold text-emerald-600"><?= count($consultas) ?></p>
            </div>
        </div>

        <!-- Seção Tutores -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">Gerenciar Tutores</h2>
                <button onclick="abrirModalCriarTutor()" class="rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                    + Novo Tutor
                </button>
            </div>
            <div class="card overflow-hidden">
                <?php if (empty($tutores)): ?>
                    <div class="text-center py-12">
                        <p class="text-slate-500 text-sm mb-4">Nenhum tutor cadastrado</p>
                        <button onclick="abrirModalCriarTutor()" class="inline-block rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                            Cadastrar Primeiro Tutor
                        </button>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Telefone</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Cadastro</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php foreach ($tutores as $tutor): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-slate-900"><?= esc($tutor['nome']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600"><?= esc(formatarTelefone($tutor['telefone'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600"><?= date('d/m/Y', strtotime($tutor['data_criacao'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="abrirModalEditarTutor(<?= $tutor['id'] ?>, '<?= esc(addslashes($tutor['nome'])) ?>', '<?= esc(addslashes($tutor['telefone'])) ?>')" class="text-emerald-600 hover:text-emerald-700 mr-3">Editar</button>
                                            <button onclick="confirmarExcluirTutor(<?= $tutor['id'] ?>, '<?= esc(addslashes($tutor['nome'])) ?>')" class="text-red-600 hover:text-red-700">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seção Pets -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">Gerenciar Pets</h2>
                <button onclick="abrirModalCriarPet()" class="rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                    + Novo Pet
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($pets)): ?>
                    <div class="col-span-full text-center py-12 card">
                        <p class="text-slate-500 text-sm mb-4">Nenhum pet cadastrado</p>
                        <button onclick="abrirModalCriarPet()" class="inline-block rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                            Cadastrar Primeiro Pet
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($pets as $pet): ?>
                        <div class="card overflow-hidden">
                            <?php if ($pet['foto']): ?>
                                <img src="<?= esc($pet['foto']) ?>" alt="<?= esc($pet['nome']) ?>" class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-slate-200 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-1"><?= esc($pet['nome']) ?></h3>
                                <p class="text-sm text-slate-600 mb-2">Tutor: <strong><?= esc($pet['tutor_nome']) ?></strong></p>
                                <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                                    <span><?= esc(ucfirst($pet['tipo'])) ?></span>
                                    <?php if ($pet['raca']): ?>
                                        <span>• <?= esc($pet['raca']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($pet['idade']): ?>
                                        <span>• <?= $pet['idade'] ?> anos</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="abrirModalTimeline(<?= $pet['id'] ?>, '<?= esc(addslashes($pet['nome'])) ?>')" class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                        Timeline
                                    </button>
                                    <button onclick="abrirModalNovaConsulta(<?= $pet['id'] ?>, <?= $pet['usuario_id'] ?>, '<?= esc(addslashes($pet['nome'])) ?>')" class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">
                                        Consulta
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seção Consultas -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">Consultas de Adestramento</h2>
                <button onclick="abrirModalNovaObservacao()" class="rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                    + Nova Observação
                </button>
            </div>
            <div class="card overflow-hidden">
                <?php if (empty($consultas)): ?>
                    <div class="text-center py-12">
                        <p class="text-slate-500 text-sm">Nenhuma consulta agendada</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Data/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Pet</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tutor</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Observações</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php foreach ($consultas as $consulta): 
                                    $data_consulta = new DateTime($consulta['data_consulta']);
                                    $data_formatada = $data_consulta->format('d/m/Y H:i');
                                ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-slate-900"><?= esc($data_formatada) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900"><?= esc($consulta['pet_nome']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600"><?= esc($consulta['tutor_nome']) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-600">
                                                <?php if ($consulta['observacoes']): ?>
                                                    <?= esc(strlen($consulta['observacoes']) > 50 ? substr($consulta['observacoes'], 0, 50) . '...' : $consulta['observacoes']) ?>
                                                <?php else: ?>
                                                    <span class="text-slate-400">Sem observações</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="abrirModalEditarConsulta(<?= $consulta['id'] ?>, '<?= esc(addslashes($data_consulta->format('Y-m-d'))) ?>', '<?= esc(addslashes($data_consulta->format('H:i'))) ?>')" class="text-emerald-600 hover:text-emerald-700 mr-3">Editar</button>
                                            <button onclick="confirmarExcluirConsulta(<?= $consulta['id'] ?>, '<?= esc(addslashes($consulta['pet_nome'])) ?>')" class="text-red-600 hover:text-red-700">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal Criar Tutor -->
    <div id="modalCriarTutor" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Novo Tutor</h2>
                    <button onclick="fecharModalCriarTutor()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php if (isset($_SESSION['erros_tutor'])): $erros = $_SESSION['erros_tutor']; unset($_SESSION['erros_tutor']); ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            <?php foreach ($erros as $erro): ?>
                                <li><?= esc($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="master/tutores/create.php" method="POST" class="space-y-5">
                    <div>
                        <label for="tutor_nome" class="block text-sm font-semibold mb-2">Nome *</label>
                        <input type="text" id="tutor_nome" name="nome" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500" value="<?= esc($_SESSION['nome_tutor'] ?? '') ?>" placeholder="Nome completo do tutor">
                        <?php unset($_SESSION['nome_tutor']); ?>
                    </div>
                    <div>
                        <label for="tutor_telefone" class="block text-sm font-semibold mb-2">Telefone *</label>
                        <input type="text" id="tutor_telefone" name="telefone" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500" value="<?= esc($_SESSION['telefone_tutor'] ?? '') ?>" placeholder="(11) 99999-9999">
                        <?php unset($_SESSION['telefone_tutor']); ?>
                    </div>
                    <button type="submit" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Cadastrar Tutor
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Tutor -->
    <div id="modalEditarTutor" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Editar Tutor</h2>
                    <button onclick="fecharModalEditarTutor()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="formEditarTutor" action="master/tutores/update.php" method="POST" class="space-y-5">
                    <input type="hidden" id="edit_tutor_id" name="id">
                    <div>
                        <label for="edit_tutor_nome" class="block text-sm font-semibold mb-2">Nome *</label>
                        <input type="text" id="edit_tutor_nome" name="nome" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="edit_tutor_telefone" class="block text-sm font-semibold mb-2">Telefone *</label>
                        <input type="text" id="edit_tutor_telefone" name="telefone" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <button type="submit" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Salvar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Tutor -->
    <div id="modalExcluirTutor" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Confirmar Exclusão</h2>
                    <button onclick="fecharModalExcluirTutor()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-slate-700 mb-6">Tem certeza que deseja excluir o tutor <strong id="tutorNomeExcluir"></strong>? Esta ação não pode ser desfeita e todos os pets e consultas vinculados serão excluídos.</p>
                <form id="formExcluirTutor" action="master/tutores/delete.php" method="POST" class="flex gap-3">
                    <input type="hidden" id="excluir_tutor_id" name="id">
                    <button type="submit" class="flex-1 rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border bg-red-600 text-white hover:bg-red-700">
                        Excluir
                    </button>
                    <button type="button" onclick="fecharModalExcluirTutor()" class="px-6 py-3 text-base font-semibold rounded-2xl border btn-secondary">
                        Cancelar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Criar Pet -->
    <div id="modalCriarPet" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Novo Pet</h2>
                    <button onclick="fecharModalCriarPet()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php if (isset($_SESSION['erros_pet_master'])): $erros = $_SESSION['erros_pet_master']; unset($_SESSION['erros_pet_master']); ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            <?php foreach ($erros as $erro): ?>
                                <li><?= esc($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="master/pets/create.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <div>
                        <label for="pet_tutor" class="block text-sm font-semibold mb-2">Tutor *</label>
                        <select id="pet_tutor" name="usuario_id" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Selecione o tutor</option>
                            <?php foreach ($tutores as $tutor): ?>
                                <option value="<?= $tutor['id'] ?>"><?= esc($tutor['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="pet_foto" class="block text-sm font-semibold mb-2">Foto</label>
                        <input type="file" id="pet_foto" name="foto" accept="image/*" class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                    </div>
                    <div>
                        <label for="pet_nome" class="block text-sm font-semibold mb-2">Nome *</label>
                        <input type="text" id="pet_nome" name="nome" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Nome do pet">
                    </div>
                    <div>
                        <label for="pet_tipo" class="block text-sm font-semibold mb-2">Tipo *</label>
                        <select id="pet_tipo" name="tipo" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Selecione</option>
                            <option value="cachorro">Cachorro</option>
                            <option value="gato">Gato</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div>
                        <label for="pet_raca" class="block text-sm font-semibold mb-2">Raça</label>
                        <input type="text" id="pet_raca" name="raca" class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Raça do pet">
                    </div>
                    <div>
                        <label for="pet_idade" class="block text-sm font-semibold mb-2">Idade (anos)</label>
                        <input type="number" id="pet_idade" name="idade" min="0" max="30" class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Idade">
                    </div>
                    <button type="submit" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Cadastrar Pet
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nova Consulta -->
    <div id="modalNovaConsulta" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Nova Consulta</h2>
                    <button onclick="fecharModalNovaConsulta()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="formNovaConsulta" action="master/consultas/create.php" method="POST" class="space-y-5">
                    <input type="hidden" id="consulta_pet_id" name="pet_id">
                    <input type="hidden" id="consulta_usuario_id" name="usuario_id">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Pet</label>
                        <input type="text" id="consulta_pet_nome" readonly class="w-full px-4 py-3 rounded-xl border card-outline bg-slate-50 text-slate-600">
                    </div>
                    <div>
                        <label for="consulta_data" class="block text-sm font-semibold mb-2">Data *</label>
                        <input type="date" id="consulta_data" name="data_consulta" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="consulta_hora" class="block text-sm font-semibold mb-2">Hora *</label>
                        <input type="time" id="consulta_hora" name="hora_consulta" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <button type="submit" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Agendar Consulta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Consulta -->
    <div id="modalEditarConsulta" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Editar Consulta</h2>
                    <button onclick="fecharModalEditarConsulta()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="formEditarConsulta" action="master/consultas/update.php" method="POST" class="space-y-5">
                    <input type="hidden" id="edit_consulta_id" name="id">
                    <div>
                        <label for="edit_consulta_data" class="block text-sm font-semibold mb-2">Data *</label>
                        <input type="date" id="edit_consulta_data" name="data_consulta" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="edit_consulta_hora" class="block text-sm font-semibold mb-2">Hora *</label>
                        <input type="time" id="edit_consulta_hora" name="hora_consulta" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <button type="submit" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Salvar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Consulta -->
    <div id="modalExcluirConsulta" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Confirmar Exclusão</h2>
                    <button onclick="fecharModalExcluirConsulta()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-slate-700 mb-6">Tem certeza que deseja excluir a consulta do pet <strong id="consultaPetExcluir"></strong>? Esta ação não pode ser desfeita.</p>
                <form id="formExcluirConsulta" action="master/consultas/delete.php" method="POST" class="flex gap-3">
                    <input type="hidden" id="excluir_consulta_id" name="id">
                    <button type="submit" class="flex-1 rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border bg-red-600 text-white hover:bg-red-700">
                        Excluir
                    </button>
                    <button type="button" onclick="fecharModalExcluirConsulta()" class="px-6 py-3 text-base font-semibold rounded-2xl border btn-secondary">
                        Cancelar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nova Observação -->
    <div id="modalObservacao" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Nova Observação</h2>
                    <button onclick="fecharModalObservacao()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php if (isset($_SESSION['erros_observacao'])): $erros = $_SESSION['erros_observacao']; unset($_SESSION['erros_observacao']); ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            <?php foreach ($erros as $erro): ?>
                                <li><?= esc($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form id="formObservacao" action="master/consultas/create_observacao.php" method="POST" class="space-y-5">
                    <div>
                        <label for="obs_pet" class="block text-sm font-semibold mb-2">Pet *</label>
                        <select id="obs_pet" name="pet_id" required onchange="atualizarTutorObservacao()" class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Selecione o pet</option>
                            <?php foreach ($pets as $pet): ?>
                                <option value="<?= $pet['id'] ?>" data-tutor-id="<?= $pet['usuario_id'] ?>" data-tutor-nome="<?= esc($pet['tutor_nome']) ?>"><?= esc($pet['nome']) ?> (<?= esc($pet['tutor_nome']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" id="obs_usuario_id" name="usuario_id">
                    <div>
                        <label for="obs_data" class="block text-sm font-semibold mb-2">Data da Consulta *</label>
                        <input type="date" id="obs_data" name="data_consulta" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="obs_hora" class="block text-sm font-semibold mb-2">Hora da Consulta *</label>
                        <input type="time" id="obs_hora" name="hora_consulta" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="obs_texto" class="block text-sm font-semibold mb-2">Observações *</label>
                        <textarea id="obs_texto" name="observacoes" rows="8" required class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Descreva as observações da consulta..."></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Salvar Observação
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sucesso -->
    <div id="modalSucesso" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-8 text-center">
                <div class="mb-6">
                    <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-2" id="modalSucessoTitulo">Sucesso!</h2>
                    <p class="text-slate-600" id="modalSucessoMensagem">Operação realizada com sucesso!</p>
                </div>
                <button onclick="fecharModalSucesso()" class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Timeline -->
    <div id="modalTimeline" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold">Timeline de Evolução</h2>
                        <p class="text-sm text-slate-600 mt-1">Pet: <strong id="timelinePetNome"></strong></p>
                    </div>
                    <button onclick="fecharModalTimeline()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="timelineContent" class="space-y-0">
                    <!-- Timeline será preenchida via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funções de Modal - Tutores
        function abrirModalCriarTutor() {
            const modal = document.getElementById('modalCriarTutor');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalCriarTutor() {
            const modal = document.getElementById('modalCriarTutor');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }
        function abrirModalEditarTutor(id, nome, telefone) {
            document.getElementById('edit_tutor_id').value = id;
            document.getElementById('edit_tutor_nome').value = nome;
            document.getElementById('edit_tutor_telefone').value = telefone;
            const modal = document.getElementById('modalEditarTutor');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalEditarTutor() {
            const modal = document.getElementById('modalEditarTutor');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }
        function confirmarExcluirTutor(id, nome) {
            document.getElementById('excluir_tutor_id').value = id;
            document.getElementById('tutorNomeExcluir').textContent = nome;
            const modal = document.getElementById('modalExcluirTutor');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalExcluirTutor() {
            const modal = document.getElementById('modalExcluirTutor');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }

        // Funções de Modal - Pets
        function abrirModalCriarPet() {
            const modal = document.getElementById('modalCriarPet');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalCriarPet() {
            const modal = document.getElementById('modalCriarPet');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }

        // Funções de Modal - Consultas
        function abrirModalNovaConsulta(petId, usuarioId, petNome) {
            document.getElementById('consulta_pet_id').value = petId;
            document.getElementById('consulta_usuario_id').value = usuarioId;
            document.getElementById('consulta_pet_nome').value = petNome;
            const modal = document.getElementById('modalNovaConsulta');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalNovaConsulta() {
            const modal = document.getElementById('modalNovaConsulta');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }
        function abrirModalEditarConsulta(id, data, hora) {
            document.getElementById('edit_consulta_id').value = id;
            document.getElementById('edit_consulta_data').value = data;
            document.getElementById('edit_consulta_hora').value = hora;
            const modal = document.getElementById('modalEditarConsulta');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalEditarConsulta() {
            const modal = document.getElementById('modalEditarConsulta');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }
        function confirmarExcluirConsulta(id, petNome) {
            document.getElementById('excluir_consulta_id').value = id;
            document.getElementById('consultaPetExcluir').textContent = petNome;
            const modal = document.getElementById('modalExcluirConsulta');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalExcluirConsulta() {
            const modal = document.getElementById('modalExcluirConsulta');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }
        function abrirModalNovaObservacao() {
            // Limpar formulário
            document.getElementById('formObservacao').reset();
            document.getElementById('obs_usuario_id').value = '';
            const modal = document.getElementById('modalObservacao');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function atualizarTutorObservacao() {
            const select = document.getElementById('obs_pet');
            const option = select.options[select.selectedIndex];
            if (option.value) {
                document.getElementById('obs_usuario_id').value = option.getAttribute('data-tutor-id');
            } else {
                document.getElementById('obs_usuario_id').value = '';
            }
        }
        function fecharModalObservacao() {
            const modal = document.getElementById('modalObservacao');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }

        // Função Timeline
        function abrirModalTimeline(petId, petNome) {
            document.getElementById('timelinePetNome').textContent = petNome;
            const timelineContent = document.getElementById('timelineContent');
            
            // Buscar consultas do pet (apenas as que têm observações)
            const consultas = <?= json_encode($consultas, JSON_UNESCAPED_UNICODE) ?>;
            const consultasDoPet = consultas.filter(function(c) {
                return c.pet_id == petId && c.observacoes && c.observacoes.trim() !== '';
            }).sort(function(a, b) {
                const dateA = new Date(a.data_consulta.replace(' ', 'T'));
                const dateB = new Date(b.data_consulta.replace(' ', 'T'));
                return dateB - dateA;
            });
            
            if (consultasDoPet.length === 0) {
                timelineContent.innerHTML = '<div class="text-center py-12"><p class="text-slate-500 text-sm mb-4">Nenhuma observação registrada para este pet</p><button onclick="fecharModalTimeline(); abrirModalNovaObservacao();" class="inline-block rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">Adicionar Observação</button></div>';
            } else {
                let html = '';
                consultasDoPet.forEach(function(consulta) {
                    const data = new Date(consulta.data_consulta.replace(' ', 'T'));
                    const dia = String(data.getDate()).padStart(2, '0');
                    const mes = String(data.getMonth() + 1).padStart(2, '0');
                    const ano = data.getFullYear();
                    const hora = String(data.getHours()).padStart(2, '0');
                    const minuto = String(data.getMinutes()).padStart(2, '0');
                    const dataFormatada = dia + '/' + mes + '/' + ano + ' às ' + hora + ':' + minuto;
                    
                    // Escapar HTML e preservar quebras de linha
                    const obsTexto = consulta.observacoes ? consulta.observacoes
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\n/g, '<br>') : '';
                    
                    html += '<div class="timeline-item">';
                    html += '<div class="timeline-dot"></div>';
                    html += '<div class="bg-slate-50 rounded-xl p-4 border border-slate-200">';
                    html += '<div class="flex items-center justify-between mb-3">';
                    html += '<h4 class="font-bold text-slate-900 text-lg">' + dataFormatada + '</h4>';
                    html += '</div>';
                    html += '<div class="prose prose-sm max-w-none">';
                    html += '<p class="text-sm text-slate-700 leading-relaxed">' + obsTexto + '</p>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                });
                timelineContent.innerHTML = html;
            }
            
            const modal = document.getElementById('modalTimeline');
            modal.style.display = 'flex';
            setTimeout(function() { modal.classList.add('show'); }, 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalTimeline() {
            const modal = document.getElementById('modalTimeline');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; document.body.style.overflow = ''; }, 300);
        }

        // Função Modal Sucesso
        function abrirModalSucesso(titulo, mensagem) {
            document.getElementById('modalSucessoTitulo').textContent = titulo;
            document.getElementById('modalSucessoMensagem').textContent = mensagem;
            const modal = document.getElementById('modalSucesso');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
        function fecharModalSucesso() {
            const modal = document.getElementById('modalSucesso');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                // Redirecionar para limpar URL
                if (window.location.search.includes('sucesso')) {
                    window.location.href = 'dashboard_master.php';
                }
            }, 300);
        }

        // Fechar modais ao clicar fora
        document.querySelectorAll('.modal-backdrop').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                    setTimeout(() => { this.style.display = 'none'; document.body.style.overflow = ''; }, 300);
                }
            });
        });

        // Abrir modal automaticamente quando houver erro
        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'tutor'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    abrirModalCriarTutor();
                }, 100);
            });
        <?php endif; ?>
        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'pet'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    abrirModalCriarPet();
                }, 100);
            });
        <?php endif; ?>

        // Mensagens de sucesso
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'tutor'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Tutor cadastrado com sucesso!');
                setTimeout(() => {
                    window.location.href = 'dashboard_master.php';
                }, 100);
            });
        <?php endif; ?>
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'pet'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Pet cadastrado com sucesso!');
                setTimeout(() => {
                    window.location.href = 'dashboard_master.php';
                }, 100);
            });
        <?php endif; ?>
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'consulta'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Consulta agendada com sucesso!');
                setTimeout(() => {
                    window.location.href = 'dashboard_master.php';
                }, 100);
            });
        <?php endif; ?>
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'observacao'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    abrirModalSucesso('Sucesso!', 'Observação salva com sucesso!');
                }, 100);
            });
        <?php endif; ?>
    </script>
</body>
</html>
