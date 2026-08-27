<?php
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$busca = trim($_GET['busca'] ?? '');
$posicao = trim($_GET['posicao'] ?? '');
$idEquipe = trim($_GET['idEquipe'] ?? '');

$where = [];
$params = [];

if ($busca !== '') {
 $where[] = "(j.Nome LIKE ? OR j.Numero = ?)";
 $params[] = "%$busca%";
 $params[] = is_numeric($busca) ? (int)$busca: -1;
}
if ($posicao !== '') {
 $where[] = "j.Posicao = ?";
 $params[] = $posicao;
}
if ($idEquipe !== '') {
 $where[] = "j.idEquipe = ?";
 $params[] = $idEquipe;
}

$sqlWhere = $where ? "WHERE ". implode(" AND ", $where): "";

$sql = $pdo->prepare("
 SELECT j.*, eq.Nome AS EquipeNome
 FROM Jogador j
 LEFT JOIN Equipe eq ON eq.ID = j.idEquipe
 $sqlWhere
 ORDER BY j.Nome
");
$sql->execute($params);
$jogadores = $sql->fetchAll();

$equipes = $pdo->query("SELECT ID, Nome FROM Equipe ORDER BY Nome")->fetchAll();

$pageTitle = "Jogadores";
require __DIR__ . '/../includes/header.php';
?>
<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
 <h1 class="text-2xl font-bold">Jogadores</h1>
 <?php if (podeEditarDados()): ?>
 <div class="flex gap-2">
 <a href="importar.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">Importar Excel</a>
 <a href="cadastrar.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">+ Novo Jogador</a>
</div>
 <?php endif; ?>
</div>

<form method="GET" class="grid md:grid-cols-4 gap-3 mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow">
 <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome ou número..." class="border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <select name="posicao" class="border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <option value="">Todas as posições</option>
 <?php foreach (['Armador','Ala-Armador','Ala','Ala-Pivô','Pivô'] as $p): ?>
 <option value="<?= $p ?>" <?= $posicao === $p ? 'selected': '' ?>><?= $p ?></option>
 <?php endforeach; ?>
</select>
 <select name="idEquipe" class="border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <option value="">Todas as equipes</option>
 <?php foreach ($equipes as $e): ?>
 <option value="<?= $e['ID'] ?>" <?= $idEquipe == $e['ID'] ? 'selected': '' ?>><?= htmlspecialchars($e['Nome']) ?></option>
 <?php endforeach; ?>
</select>
 <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded px-4 py-2">Filtrar</button>
</form>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-100 dark:bg-gray-700 text-left">
<tr><th class="p-3">#</th><th class="p-3">Nome</th><th class="p-3">Posição</th><th class="p-3">Equipe</th><th class="p-3">Altura</th><th class="p-3">Ações</th></tr>
</thead>
<tbody>
<?php foreach ($jogadores as $j): ?>
<tr class="border-t dark:border-gray-700">
 <td class="p-3"><?= htmlspecialchars($j['Numero'] ?? '-') ?></td>
 <td class="p-3"><?= htmlspecialchars($j['Nome']) ?></td>
 <td class="p-3"><?= htmlspecialchars($j['Posicao'] ?? '-') ?></td>
 <td class="p-3"><?= htmlspecialchars($j['EquipeNome'] ?? 'Sem equipe') ?></td>
 <td class="p-3"><?= $j['Altura'] ? $j['Altura']. ' m': '-' ?></td>
 <td class="p-3 space-x-2">
 <a href="visualizar.php?id=<?= $j['ID'] ?>" class="text-green-600 hover:underline">Ver</a>
 <?php if (podeEditarDados()): ?>
 <a href="editar.php?id=<?= $j['ID'] ?>" class="text-blue-600 hover:underline">Editar</a>
 <a href="excluir.php?id=<?= $j['ID'] ?>" onclick="return confirm('Excluir este jogador?')" class="text-red-600 hover:underline">Excluir</a>
 <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$jogadores): ?><tr><td colspan="6" class="p-4 text-center text-gray-400">Nenhum jogador encontrado.</td></tr><?php endif; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
