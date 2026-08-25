<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$message = "";
$erros = [];
$avisos = [];
$sucesso = 0;
$duplicados = 0;
$equipesCriadas = [];

$statusValidos = ['Agendada', 'Em andamento', 'Finalizada'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $message = "Erro no upload do arquivo.";
    } elseif (!in_array($ext, ['csv', 'xlsx', 'xls'], true)) {
        $message = "Formato inválido. Envie um arquivo .xlsx, .xls ou .csv exportado do Excel.";
    } elseif ($ext !== 'csv') {
        $message = "Este esqueleto processa diretamente arquivos .csv (compatível com Excel). "
            . "Para .xlsx/.xls nativos, integre a biblioteca PhpSpreadsheet (composer require phpoffice/phpspreadsheet).";
    } else {
        // Colunas esperadas: DataHora; Local; EquipeCasa; EquipeVisitante; PlacarCasa; PlacarVisitante; Status
        // DataHora no formato AAAA-MM-DD HH:MM (ex: 2026-08-19 19:00)
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

                [$dataHora, $local, $equipeCasa, $equipeVisitante, $placarCasa, $placarVisitante, $status] =
                    array_pad(array_map('trim', $dados), 7, null);

                if (empty($dataHora) || empty($equipeCasa) || empty($equipeVisitante)) {
                    $erros[] = "Linha $linha: data/hora e as duas equipes são obrigatórias — linha ignorada.";
                    continue;
                }

                if (mb_strtolower($equipeCasa) === mb_strtolower($equipeVisitante)) {
                    $erros[] = "Linha $linha: a equipe da casa e a visitante não podem ser a mesma — linha ignorada.";
                    continue;
                }

                $timestamp = strtotime($dataHora);
                if ($timestamp === false) {
                    $erros[] = "Linha $linha: data/hora \"$dataHora\" em formato inválido (use AAAA-MM-DD HH:MM) — linha ignorada.";
                    continue;
                }
                $dataHoraSql = date('Y-m-d H:i:s', $timestamp);

                // Resolve (ou cria) as equipes pelo nome
                $checaCasa = $pdo->prepare("SELECT ID FROM Equipe WHERE LOWER(Nome) = LOWER(?) LIMIT 1");
                $checaCasa->execute([$equipeCasa]);
                $existiaCasa = (bool)$checaCasa->fetchColumn();
                $idCasa = obterOuCriarEquipePorNome($pdo, $equipeCasa);
                if (!$existiaCasa && !in_array($equipeCasa, $equipesCriadas, true)) {
                    $equipesCriadas[] = $equipeCasa;
                }

                $checaVisitante = $pdo->prepare("SELECT ID FROM Equipe WHERE LOWER(Nome) = LOWER(?) LIMIT 1");
                $checaVisitante->execute([$equipeVisitante]);
                $existiaVisitante = (bool)$checaVisitante->fetchColumn();
                $idVisitante = obterOuCriarEquipePorNome($pdo, $equipeVisitante);
                if (!$existiaVisitante && !in_array($equipeVisitante, $equipesCriadas, true)) {
                    $equipesCriadas[] = $equipeVisitante;
                }

                // Evita duplicar a mesma partida (mesmas equipes na mesma data/hora)
                if (partidaJaExiste($pdo, $dataHoraSql, $idCasa, $idVisitante)) {
                    $avisos[] = "Linha $linha: partida \"$equipeCasa x $equipeVisitante\" em $dataHoraSql já cadastrada — não foi importada novamente.";
                    $duplicados++;
                    continue;
                }

                $statusFinal = in_array($status, $statusValidos, true) ? $status : 'Agendada';

                try {
                    $sql = $pdo->prepare("
                        INSERT INTO Partida
                            (DataHora, Local, Status, PlacarCasa, PlacarVisitante, idEquipeCasa, idEquipeVisitante)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $sql->execute([
                        $dataHoraSql,
                        $local ?: null,
                        $statusFinal,
                        $placarCasa !== null && $placarCasa !== '' ? (int)$placarCasa : 0,
                        $placarVisitante !== null && $placarVisitante !== '' ? (int)$placarVisitante : 0,
                        $idCasa,
                        $idVisitante
                    ]);
                    $sucesso++;
                } catch (PDOException $e) {
                    $erros[] = "Linha $linha: erro ao importar — " . $e->getMessage();
                }
            }
            fclose($handle);

            $partesMsg = ["$sucesso partida(s) importada(s) com sucesso."];
            if ($duplicados > 0) {
                $partesMsg[] = "$duplicados registro(s) ignorado(s) por já existirem.";
            }
            if ($equipesCriadas) {
                $partesMsg[] = "Equipe(s) criada(s) automaticamente: " . implode(', ', $equipesCriadas) . ".";
            }
            $message = implode(' ', $partesMsg);
        } else {
            $message = "Não foi possível abrir o arquivo enviado.";
        }
    }
}

$pageTitle = "Importar Partidas";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Importação de Partidas (Excel/CSV)</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-blue-100 text-blue-700 max-w-xl"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($avisos): ?>
<div class="mb-4 p-3 rounded bg-yellow-100 text-yellow-800 max-w-xl text-sm">
    <strong>Duplicados ignorados:</strong>
    <ul class="list-disc ml-5">
        <?php foreach ($avisos as $a): ?><li><?= htmlspecialchars($a) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($erros): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-xl text-sm">
    <strong>Erros:</strong>
    <ul class="list-disc ml-5">
        <?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-xl">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Envie um arquivo <strong>.csv</strong> exportado do Excel, com as colunas na ordem:
        <br><code>DataHora; Local; EquipeCasa; EquipeVisitante; PlacarCasa; PlacarVisitante; Status</code>
    </p>
    <ul class="text-xs text-gray-500 dark:text-gray-400 mb-4 list-disc ml-5 space-y-1">
        <li>O separador pode ser <strong>ponto e vírgula</strong> ou <strong>vírgula</strong> — é detectado automaticamente.</li>
        <li>DataHora no formato <strong>AAAA-MM-DD HH:MM</strong> (ex: 2026-08-19 19:00).</li>
        <li>Se alguma das equipes informadas não existir ainda, ela é <strong>criada automaticamente</strong>.</li>
        <li>Status aceito: <strong>Agendada</strong>, <strong>Em andamento</strong> ou <strong>Finalizada</strong> (em branco vira "Agendada").</li>
        <li>Partidas com as mesmas duas equipes e a mesma data/hora <strong>não são duplicadas</strong>.</li>
    </ul>
    <a href="baixar_modelo.php" class="inline-block mb-4 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
        Baixar planilha modelo (.csv)
    </a>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="file" name="arquivo" accept=".csv,.xlsx,.xls" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
        <div class="flex gap-3">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded">Importar</button>
            <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
