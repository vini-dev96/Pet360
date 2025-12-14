<?php
// index.php — Landing page Pets (PHP + Tailwind)

// ---- CONFIGURAÇÃO RÁPIDA ----
$business = [
  'name'       => 'Pet360',
  'tagline'    => 'Seu pet feliz e bem cuidado',
  'phone'      => '+55 11 99999-9999',
  'whatsapp'   => 'https://wa.me/5511999999999?text=Quero%20agendar%20um%20servi%C3%A7o%20para%20meu%20pet',
  'email'      => 'contato@pet360.com.br',
  'logo'       => './img/logo-pet360-2.png',
  'priceRange' => '$$',
  'address'    => [
    'street' => 'Rua das Patinhas, 360',
    'city'   => 'São Paulo',
    'state'  => 'SP',
    'zip'    => '01234-567',
    'country'=> 'BR'
  ],
  'geo' => ['lat' => -23.55052, 'lng' => -46.633308],
  'hours' => [
    ['day' => 'Mo-Fr', 'opens' => '08:30', 'closes' => '19:30'],
    ['day' => 'Sa',    'opens' => '09:00', 'closes' => '17:00'],
  ],
  'heroImg'    => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?q=80&w=1600&auto=format&fit=crop',
  'serviceImgs'=> [
    'adestramento' => './img/adestramento.jpg',
    'passeios'     => './img/passeio.jpg',
    'banho'        => './img/banho.jpg',
  ],
];

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$phoneTel = preg_replace('/\s|\(|\)|-|\+/', '', $business['phone']);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title><?= esc($business['name']) ?> — Adestramento, Passeios, Banho & Tosa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Adestramento, passeios, banho e tosa com profissionais certificados. Agendamento fácil por WhatsApp.">
  <link rel="icon" type="image/png" href="./img/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root{
      --color-cream:#f6efe4;
      --color-cream-soft:#f2e6d6;
      --color-card:#fff9ee;
      --color-emerald:#0f5c47;
      --color-emerald-dark:#0b4736;
      --color-sage:#1d7f63;
      --color-text:#24313b;
    }
    body{
      background:var(--color-cream);
      color:var(--color-text);
      font-family:"Inter",system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    }
    .container{max-width:1152px}
    .surface-card{
      background:var(--color-card);
      box-shadow:0 10px 30px rgba(15,92,71,0.05);
    }
    .chip{
      background:rgba(15,92,71,0.08);
      color:var(--color-emerald);
    }
    .btn-primary{
      background:var(--color-emerald);
      color:#fff;
    }
    .btn-primary:hover{
      background:var(--color-emerald-dark);
      color:#fff;
    }
    .btn-secondary{
      background:#fff;
      color:var(--color-emerald);
      border-color:rgba(15,92,71,0.25);
    }
    .btn-link{
      color:var(--color-emerald);
    }
    .badge{
      background:var(--color-emerald);
      color:#fff;
    }
    .card-outline{
      border-color:rgba(15,92,71,0.18);
    }
  </style>
