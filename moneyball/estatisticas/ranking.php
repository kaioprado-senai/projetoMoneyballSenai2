<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirLogin();

$rankingJog = rankingJogadores($pdo);
$top5 = array_slice($rankingJog, 0, 5);
$rankingEq = rankingEquipes($pdo);
$influente = estatisticaMaisInfluente($pdo);

$piorJogador = $rankingJog ? end($rankingJog): null;

$pageTitle = "Rankings";
require __DIR__ . '/../includes/header.php';
?>
<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
 <h1 class="text-2xl font-bold">Rankings e Indicadores</h1>
 <a href="importar.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm">Importar Estatísticas</a>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-8">
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold text-lg mb-1">Top 5 Jogadores</h2>
 <p class="text-xs text-gray-400 mb-4">Classificação pela média de Eficiência (PTS + REB + AST + ROU + TOC − erros de arremesso − LL perdidos − turnovers).
</p>
 <table class="w-full text-sm">
 <thead><tr class="text-left text-gray-500"><th>#</th><th>Jogador</th><th>Equipe</th><th>EFF</th></tr></thead>
 <tbody>
 <?php foreach ($top5 as $i => $j): ?>
 <tr class="border-t dark:border-gray-700">
 <td class="py-2"><?= $i + 1 ?></td>
 <td><a href="../jogadores/visualizar.php?id=<?= $j['ID'] ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($j['Nome']) ?></a></td>
 <td><?= htmlspecialchars($j['Equipe'] ?? '-') ?></td>
 <td class="font-bold text-orange-600"><?= $j['MediaEficiencia'] ?></td>
</tr>
 <?php endforeach; ?>
 <?php if (!$top5): ?><tr><td colspan="4" class="text-gray-400 py-3">Sem dados suficientes.</td></tr><?php endif; ?>
</tbody>
</table>
</div>

 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold text-lg mb-4">Ranking de Equipes</h2>
 <table class="w-full text-sm">
 <thead><tr class="text-left text-gray-500"><th>#</th><th>Equipe</th><th>EFF Média</th><th>PTS Média</th></tr></thead>
 <tbody>
 <?php foreach ($rankingEq as $i => $e): ?>
 <tr class="border-t dark:border-gray-700">
 <td class="py-2"><?= $i + 1 ?></td>
 <td><?= htmlspecialchars($e['Nome']) ?></td>
 <td class="font-bold"><?= $e['MediaEficiencia'] ?></td>
 <td><?= $e['MediaPontos'] ?></td>
</tr>
 <?php endforeach; ?>
 <?php if (!$rankingEq): ?><tr><td colspan="4" class="text-gray-400 py-3">Sem dados suficientes.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</div>

<div class="grid md:grid-cols-3 gap-6">
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold mb-2">Melhor Desempenho Geral</h2>
 <?php if ($top5): ?>
 <p class="text-xl font-bold text-green-600"><?= htmlspecialchars($top5[0]['Nome']) ?></p>
 <p class="text-sm text-gray-500">Eficiência média: <?= $top5[0]['MediaEficiencia'] ?></p>
 <?php else: ?><p class="text-gray-400">Sem dados.</p><?php endif; ?>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold mb-2">Pior Desempenho Geral</h2>
 <?php if ($piorJogador): ?>
 <p class="text-xl font-bold text-red-600"><?= htmlspecialchars($piorJogador['Nome']) ?></p>
 <p class="text-sm text-gray-500">Eficiência média: <?= $piorJogador['MediaEficiencia'] ?></p>
 <?php else: ?><p class="text-gray-400">Sem dados.</p><?php endif; ?>
</div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold mb-2">Estatística Mais Influente</h2>
 <?php if ($influente['campo']): ?>
 <p class="text-xl font-bold text-blue-600"><?= htmlspecialchars($influente['campo']) ?></p>
 <p class="text-sm text-gray-500">Correlação com a eficiência geral: <?= $influente['correlacao'] ?>
 (quanto mais próxima de 1, maior a influência dessa estatística na classificação).
</p>
 <?php else: ?><p class="text-gray-400">Dados insuficientes para calcular.</p><?php endif; ?>
</div>
</div>

<h2 class="text-xl font-bold mt-10 mb-4">Regularidade dos Jogadores</h2>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-100 dark:bg-gray-700 text-left"><tr><th class="p-3">Jogador</th><th class="p-3">Partidas</th><th class="p-3">Regularidade</th></tr></thead>
<tbody>
<?php foreach ($rankingJog as $j): ?>
<tr class="border-t dark:border-gray-700">
 <td class="p-3"><?= htmlspecialchars($j['Nome']) ?></td>
 <td class="p-3"><?= $j['PartidasJogadas'] ?></td>
 <td class="p-3 font-semibold"><?= $j['MediaRegularidade'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
