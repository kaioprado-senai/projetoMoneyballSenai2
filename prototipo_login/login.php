<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    if (!empty($email) && !empty($senha)) {

        $sql = $pdo->prepare("
            SELECT *
            FROM Usuario
            WHERE Email = ?
            LIMIT 1
        ");

        $sql->execute([$email]);

        $usuario = $sql->fetch();

        if ($usuario) {

            if (password_verify($senha, $usuario["Senha"])) {

                $_SESSION["usuario_id"] = $usuario["ID"];
                $_SESSION["usuario_nome"] = $usuario["Nome"];
                $_SESSION["usuario_email"] = $usuario["Email"];
                $_SESSION["usuario_perfil"] = $usuario["Perfil"];

                // Atualiza o último acesso
                $update = $pdo->prepare("
                    UPDATE Usuario
                    SET UltimoAcesso = NOW()
                    WHERE ID = ?
                ");

                $update->execute([$usuario["ID"]]);

                header("Location: dashboard.php");
                exit;

            } else {

                $message = "Senha incorreta.";

            }

        } else {

            $message = "Usuário não encontrado.";

        }

    } else {

        $message = "Preencha todos os campos.";

    }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - Moneyball SENAI</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">

<div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">

<h1 class="text-3xl font-bold text-center text-blue-700">

Moneyball SENAI

</h1>

<p class="text-center text-gray-500 mb-8">

Acesso ao Sistema

</p>

<?php if($message != ""){ ?>

<div class="mb-4 bg-red-100 text-red-700 p-3 rounded">

<?php echo $message; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-4">

<label class="block mb-1 font-semibold">

E-mail

</label>

<input
type="email"
name="email"
required
class="w-full border rounded p-2">

</div>

<div class="mb-6">

<label class="block mb-1 font-semibold">

Senha

</label>

<input
type="password"
name="senha"
required
class="w-full border rounded p-2">

</div>

<button
type="submit"
class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">

Entrar

</button>

</form>

<div class="mt-6 text-center">

<a
href="register_user.php"
class="text-green-600 hover:underline">

Cadastrar novo usuário

</a>

<br><br>

<a
href="index.php"
class="text-blue-600 hover:underline">

Voltar ao início

</a>

</div>

</div>

</body>

</html>