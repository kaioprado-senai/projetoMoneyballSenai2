<?php
// Espera que $pageTitle esteja definida antes do include.
$raiz = caminhoRaiz();
$pageTitle = $pageTitle ?? 'Moneyball SENAI';
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ dark: localStorage.getItem('mb_dark') === '1' }" x-init="$watch('dark', v => localStorage.setItem('mb_dark', v ? '1' : '0'))" :class="{ 'dark': dark }">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> - Moneyball SENAI</title>
<link rel="icon" type="image/svg+xml" href="<?= $raiz ?>assets/img/favicon.svg">
<link rel="alternate icon" type="image/x-icon" href="<?= $raiz ?>assets/img/favicon.ico">
<link rel="apple-touch-icon" href="<?= $raiz ?>assets/img/apple-touch-icon.png">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config = { darkMode: 'class' }</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 dark:text-gray-100 min-h-screen">
<div class="flex min-h-screen">

 <?php if (usuarioLogado()): ?>
 <!-- Sidebar -->
 <aside class="w-64 bg-blue-900 text-white flex-shrink-0 hidden md:flex flex-col">
 <div class="p-5 border-b border-blue-800">
 <h1 class="text-xl font-bold">Moneyball</h1>
 <p class="text-xs text-blue-300">Scouting de Basquete</p>
</div>
 <nav class="flex-1 p-3 space-y-1 text-sm">
 <a href="<?= $raiz ?>dashboard.php" class="block px-3 py-2 rounded hover:bg-blue-800">Dashboard</a>
 <a href="<?= $raiz ?>equipes/listar.php" class="block px-3 py-2 rounded hover:bg-blue-800">Equipes</a>
 <a href="<?= $raiz ?>jogadores/listar.php" class="block px-3 py-2 rounded hover:bg-blue-800">Jogadores</a>
 <a href="<?= $raiz ?>partidas/listar.php" class="block px-3 py-2 rounded hover:bg-blue-800">Partidas</a>
 <a href="<?= $raiz ?>estatisticas/ranking.php" class="block px-3 py-2 rounded hover:bg-blue-800">Rankings</a>
 <a href="<?= $raiz ?>estatisticas/comparar.php" class="block px-3 py-2 rounded hover:bg-blue-800">Comparar Jogadores</a>
 <a href="<?= $raiz ?>estatisticas/importar.php" class="block px-3 py-2 rounded hover:bg-blue-800">Importar Estatísticas</a>
 <?php if (podeGerenciarUsuarios()): ?>
 <a href="<?= $raiz ?>usuarios/listar.php" class="block px-3 py-2 rounded hover:bg-blue-800">Usuários</a>
 <?php endif; ?>
</nav>
 <div class="p-3 border-t border-blue-800 text-sm">
 <div class="mb-2"><?= htmlspecialchars($_SESSION['usuario_nome']) ?> <span class="text-blue-300">(<?= htmlspecialchars($_SESSION['usuario_perfil']) ?>)</span></div>
 <button @click="dark = !dark" class="w-full text-left px-3 py-1.5 rounded bg-blue-800 hover:bg-blue-700 mb-2">Alternar tema</button>
 <a href="<?= $raiz ?>logout.php" class="block px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-center">Sair</a>
</div>
</aside>
 <?php endif; ?>

 <main class="flex-1 p-4 md:p-8">
