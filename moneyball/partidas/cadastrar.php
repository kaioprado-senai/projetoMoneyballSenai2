<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$message = "";
$equipes = $pdo->query("SELECT ID, Nome FROM Equipe ORDER BY Nome")->fetchAll();
$campeonatos = $pdo->query("SELECT ID, Nome, Temporada FROM Campeonato ORDER BY Ano DESC")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $dataHora = $_POST['dataHora'];
 $local = trim($_POST['local']);
 $idCasa = $_POST['idEquipeCasa'];
 $idVisitante = $_POST['idEquipeVisitante'];
 $idCampeonato = !empty($_POST['idCampeonato']) ? $_POST['idCampeonato']: null;

 if ($idCasa === $idVisitante) {
 $message = "A equipe da casa e a visitante devem ser diferentes.";
 } else {
 $sql = $pdo->prepare("
 INSERT INTO Partida (DataHora, Local, idCampeonato, idEquipeCasa, idEquipeVisitante)
 VALUES (?, ?, ?, ?, ?)
 ");
 $sql->execute([$dataHora, $local, $idCampeonato, $idCasa, $idVisitante]);
 header("Location: listar.php");
 exit;
 }
}

$pageTitle = "Cadastrar Partida";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Cadastrar Partida</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-xl"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-xl">
<form method="POST" class="space-y-4">
 <div>
 <label class="block font-semibold mb-1">Data e Hora</label>
 <input type="datetime-local" name="dataHora" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Local</label>
 <input type="text" name="local" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Equipe da Casa</label>
 <select name="idEquipeCasa" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach ($equipes as $e): ?><option value="<?= $e['ID'] ?>"><?= htmlspecialchars($e['Nome']) ?></option><?php endforeach; ?>
</select>
</div>
 <div>
 <label class="block font-semibold mb-1">Equipe Visitante</label>
 <select name="idEquipeVisitante" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach ($equipes as $e): ?><option value="<?= $e['ID'] ?>"><?= htmlspecialchars($e['Nome']) ?></option><?php endforeach; ?>
</select>
</div>
</div>
 <div>
 <label class="block font-semibold mb-1">Campeonato / Temporada</label>
 <select name="idCampeonato" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <option value="">Nenhum</option>
 <?php foreach ($campeonatos as $c): ?>
 <option value="<?= $c['ID'] ?>"><?= htmlspecialchars($c['Nome']) ?> (<?= htmlspecialchars($c['Temporada']) ?>)</option>
 <?php endforeach; ?>
</select>
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Cadastrar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
