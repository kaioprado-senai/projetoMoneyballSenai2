<?php
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$id = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("SELECT j.*, eq.Nome AS EquipeNome FROM Jogador j LEFT JOIN Equipe eq ON eq.ID = j.idEquipe WHERE j.ID = ?");
$sql->execute([$id]);
$jogador = $sql->fetch();
if (!$jogador) die("Jogador não encontrado.");

$stats = $pdo->prepare("
 SELECT e.*, p.DataHora, ec.Nome AS Casa, ev.Nome AS Visitante
 FROM Estatisticas e
 JOIN Partida p ON p.ID = e.idPartida
 JOIN Equipe ec ON ec.ID = p.idEquipeCasa
 JOIN Equipe ev ON ev.ID = p.idEquipeVisitante
 WHERE e.idJogador = ?
 ORDER BY p.DataHora DESC
");
$stats->execute([$id]);
$historico = $stats->fetchAll();

$medias = $pdo->prepare("
 SELECT ROUND(AVG(Pontos),1) AS Pontos, ROUND(AVG(Assistencias),1) AS Assistencias,
 ROUND(AVG(Rebotes),1) AS Rebotes, ROUND(AVG(Eficiencia),2) AS Eficiencia,
 ROUND(AVG(TS),1) AS TS, ROUND(AVG(eFG),1) AS eFG, ROUND(AVG(Regularidade),1) AS Regularidade
 FROM Estatisticas WHERE idJogador = ?
");
$medias->execute([$id]);
$media = $medias->fetch();

// Arremessos para o shot chart
$arremessos = $pdo->prepare("
 SELECT CoordenadaX, CoordenadaY, TipoEvento
 FROM Evento
 WHERE idJogador = ? AND TipoEvento IN ('CESTA_2','TENTATIVA_2','CESTA_3','TENTATIVA_3')
 AND CoordenadaX IS NOT NULL AND CoordenadaY IS NOT NULL
");
$arremessos->execute([$id]);
$shots = $arremessos->fetchAll();

$pageTitle = $jogador['Nome'];
require __DIR__ . '/../includes/header.php';
?>
<div class="flex justify-between items-start mb-6 flex-wrap gap-3">
 <div>
 <h1 class="text-2xl font-bold"> <?= htmlspecialchars($jogador['Nome']) ?></h1>
 <p class="text-gray-500 dark:text-gray-400">
 #<?= htmlspecialchars($jogador['Numero'] ?? '-') ?> · <?= htmlspecialchars($jogador['Posicao'] ?? '-') ?> ·
 <?= htmlspecialchars($jogador['EquipeNome'] ?? 'Sem equipe') ?>
</p>
</div>
 <a href="listar.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Voltar</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">Pontos/jogo</p><p class="text-2xl font-bold"><?= $media['Pontos'] ?? '-' ?></p></div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">Assist./jogo</p><p class="text-2xl font-bold"><?= $media['Assistencias'] ?? '-' ?></p></div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">Rebotes/jogo</p><p class="text-2xl font-bold"><?= $media['Rebotes'] ?? '-' ?></p></div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">Eficiência</p><p class="text-2xl font-bold text-orange-600"><?= $media['Eficiencia'] ?? '-' ?></p></div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">TS%</p><p class="text-2xl font-bold"><?= $media['TS'] ?? '-' ?></p></div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">eFG%</p><p class="text-2xl font-bold"><?= $media['eFG'] ?? '-' ?></p></div>
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4"><p class="text-xs text-gray-500">Regularidade</p><p class="text-2xl font-bold"><?= $media['Regularidade'] ?? '-' ?></p></div>
</div>

<div class="grid md:grid-cols-2 gap-6">
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold text-lg mb-4">Histórico por Partida</h2>
 <table class="w-full text-sm">
 <thead><tr class="text-left text-gray-500"><th>Partida</th><th>PTS</th><th>AST</th><th>REB</th><th>EFF</th></tr></thead>
 <tbody>
 <?php foreach ($historico as $h): ?>
 <tr class="border-t dark:border-gray-700">
 <td class="py-1"><?= htmlspecialchars($h['Casa']) ?> x <?= htmlspecialchars($h['Visitante']) ?></td>
 <td><?= $h['Pontos'] ?></td>
 <td><?= $h['Assistencias'] ?></td>
 <td><?= $h['Rebotes'] ?></td>
 <td class="font-semibold"><?= $h['Eficiencia'] ?></td>
</tr>
 <?php endforeach; ?>
 <?php if (!$historico): ?><tr><td colspan="5" class="text-gray-400 py-3">Nenhuma partida registrada.</td></tr><?php endif; ?>
</tbody>
</table>
</div>

 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
 <h2 class="font-bold text-lg mb-4">Shot Chart</h2>
 <svg viewBox="0 0 500 470" class="w-full max-w-sm mx-auto border rounded dark:border-gray-700">
 <rect x="0" y="0" width="500" height="470" fill="#f3f4f6" />
 <rect x="150" y="0" width="200" height="190" fill="none" stroke="#9ca3af" stroke-width="2"/>
 <circle cx="250" cy="190" r="60" fill="none" stroke="#9ca3af" stroke-width="2"/>
 <path d="M 30 0 L 30 300 A 220 220 0 0 0 470 300 L 470 0" fill="none" stroke="#9ca3af" stroke-width="2"/>
 <?php foreach ($shots as $s): ?>
 <?php $cor = str_contains($s['TipoEvento'], 'CESTA') ? '#16a34a': '#dc2626'; ?>
 <circle cx="<?= (float)$s['CoordenadaX'] ?>" cy="<?= (float)$s['CoordenadaY'] ?>" r="6" fill="<?= $cor ?>" fill-opacity="0.75" />
 <?php endforeach; ?>
</svg>
 <p class="text-xs text-gray-400 text-center mt-2">
 <span class="text-green-600 font-semibold">●</span>Convertido &nbsp;
 <span class="text-red-600 font-semibold">●</span>Errado
</p>
 <?php if (!$shots): ?><p class="text-gray-400 text-sm text-center mt-2">Nenhum arremesso registrado ainda.</p><?php endif; ?>
</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
