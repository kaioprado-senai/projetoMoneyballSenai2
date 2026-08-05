<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $perfil = $_POST["perfil"];

    if (!empty($nome) && !empty($email) && !empty($_POST["senha"])) {

        try {

            // Verifica se o e-mail já existe
            $verifica = $pdo->prepare("SELECT ID FROM Usuario WHERE Email = ?");
            $verifica->execute([$email]);

            if ($verifica->rowCount() > 0) {

                $message = "Este e-mail já está cadastrado.";

            } else {

                $sql = $pdo->prepare("
                    INSERT INTO Usuario
                    (
                        Nome,
                        Email,
                        Senha,
                        Perfil
                    )
                    VALUES
                    (
                        ?, ?, ?, ?
                    )
                ");

                $sql->execute([
                    $nome,
                    $email,
                    $senha,
                    $perfil
                ]);

                $message = "Usuário cadastrado com sucesso!";

            }

        } catch (PDOException $e) {

            $message = "Erro: " . $e->getMessage();

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

<title>Cadastro de Usuário</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-lg mx-auto mt-10">

<div class="bg-white rounded-xl shadow-lg p-8">

<h1 class="text-3xl font-bold text-green-700 mb-6">

Cadastro de Usuário

</h1>

<?php if($message!=""){ ?>

<div class="mb-4 p-3 rounded bg-blue-100 text-blue-700">

<?php echo $message; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-4">

<label class="block mb-1 font-semibold">

Nome

</label>

<input
type="text"
name="nome"
required
class="w-full border rounded p-2">

</div>

<div class="mb-4">

<label class="block mb-1 font-semibold">

Email

</label>

<input
type="email"
name="email"
required
class="w-full border rounded p-2">

</div>

<div class="mb-4">

<label class="block mb-1 font-semibold">

Senha

</label>

<input
type="password"
name="senha"
required
class="w-full border rounded p-2">

</div>

<div class="mb-6">

<label class="block mb-1 font-semibold">

Perfil

</label>

<select
name="perfil"
class="w-full border rounded p-2">

<option value="Usuario">

Usuário

</option>

<option value="Comissao">

Comissão Técnica

</option>

<option value="Administrador">

Administrador

</option>

</select>

</div>

<button
type="submit"
class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">

Cadastrar

</button>

<a
href="index.php"
class="ml-3 text-blue-600">

Voltar

</a>

</form>

</div>

</div>

</body>

</html>