<?php
/**
 * Funções de cálculo estatístico, regras de negócio e utilitários de importação.
 * Cobre: eficiência, eFG%, TS%, PPP, regularidade, rankings, estatística mais
 * influente, criação automática de equipes e verificação de duplicidade.
 */

/**
 * Recalcula (ou cria) a linha agregada de Estatisticas de um jogador em uma partida,
 * a partir da soma dos eventos registrados na tabela Evento.
 * Chamada automaticamente após cada evento de scouting.
 */
function recalcularEstatisticasPartida(PDO $pdo, int $idJogador, int $idPartida): void
{
    // Soma os eventos da partida para o jogador
    $sql = $pdo->prepare("
        SELECT
            SUM(CASE WHEN TipoEvento IN ('CESTA_1','CESTA_2','CESTA_3') THEN Pontos ELSE 0 END) AS Pontos,
            SUM(TipoEvento = 'ASSISTENCIA') AS Assistencias,
            SUM(TipoEvento IN ('REBOTE_OFENSIVO','REBOTE_DEFENSIVO')) AS Rebotes,
            SUM(TipoEvento = 'ROUBO') AS Roubos,
            SUM(TipoEvento = 'TOCO') AS Tocos,
            SUM(TipoEvento = 'TURNOVER') AS Turnovers,
            SUM(TipoEvento = 'FALTA') AS Faltas,
            SUM(TipoEvento = 'CESTA_2') AS Cestas2Convertidas,
            SUM(TipoEvento = 'TENTATIVA_2') AS Tentativas2,
            SUM(TipoEvento = 'CESTA_3') AS Cestas3Convertidas,
            SUM(TipoEvento = 'TENTATIVA_3') AS Tentativas3,
            SUM(TipoEvento = 'LANCE_LIVRE_CONVERTIDO') AS LancesConvertidos,
            SUM(TipoEvento = 'LANCE_LIVRE_TENTATIVA') AS LancesTentados
        FROM Evento
        WHERE idJogador = ? AND idPartida = ?
    ");
    $sql->execute([$idJogador, $idPartida]);
    $e = $sql->fetch();

    $pontos       = (int)($e['Pontos'] ?? 0);
    $assistencias = (int)($e['Assistencias'] ?? 0);
    $rebotes      = (int)($e['Rebotes'] ?? 0);
    $roubos       = (int)($e['Roubos'] ?? 0);
    $tocos        = (int)($e['Tocos'] ?? 0);
    $turnovers    = (int)($e['Turnovers'] ?? 0);
    $faltas       = (int)($e['Faltas'] ?? 0);

    $fgTentados = (int)($e['Tentativas2'] ?? 0) + (int)($e['Tentativas3'] ?? 0)
                + (int)($e['Cestas2Convertidas'] ?? 0) + (int)($e['Cestas3Convertidas'] ?? 0);
    $fgConvertidos = (int)($e['Cestas2Convertidas'] ?? 0) + (int)($e['Cestas3Convertidas'] ?? 0);
    $cestas3       = (int)($e['Cestas3Convertidas'] ?? 0);
    $ltTentados    = (int)($e['LancesTentados'] ?? 0);
    $ltConvertidos = (int)($e['LancesConvertidos'] ?? 0);

    // eFG% = (FG + 0.5 * 3PM) / FGA
    $efg = $fgTentados > 0 ? (($fgConvertidos + 0.5 * $cestas3) / $fgTentados) * 100 : 0;

    // TS% = PTS / (2 * (FGA + 0.44 * FTA))
    $tsDenominador = 2 * ($fgTentados + 0.44 * $ltTentados);
    $ts = $tsDenominador > 0 ? ($pontos / $tsDenominador) * 100 : 0;

    // Eficiência (fórmula clássica NBA/FIBA simplificada)
    $eficiencia = $pontos + $rebotes + $assistencias + $roubos + $tocos
                - ($fgTentados - $fgConvertidos)
                - ($ltTentados - $ltConvertidos)
                - $turnovers;

    // Posses de bola estimadas e PPP (pontos por posse)
    $posses = $fgTentados + 0.44 * $ltTentados + $turnovers;
    $ppp = $posses > 0 ? $pontos / $posses : 0;

    $existe = $pdo->prepare("SELECT ID FROM Estatisticas WHERE idJogador = ? AND idPartida = ?");
    $existe->execute([$idJogador, $idPartida]);
    $linha = $existe->fetch();

    if ($linha) {
        $upd = $pdo->prepare("
            UPDATE Estatisticas SET
                Pontos = ?, Assistencias = ?, Rebotes = ?, Roubos = ?, Tocos = ?,
                Turnovers = ?, Faltas = ?, Eficiencia = ?, eFG = ?, TS = ?, PPP = ?
            WHERE ID = ?
        ");
        $upd->execute([$pontos, $assistencias, $rebotes, $roubos, $tocos, $turnovers,
            $faltas, $eficiencia, $efg, $ts, $ppp, $linha['ID']]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO Estatisticas
                (idJogador, idPartida, Pontos, Assistencias, Rebotes, Roubos, Tocos,
                 Turnovers, Faltas, Eficiencia, eFG, TS, PPP)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([$idJogador, $idPartida, $pontos, $assistencias, $rebotes, $roubos,
            $tocos, $turnovers, $faltas, $eficiencia, $efg, $ts, $ppp]);
    }

    atualizarRegularidade($pdo, $idJogador);
}

/**
 * Índice de regularidade: 100 - coeficiente de variação da eficiência entre partidas.
 * Quanto mais próximo de 100, mais regular é o jogador.
 */
function atualizarRegularidade(PDO $pdo, int $idJogador): void
{
    $sql = $pdo->prepare("SELECT Eficiencia FROM Estatisticas WHERE idJogador = ?");
    $sql->execute([$idJogador]);
    $valores = array_column($sql->fetchAll(), 'Eficiencia');
    $n = count($valores);

    if ($n < 2) {
        $regularidade = $n === 1 ? 100 : null;
    } else {
        $media = array_sum($valores) / $n;
        $variancia = 0;
        foreach ($valores as $v) {
            $variancia += ($v - $media) ** 2;
        }
        $variancia /= $n;
        $desvio = sqrt($variancia);
        $cv = $media != 0 ? abs($desvio / $media) : 1;
        $regularidade = max(0, min(100, 100 - ($cv * 100)));
    }

    $upd = $pdo->prepare("UPDATE Estatisticas SET Regularidade = ? WHERE idJogador = ?");
    $upd->execute([$regularidade, $idJogador]);
}

/** Ranking geral (média de eficiência por jogador) e Top N quando $limite for informado. */
function rankingJogadores(PDO $pdo, int $limite = null): array
{
    $sql = "
        SELECT
            j.ID, j.Nome, j.Numero, j.Posicao, eq.Nome AS Equipe,
            ROUND(AVG(e.Pontos), 1) AS MediaPontos,
            ROUND(AVG(e.Assistencias), 1) AS MediaAssistencias,
            ROUND(AVG(e.Rebotes), 1) AS MediaRebotes,
            ROUND(AVG(e.Eficiencia), 2) AS MediaEficiencia,
            ROUND(AVG(e.TS), 1) AS MediaTS,
            ROUND(AVG(e.eFG), 1) AS MediaEFG,
            ROUND(AVG(e.Regularidade), 1) AS MediaRegularidade,
            COUNT(e.ID) AS PartidasJogadas
        FROM Jogador j
        JOIN Estatisticas e ON e.idJogador = j.ID
        LEFT JOIN Equipe eq ON eq.ID = j.idEquipe
        GROUP BY j.ID
        ORDER BY MediaEficiencia DESC
    ";
    if ($limite) {
        $sql .= " LIMIT " . (int)$limite;
    }
    return $pdo->query($sql)->fetchAll();
}

/** Ranking de equipes pela média de eficiência dos seus jogadores. */
function rankingEquipes(PDO $pdo): array
{
    $sql = "
        SELECT
            eq.ID, eq.Nome, eq.Cidade, eq.Tecnico,
            ROUND(AVG(e.Eficiencia), 2) AS MediaEficiencia,
            ROUND(AVG(e.Pontos), 1) AS MediaPontos,
            COUNT(DISTINCT j.ID) AS TotalJogadores
        FROM Equipe eq
        JOIN Jogador j ON j.idEquipe = eq.ID
        JOIN Estatisticas e ON e.idJogador = j.ID
        GROUP BY eq.ID
        ORDER BY MediaEficiencia DESC
    ";
    return $pdo->query($sql)->fetchAll();
}

/** Estatística mais influente na classificação (maior correlação simples com a Eficiência). */
function estatisticaMaisInfluente(PDO $pdo): array
{
    $campos = ['Pontos', 'Assistencias', 'Rebotes', 'Roubos', 'Tocos', 'Turnovers'];
    $dados = $pdo->query("SELECT " . implode(',', $campos) . ", Eficiencia FROM Estatisticas")->fetchAll();

    if (count($dados) < 2) {
        return ['campo' => null, 'correlacao' => 0];
    }

    $melhor = null;
    $melhorValor = -1;

    foreach ($campos as $campo) {
        $x = array_column($dados, $campo);
        $y = array_column($dados, 'Eficiencia');
        $r = abs(correlacaoPearson($x, $y));
        if ($r > $melhorValor) {
            $melhorValor = $r;
            $melhor = $campo;
        }
    }

    return ['campo' => $melhor, 'correlacao' => round($melhorValor, 3)];
}

function correlacaoPearson(array $x, array $y): float
{
    $n = count($x);
    if ($n === 0) return 0;
    $mediaX = array_sum($x) / $n;
    $mediaY = array_sum($y) / $n;
    $num = 0;
    $denX = 0;
    $denY = 0;
    for ($i = 0; $i < $n; $i++) {
        $dx = $x[$i] - $mediaX;
        $dy = $y[$i] - $mediaY;
        $num += $dx * $dy;
        $denX += $dx ** 2;
        $denY += $dy ** 2;
    }
    $den = sqrt($denX * $denY);
    return $den != 0 ? $num / $den : 0;
}

/**
 * Retorna o ID da equipe "Sem equipe", criando-a uma única vez caso ainda não exista.
 * Usada quando a planilha de importação vem com a coluna Equipe em branco ou com o
 * valor "sem equipe" (qualquer variação de maiúsculas/minúsculas).
 */
function obterOuCriarEquipeSemEquipe(PDO $pdo): int
{
    $busca = $pdo->prepare("SELECT ID FROM Equipe WHERE LOWER(Nome) = 'sem equipe' LIMIT 1");
    $busca->execute();
    $id = $busca->fetchColumn();

    if ($id) {
        return (int)$id;
    }

    $criar = $pdo->prepare("INSERT INTO Equipe (Nome, Cidade, Tecnico) VALUES ('Sem equipe', NULL, NULL)");
    $criar->execute();
    return (int)$pdo->lastInsertId();
}

/**
 * Retorna o ID de uma equipe pelo nome (comparação sem diferenciar maiúsculas/minúsculas),
 * criando-a automaticamente se ainda não existir. Nomes em branco ou "sem equipe" retornam
 * a equipe especial "Sem equipe" (obterOuCriarEquipeSemEquipe).
 */
function obterOuCriarEquipePorNome(PDO $pdo, ?string $nome): int
{
    $nome = trim((string)$nome);

    if ($nome === '' || mb_strtolower($nome) === 'sem equipe') {
        return obterOuCriarEquipeSemEquipe($pdo);
    }

    $busca = $pdo->prepare("SELECT ID FROM Equipe WHERE LOWER(Nome) = LOWER(?) LIMIT 1");
    $busca->execute([$nome]);
    $id = $busca->fetchColumn();

    if ($id) {
        return (int)$id;
    }

    $criar = $pdo->prepare("INSERT INTO Equipe (Nome) VALUES (?)");
    $criar->execute([$nome]);
    return (int)$pdo->lastInsertId();
}

/** Verifica se já existe uma equipe com esse nome (ignorando maiúsculas/minúsculas), opcionalmente ignorando um ID (para edição). */
function equipeJaExiste(PDO $pdo, string $nome, ?int $ignorarId = null): bool
{
    $sql = "SELECT ID FROM Equipe WHERE LOWER(Nome) = LOWER(?)";
    $params = [trim($nome)];
    if ($ignorarId) {
        $sql .= " AND ID <> ?";
        $params[] = $ignorarId;
    }
    $busca = $pdo->prepare($sql);
    $busca->execute($params);
    return (bool)$busca->fetchColumn();
}

/** Verifica se já existe um jogador com o mesmo nome na mesma equipe (ignorando maiúsculas/minúsculas). */
function jogadorJaExiste(PDO $pdo, string $nome, ?int $idEquipe, ?int $ignorarId = null): bool
{
    $sql = "SELECT ID FROM Jogador WHERE LOWER(Nome) = LOWER(?) AND idEquipe <=> ?";
    $params = [trim($nome), $idEquipe];
    if ($ignorarId) {
        $sql .= " AND ID <> ?";
        $params[] = $ignorarId;
    }
    $busca = $pdo->prepare($sql);
    $busca->execute($params);
    return (bool)$busca->fetchColumn();
}

/** Verifica se já existe uma partida idêntica (mesmas equipes e mesma data/hora). */
function partidaJaExiste(PDO $pdo, string $dataHora, int $idCasa, int $idVisitante): bool
{
    $busca = $pdo->prepare("
        SELECT ID FROM Partida
        WHERE DataHora = ? AND idEquipeCasa = ? AND idEquipeVisitante = ?
    ");
    $busca->execute([$dataHora, $idCasa, $idVisitante]);
    return (bool)$busca->fetchColumn();
}

/** Traduz o código do evento em rótulo legível para a interface de scouting. */
/**
 * Detecta automaticamente o separador usado em um CSV (";" ou ",") a partir da
 * primeira linha do arquivo, para aceitar tanto o padrão brasileiro do Excel
 * (ponto e vírgula) quanto o padrão internacional (vírgula) sem exigir que o
 * usuário formate o arquivo de um jeito específico.
 */
function detectarDelimitadorCSV(string $caminhoArquivo): string
{
    $handle = fopen($caminhoArquivo, 'r');
    $primeiraLinha = $handle ? fgets($handle) : '';
    if ($handle) {
        fclose($handle);
    }

    $qtdPontoVirgula = substr_count((string)$primeiraLinha, ';');
    $qtdVirgula = substr_count((string)$primeiraLinha, ',');

    return $qtdPontoVirgula >= $qtdVirgula ? ';' : ',';
}

function rotuloEvento(string $tipo): string
{
    $rotulos = [
        'CESTA_2' => 'Cesta de 2 pontos',
        'TENTATIVA_2' => 'Tentativa de 2 (errada)',
        'CESTA_3' => 'Cesta de 3 pontos',
        'TENTATIVA_3' => 'Tentativa de 3 (errada)',
        'LANCE_LIVRE_CONVERTIDO' => 'Lance livre convertido',
        'LANCE_LIVRE_TENTATIVA' => 'Lance livre errado',
        'ASSISTENCIA' => 'Assistência',
        'REBOTE_OFENSIVO' => 'Rebote ofensivo',
        'REBOTE_DEFENSIVO' => 'Rebote defensivo',
        'ROUBO' => 'Roubo de bola',
        'TOCO' => 'Toco',
        'TURNOVER' => 'Turnover',
        'FALTA' => 'Falta',
    ];
    return $rotulos[$tipo] ?? $tipo;
}
