<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$message = "";
$erros = [];
$avisos = [];
$sucesso = 0;
$atualizados = 0;

// Ordem das colunas esperadas na planilha:
// NomeJogador; EquipeCasa; EquipeVisitante; DataHora; Pontos; Rebotes; Assistencias;
// Roubos; Tocos; Turnovers; Faltas; PlusMinus; Cestas2Convertidas; Cestas2Tentadas;
// Cestas3Convertidas; Cestas3Tentadas; LancesConvertidos; LancesTentados
//
// "Tentadas" = total de tentativas de arremesso na partida, incluindo as convertidas
// (ex.: se o jogador acertou 4 de 2 pontos e errou 3, Cestas2Convertidas=4 e Cestas2Tentadas=7).

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
        if (($handle = fopen($arquivo['tmp_name'], 'r')) !== false) {
            $delimitador = detectarDelimitadorCSV($arquivo['tmp_name']);
            $linha = 0;
            while (($dados = fgetcsv($handle, 0, $delimitador)) !== false) {
                $linha++;
                if ($linha === 1) continue; // pula cabeçalho

                if (count($dados) < 4) {
                    $erros[] = "Linha $linha: não foi possível separar as colunas (verifique o separador) — linha ignorada.";
                    continue;
                }

                $dados = array_pad(array_map('trim', $dados), 18, null);
                [
                    $nomeJogador, $equipeCasa, $equipeVisitante, $dataHora,
                    $pontos, $rebotes, $assistencias, $roubos, $tocos, $turnovers, $faltas, $plusMinus,
                    $c2c, $c2t, $c3c, $c3t, $ltc, $ltt
                ] = $dados;

                if (empty($nomeJogador) || empty($equipeCasa) || empty($equipeVisitante) || empty($dataHora)) {
                    $erros[] = "Linha $linha: jogador, equipe da casa, visitante e data/hora são obrigatórios — linha ignorada.";
                    continue;
                }

                $timestamp = strtotime($dataHora);
                if ($timestamp === false) {
                    $erros[] = "Linha $linha: data/hora \"$dataHora\" em formato inválido (use AAAA-MM-DD HH:MM) — linha ignorada.";
                    continue;
                }
                $dataHoraSql = date('Y-m-d H:i:s', $timestamp);

                // Localiza as equipes (precisam já existir — cadastre-as antes)
                $buscaCasa = $pdo->prepare("SELECT ID FROM Equipe WHERE LOWER(Nome) = LOWER(?) LIMIT 1");
                $buscaCasa->execute([$equipeCasa]);
                $idCasa = $buscaCasa->fetchColumn();

                $buscaVisitante = $pdo->prepare("SELECT ID FROM Equipe WHERE LOWER(Nome) = LOWER(?) LIMIT 1");
                $buscaVisitante->execute([$equipeVisitante]);
                $idVisitante = $buscaVisitante->fetchColumn();

                if (!$idCasa || !$idVisitante) {
                    $erros[] = "Linha $linha: equipe \"" . (!$idCasa ? $equipeCasa : $equipeVisitante) . "\" não encontrada — cadastre a equipe antes de importar.";
                    continue;
                }

                // Localiza a partida pelas equipes e data/hora exatas
                $buscaPartida = $pdo->prepare("
                    SELECT ID FROM Partida
                    WHERE DataHora = ? AND idEquipeCasa = ? AND idEquipeVisitante = ?
                ");
                $buscaPartida->execute([$dataHoraSql, $idCasa, $idVisitante]);
                $idPartida = $buscaPartida->fetchColumn();

                if (!$idPartida) {
                    $erros[] = "Linha $linha: partida \"$equipeCasa x $equipeVisitante\" em $dataHoraSql não encontrada — cadastre/importe a partida antes de importar as estatísticas.";
                    continue;
                }

                // Localiza o jogador pelo nome, restrito às duas equipes da partida
                $buscaJogador = $pdo->prepare("
                    SELECT ID FROM Jogador
                    WHERE LOWER(Nome) = LOWER(?) AND idEquipe IN (?, ?)
                ");
                $buscaJogador->execute([$nomeJogador, $idCasa, $idVisitante]);
                $jogadoresEncontrados = $buscaJogador->fetchAll();

                if (count($jogadoresEncontrados) === 0) {
                    $erros[] = "Linha $linha: jogador \"$nomeJogador\" não encontrado em nenhuma das duas equipes da partida — linha ignorada.";
                    continue;
                }
                if (count($jogadoresEncontrados) > 1) {
                    $erros[] = "Linha $linha: mais de um jogador chamado \"$nomeJogador\" nas equipes da partida — ambíguo, linha ignorada.";
                    continue;
                }
                $idJogador = (int)$jogadoresEncontrados[0]['ID'];

                // Converte números (em branco = 0)
                $numInt = function ($v) {
                    if ($v === null || $v === '') return 0;
                    return (int)$v;
                };
                $pontosN       = $numInt($pontos);
                $rebotesN      = $numInt($rebotes);
                $assistenciasN = $numInt($assistencias);
                $roubosN       = $numInt($roubos);
                $tocosN        = $numInt($tocos);
                $turnoversN    = $numInt($turnovers);
                $faltasN       = $numInt($faltas);
                $plusMinusN    = $numInt($plusMinus);
                $c2cN = $numInt($c2c); $c2tN = $numInt($c2t);
                $c3cN = $numInt($c3c); $c3tN = $numInt($c3t);
                $ltcN = $numInt($ltc); $lttN = $numInt($ltt);

                if ($c2cN > $c2tN || $c3cN > $c3tN || $ltcN > $lttN) {
                    $erros[] = "Linha $linha: quantidade de cestas/lances convertidos maior que tentados — linha ignorada.";
                    continue;
                }

                $fgTentados    = $c2tN + $c3tN;
                $fgConvertidos = $c2cN + $c3cN;

                // eFG% = (FG + 0.5 * 3PM) / FGA
                $efg = $fgTentados > 0 ? (($fgConvertidos + 0.5 * $c3cN) / $fgTentados) * 100 : 0;

                // TS% = PTS / (2 * (FGA + 0.44 * FTA))
                $tsDenominador = 2 * ($fgTentados + 0.44 * $lttN);
                $ts = $tsDenominador > 0 ? ($pontosN / $tsDenominador) * 100 : 0;

                // Eficiência (fórmula clássica NBA/FIBA simplificada)
                $eficiencia = $pontosN + $rebotesN + $assistenciasN + $roubosN + $tocosN
                    - ($fgTentados - $fgConvertidos)
                    - ($lttN - $ltcN)
                    - $turnoversN;

                // Posses de bola estimadas e PPP (pontos por posse)
                $posses = $fgTentados + 0.44 * $lttN + $turnoversN;
                $ppp = $posses > 0 ? $pontosN / $posses : 0;

                try {
                    $existe = $pdo->prepare("SELECT ID FROM Estatisticas WHERE idJogador = ? AND idPartida = ?");
                    $existe->execute([$idJogador, $idPartida]);
                    $linhaExistente = $existe->fetch();

                    if ($linhaExistente) {
                        $upd = $pdo->prepare("
                            UPDATE Estatisticas SET
                                Pontos = ?, Assistencias = ?, Rebotes = ?, Roubos = ?, Tocos = ?,
                                Turnovers = ?, Faltas = ?, PlusMinus = ?, Eficiencia = ?, eFG = ?, TS = ?, PPP = ?
                            WHERE ID = ?
                        ");
                        $upd->execute([
                            $pontosN, $assistenciasN, $rebotesN, $roubosN, $tocosN, $turnoversN,
                            $faltasN, $plusMinusN, $eficiencia, $efg, $ts, $ppp, $linhaExistente['ID']
                        ]);
                        $atualizados++;
                    } else {
                        $ins = $pdo->prepare("
                            INSERT INTO Estatisticas
                                (idJogador, idPartida, Pontos, Assistencias, Rebotes, Roubos, Tocos,
                                 Turnovers, Faltas, PlusMinus, Eficiencia, eFG, TS, PPP)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $ins->execute([
                            $idJogador, $idPartida, $pontosN, $assistenciasN, $rebotesN, $roubosN,
                            $tocosN, $turnoversN, $faltasN, $plusMinusN, $eficiencia, $efg, $ts, $ppp
                        ]);
                        $sucesso++;
                    }

                    atualizarRegularidade($pdo, $idJogador);
                } catch (PDOException $e) {
                    $erros[] = "Linha $linha: erro ao importar — " . $e->getMessage();
                }
            }
            fclose($handle);

            $partesMsg = ["$sucesso registro(s) novo(s) importado(s)."];
            if ($atualizados > 0) {
                $partesMsg[] = "$atualizados registro(s) já existente(s) foram atualizados.";
            }
            $message = implode(' ', $partesMsg);
        } else {
            $message = "Não foi possível abrir o arquivo enviado.";
        }
    }
}

$pageTitle = "Importar Estatísticas";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-6">Importação de Estatísticas de Partida (Excel/CSV)</h1>

<?php if ($message !== ""): ?>
<div class="mb-4 p-3 rounded bg-blue-100 text-blue-700 max-w-2xl"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($erros): ?>
<div class="mb-4 p-3 rounded bg-red-100 text-red-700 max-w-2xl text-sm">
    <strong>Erros:</strong>
    <ul class="list-disc ml-5">
        <?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-2xl">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Envie um arquivo <strong>.csv</strong> exportado do Excel, com as colunas na ordem:
    </p>
    <code class="block text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded mb-4 overflow-x-auto whitespace-pre">NomeJogador; EquipeCasa; EquipeVisitante; DataHora; Pontos; Rebotes; Assistencias; Roubos; Tocos; Turnovers; Faltas; PlusMinus; Cestas2Convertidas; Cestas2Tentadas; Cestas3Convertidas; Cestas3Tentadas; LancesConvertidos; LancesTentados</code>
    <ul class="text-xs text-gray-500 dark:text-gray-400 mb-4 list-disc ml-5 space-y-1">
        <li>O separador pode ser <strong>ponto e vírgula</strong> ou <strong>vírgula</strong> — detectado automaticamente.</li>
        <li><strong>DataHora</strong> no formato <strong>AAAA-MM-DD HH:MM</strong>, igual à cadastrada na partida.</li>
        <li>A <strong>equipe da casa, a visitante e a partida já precisam existir</strong> no sistema (cadastre ou importe as partidas antes).</li>
        <li>O jogador é localizado pelo nome dentro das duas equipes da partida — nomes duplicados na mesma partida são rejeitados por ambiguidade.</li>
        <li>"Tentadas" = total de tentativas na partida, <strong>incluindo</strong> as convertidas (ex.: 4 convertidas + 3 erradas = 7 tentadas).</li>
        <li>Colunas de arremesso em branco viram 0 — nesse caso eFG%, TS% e Eficiência são calculados só com os totais informados.</li>
        <li>Se já existir estatística do jogador nessa partida, os valores são <strong>atualizados</strong> (não duplicados).</li>
        <li><strong>Atenção:</strong> se essa partida também for registrada pela tela de Scouting (lançamento de eventos), o próximo evento lançado recalcula e <strong>sobrescreve</strong> os valores importados aqui.</li>
    </ul>
    <a href="baixar_modelo.php" class="inline-block mb-4 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
        Baixar planilha modelo (.csv)
    </a>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="file" name="arquivo" accept=".csv,.xlsx,.xls" required class="w-full border rounded p-2 bg-white text-gray-900 placeholder-gray-400">
        <div class="flex gap-3">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded">Importar</button>
            <a href="ranking.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Voltar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
