<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$id = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("SELECT * FROM Partida WHERE ID = ?");
$sql->execute([$id]);
$partida = $sql->fetch();
if (!$partida) die("Partida não encontrada.");

$equipes = $pdo->query("SELECT ID, Nome FROM Equipe ORDER BY Nome")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $upd = $pdo->prepare("
 UPDATE Partida SET DataHora=?, Local=?, Status=?, PlacarCasa=?, PlacarVisitante=?,
 idEquipeCasa=?, idEquipeVisitante=?
 WHERE ID=?
 ");
 $upd->execute([
 $_POST['dataHora'], trim($_POST['local']), $_POST['status'],
 $_POST['placarCasa'], $_POST['placarVisitante'],
 $_POST['idEquipeCasa'], $_POST['idEquipeVisitante'], $id
 ]);
 header("Location: listar.php");
 exit;
}

$pageTitle = "Editar Partida";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Editar Partida</h1>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-xl">
<form method="POST" class="space-y-4">
 <div>
 <label class="block font-semibold mb-1">Data e Hora</label>
 <input type="datetime-local" name="dataHora" required value="<?= date('Y-m-d\TH:i', strtotime($partida['DataHora'])) ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Local</label>
 <input type="text" name="local" value="<?= htmlspecialchars($partida['Local'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Equipe da Casa</label>
 <select name="idEquipeCasa" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach ($equipes as $e): ?><option value="<?= $e['ID'] ?>" <?= $partida['idEquipeCasa'] == $e['ID'] ? 'selected': '' ?>><?= htmlspecialchars($e['Nome']) ?></option><?php endforeach; ?>
</select>
</div>
 <div>
 <label class="block font-semibold mb-1">Equipe Visitante</label>
 <select name="idEquipeVisitante" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach ($equipes as $e): ?><option value="<?= $e['ID'] ?>" <?= $partida['idEquipeVisitante'] == $e['ID'] ? 'selected': '' ?>><?= htmlspecialchars($e['Nome']) ?></option><?php endforeach; ?>
</select>
</div>
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Placar Casa</label>
 <input type="number" name="placarCasa" value="<?= $partida['PlacarCasa'] ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Placar Visitante</label>
 <input type="number" name="placarVisitante" value="<?= $partida['PlacarVisitante'] ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
</div>
 <div>
 <label class="block font-semibold mb-1">Status</label>
 <select name="status" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach (['Agendada','Em andamento','Finalizada'] as $s): ?>
 <option value="<?= $s ?>" <?= $partida['Status'] === $s ? 'selected': '' ?>><?= $s ?></option>
 <?php endforeach; ?>
</select>
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Salvar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
