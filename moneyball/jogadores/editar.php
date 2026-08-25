<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$id = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("SELECT * FROM Jogador WHERE ID = ?");
$sql->execute([$id]);
$jogador = $sql->fetch();
if (!$jogador) die("Jogador não encontrado.");

$equipes = $pdo->query("SELECT ID, Nome FROM Equipe WHERE LOWER(Nome) <> 'sem equipe' ORDER BY Nome")->fetchAll();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $nome = trim($_POST["nome"]);
 $numero = !empty($_POST["numero"]) ? $_POST["numero"]: null;
 $posicao = $_POST["posicao"];
 $altura = !empty($_POST["altura"]) ? $_POST["altura"]: null;
 $peso = !empty($_POST["peso"]) ? $_POST["peso"]: null;
 $dataNascimento = !empty($_POST["dataNascimento"]) ? $_POST["dataNascimento"]: null;
 $idEquipe = !empty($_POST["idEquipe"]) ? (int)$_POST["idEquipe"]: obterOuCriarEquipeSemEquipe($pdo);

 if (jogadorJaExiste($pdo, $nome, $idEquipe, $id)) {
 $message = "Já existe outro jogador com este nome cadastrado nesta equipe.";
 } else {
 $upd = $pdo->prepare("
 UPDATE Jogador SET Nome=?, Numero=?, Posicao=?, Altura=?, Peso=?, DataNascimento=?, idEquipe=?
 WHERE ID=?
 ");
 $upd->execute([$nome, $numero, $posicao, $altura, $peso, $dataNascimento, $idEquipe, $id]);
 header("Location: listar.php");
 exit;
 }
}

$pageTitle = "Editar Jogador";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Editar Jogador</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-xl"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-xl">
<form method="POST" class="space-y-4">
 <div>
 <label class="block font-semibold mb-1">Nome</label>
 <input type="text" name="nome" required value="<?= htmlspecialchars($jogador['Nome']) ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Número</label>
 <input type="number" name="numero" value="<?= htmlspecialchars($jogador['Numero'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Posição</label>
 <select name="posicao" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach (['Armador','Ala-Armador','Ala','Ala-Pivô','Pivô'] as $p): ?>
 <option value="<?= $p ?>" <?= $jogador['Posicao'] === $p ? 'selected': '' ?>><?= $p ?></option>
 <?php endforeach; ?>
</select>
</div>
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Altura (m)</label>
 <input type="number" step="0.01" name="altura" value="<?= htmlspecialchars($jogador['Altura'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Peso (kg)</label>
 <input type="number" step="0.01" name="peso" value="<?= htmlspecialchars($jogador['Peso'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
</div>
 <div>
 <label class="block font-semibold mb-1">Data de Nascimento</label>
 <input type="date" name="dataNascimento" value="<?= htmlspecialchars($jogador['DataNascimento'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Equipe</label>
 <select name="idEquipe" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <option value="">Sem equipe</option>
 <?php foreach ($equipes as $e): ?>
 <option value="<?= $e['ID'] ?>" <?= $jogador['idEquipe'] == $e['ID'] ? 'selected': '' ?>><?= htmlspecialchars($e['Nome']) ?></option>
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
