<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$message = "";
$erros = [];
$sucesso = 0;

$equipes = $pdo->query("SELECT ID, Nome FROM Equipe ORDER BY Nome")->fetchAll();
$equipesPorNome = [];
foreach ($equipes as $e) {
    $equipesPorNome[mb_strtolower($e['Nome'])] = $e['ID'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $message = "Erro no upload do arquivo.";
    } elseif (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
        $message = "Formato inválido. Envie um arquivo .xlsx, .xls ou .csv exportado do Excel.";
    } elseif ($ext !== 'csv') {
        $message = "Este esqueleto processa diretamente arquivos .csv (compatível com Excel). "
            . "Para .xlsx/.xls nativos, integre a biblioteca PhpSpreadsheet (composer require phpoffice/phpspreadsheet) "
            . "e substitua a leitura abaixo por \$spreadsheet = IOFactory::load(\$arquivo['tmp_name']).";
    } else {
        // Formato esperado das colunas: Nome, Numero, Posicao, Altura, Peso, DataNascimento, Equipe
        if (($handle = fopen($arquivo['tmp_name'], 'r')) !== false) {
            $delimitador = detectarDelimitadorCSV($arquivo['tmp_name']);
            $linha = 0;
            while (($dados = fgetcsv($handle, 0, $delimitador)) !== false) {
                $linha++;
                if ($linha === 1) continue; // pula cabeçalho

                if (count($dados) < 2) {
                    $erros[] = "Linha $linha: não foi possível separar as colunas (verifique se o separador é ponto e vírgula ou vírgula) — linha ignorada.";
                    continue;
                }

                [$nome, $numero, $posicao, $altura, $peso, $nascimento, $nomeEquipe] =
                    array_pad(array_map('trim', $dados), 7, null);

                if (empty($nome)) {
                    $erros[] = "Linha $linha: nome em branco, ignorada.";
                    continue;
                }

                $idEquipe = $nomeEquipe ? ($equipesPorNome[mb_strtolower($nomeEquipe)] ?? null) : null;

                if ($nomeEquipe && $idEquipe === null) {
                    $erros[] = "Linha $linha: equipe \"$nomeEquipe\" não encontrada — jogador importado sem equipe vinculada. Cadastre a equipe antes ou associe manualmente depois.";
                }

                try {
                    $sql = $pdo->prepare("
                        INSERT INTO Jogador (Nome, Numero, Posicao, Altura, Peso, DataNascimento, idEquipe)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $sql->execute([
                        $nome,
                        $numero ?: null,
                        $posicao ?: null,
                        $altura ?: null,
                        $peso ?: null,
                        $nascimento ?: null,
                        $idEquipe
                    ]);
                    $sucesso++;
                } catch (PDOException $e) {
                    $erros[] = "Linha $linha: erro ao importar ($nome) — " . $e->getMessage();
                }
            }
            fclose($handle);
            $message = "$sucesso jogador(es) importado(s) com sucesso.";
        } else {
            $message = "Não foi possível abrir o arquivo enviado.";
        }
    }
}

$pageTitle = "Importar Jogadores";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Importação de Jogadores (Excel/CSV)</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-blue-100 text-blue-700 max-w-xl"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($erros): ?>
<div class="mb-4 p-3 rounded bg-yellow-100 text-yellow-800 max-w-xl text-sm">
    <strong>Avisos:</strong>
    <ul class="list-disc ml-5">
        <?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-xl">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Envie um arquivo <strong>.csv</strong> exportado do Excel, com as colunas na ordem:
        <br><code>Nome; Numero; Posicao; Altura; Peso; DataNascimento; Equipe</code>
    </p>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
        O separador pode ser <strong>ponto e vírgula</strong> ou <strong>vírgula</strong> — é detectado automaticamente.
    </p>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
        A equipe informada precisa já estar cadastrada no sistema (veja a seção
        <a href="../equipes/listar.php" class="text-blue-600 hover:underline">Equipes</a>).
        Se não for encontrada, o jogador é importado sem equipe vinculada.
    </p>
    <a href="baixar_modelo.php" class="inline-block mb-4 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
        Baixar planilha modelo (.csv)
    </a>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="file" name="arquivo" accept=".csv,.xlsx,.xls" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
        <div class="flex gap-3">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded">Importar</button>
            <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
            <a href="historico_importacoes.php" class="text-blue-600 hover:underline self-center text-sm">Ver histórico de importações</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
