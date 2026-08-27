<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador']);

$id = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("SELECT * FROM Usuario WHERE ID = ?");
$sql->execute([$id]);
$usuario = $sql->fetch();

if (!$usuario) {
 die("Usuário não encontrado.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $nome = trim($_POST["nome"]);
 $email = trim($_POST["email"]);
 $perfil = $_POST["perfil"];
 $status = isset($_POST["status"]) ? 1: 0;
 $novaSenha = $_POST["senha"];

 if ($novaSenha !== "") {
 $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
 $sql = $pdo->prepare("UPDATE Usuario SET Nome=?, Email=?, Perfil=?, Status=?, Senha=? WHERE ID=?");
 $sql->execute([$nome, $email, $perfil, $status, $hash, $id]);
 } else {
 $sql = $pdo->prepare("UPDATE Usuario SET Nome=?, Email=?, Perfil=?, Status=? WHERE ID=?");
 $sql->execute([$nome, $email, $perfil, $status, $id]);
 }

 header("Location: listar.php");
 exit;
}

$pageTitle = "Editar Usuário";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Editar Usuário</h1>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-lg">
<form method="POST" class="space-y-4">
 <div>
 <label class="block mb-1 font-semibold">Nome</label>
 <input type="text" name="nome" required value="<?= htmlspecialchars($usuario['Nome']) ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">E-mail</label>
 <input type="email" name="email" required value="<?= htmlspecialchars($usuario['Email']) ?>" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Nova senha (deixe em branco para manter)</label>
 <input type="password" name="senha" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Perfil</label>
 <select name="perfil" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <?php foreach (['Usuario' => 'Usuário', 'Comissao' => 'Comissão Técnica', 'Administrador' => 'Administrador'] as $val => $label): ?>
 <option value="<?= $val ?>" <?= $usuario['Perfil'] === $val ? 'selected': '' ?>><?= $label ?></option>
 <?php endforeach; ?>
</select>
</div>
 <div class="flex items-center gap-2">
 <input type="checkbox" name="status" id="status" <?= $usuario['Status'] ? 'checked': '' ?>>
 <label for="status">Usuário ativo</label>
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Salvar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
