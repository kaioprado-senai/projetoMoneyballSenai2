<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador']);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
 $nome = trim($_POST["nome"]);
 $email = trim($_POST["email"]);
 $senha = $_POST["senha"];
 $perfil = $_POST["perfil"];

 if ($nome !== "" && $email !== "" && $senha !== "") {
 $verifica = $pdo->prepare("SELECT ID FROM Usuario WHERE Email = ?");
 $verifica->execute([$email]);

 if ($verifica->rowCount() > 0) {
 $message = "Este e-mail já está cadastrado.";
 } else {
 $hash = password_hash($senha, PASSWORD_DEFAULT);
 $sql = $pdo->prepare("INSERT INTO Usuario (Nome, Email, Senha, Perfil) VALUES (?, ?, ?, ?)");
 $sql->execute([$nome, $email, $hash, $perfil]);
 header("Location: listar.php");
 exit;
 }
 } else {
 $message = "Preencha todos os campos.";
 }
}

$pageTitle = "Cadastrar Usuário";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Cadastrar Usuário</h1>

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
 <label class="block mb-1 font-semibold">E-mail</label>
 <input type="email" name="email" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Senha</label>
 <input type="password" name="senha" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
</div>
 <div>
 <label class="block mb-1 font-semibold">Perfil</label>
 <select name="perfil" class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <option value="Usuario">Usuário</option>
 <option value="Comissao">Comissão Técnica</option>
 <option value="Administrador">Administrador</option>
</select>
</div>
 <div class="flex gap-3 pt-2">
 <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Cadastrar</button>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
</div>
</form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