</head>
<body class="bg-gradient-to-b from-white to-slate-50 text-slate-900">
  <!-- Header -->
  <header class="sticky top-0 z-50 border-b" style="background:rgba(246,239,228,0.92);backdrop-filter:blur(18px);">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
      <a href="#top" class="flex items-center gap-3">
        <img src="<?= esc($business['logo']) ?>" alt="<?= esc($business['name']) ?>" class="h-20">
      </a>
      <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
        <a href="#hero" class="transition-colors hover:text-emerald-600">Home</a>
        <a href="#servicos" class="transition-colors hover:text-emerald-600">Serviços</a>
        <a href="#precos" class="transition-colors hover:text-emerald-600">Planos</a>
        <a href="#depoimentos" class="transition-colors hover:text-emerald-600">Depoimentos</a>
        <a href="#faq" class="transition-colors hover:text-emerald-600">FAQ</a>
        <a href="#contato" class="transition-colors hover:text-emerald-600">Contato</a>
      </nav>
      <div class="flex items-center gap-3">
        <a href="tel:<?= esc($phoneTel) ?>" class="hidden sm:flex items-center gap-2 text-sm font-semibold btn-link">
          <?= esc($business['phone']) ?>
        </a>
        <a href="<?= esc($business['whatsapp']) ?>" class="rounded-xl px-4 py-2 text-sm font-semibold shadow-sm border btn-primary hover:shadow-md">
          Agendar agora
        </a>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section id="hero" class="container mx-auto px-4 pt-16 pb-14 grid lg:grid-cols-[1.05fr,1fr] gap-12 items-center">
    <div>
      <span class="chip inline-flex items-center gap-2 rounded-full px-4 py-1 text-xs font-semibold uppercase tracking-wide">
        Cuidado completo 360º
      </span>
      <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold leading-tight">
        <?= esc($business['tagline']) ?> com adestramento, pet sitter e banho & tosa premium.
      </h1>
      <p class="mt-4 text-lg text-slate-700 max-w-xl">
        Serviços pensados para o bem-estar do seu melhor amigo, com relatórios, fotos e acompanhamento em tempo real pelo WhatsApp.
      </p>
      <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="<?= esc($business['whatsapp']) ?>" class="rounded-2xl px-6 py-3 text-base font-semibold shadow-sm border btn-primary inline-flex items-center gap-2">
          Agendar sessão
        </a>
        <a href="tel:<?= esc($phoneTel) ?>" class="rounded-2xl px-6 py-3 text-base font-semibold border btn-secondary inline-flex items-center gap-2">
          Ver telefone
        </a>
      </div>
      <div class="mt-8 flex flex-wrap gap-8 text-sm text-slate-700">
        <div>
          <p class="text-3xl font-black text-emerald-900">4.9★</p>
          <p>+320 tutores satisfeitos</p>
        </div>
        <div>
          <p class="text-3xl font-black text-emerald-900">24h</p>
          <p>Monitoramento e relatórios</p>
        </div>
        <div>
          <p class="text-3xl font-black text-emerald-900">100%</p>
          <p>Equipe certificada e carinhosa</p>
        </div>
      </div>
    </div>
    <div class="relative">
      <div class="aspect-[4/3] w-full overflow-hidden rounded-3xl border card-outline surface-card">
        <img src="<?= esc($business['heroImg']) ?>" alt="Profissional cuidando de um cachorro" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="absolute -bottom-6 left-6 right-auto">
        <div class="surface-card rounded-3xl border card-outline px-5 py-4 shadow-md max-w-[320px]">
          <div class="flex items-center gap-3">
            <span class="badge rounded-full px-3 py-1 text-xs font-semibold">Passeio concluído</span>
            <p class="text-xs text-slate-500">Hoje • 15:30</p>
          </div>
          <p class="mt-2 text-sm font-semibold">Belinha completou 2,4 km no Parque das Árvores</p>
          <p class="text-xs text-slate-600 mt-1">34 minutos • 5 fotos enviadas • Monitoramento GPS ativo</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Como funciona -->
  <section class="container mx-auto px-4 py-14" id="como-funciona">
    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-800">Como funciona</span>
    <h2 class="mt-2 text-3xl font-extrabold">Seu pet no centro de tudo em quatro passos</h2>
    <p class="mt-2 text-slate-700 max-w-2xl">Do primeiro contato ao acompanhamento na volta pra casa, a Pet360 cuida de cada detalhe com carinho e transparência.</p>
    <div class="mt-8 grid md:grid-cols-4 gap-5">
      <?php
      $steps = [
        ['icon'=>'🗓️','title'=>'Escolha o serviço','text'=>'Adestramento, pet sitter ou banho & tosa sob medida.'],
        ['icon'=>'📞','title'=>'Agende em minutos','text'=>'Combine horários pelo WhatsApp e receba confirmação.'],
        ['icon'=>'🧠','title'=>'Profissionais certificados','text'=>'Equipe treinada, plano personalizado e cuidados 360º.'],
        ['icon'=>'📲','title'=>'Acompanhe em tempo real','text'=>'Relatórios, fotos e rotas direto no seu celular.'],
      ];
      foreach($steps as $s): ?>
        <div class="surface-card rounded-3xl border card-outline p-6 flex flex-col gap-3">
          <span class="text-2xl"><?= esc($s['icon']) ?></span>
          <h3 class="font-semibold text-lg"><?= esc($s['title']) ?></h3>
          <p class="text-sm text-slate-600 leading-relaxed"><?= esc($s['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Serviços -->
  <section class="container mx-auto px-4 py-14" id="servicos">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div>
        <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-800">Serviços Pet360</span>
        <h2 class="mt-2 text-3xl font-extrabold">Adestramento, cuidados e companhia em um só lugar</h2>
        <p class="mt-2 text-slate-700 max-w-2xl">Planos pensados para rotina de filhotes, adultos e seniores, com acompanhamento contínuo e feedback transparente.</p>
      </div>
      <a href="<?= esc($business['whatsapp']) ?>" class="rounded-2xl px-5 py-2 text-sm font-semibold border btn-secondary">Ver disponibilidade</a>
    </div>
    <div class="mt-10 grid md:grid-cols-3 gap-6">
      <?php
      $services = [
        [
          'slug' => 'adestramento',
          'title'=> 'Adestramento positivo',
          'desc' => 'Protocolos personalizados para cada fase da vida do pet, com reforço positivo e acompanhamento familiar.',
          'items'=> ['Avaliação comportamental completa','Sessões presenciais e atividades guiadas em casa','Relatórios com vídeos e metas semanais'],
          'cta'  => 'Agendar avaliação'
        ],
        [
          'slug' => 'banho',
          'title'=> 'Banho & tosa premium',
          'desc' => 'Higiene completa, produtos hipoalergênicos e ambiente seguro para que o pet se sinta tranquilo.',
          'items'=> ['Banho terapêutico e tosa higiênica','Escovação, ouvidos e corte de unhas','Antes e depois para compartilhar'],
          'cta'  => 'Reservar horário'
        ],
        [
          'slug' => 'passeios',
          'title'=> 'Pet sitter & passeios',
          'desc' => 'Companhia carinhosa quando você não pode estar, com alimentação, passeios monitorados e muita diversão.',
          'items'=> ['Monitoramento GPS e fotos em tempo real','Alimentação conforme rotina do pet','Relatório diário com check-ins'],
          'cta'  => 'Montar pacote'
        ],
      ];
      foreach($services as $service):
        $image = $business['serviceImgs'][$service['slug']] ?? $business['heroImg'];
      ?>
      <article class="surface-card rounded-3xl border card-outline overflow-hidden flex flex-col">
        <div class="h-48 w-full overflow-hidden">
          <img src="<?= esc($image) ?>" alt="<?= esc($service['title']) ?>" class="h-full w-full object-cover" loading="lazy">
        </div>
        <div class="p-6 flex flex-col flex-1 gap-4">
          <h3 class="text-xl font-bold"><?= esc($service['title']) ?></h3>
          <p class="text-sm text-slate-600 leading-relaxed"><?= esc($service['desc']) ?></p>
          <ul class="text-sm text-slate-700 space-y-2">
            <?php foreach($service['items'] as $item): ?>
              <li class="flex items-start gap-2">
                <span class="text-emerald-700 mt-0.5">•</span>
                <span><?= esc($item) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="mt-auto pt-2">
            <a href="<?= esc($business['whatsapp']) ?>" class="rounded-2xl px-4 py-2 text-sm font-semibold border btn-primary inline-flex items-center gap-2"> <?= esc($service['cta']) ?> </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Preços -->
  <section class="container mx-auto px-4 py-14" id="precos">
    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-800">Planos flexíveis</span>
    <h2 class="mt-2 text-3xl font-extrabold">Escolha o ritmo ideal para a rotina do seu pet</h2>
    <p class="text-slate-700 mt-2 max-w-2xl">Valores referência para pets de porte pequeno e médio. Ajustamos tudo após a avaliação inicial para garantir o melhor cuidado.</p>
    <div class="mt-10 grid md:grid-cols-3 gap-6">
      <?php
      $plans = [
        [
          'title'=>'Plano Essencial',
          'price'=>'R$ 99',
          'subtitle'=>'por visita agendada',
          'features'=>['Banho & tosa premium','Passeio de 45 minutos','Atualização por WhatsApp'],
          'cta'=>'Quero este plano',
        ],
        [
          'title'=>'Plano Padrão',
          'highlight'=>true,
          'price'=>'R$ 149',
          'subtitle'=>'por semana',
          'features'=>['2 passeios monitorados','Check-in alimentar e hidratação','Relatório com fotos e vídeo'],
          'cta'=>'Agendar avaliação',
        ],
        [
          'title'=>'Plano Premium',
          'price'=>'R$ 199',
          'subtitle'=>'por semana',
          'features'=>['3 adestramentos + 2 passeios','Supervisão em casa até 2h/dia','Canal direto com especialista'],
          'cta'=>'Conversar com especialista',
        ],
      ];
      foreach($plans as $p): ?>
      <div class="rounded-3xl border card-outline p-6 shadow-sm surface-card <?= !empty($p['highlight']) ? 'relative overflow-hidden' : '' ?>">
        <?php if(!empty($p['highlight'])): ?>
          <span class="badge absolute top-5 right-5 rounded-full px-3 py-1 text-xs font-semibold shadow-sm">Mais popular</span>
        <?php endif; ?>
        <h3 class="font-bold text-xl"><?= esc($p['title']) ?></h3>
        <p class="mt-3 text-3xl font-black text-emerald-900"><?= esc($p['price']) ?></p>
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500"><?= esc($p['subtitle']) ?></p>
        <ul class="mt-4 text-sm text-slate-700 space-y-2">
          <?php foreach($p['features'] as $f): ?>
            <li class="flex items-start gap-2">
              <span class="text-emerald-700 mt-0.5">•</span>
              <span><?= esc($f) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= esc($business['whatsapp']) ?>" class="mt-6 inline-flex rounded-2xl px-4 py-2 text-sm font-semibold shadow-sm <?= !empty($p['highlight']) ? 'btn-primary border-transparent' : 'btn-secondary border' ?>">
          <?= esc($p['cta']) ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Depoimentos -->
  <section class="container mx-auto px-4 py-14" id="depoimentos">
    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-800">Depoimentos reais</span>
    <h2 class="mt-2 text-3xl font-extrabold">Histórias de tutores que confiam na Pet360</h2>
    <div class="mt-10 grid md:grid-cols-3 gap-6">
      <?php
      $testimonials = [
        ['name'=>'Jéssica Moura','role'=>'Tutora do Thor','text'=>'“Meu cãozinho tinha muita ansiedade quando ficava sozinho. Com a Pet360, ele recebe carinho, passeios e volta relaxado. A equipe envia tudo com fotos e vídeos!”'],
        ['name'=>'Marcos Esteves','role'=>'Tutor da Lili','text'=>'“Os passeios monitorados são impecáveis. A rota chega pelo celular e eu acompanho o trajeto em tempo real. Confiança total.”'],
        ['name'=>'Felipe Ramos','role'=>'Tutor da Nala','text'=>'“O banho & tosa é perfeito! Produtos cheirosos e uma atenção absurda aos detalhes. Nala sai feliz, sem estresse.”'],
      ];
      foreach($testimonials as $t): ?>
        <article class="surface-card rounded-3xl border card-outline p-6 flex flex-col gap-4">
          <div class="text-amber-500 text-lg">★★★★★</div>
          <p class="text-sm text-slate-700 leading-relaxed"><?= esc($t['text']) ?></p>
          <div class="mt-auto">
            <p class="font-semibold text-sm"><?= esc($t['name']) ?></p>
            <p class="text-xs text-slate-500"><?= esc($t['role']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Área de atendimento -->
  <section class="container mx-auto px-4 py-14">
    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-800">Área de atendimento</span>
    <h2 class="mt-2 text-3xl font-extrabold">Estamos pertinho de você na Zona Oeste</h2>
    <p class="mt-2 text-slate-700 max-w-2xl">Atendemos os bairros de Perdizes, Pompeia, Pinheiros e Lapa. Consulte disponibilidade em outras regiões pelo WhatsApp.</p>
    <div class="mt-8 rounded-3xl overflow-hidden border card-outline surface-card">
      <iframe
        title="Mapa de atendimento"
        class="w-full h-[320px]"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps?q=<?= esc($business['geo']['lat']) ?>,<?= esc($business['geo']['lng']) ?>&z=14&output=embed">
      </iframe>
    </div>
  </section>

  <!-- FAQ -->
  <section class="container mx-auto px-4 py-14" id="faq">
    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-800">FAQ</span>
    <h2 class="mt-2 text-3xl font-extrabold">Perguntas frequentes</h2>
    <p class="mt-2 text-slate-700 max-w-2xl">Algumas respostas rápidas. Se ainda tiver dúvidas, nossa equipe está online no WhatsApp para ajudar.</p>
    <div class="mt-8 grid md:grid-cols-2 gap-5">
      <?php
      $faq = [
        ['q'=>'Como funcionam os passeios?','a'=>'Definimos rotas seguras, registramos a caminhada e enviamos relatório com fotos e horário.'],
        ['q'=>'O adestramento é com reforço positivo?','a'=>'Sim. Trabalhamos reforço positivo, metas claras e atividades para casa.'],
        ['q'=>'Quais cuidados no banho & tosa?','a'=>'Produtos hipoalergênicos, esterilização de materiais e ambiente controlado.'],
        ['q'=>'Atendem meu bairro?','a'=>'Consulte o mapa ou fale via WhatsApp para confirmar.'],
      ];
      foreach($faq as $f): ?>
      <div class="surface-card rounded-3xl border card-outline p-6 shadow-sm">
        <details class="group">
          <summary class="font-semibold cursor-pointer text-slate-900 flex items-center justify-between">
            <?= esc($f['q']) ?>
            <span class="ml-2 text-emerald-700 transition-transform group-open:rotate-45">+</span>
          </summary>
          <p class="mt-3 text-sm text-slate-700 leading-relaxed"><?= esc($f['a']) ?></p>
        </details>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CTA final -->
  <section class="container mx-auto px-4 py-16" id="contato">
    <div class="rounded-3xl overflow-hidden shadow-lg border card-outline">
      <div class="grid md:grid-cols-2">
        <div class="p-8 md:p-12" style="background:var(--color-emerald);color:#fff;">
          <span class="text-xs font-semibold uppercase tracking-[0.3em]" style="color:rgba(255,255,255,0.75);">Pronto para começar?</span>
          <h2 class="mt-4 text-3xl font-extrabold">Agende uma visita ou peça uma avaliação gratuita</h2>
          <p class="mt-3 text-sm" style="color:rgba(255,255,255,0.85);">Responda algumas perguntas rápidas e montamos um plano sob medida para o seu pet em minutos.</p>
          <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= esc($business['whatsapp']) ?>" class="rounded-2xl px-6 py-3 text-base font-semibold border btn-primary" style="background:#fff;color:var(--color-emerald);border:none;">Falar no WhatsApp</a>
            <a href="tel:<?= esc($phoneTel) ?>" class="rounded-2xl px-6 py-3 text-base font-semibold border" style="border-color:rgba(255,255,255,0.4);color:#fff;">Ligar agora</a>
          </div>
        </div>
        <div class="p-8 md:p-12 surface-card">
          <h3 class="text-xl font-bold">Contato direto</h3>
          <ul class="mt-4 text-sm text-slate-700 space-y-3">
            <li><span class="font-semibold text-slate-900">WhatsApp:</span> <?= esc($business['phone']) ?></li>
            <li><span class="font-semibold text-slate-900">E-mail:</span> <?= esc($business['email']) ?></li>
            <li><span class="font-semibold text-slate-900">Endereço:</span> <?= esc($business['address']['street']) ?>, <?= esc($business['address']['city']) ?> - <?= esc($business['address']['state']) ?></li>
            <li><span class="font-semibold text-slate-900">Horários:</span>
              <ul class="mt-2 space-y-1">
                <?php foreach($business['hours'] as $h): ?>
                  <li><?= esc($h['day']) ?> <?= esc($h['opens']) ?>–<?= esc($h['closes']) ?></li>
                <?php endforeach; ?>
              </ul>
            </li>
          </ul>
          <div class="mt-8 rounded-2xl border card-outline p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-emerald-800 font-semibold">Endereço Pet360</p>
            <p class="mt-2 text-sm text-slate-700">Rua das Patinhas, 360 • São Paulo/SP</p>
            <p class="text-xs text-slate-500 mt-2">Pet sitter, adestramento, passeios monitorados e banho & tosa com hora marcada.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="border-t" style="background:var(--color-cream-soft);">
    <div class="container mx-auto px-4 py-10 grid md:grid-cols-4 gap-8 text-sm">
      <div class="space-y-4">
        <img src="<?= esc($business['logo']) ?>" alt="<?= esc($business['name']) ?>" class="h-16">
        <p class="text-slate-700">Cuidado integral que une adestramento, companhia e bem-estar com muita transparência.</p>
        <div class="flex gap-4">
          <a href="<?= esc($business['whatsapp']) ?>" class="text-emerald-700 font-semibold">WhatsApp</a>
          <a href="tel:<?= esc($phoneTel) ?>" class="text-emerald-700 font-semibold">Telefone</a>
        </div>
      </div>
      <div>
        <p class="font-bold text-slate-900">Navegação</p>
        <ul class="mt-3 space-y-2 text-slate-700">
          <li><a href="#hero" class="hover:text-emerald-700">Home</a></li>
          <li><a href="#servicos" class="hover:text-emerald-700">Serviços</a></li>
          <li><a href="#precos" class="hover:text-emerald-700">Planos</a></li>
          <li><a href="#depoimentos" class="hover:text-emerald-700">Depoimentos</a></li>
          <li><a href="#faq" class="hover:text-emerald-700">FAQ</a></li>
        </ul>
      </div>
      <div>
        <p class="font-bold text-slate-900">Contato</p>
        <ul class="mt-3 space-y-2 text-slate-700">
          <li><?= esc($business['phone']) ?></li>
          <li><?= esc($business['email']) ?></li>
          <li><?= esc($business['address']['street']) ?>, <?= esc($business['address']['city']) ?> - <?= esc($business['address']['state']) ?></li>
          <li>CEP <?= esc($business['address']['zip']) ?></li>
        </ul>
      </div>
      <div>
        <p class="font-bold text-slate-900">Horários</p>
        <ul class="mt-3 space-y-2 text-slate-700">
          <?php foreach($business['hours'] as $h): ?>
            <li><?= esc($h['day']) ?> <?= esc($h['opens']) ?>–<?= esc($h['closes']) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="border-t py-4 text-center text-xs text-slate-500">
      © <?= date('Y') ?> <?= esc($business['name']) ?>. Todos os direitos reservados.
    </div>
  </footer>

  <!-- Botão flutuante WhatsApp -->
  <a href="<?= esc($business['whatsapp']) ?>"
     class="fixed bottom-5 right-5 z-50 rounded-full shadow-lg px-5 py-3 font-semibold hover:shadow-xl"
     style="background:var(--color-emerald);color:#fff;"
     aria-label="Agendar no WhatsApp">Agendar no WhatsApp</a>

  <!-- JSON-LD: LocalBusiness -->
  <script type="application/ld+json">
  <?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'LocalBusiness',
    'name'     => $business['name'],
    'telephone'=> $business['phone'],
    'priceRange'=> $business['priceRange'],
    'address'  => [
      '@type' => 'PostalAddress',
      'streetAddress'=> $business['address']['street'],
      'addressLocality'=> $business['address']['city'],
      'addressRegion'  => $business['address']['state'],
      'postalCode'     => $business['address']['zip'],
      'addressCountry' => $business['address']['country'],
    ],
    'geo'      => [
      '@type'=>'GeoCoordinates',
      'latitude' => $business['geo']['lat'],
      'longitude'=> $business['geo']['lng'],
    ],
    'openingHoursSpecification' => array_map(fn($h)=>[
      '@type'=>'OpeningHoursSpecification',
      'dayOfWeek'=>$h['day'], 'opens'=>$h['opens'], 'closes'=>$h['closes']
    ], $business['hours']),
    'makesOffer' => [
      '@type'=>'OfferCatalog', 'name'=>'Serviços para Pets',
      'itemListElement'=>[
        ['@type'=>'Offer','itemOffered'=>['@type'=>'Service','serviceType'=>'Adestramento']],
        ['@type'=>'Offer','itemOffered'=>['@type'=>'Service','serviceType'=>'Passeio (Dog Walking)']],
        ['@type'=>'Offer','itemOffered'=>['@type'=>'Service','serviceType'=>'Banho e Tosa (Pet Grooming)']],
      ]
    ],
    'aggregateRating'=>['@type'=>'AggregateRating','ratingValue'=>4.9,'reviewCount'=>320]
  ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) ?>
  </script>

  <!-- JSON-LD: FAQ -->
  <script type="application/ld+json">
  <?= json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'FAQPage',
    'mainEntity'=>array_map(fn($f)=>[
      '@type'=>'Question','name'=>$f['q'],
      'acceptedAnswer'=>['@type'=>'Answer','text'=>$f['a']]
    ], $faq)
  ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) ?>
  </script>
</body>
</html>