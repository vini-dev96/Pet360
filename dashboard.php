<?php
// dashboard.php — Dashboard do Usuário
require_once __DIR__ . '/config/session.php';
startSecureSession();

// Verificar se o usuário está autenticado (middleware)
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

// Buscar informações atualizadas do usuário e seus pets
$pets = [];
$servicos = [];
$agendamentos = [];

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT id, nome, email, telefone, data_criacao, data_atualizacao FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        // Se o usuário não existe mais, fazer logout
        session_destroy();
        header('Location: index.php');
        exit;
    }
    
    // Buscar pets do usuário (bloco separado para não perder dados se outras queries falharem)
    try {
        $stmtPets = $pdo->prepare("SELECT id, nome, idade, raca, tipo, foto, data_criacao FROM pets WHERE usuario_id = :usuario_id AND ativo = 1 ORDER BY data_criacao DESC");
        $stmtPets->execute(['usuario_id' => $_SESSION['usuario_id']]);
        $pets = $stmtPets->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao buscar pets: ' . $e->getMessage());
        $pets = [];
    }
    
    // Buscar serviços disponíveis (bloco separado - opcional)
    try {
        $stmtServicos = $pdo->prepare("SELECT id, nome, descricao, preco, duracao, tipo FROM servicos WHERE ativo = 1 AND tipo = 'adestramento' ORDER BY tipo, nome");
        $stmtServicos->execute();
        $servicos = $stmtServicos->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao buscar serviços: ' . $e->getMessage());
        $servicos = [];
    }
    
    // Buscar agendamentos do usuário (bloco separado - opcional)
    try {
        $stmtAgendamentos = $pdo->prepare("
            SELECT a.id, a.data_agendamento, a.status, a.valor_pago, a.data_criacao,
                   s.nome as servico_nome, s.tipo as servico_tipo,
                   p.nome as pet_nome
            FROM agendamentos a
            INNER JOIN servicos s ON a.servico_id = s.id
            INNER JOIN pets p ON a.pet_id = p.id
            WHERE a.usuario_id = :usuario_id
            ORDER BY a.data_criacao DESC
            LIMIT 10
        ");
        $stmtAgendamentos->execute(['usuario_id' => $_SESSION['usuario_id']]);
        $agendamentos = $stmtAgendamentos->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao buscar agendamentos: ' . $e->getMessage());
        $agendamentos = [];
    }
    
} catch (PDOException $e) {
    error_log('Erro ao buscar dados do usuário: ' . $e->getMessage());
    $usuario = [
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email' => $_SESSION['usuario_email'] ?? '',
        'telefone' => '',
        'data_criacao' => date('Y-m-d H:i:s')
    ];
    // Não sobrescrever $pets aqui - já foi definido no bloco separado acima
}

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Formatar telefone para exibição
$telefoneFormatado = '';
if (!empty($usuario['telefone'])) {
    $telefone_numeros = preg_replace('/\D/', '', $usuario['telefone']);
    if (strlen($telefone_numeros) == 10) {
        $telefoneFormatado = preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone_numeros);
    } elseif (strlen($telefone_numeros) == 11) {
        $telefoneFormatado = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone_numeros);
    } else {
        $telefoneFormatado = $usuario['telefone'];
    }
}

// Formatar data de criação
$dataCriacao = new DateTime($usuario['data_criacao']);
$dataFormatada = $dataCriacao->format('d/m/Y');

