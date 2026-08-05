<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-5xl mx-auto mt-10">

<div class="bg-white rounded-xl shadow-lg p-8">

<h1 class="text-3xl font-bold text-blue-700">

Bem-vindo ao Moneyball SENAI

</h1>

<p class="mt-4">

<strong>Usuário:</strong>

<?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?>

</p>

<p>

<strong>Email:</strong>

<?php echo htmlspecialchars($_SESSION["usuario_email"]); ?>

</p>

<p>

<strong>Perfil:</strong>

<?php echo htmlspecialchars($_SESSION["usuario_perfil"]); ?>

</p>

<div class="mt-8">

<a
href="logout.php"
class="bg-red-600 text-white px-5 py-2 rounded">

Sair

</a>

</div>

</div>

</div>

</body>

</html>