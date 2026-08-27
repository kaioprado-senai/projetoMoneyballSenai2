<?php
/**
 * EXECUTE ESTE ARQUIVO APENAS UMA VEZ, PARA CRIAR O PRIMEIRO ADMINISTRADOR.
 * Depois de criar o admin, DELETE este arquivo do servidor por segurança.
 *
 * Acesse pelo navegador: http://localhost/moneyball/criar_admin.php
 */

require_once __DIR__ . '/config.php';

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $nome = trim($_POST['nome']);
 $email = trim($_POST['email']);
 $senha = $_POST['senha'];

 $verifica = $pdo->prepare("SELECT ID FROM Usuario WHERE Perfil = 'Administrador' LIMIT 1");
 $verifica->execute();

 if ($verifica->fetch()) {
 $mensagem = "Já existe um administrador cadastrado. Por segurança, apague este arquivo.";
 } elseif ($nome && $email && $senha) {
 $hash = password_hash($senha, PASSWORD_DEFAULT);
 $sql = $pdo->prepare("INSERT INTO Usuario (Nome, Email, Senha, Perfil) VALUES (?, ?, ?, 'Administrador')");
 $sql->execute([$nome, $email, $hash]);
 $mensagem = "Administrador criado com sucesso! Você já pode fazer login e, por segurança, apague este arquivo (criar_admin.php).";
 } else {
 $mensagem = "Preencha todos os campos.";
 }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Criar Administrador - Moneyball SENAI</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
<div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
 <h1 class="text-2xl font-bold text-center text-blue-700 mb-2">Criar Administrador</h1>
 <p class="text-center text-gray-500 text-sm mb-6">Execute apenas uma vez, depois apague este arquivo.</p>
 <?php if ($mensagem): ?>
 <div class="mb-4 p-3 rounded bg-blue-100 text-blue-700 text-sm"><?= htmlspecialchars($mensagem) ?></div>
 <?php endif; ?>
 <form method="POST" class="space-y-4">
 <input type="text" name="nome" placeholder="Nome" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <input type="email" name="email" placeholder="E-mail" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <input type="password" name="senha" placeholder="Senha" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
 <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Criar Administrador</button>
</form>
 <a href="login.php" class="block text-center text-blue-600 hover:underline mt-6 text-sm">Ir para o login</a>
</div>
</body>
</html>
