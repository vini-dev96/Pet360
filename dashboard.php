<?php
// dashboard.php — Dashboard do Usuário
session_start();

// Verificar se o usuário está autenticado (middleware)
if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

// Buscar informações atualizadas do usuário
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
} catch (PDOException $e) {
    error_log('Erro ao buscar dados do usuário: ' . $e->getMessage());
    $usuario = [
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email' => $_SESSION['usuario_email'] ?? '',
        'telefone' => '',
        'data_criacao' => date('Y-m-d H:i:s')
    ];
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
                <div class="text-center py-8">
                    <p class="text-slate-500 text-sm mb-4">Nenhum serviço agendado no momento</p>
                    <a href="index.php" class="inline-block rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
                        Agendar Serviço
                    </a>
                </div>
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
</body>
</html>
