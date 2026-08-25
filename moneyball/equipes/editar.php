<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$id = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("SELECT * FROM Equipe WHERE ID = ?");
$sql->execute([$id]);
$equipe = $sql->fetch();
if (!$equipe) die("Equipe não encontrada.");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $nome = trim($_POST['nome']);

 if (equipeJaExiste($pdo, $nome, $id)) {
 $message = "Já existe outra equipe cadastrada com este nome.";
 } else {
 $upd = $pdo->prepare("UPDATE Equipe SET Nome=?, Cidade=?, Tecnico=? WHERE ID=?");
 $upd->execute([$nome, trim($_POST['cidade']), trim($_POST['tecnico']), $id]);
 header("Location: listar.php");
 exit;
 }
}

$pageTitle = "Editar Equipe";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Editar Equipe</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-lg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-lg">
<form method="POST" class="space-y-4">
 <div>
 <label class="block mb-1 font-semibold">Nome</label>
 <input type="text" name="nome" required value="<?= htmlspecialchars($equipe['Nome']) ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Cidade</label>
 <input type="text" name="cidade" value="<?= htmlspecialchars($equipe['Cidade'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Técnico</label>
 <input type="text" name="tecnico" value="<?= htmlspecialchars($equipe['Tecnico'] ?? '') ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Salvar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
