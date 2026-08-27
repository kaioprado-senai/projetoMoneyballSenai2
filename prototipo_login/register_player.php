<?php
require 'db.php';

$message = "";

// Busca as equipes para preencher o SELECT
$equipes = $pdo->query("
    SELECT ID, Nome
    FROM Equipe
    ORDER BY Nome
")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $numero = !empty($_POST["numero"]) ? $_POST["numero"] : null;
    $posicao = $_POST["posicao"];
    $altura = !empty($_POST["altura"]) ? $_POST["altura"] : null;
    $peso = !empty($_POST["peso"]) ? $_POST["peso"] : null;
    $dataNascimento = !empty($_POST["dataNascimento"]) ? $_POST["dataNascimento"] : null;
    $idEquipe = !empty($_POST["idEquipe"]) ? $_POST["idEquipe"] : null;

    if (!empty($nome)) {

        try {

            $sql = $pdo->prepare("
                INSERT INTO Jogador
                (
                    Nome,
                    Numero,
                    Posicao,
                    Altura,
                    Peso,
                    DataNascimento,
                    idEquipe
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $sql->execute([
                $nome,
                $numero,
                $posicao,
                $altura,
                $peso,
                $dataNascimento,
                $idEquipe
            ]);

            $message = "Jogador cadastrado com sucesso!";

        } catch (PDOException $e) {

            $message = "Erro: " . $e->getMessage();

        }

    } else {

        $message = "Informe o nome do jogador.";

    }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cadastro de Jogadores</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-10">

<div class="bg-white rounded-xl shadow-lg p-8">

<h1 class="text-3xl font-bold text-blue-700 mb-6">

Cadastro de Jogador

</h1>

<?php if($message != ""){ ?>

<div class="mb-5 p-3 rounded bg-blue-100 text-blue-700">

<?php echo $message; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-4">

<label class="block font-semibold mb-1">

Nome

</label>

<input
type="text"
name="nome"
required
class="w-full border rounded p-2">

</div>

<div class="grid grid-cols-2 gap-4">

<div>

<label class="block font-semibold mb-1">

Número

</label>

<input
type="number"
name="numero"
class="w-full border rounded p-2">

</div>

<div>

<label class="block font-semibold mb-1">

Posição

</label>

<select
name="posicao"
class="w-full border rounded p-2">

<option value="Armador">Armador</option>

<option value="Ala-Armador">Ala-Armador</option>

<option value="Ala">Ala</option>

<option value="Ala-Pivô">Ala-Pivô</option>

<option value="Pivô">Pivô</option>

</select>

</div>

</div>

<div class="grid grid-cols-2 gap-4 mt-4">

<div>

<label class="block font-semibold mb-1">

Altura (m)

</label>

<input
type="number"
step="0.01"
name="altura"
placeholder="1.85"
class="w-full border rounded p-2">

</div>

<div>

<label class="block font-semibold mb-1">

Peso (kg)

</label>

<input
type="number"
step="0.01"
name="peso"
placeholder="82.5"
class="w-full border rounded p-2">

</div>

</div>

<div class="mt-4">

<label class="block font-semibold mb-1">

Data de Nascimento

</label>

<input
type="date"
name="dataNascimento"
class="w-full border rounded p-2">

</div>

<div class="mt-4">

<label class="block font-semibold mb-1">

Equipe

</label>

<select
name="idEquipe"
class="w-full border rounded p-2">

<option value="">Sem equipe</option>

<?php foreach($equipes as $e){ ?>

<option value="<?= $e['ID']; ?>">

<?= htmlspecialchars($e['Nome']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="mt-8 flex gap-3">

<button
type="submit"
class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">

Cadastrar

</button>

<a
href="index.php"
class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">

Voltar

</a>

</div>

</form>

</div>

</div>

</body>

</html>