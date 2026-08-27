<?php
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$partidas = $pdo->query("
 SELECT p.*, ec.Nome AS Casa, ev.Nome AS Visitante
 FROM Partida p
 JOIN Equipe ec ON ec.ID = p.idEquipeCasa
 JOIN Equipe ev ON ev.ID = p.idEquipeVisitante
 ORDER BY p.DataHora DESC
")->fetchAll();

$pageTitle = "Partidas";
require __DIR__ . '/../includes/header.php';
?>
<div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold">Partidas</h1>
 <?php if (podeEditarDados()): ?>
 <div class="flex gap-2">
 <a href="importar.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">Importar Excel</a>
 <a href="cadastrar.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">+ Nova Partida</a>
 </div>
 <?php endif; ?>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-100 dark:bg-gray-700 text-left">
<tr><th class="p-3">Data</th><th class="p-3">Confronto</th><th class="p-3">Placar</th><th class="p-3">Status</th><th class="p-3">Ações</th></tr>
</thead>
<tbody>
<?php foreach ($partidas as $p): ?>
<tr class="border-t dark:border-gray-700">
 <td class="p-3"><?= htmlspecialchars($p['DataHora']) ?></td>
 <td class="p-3"><?= htmlspecialchars($p['Casa']) ?> x <?= htmlspecialchars($p['Visitante']) ?></td>
 <td class="p-3 font-semibold"><?= $p['PlacarCasa'] ?> - <?= $p['PlacarVisitante'] ?></td>
 <td class="p-3">
 <span class="px-2 py-1 rounded text-xs
 <?= $p['Status'] === 'Finalizada' ? 'bg-green-100 text-green-700': ($p['Status'] === 'Em andamento' ? 'bg-orange-100 text-orange-700': 'bg-gray-100 text-gray-700') ?>">
 <?= htmlspecialchars($p['Status']) ?>
</span>
</td>
 <td class="p-3 space-x-2">
 <a href="../scouting/registrar.php?id=<?= $p['ID'] ?>" class="text-orange-600 hover:underline">Scouting</a>
 <?php if (podeEditarDados()): ?>
 <a href="editar.php?id=<?= $p['ID'] ?>" class="text-blue-600 hover:underline">Editar</a>
 <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$partidas): ?><tr><td colspan="5" class="p-4 text-center text-gray-400">Nenhuma partida cadastrada.</td></tr><?php endif; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