$business = [
    'name' => 'Pet360',
    'tagline' => 'Seu pet feliz e bem cuidado',
    'logo' => './img/logo-pet360-2.png',
];
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Dashboard — <?= esc($business['name']) ?></title>
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
                        <span class="text-xl font-extrabold text-emerald-600"><?= esc($business['name']) ?></span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-3">
                        <span class="text-sm text-slate-600">Olá, <strong><?= esc($usuario['nome']) ?></strong></span>
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
                Bem-vindo, <?= esc($usuario['nome']) ?>! 👋
            </h1>
            <p class="text-slate-600">Gerencie sua conta e acompanhe seus serviços</p>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Card Perfil -->
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-emerald-100 rounded-xl">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Meu Perfil</h2>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Nome</p>
                        <p class="text-sm font-semibold text-slate-900"><?= esc($usuario['nome']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Email</p>
                        <p class="text-sm font-semibold text-slate-900"><?= esc($usuario['email']) ?></p>
                    </div>
                    <?php if (!empty($telefoneFormatado)): ?>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Telefone</p>
                        <p class="text-sm font-semibold text-slate-900"><?= esc($telefoneFormatado) ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Membro desde</p>
                        <p class="text-sm font-semibold text-slate-900"><?= esc($dataFormatada) ?></p>
                    </div>
                </div>
            </div>

            <!-- Card Serviços Agendados -->
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Serviços Agendados</h2>
                </div>
                <?php if (empty($agendamentos)): ?>
                    <div class="text-center py-8">
                        <p class="text-slate-500 text-sm mb-4">Nenhum serviço agendado no momento</p>
                        <button onclick="scrollToServicos()" class="inline-block rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                            Ver Serviços
                        </button>
                    </div>
                <?php else: ?>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <div class="border border-slate-200 rounded-lg p-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-semibold text-sm text-slate-900"><?= esc($agendamento['servico_nome']) ?></p>
                                        <p class="text-xs text-slate-500">Pet: <?= esc($agendamento['pet_nome']) ?></p>
                                        <?php if ($agendamento['data_agendamento']): ?>
                                            <p class="text-xs text-slate-500">Data: <?= date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-emerald-600">R$ <?= number_format($agendamento['valor_pago'], 2, ',', '.') ?></p>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                            echo $agendamento['status'] == 'concluido' ? 'bg-green-100 text-green-700' : 
                                                ($agendamento['status'] == 'confirmado' ? 'bg-blue-100 text-blue-700' : 
                                                ($agendamento['status'] == 'cancelado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'));
                                        ?>">
                                            <?= ucfirst($agendamento['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Card Histórico -->
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-purple-100 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Histórico</h2>
                </div>
                <div class="text-center py-8">
                    <p class="text-slate-500 text-sm">Seu histórico de serviços aparecerá aqui</p>
                </div>
            </div>
        </div>

        <!-- Seção Meus Pets -->
        <div class="card p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">Meus Pets</h2>
                <button onclick="abrirModalAdicionarPet()" class="rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar Pet
                </button>
            </div>
            
            <?php if (empty($pets)): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <p class="text-slate-500 text-sm mb-4">Você ainda não cadastrou nenhum pet</p>
                    <button onclick="abrirModalAdicionarPet()" class="inline-block rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Cadastrar Primeiro Pet
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($pets as $pet): ?>
                        <div class="border border-slate-200 rounded-xl p-4 hover:border-emerald-500 hover:shadow-md transition-all">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($pet['foto'])): ?>
                                        <img src="<?= esc($pet['foto']) ?>" alt="<?= esc($pet['nome']) ?>" class="w-16 h-16 rounded-lg object-cover">
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-emerald-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="font-bold text-slate-900"><?= esc($pet['nome']) ?></h3>
                                        <p class="text-xs text-slate-500 capitalize"><?= esc($pet['tipo']) ?></p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button onclick="abrirModalEditarPet(<?= $pet['id'] ?>)" class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="confirmarExcluirPet(<?= $pet['id'] ?>, '<?= esc(addslashes($pet['nome'])) ?>')" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <?php if (!empty($pet['raca'])): ?>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500">Raça:</span>
                                        <span class="font-semibold text-slate-900"><?= esc($pet['raca']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($pet['idade'])): ?>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500">Idade:</span>
                                        <span class="font-semibold text-slate-900"><?= esc($pet['idade']) ?> <?= $pet['idade'] == 1 ? 'ano' : 'anos' ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <div class="flex gap-2">
                                    <button 
                                        onclick="abrirModalSelecionarServico(<?= $pet['id'] ?>, '<?= esc(addslashes($pet['nome'])) ?>')" 
                                        class="flex-1 text-center rounded-lg px-3 py-2 text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                                    >
                                        Agendar Serviço
                                    </button>
                                    <button 
                                        onclick="scrollToServicos()" 
                                        class="px-3 py-2 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors rounded-lg"
                                        title="Ver todos os serviços"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Seção Serviços Disponíveis -->
        <div class="card p-6 mb-8" id="servicos-disponiveis">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">Serviços Disponíveis</h2>
            </div>
            
            <?php if (empty($servicos)): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-slate-500 text-sm mb-2">Nenhum serviço disponível no momento</p>
                    <p class="text-xs text-slate-400 mb-4">Os serviços ainda não foram inseridos no banco de dados</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                        <a 
                            href="servicos/init.php" 
                            class="inline-block rounded-xl px-6 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md transition-all"
                            onclick="return confirm('Deseja inserir os serviços padrão (Adestramento) no banco de dados?');"
                        >
                            Inserir Serviços Padrão
                        </a>
                        <a 
                            href="database/insert_servicos.sql" 
                            target="_blank"
                            class="inline-block rounded-xl px-6 py-2 text-sm font-semibold shadow-sm border btn-secondary hover:shadow-md transition-all"
                        >
                            Ver Script SQL
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php 
                    // Ícones por tipo de serviço
                    $iconesServicos = [
                        'banho_tosa' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
                        'adestramento' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>',
                        'passeios' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>'
                    ];
                    $coresServicos = [
                        'banho_tosa' => 'bg-blue-100 text-blue-600',
                        'adestramento' => 'bg-purple-100 text-purple-600',
                        'passeios' => 'bg-green-100 text-green-600'
                    ];
                    foreach ($servicos as $servico): 
                        $icone = $iconesServicos[$servico['tipo']] ?? '';
                        $cor = $coresServicos[$servico['tipo']] ?? 'bg-slate-100 text-slate-600';
                    ?>
                        <div class="border border-slate-200 rounded-xl p-6 hover:border-emerald-500 hover:shadow-md transition-all flex flex-col">
                            <div class="mb-4 flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 <?= $cor ?> rounded-lg">
                                        <?= $icone ?>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900"><?= esc($servico['nome']) ?></h3>
                                </div>
                                <p class="text-sm text-slate-600 mb-4 line-clamp-3"><?= esc($servico['descricao']) ?></p>
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="text-2xl font-extrabold text-emerald-600">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                                <?php if ($servico['duracao']): ?>
                                    <div class="flex items-center gap-1 text-xs text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Duração: <?= $servico['duracao'] ?> minutos</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button 
                                onclick="abrirModalCheckout(<?= $servico['id'] ?>, '<?= esc(addslashes($servico['nome'])) ?>', <?= $servico['preco'] ?>)"
                                class="w-full rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md transition-all mt-auto"
                            >
                                Agendar Serviço
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ações Rápidas -->
        <div class="card p-6 mb-8">
            <h2 class="text-xl font-bold text-slate-900 mb-4">Ações Rápidas</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="index.php" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">Agendar Serviço</span>
                </a>
                <a href="index.php#servicos" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">Ver Serviços</span>
                </a>
                <a href="index.php#contato" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">Contato</span>
                </a>
                <a href="auth/logout.php" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 hover:border-red-500 hover:bg-red-50 transition-all">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="text-sm font-semibold text-slate-700">Sair</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-slate-500">
                <p>&copy; <?= date('Y') ?> <?= esc($business['name']) ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal Adicionar Pet -->
    <div id="modalAdicionarPet" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Adicionar Pet</h2>
                    <button onclick="fecharModalAdicionarPet()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mensagens de Erro -->
                <div id="mensagensErroPet" class="hidden mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <ul id="listaErrosPet" class="list-disc list-inside text-sm text-red-700">
                    </ul>
                </div>

                <!-- Mensagem de Sucesso -->
                <div id="mensagemSucessoPet" class="hidden mb-6 p-6 bg-green-50 border border-green-200 rounded-xl text-center">
                    <div class="mb-3">
                        <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-green-800 mb-2">Pet cadastrado com sucesso!</h3>
                    <p class="text-sm text-green-700 mb-4">Seu pet foi cadastrado e já pode agendar serviços.</p>
                    <button onclick="window.location.href='dashboard.php'" class="inline-block rounded-xl px-6 py-2 text-sm font-semibold btn-primary hover:shadow-md transition-all">
                        Fechar
                    </button>
                </div>

                <!-- Formulário -->
                <form id="formAdicionarPet" action="pets/create.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <div>
                        <label for="pet_foto" class="block text-sm font-semibold mb-2">Foto do Pet</label>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    id="pet_foto" 
                                    name="foto" 
                                    accept="image/*"
                                    class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                                    onchange="previewFoto(this, 'previewFotoAdicionar')"
                                >
                                <p class="text-xs text-slate-500 mt-1">Formatos aceitos: JPG, PNG, GIF (máx. 5MB)</p>
                            </div>
                            <div id="previewFotoAdicionar" class="w-20 h-20 rounded-lg overflow-hidden border border-slate-200 hidden">
                                <img id="imgPreviewAdicionar" src="" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="pet_nome" class="block text-sm font-semibold mb-2">Nome do Pet *</label>
                        <input 
                            type="text" 
                            id="pet_nome" 
                            name="nome" 
                            required
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Ex: Rex, Luna, Max"
                        >
                    </div>

                    <div>
                        <label for="pet_tipo" class="block text-sm font-semibold mb-2">Tipo *</label>
                        <select 
                            id="pet_tipo" 
                            name="tipo" 
                            required
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Selecione o tipo</option>
                            <option value="cachorro">Cachorro</option>
                            <option value="gato">Gato</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>

                    <div>
                        <label for="pet_raca" class="block text-sm font-semibold mb-2">Raça</label>
                        <input 
                            type="text" 
                            id="pet_raca" 
                            name="raca" 
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Ex: Golden Retriever, Persa, SRD"
                        >
                    </div>

                    <div>
                        <label for="pet_idade" class="block text-sm font-semibold mb-2">Idade (anos)</label>
                        <input 
                            type="number" 
                            id="pet_idade" 
                            name="idade" 
                            min="0" 
                            max="30"
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Ex: 2"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md transition-all"
                    >
                        Cadastrar Pet
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Pet -->
    <div id="modalEditarPet" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Editar Pet</h2>
                    <button onclick="fecharModalEditarPet()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mensagens de Erro -->
                <div id="mensagensErroEditarPet" class="hidden mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <ul id="listaErrosEditarPet" class="list-disc list-inside text-sm text-red-700">
                    </ul>
                </div>

                <!-- Formulário -->
                <form id="formEditarPet" action="pets/update.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" id="edit_pet_id" name="id" value="">
                    
                    <div>
                        <label for="edit_pet_foto" class="block text-sm font-semibold mb-2">Foto do Pet</label>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    id="edit_pet_foto" 
                                    name="foto" 
                                    accept="image/*"
                                    class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                                    onchange="previewFoto(this, 'previewFotoEditar')"
                                >
                                <p class="text-xs text-slate-500 mt-1">Formatos aceitos: JPG, PNG, GIF (máx. 5MB)</p>
                            </div>
                            <div id="previewFotoEditar" class="w-20 h-20 rounded-lg overflow-hidden border border-slate-200 hidden">
                                <img id="imgPreviewEditar" src="" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="edit_pet_nome" class="block text-sm font-semibold mb-2">Nome do Pet *</label>
                        <input 
                            type="text" 
                            id="edit_pet_nome" 
                            name="nome" 
                            required
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Ex: Rex, Luna, Max"
                        >
                    </div>

                    <div>
                        <label for="edit_pet_tipo" class="block text-sm font-semibold mb-2">Tipo *</label>
                        <select 
                            id="edit_pet_tipo" 
                            name="tipo" 
                            required
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Selecione o tipo</option>
                            <option value="cachorro">Cachorro</option>
                            <option value="gato">Gato</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_pet_raca" class="block text-sm font-semibold mb-2">Raça</label>
                        <input 
                            type="text" 
                            id="edit_pet_raca" 
                            name="raca" 
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Ex: Golden Retriever, Persa, SRD"
                        >
                    </div>

                    <div>
                        <label for="edit_pet_idade" class="block text-sm font-semibold mb-2">Idade (anos)</label>
                        <input 
                            type="number" 
                            id="edit_pet_idade" 
                            name="idade" 
                            min="0" 
                            max="30"
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Ex: 2"
                        >
                    </div>

                    <div class="flex gap-3">
                        <button 
                            type="submit" 
                            class="flex-1 rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md transition-all"
                        >
                            Salvar Alterações
                        </button>
                        <button 
                            type="button"
                            onclick="fecharModalEditarPet()"
                            class="px-6 py-3 text-base font-semibold rounded-2xl border btn-secondary hover:shadow-md transition-all"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão de Pet -->
    <div id="modalExcluirPet" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-8">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900">Confirmar Exclusão</h2>
                    <button onclick="fecharModalExcluirPet()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Conteúdo -->
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Tem certeza que deseja excluir?</h3>
                    <p class="text-sm text-slate-600 mb-1">
                        O pet <strong id="nomePetExcluir" class="text-slate-900"></strong> será permanentemente removido.
                    </p>
                    <p class="text-xs text-red-600 font-semibold mt-2">Esta ação não pode ser desfeita!</p>
                </div>

                <!-- Botões -->
                <div class="flex gap-3">
                    <button 
                        type="button"
                        onclick="fecharModalExcluirPet()"
                        class="flex-1 px-6 py-3 text-base font-semibold rounded-2xl border btn-secondary hover:shadow-md transition-all"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button"
                        id="btnConfirmarExcluir"
                        onclick="executarExcluirPet()"
                        class="flex-1 px-6 py-3 text-base font-semibold rounded-2xl border text-white bg-red-600 hover:bg-red-700 border-red-600 hover:border-red-700 hover:shadow-md transition-all"
                    >
                        Excluir Pet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Selecionar Serviço para Pet -->
    <div id="modalSelecionarServico" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold">Selecionar Serviço</h2>
                        <p class="text-sm text-slate-600 mt-1">Para: <strong id="petNomeSelecionado"></strong></p>
                    </div>
                    <button onclick="fecharModalSelecionarServico()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Lista de Serviços -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (!empty($servicos)): ?>
                        <?php 
                        $iconesServicos = [
                            'banho_tosa' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
                            'adestramento' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>',
                            'passeios' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>'
                        ];
                        $coresServicos = [
                            'banho_tosa' => 'bg-blue-100 text-blue-600',
                            'adestramento' => 'bg-purple-100 text-purple-600',
                            'passeios' => 'bg-green-100 text-green-600'
                        ];
                        foreach ($servicos as $servico): 
                            $icone = $iconesServicos[$servico['tipo']] ?? '';
                            $cor = $coresServicos[$servico['tipo']] ?? 'bg-slate-100 text-slate-600';
                        ?>
                            <div class="border border-slate-200 rounded-xl p-5 hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer" onclick="selecionarServicoParaPet(<?= $servico['id'] ?>, '<?= esc(addslashes($servico['nome'])) ?>', <?= $servico['preco'] ?>)">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 <?= $cor ?> rounded-lg">
                                        <?= $icone ?>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900"><?= esc($servico['nome']) ?></h3>
                                </div>
                                <p class="text-sm text-slate-600 mb-3 line-clamp-2"><?= esc($servico['descricao']) ?></p>
                                <div class="flex items-center justify-between">
                                    <p class="text-xl font-extrabold text-emerald-600">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></p>
                                    <?php if ($servico['duracao']): ?>
                                        <span class="text-xs text-slate-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <?= $servico['duracao'] ?> min
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-2 text-center py-8">
                            <p class="text-slate-500 text-sm">Nenhum serviço disponível no momento</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6 flex justify-end">
                    <button 
                        type="button"
                        onclick="fecharModalSelecionarServico()"
                        class="px-6 py-2 text-sm font-semibold rounded-xl border btn-secondary hover:shadow-md transition-all"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Checkout -->
    <div id="modalCheckout" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold">Finalizar Compra</h2>
                    <button onclick="fecharModalCheckout()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mensagens de Erro -->
                <div id="mensagensErroCheckout" class="hidden mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <ul id="listaErrosCheckout" class="list-disc list-inside text-sm text-red-700">
                    </ul>
                </div>

                <!-- Resumo do Serviço -->
                <div class="mb-6 p-4 bg-slate-50 rounded-xl">
                    <h3 class="font-bold text-slate-900 mb-2" id="checkoutServicoNome"></h3>
                    <p class="text-2xl font-extrabold text-emerald-600" id="checkoutServicoPreco"></p>
                </div>

                <!-- Formulário -->
                <form id="formCheckout" action="agendamentos/create.php" method="POST" class="space-y-5">
                    <input type="hidden" id="checkout_servico_id" name="servico_id" value="">
                    
                    <div>
                        <label for="checkout_pet_id" class="block text-sm font-semibold mb-2">Selecione o Pet *</label>
                        <select 
                            id="checkout_pet_id" 
                            name="pet_id" 
                            required
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Selecione um pet</option>
                            <?php foreach ($pets as $pet): ?>
                                <option value="<?= $pet['id'] ?>"><?= esc($pet['nome']) ?> (<?= esc($pet['tipo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($pets)): ?>
                            <p class="text-xs text-red-600 mt-1">Você precisa cadastrar um pet antes de agendar um serviço.</p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="checkout_data_agendamento" class="block text-sm font-semibold mb-2">Data e Hora do Agendamento</label>
                        <input 
                            type="datetime-local" 
                            id="checkout_data_agendamento" 
                            name="data_agendamento" 
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            min="<?= date('Y-m-d\TH:i') ?>"
                        >
                        <p class="text-xs text-slate-500 mt-1">Deixe em branco para agendar sem data específica</p>
                    </div>

                    <div>
                        <label for="checkout_observacoes" class="block text-sm font-semibold mb-2">Observações (opcional)</label>
                        <textarea 
                            id="checkout_observacoes" 
                            name="observacoes" 
                            rows="3"
                            class="w-full px-4 py-3 rounded-xl border card-outline focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="Informações adicionais sobre o serviço..."
                        ></textarea>
                    </div>

                    <!-- Resumo do Pedido -->
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-slate-600">Subtotal</span>
                            <span class="text-sm font-semibold text-slate-900" id="checkoutSubtotal">R$ 0,00</span>
                        </div>
                        <div class="flex items-center justify-between text-lg font-bold">
                            <span class="text-slate-900">Total</span>
                            <span class="text-emerald-600" id="checkoutTotal">R$ 0,00</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            type="submit" 
                            id="btnFinalizarCompra"
                            class="flex-1 rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary hover:shadow-md transition-all"
                            <?= empty($pets) ? 'disabled' : '' ?>
                        >
                            Finalizar Compra
                        </button>
                        <button 
                            type="button"
                            onclick="fecharModalCheckout()"
                            class="px-6 py-3 text-base font-semibold rounded-2xl border btn-secondary hover:shadow-md transition-all"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Modal Backdrop */
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
        
        .modal-backdrop.show {
            opacity: 1;
            animation: fadeInBackdrop 0.3s ease-out;
        }
        
        @keyframes fadeInBackdrop {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
                -webkit-backdrop-filter: blur(0px);
            }
            to {
                opacity: 1;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
        }
        
        /* Modal Content */
        .modal-content {
            transform: translateY(20px) scale(0.95);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        #modalAdicionarPet.show,
        #modalEditarPet.show,
        #modalExcluirPet.show,
        #modalCheckout.show,
        #modalSelecionarServico.show {
            display: flex !important;
        }
        
        #modalAdicionarPet.show .modal-content,
        #modalEditarPet.show .modal-content,
        #modalExcluirPet.show .modal-content,
        #modalCheckout.show .modal-content,
        #modalSelecionarServico.show .modal-content {
            animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        
        @keyframes modalSlideIn {
            0% {
                transform: translateY(30px) scale(0.9);
                opacity: 0;
            }
            60% {
                transform: translateY(-5px) scale(1.02);
            }
            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }
        
        /* Animação de saída */
        #modalAdicionarPet.closing .modal-content,
        #modalEditarPet.closing .modal-content,
        #modalExcluirPet.closing .modal-content,
        #modalCheckout.closing .modal-content,
        #modalSelecionarServico.closing .modal-content {
            animation: modalSlideOut 0.3s ease-in forwards;
        }
        
        #modalAdicionarPet.closing,
        #modalEditarPet.closing,
        #modalExcluirPet.closing,
        #modalCheckout.closing,
        #modalSelecionarServico.closing {
            animation: fadeOutBackdrop 0.3s ease-in forwards;
        }
        
        @keyframes modalSlideOut {
            from {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            to {
                transform: translateY(20px) scale(0.95);
                opacity: 0;
            }
        }
        
        @keyframes fadeOutBackdrop {
            from {
                opacity: 1;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
            to {
                opacity: 0;
                backdrop-filter: blur(0px);
                -webkit-backdrop-filter: blur(0px);
            }
        }
    </style>

    <script>
        // Funções para abrir/fechar modal de adicionar pet
        function abrirModalAdicionarPet() {
            const modal = document.getElementById('modalAdicionarPet');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function fecharModalAdicionarPet() {
            const modal = document.getElementById('modalAdicionarPet');
            modal.classList.add('closing');
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.classList.remove('closing');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.getElementById('formAdicionarPet').reset();
                document.getElementById('mensagensErroPet').classList.add('hidden');
                document.getElementById('mensagemSucessoPet').classList.add('hidden');
                document.getElementById('formAdicionarPet').style.display = 'block';
            }, 300);
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalAdicionarPet').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalAdicionarPet();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modalAdicionar = document.getElementById('modalAdicionarPet');
                const modalEditar = document.getElementById('modalEditarPet');
                const modalExcluir = document.getElementById('modalExcluirPet');
                const modalCheckout = document.getElementById('modalCheckout');
                const modalSelecionarServico = document.getElementById('modalSelecionarServico');
                if (modalAdicionar.classList.contains('show')) {
                    fecharModalAdicionarPet();
                }
                if (modalEditar.classList.contains('show')) {
                    fecharModalEditarPet();
                }
                if (modalExcluir.classList.contains('show')) {
                    fecharModalExcluirPet();
                }
                if (modalCheckout.classList.contains('show')) {
                    fecharModalCheckout();
                }
                if (modalSelecionarServico.classList.contains('show')) {
                    fecharModalSelecionarServico();
                }
            }
        });

        // Verificar se há erros ou sucesso na URL
        <?php 
        if (isset($_GET['erro']) && $_GET['erro'] == 'pet'): 
            $errosPet = $_SESSION['erros_pet'] ?? [];
            unset($_SESSION['erros_pet']);
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                abrirModalAdicionarPet();
                <?php if (!empty($errosPet)): ?>
                    const mensagensErro = document.getElementById('mensagensErroPet');
                    const listaErros = document.getElementById('listaErrosPet');
                    listaErros.innerHTML = '';
                    <?php foreach ($errosPet as $erro): ?>
                        const li = document.createElement('li');
                        li.textContent = <?= json_encode($erro, JSON_UNESCAPED_UNICODE) ?>;
                        listaErros.appendChild(li);
                    <?php endforeach; ?>
                    mensagensErro.classList.remove('hidden');
                <?php endif; ?>
            });
        <?php endif; ?>

        <?php 
        if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'pet'): 
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                abrirModalAdicionarPet();
                document.getElementById('formAdicionarPet').style.display = 'none';
                document.getElementById('mensagemSucessoPet').classList.remove('hidden');
                
                // Limpar parâmetros da URL para evitar reabertura do modal
                if (window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        <?php endif; ?>

        // Funções para modal de editar pet
        function abrirModalEditarPet(id) {
            // Buscar dados do pet via API
            fetch('pets/get.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.pet) {
                        const pet = data.pet;
                        
                        // Preencher formulário
                        document.getElementById('edit_pet_id').value = pet.id;
                        document.getElementById('edit_pet_nome').value = pet.nome || '';
                        document.getElementById('edit_pet_tipo').value = pet.tipo || '';
                        document.getElementById('edit_pet_raca').value = pet.raca || '';
                        document.getElementById('edit_pet_idade').value = pet.idade || '';
                        
                        // Preview da foto
                        const previewDiv = document.getElementById('previewFotoEditar');
                        const previewImg = document.getElementById('imgPreviewEditar');
                        if (pet.foto) {
                            previewImg.src = pet.foto;
                            previewDiv.classList.remove('hidden');
                        } else {
                            previewDiv.classList.add('hidden');
                        }
                        
                        // Limpar mensagens de erro
                        document.getElementById('mensagensErroEditarPet').classList.add('hidden');
                        
                        // Abrir modal
                        const modal = document.getElementById('modalEditarPet');
                        modal.style.display = 'flex';
                        setTimeout(() => {
                            modal.classList.add('show');
                        }, 10);
                        document.body.style.overflow = 'hidden';
                    } else {
                        alert('Erro ao carregar dados do pet. Tente novamente.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar pet:', error);
                    alert('Erro ao carregar dados do pet. Tente novamente.');
                });
        }

        function fecharModalEditarPet() {
            const modal = document.getElementById('modalEditarPet');
            modal.classList.add('closing');
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.classList.remove('closing');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.getElementById('formEditarPet').reset();
                document.getElementById('mensagensErroEditarPet').classList.add('hidden');
            }, 300);
        }

        // Fechar modal de editar ao clicar fora
        document.getElementById('modalEditarPet').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalEditarPet();
            }
        });

        // Variáveis globais para exclusão
        let petIdParaExcluir = null;

        // Função para abrir modal de exclusão
        function confirmarExcluirPet(id, nome) {
            petIdParaExcluir = id;
            document.getElementById('nomePetExcluir').textContent = nome;
            
            const modal = document.getElementById('modalExcluirPet');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        // Função para fechar modal de exclusão
        function fecharModalExcluirPet() {
            const modal = document.getElementById('modalExcluirPet');
            modal.classList.add('closing');
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.classList.remove('closing');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                petIdParaExcluir = null;
            }, 300);
        }

        // Função para executar exclusão
        function executarExcluirPet() {
            if (petIdParaExcluir) {
                window.location.href = 'pets/delete.php?id=' + petIdParaExcluir;
            }
        }

        // Fechar modal de exclusão ao clicar fora
        document.getElementById('modalExcluirPet').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalExcluirPet();
            }
        });

        // Função para preview de foto
        function previewFoto(input, previewId) {
            const previewDiv = document.getElementById(previewId);
            const previewImg = document.getElementById(previewId === 'previewFotoAdicionar' ? 'imgPreviewAdicionar' : 'imgPreviewEditar');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewDiv.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                previewDiv.classList.add('hidden');
            }
        }

        // Verificar erros de edição
        <?php 
        if (isset($_GET['erro']) && $_GET['erro'] == 'editar_pet'): 
            $errosEditarPet = $_SESSION['erros_editar_pet'] ?? [];
            $petId = $_SESSION['pet_edit_id'] ?? null;
            unset($_SESSION['erros_editar_pet'], $_SESSION['pet_edit_id']);
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($petId): ?>
                    // Abrir modal e buscar dados do pet
                    abrirModalEditarPet(<?= $petId ?>);
                    
                    // Exibir erros após o modal abrir
                    setTimeout(() => {
                        <?php if (!empty($errosEditarPet)): ?>
                            const mensagensErro = document.getElementById('mensagensErroEditarPet');
                            const listaErros = document.getElementById('listaErrosEditarPet');
                            listaErros.innerHTML = '';
                            <?php foreach ($errosEditarPet as $erro): ?>
                                const li = document.createElement('li');
                                li.textContent = <?= json_encode($erro, JSON_UNESCAPED_UNICODE) ?>;
                                listaErros.appendChild(li);
                            <?php endforeach; ?>
                            mensagensErro.classList.remove('hidden');
                        <?php endif; ?>
                    }, 500);
                <?php endif; ?>
            });
        <?php endif; ?>

        // Verificar sucesso de edição ou exclusão
        <?php if (isset($_GET['sucesso']) && ($_GET['sucesso'] == 'editar_pet' || $_GET['sucesso'] == 'excluir_pet')): ?>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 500);
            });
        <?php endif; ?>

        // Funções para modal de checkout
        function abrirModalCheckout(servicoId, servicoNome, servicoPreco, petId = null) {
            document.getElementById('checkout_servico_id').value = servicoId;
            document.getElementById('checkoutServicoNome').textContent = servicoNome;
            document.getElementById('checkoutServicoPreco').textContent = 'R$ ' + servicoPreco.toFixed(2).replace('.', ',');
            document.getElementById('checkoutSubtotal').textContent = 'R$ ' + servicoPreco.toFixed(2).replace('.', ',');
            document.getElementById('checkoutTotal').textContent = 'R$ ' + servicoPreco.toFixed(2).replace('.', ',');
            
            // Limpar formulário
            document.getElementById('formCheckout').reset();
            document.getElementById('checkout_servico_id').value = servicoId;
            
            // Pré-selecionar pet se fornecido
            if (petId) {
                document.getElementById('checkout_pet_id').value = petId;
            }
            
            document.getElementById('mensagensErroCheckout').classList.add('hidden');
            
            const modal = document.getElementById('modalCheckout');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function fecharModalCheckout() {
            const modal = document.getElementById('modalCheckout');
            modal.classList.add('closing');
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.classList.remove('closing');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.getElementById('formCheckout').reset();
                document.getElementById('mensagensErroCheckout').classList.add('hidden');
            }, 300);
        }

        // Fechar modal de checkout ao clicar fora
        document.getElementById('modalCheckout').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalCheckout();
            }
        });

        // Função para scroll até serviços
        function scrollToServicos() {
            document.getElementById('servicos-disponiveis').scrollIntoView({ behavior: 'smooth' });
        }

        // Variáveis globais para seleção de serviço
        let petIdSelecionado = null;

        // Função para abrir modal de seleção de serviço
        function abrirModalSelecionarServico(petId, petNome) {
            petIdSelecionado = petId;
            document.getElementById('petNomeSelecionado').textContent = petNome;
            
            const modal = document.getElementById('modalSelecionarServico');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        // Função para fechar modal de seleção de serviço
        function fecharModalSelecionarServico() {
            const modal = document.getElementById('modalSelecionarServico');
            modal.classList.add('closing');
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.classList.remove('closing');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                petIdSelecionado = null;
            }, 300);
        }

        // Função para selecionar serviço e abrir checkout
        function selecionarServicoParaPet(servicoId, servicoNome, servicoPreco) {
            fecharModalSelecionarServico();
            setTimeout(() => {
                abrirModalCheckout(servicoId, servicoNome, servicoPreco, petIdSelecionado);
            }, 350);
        }

        // Fechar modal de seleção de serviço ao clicar fora
        document.getElementById('modalSelecionarServico').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalSelecionarServico();
            }
        });

        // Verificar erros de checkout
        <?php 
        if (isset($_GET['erro']) && $_GET['erro'] == 'checkout'): 
            $errosCheckout = $_SESSION['erros_checkout'] ?? [];
            $servicoId = $_SESSION['checkout_servico_id'] ?? null;
            unset($_SESSION['erros_checkout'], $_SESSION['checkout_servico_id']);
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($servicoId): ?>
                    // Buscar dados do serviço
                    fetch('servicos/get.php?id=<?= $servicoId ?>')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                abrirModalCheckout(
                                    data.servico.id,
                                    data.servico.nome,
                                    parseFloat(data.servico.preco)
                                );
                                <?php if (!empty($errosCheckout)): ?>
                                    const mensagensErro = document.getElementById('mensagensErroCheckout');
                                    const listaErros = document.getElementById('listaErrosCheckout');
                                    listaErros.innerHTML = '';
                                    <?php foreach ($errosCheckout as $erro): ?>
                                        const li = document.createElement('li');
                                        li.textContent = <?= json_encode($erro, JSON_UNESCAPED_UNICODE) ?>;
                                        listaErros.appendChild(li);
                                    <?php endforeach; ?>
                                    mensagensErro.classList.remove('hidden');
                                <?php endif; ?>
                            }
                        });
                <?php endif; ?>
            });
        <?php endif; ?>

        // Verificar sucesso de compra
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'compra'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 500);
            });
        <?php endif; ?>

        // Verificar se serviços foram inseridos
        <?php if (isset($_GET['servicos_inseridos'])): 
            $inseridos = $_SESSION['servicos_inseridos'] ?? 0;
            unset($_SESSION['servicos_inseridos']);
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($inseridos > 0): ?>
                    alert('Serviços inseridos com sucesso! <?= $inseridos ?> serviço(s) adicionado(s) ao banco de dados.');
                <?php else: ?>
                    // Serviços já existiam
                <?php endif; ?>
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 100);
            });
        <?php endif; ?>

        // Verificar erro ao inserir serviços
        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'servicos'): 
            $erroMsg = $_SESSION['erro_servicos'] ?? 'Erro desconhecido';
            unset($_SESSION['erro_servicos']);
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                alert('Erro ao inserir serviços: <?= esc(addslashes($erroMsg)) ?>');
            });
        <?php endif; ?>
    </script>
</body>
</html>
