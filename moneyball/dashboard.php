<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
exigirLogin();

$totalJogadores = $pdo->query("SELECT COUNT(*) FROM Jogador")->fetchColumn();
$totalPartidas = $pdo->query("SELECT COUNT(*) FROM Partida")->fetchColumn();
$totalEquipes = $pdo->query("SELECT COUNT(*) FROM Equipe")->fetchColumn();

$medias = $pdo->query("
 SELECT
 ROUND(AVG(Pontos),1) AS MediaPontos,
 ROUND(AVG(Assistencias),1) AS MediaAssist,
 ROUND(AVG(Rebotes),1) AS MediaRebotes,
 ROUND(AVG(Eficiencia),1) AS MediaEficiencia
 FROM Estatisticas
")->fetch();

$top3 = rankingJogadores($pdo, 3);
$melhorEquipeArr = rankingEquipes($pdo);
$melhorEquipe = $melhorEquipeArr[0] ?? null;

$ultimasPartidas = $pdo->query("
 SELECT p.*, ec.Nome AS Casa, ev.Nome AS Visitante
 FROM Partida p
 JOIN Equipe ec ON ec.ID = p.idEquipeCasa
 JOIN Equipe ev ON ev.ID = p.idEquipeVisitante
 ORDER BY p.DataHora DESC
 LIMIT 5
")->fetchAll();

$pageTitle = "Dashboard";
require __DIR__ . '/includes/header.php';
?>

<h1 class="text-2xl md:text-3xl font-bold mb-6">Dashboard Gerencial</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Jogadores</p>
 <p class="text-3xl font-bold text-blue-700 dark:text-blue-400"><?= $totalJogadores ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Equipes</p>
 <p class="text-3xl font-bold text-blue-700 dark:text-blue-400"><?= $totalEquipes ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Partidas</p>
 <p class="text-3xl font-bold text-blue-700 dark:text-blue-400"><?= $totalPartidas ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Eficiência Média</p>
 <p class="text-3xl font-bold text-orange-600"><?= $medias['MediaEficiencia'] ?? '-' ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Média de Pontos</p>
 <p class="text-2xl font-bold"><?= $medias['MediaPontos'] ?? '-' ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Média de Assistências</p>
 <p class="text-2xl font-bold"><?= $medias['MediaAssist'] ?? '-' ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Média de Rebotes</p>
 <p class="text-2xl font-bold"><?= $medias['MediaRebotes'] ?? '-' ?></p>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
 <p class="text-sm text-gray-500 dark:text-gray-400">Melhor Equipe</p>
 <p class="text-lg font-bold text-green-700"><?= $melhorEquipe ? htmlspecialchars($melhorEquipe['Nome']): '-' ?></p>
</div>
</div>

<div class="grid md:grid-cols-2 gap-6">
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold text-lg mb-4">Top 3 Jogadores (Eficiência)</h2>
 <ol class="space-y-2">
 <?php foreach ($top3 as $i => $j): ?>
 <li class="flex justify-between border-b pb-2 dark:border-gray-700">
 <span><?= $i + 1 ?>º — <?= htmlspecialchars($j['Nome']) ?> <span class="text-xs text-gray-400">(<?= htmlspecialchars($j['Equipe'] ?? 'Sem equipe') ?>)</span></span>
 <span class="font-semibold text-orange-600"><?= $j['MediaEficiencia'] ?></span>
</li>
 <?php endforeach; ?>
 <?php if (!$top3): ?><p class="text-gray-400 text-sm">Sem estatísticas registradas ainda.</p><?php endif; ?>
</ol>
 <a href="estatisticas/ranking.php" class="inline-block mt-4 text-blue-600 hover:underline text-sm">Ver ranking completo</a>
</div>

 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold text-lg mb-4">Últimas Partidas</h2>
 <ul class="space-y-2 text-sm">
 <?php foreach ($ultimasPartidas as $p): ?>
 <li class="flex justify-between border-b pb-2 dark:border-gray-700">
 <span><?= htmlspecialchars($p['Casa']) ?> x <?= htmlspecialchars($p['Visitante']) ?></span>
 <span class="font-semibold"><?= $p['PlacarCasa'] ?> - <?= $p['PlacarVisitante'] ?>
 <span class="text-xs text-gray-400">(<?= htmlspecialchars($p['Status']) ?>)</span>
</span>
</li>
 <?php endforeach; ?>
 <?php if (!$ultimasPartidas): ?><p class="text-gray-400 text-sm">Nenhuma partida cadastrada ainda.</p><?php endif; ?>
</ul>
 <a href="partidas/listar.php" class="inline-block mt-4 text-blue-600 hover:underline text-sm">Ver todas as partidas</a>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
