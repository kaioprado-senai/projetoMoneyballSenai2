<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!usuarioLogado() || !podeEditarDados()) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$idPartida  = (int)($input['idPartida'] ?? 0);
$idJogador  = (int)($input['idJogador'] ?? 0);
$tipoEvento = $input['tipoEvento'] ?? '';
$pontos     = (int)($input['pontos'] ?? 0);
$coordX     = $input['coordenadaX'] ?? null;
$coordY     = $input['coordenadaY'] ?? null;

$tiposValidos = [
    'CESTA_2', 'TENTATIVA_2', 'CESTA_3', 'TENTATIVA_3',
    'LANCE_LIVRE_CONVERTIDO', 'LANCE_LIVRE_TENTATIVA',
    'ASSISTENCIA', 'REBOTE_OFENSIVO', 'REBOTE_DEFENSIVO',
    'ROUBO', 'TOCO', 'TURNOVER', 'FALTA'
];

// Validação de integridade dos dados antes de gravar
if (!$idPartida || !$idJogador || !in_array($tipoEvento, $tiposValidos, true)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos para o registro do evento.']);
    exit;
}

$verificaPartida = $pdo->prepare("SELECT idEquipeCasa, idEquipeVisitante FROM Partida WHERE ID = ?");
$verificaPartida->execute([$idPartida]);
$partida = $verificaPartida->fetch();

$verificaJogador = $pdo->prepare("SELECT idEquipe FROM Jogador WHERE ID = ?");
$verificaJogador->execute([$idJogador]);
$jogador = $verificaJogador->fetch();

if (!$partida || !$jogador) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Partida ou jogador não encontrado.']);
    exit;
}

if (!in_array($jogador['idEquipe'], [$partida['idEquipeCasa'], $partida['idEquipeVisitante']], true)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Jogador não pertence a nenhuma das equipes desta partida.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $ins = $pdo->prepare("
        INSERT INTO Evento (idPartida, idJogador, TipoEvento, Pontos, CoordenadaX, CoordenadaY)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([$idPartida, $idJogador, $tipoEvento, $pontos, $coordX, $coordY]);

    // Atualiza estatísticas individuais automaticamente
    recalcularEstatisticasPartida($pdo, $idJogador, $idPartida);

    // Atualiza o placar da partida quando o evento pontua
    if ($pontos > 0) {
        $campoPlacar = $jogador['idEquipe'] == $partida['idEquipeCasa'] ? 'PlacarCasa' : 'PlacarVisitante';
        $upd = $pdo->prepare("UPDATE Partida SET $campoPlacar = $campoPlacar + ?, Status = 'Em andamento' WHERE ID = ?");
        $upd->execute([$pontos, $idPartida]);
    }

    $pdo->commit();

    echo json_encode(['sucesso' => true, 'mensagem' => rotuloEvento($tipoEvento) . ' registrado com sucesso.']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao registrar evento: ' . $e->getMessage()]);
}
