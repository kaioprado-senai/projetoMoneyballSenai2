<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$message = "";
$equipes = $pdo->query("SELECT ID, Nome FROM Equipe WHERE LOWER(Nome) <> 'sem equipe' ORDER BY Nome")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $nome = trim($_POST["nome"]);
 $numero = !empty($_POST["numero"]) ? $_POST["numero"]: null;
 $posicao = $_POST["posicao"];
 $altura = !empty($_POST["altura"]) ? $_POST["altura"]: null;
 $peso = !empty($_POST["peso"]) ? $_POST["peso"]: null;
 $dataNascimento = !empty($_POST["dataNascimento"]) ? $_POST["dataNascimento"]: null;
 $idEquipe = !empty($_POST["idEquipe"]) ? (int)$_POST["idEquipe"]: obterOuCriarEquipeSemEquipe($pdo);

 if ($nome === "") {
 $message = "Informe o nome do jogador.";
 } elseif (jogadorJaExiste($pdo, $nome, $idEquipe)) {
 $message = "Já existe um jogador com este nome cadastrado nesta equipe.";
 } else {
 $sql = $pdo->prepare("
 INSERT INTO Jogador (Nome, Numero, Posicao, Altura, Peso, DataNascimento, idEquipe)
 VALUES (?, ?, ?, ?, ?, ?, ?)
 ");
 $sql->execute([$nome, $numero, $posicao, $altura, $peso, $dataNascimento, $idEquipe]);
 header("Location: listar.php");
 exit;
 }
}

$pageTitle = "Cadastrar Jogador";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Cadastrar Jogador</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-xl"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-xl">
<form method="POST" class="space-y-4">
 <div>
 <label class="block font-semibold mb-1">Nome</label>
 <input type="text" name="nome" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Número</label>
 <input type="number" name="numero" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Posição</label>
 <select name="posicao" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach (['Armador','Ala-Armador','Ala','Ala-Pivô','Pivô'] as $p): ?>
 <option value="<?= $p ?>"><?= $p ?></option>
 <?php endforeach; ?>
</select>
</div>
</div>
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold mb-1">Altura (m)</label>
 <input type="number" step="0.01" name="altura" placeholder="1.85" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Peso (kg)</label>
 <input type="number" step="0.01" name="peso" placeholder="82.5" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
</div>
 <div>
 <label class="block font-semibold mb-1">Data de Nascimento</label>
 <input type="date" name="dataNascimento" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block font-semibold mb-1">Equipe</label>
 <select name="idEquipe" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <option value="">Sem equipe</option>
 <?php foreach ($equipes as $e): ?>
 <option value="<?= $e['ID'] ?>"><?= htmlspecialchars($e['Nome']) ?></option>
 <?php endforeach; ?>
</select>
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Cadastrar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
