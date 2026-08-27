<?php
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$equipes = $pdo->query("
    SELECT eq.*, COUNT(j.ID) AS TotalJogadores
    FROM Equipe eq
    LEFT JOIN Jogador j ON j.idEquipe = eq.ID
    GROUP BY eq.ID
    ORDER BY eq.Nome
")->fetchAll();

$pageTitle = "Equipes";
require __DIR__ . '/../includes/header.php';
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Equipes</h1>
    <?php if (podeEditarDados()): ?>
    <div class="flex gap-2">
        <a href="importar.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">Importar Excel</a>
        <a href="cadastrar.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">+ Nova Equipe</a>
    </div>
    <?php endif; ?>
</div>

<div class="grid md:grid-cols-3 gap-4">
<?php foreach ($equipes as $e): ?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
    <h2 class="font-bold text-lg"><?= htmlspecialchars($e['Nome']) ?></h2>
    <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($e['Cidade'] ?? '-') ?></p>
    <p class="text-sm mt-2">Técnico: <?= htmlspecialchars($e['Tecnico'] ?? '-') ?></p>
    <p class="text-sm">Jogadores: <?= $e['TotalJogadores'] ?></p>
    <?php if (podeEditarDados()): ?>
    <a href="editar.php?id=<?= $e['ID'] ?>" class="inline-block mt-3 text-blue-600 hover:underline text-sm">Editar</a>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (!$equipes): ?><p class="text-gray-400">Nenhuma equipe cadastrada.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
