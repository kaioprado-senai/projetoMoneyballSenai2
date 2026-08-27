<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $nome = trim($_POST['nome']);

 if (equipeJaExiste($pdo, $nome)) {
 $message = "Já existe uma equipe cadastrada com este nome.";
 } else {
 $sql = $pdo->prepare("INSERT INTO Equipe (Nome, Cidade, Tecnico) VALUES (?, ?, ?)");
 $sql->execute([$nome, trim($_POST['cidade']), trim($_POST['tecnico'])]);
 header("Location: listar.php");
 exit;
 }
}

$pageTitle = "Cadastrar Equipe";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Cadastrar Equipe</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-lg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-lg">
<form method="POST" class="space-y-4">
 <div>
 <label class="block mb-1 font-semibold">Nome</label>
 <input type="text" name="nome" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Cidade</label>
 <input type="text" name="cidade" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Técnico</label>
 <input type="text" name="tecnico" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Cadastrar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
