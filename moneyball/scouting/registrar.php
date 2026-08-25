<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
exigirPerfil(['Administrador', 'Comissao']);

$idPartida = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("
 SELECT p.*, ec.Nome AS Casa, ev.Nome AS Visitante, ec.ID AS idCasa, ev.ID AS idVisitante
 FROM Partida p
 JOIN Equipe ec ON ec.ID = p.idEquipeCasa
 JOIN Equipe ev ON ev.ID = p.idEquipeVisitante
 WHERE p.ID = ?
");
$sql->execute([$idPartida]);
$partida = $sql->fetch();
if (!$partida) die("Partida não encontrada.");

$jogCasa = $pdo->prepare("SELECT ID, Nome, Numero FROM Jogador WHERE idEquipe = ? ORDER BY Numero");
$jogCasa->execute([$partida['idCasa']]);
$jogCasa = $jogCasa->fetchAll();

$jogVisit = $pdo->prepare("SELECT ID, Nome, Numero FROM Jogador WHERE idEquipe = ? ORDER BY Numero");
$jogVisit->execute([$partida['idVisitante']]);
$jogVisit = $jogVisit->fetchAll();

$pageTitle = "Scouting - ". $partida['Casa']. " x ". $partida['Visitante'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-xl md:text-2xl font-bold mb-1">Scouting ao Vivo</h1>
<p class="text-gray-500 dark:text-gray-400 mb-6"><?= htmlspecialchars($partida['Casa']) ?> x <?= htmlspecialchars($partida['Visitante']) ?></p>

<div x-data="scouting()" class="grid lg:grid-cols-3 gap-6">

 <!-- Seleção de jogador -->
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
 <h2 class="font-bold mb-3"><?= htmlspecialchars($partida['Casa']) ?></h2>
 <div class="grid grid-cols-3 gap-2 mb-6">
 <?php foreach ($jogCasa as $j): ?>
 <button @click="selecionar(<?= $j['ID'] ?>, '<?= htmlspecialchars(addslashes($j['Nome'])) ?>')"
:class="jogadorId === <?= $j['ID'] ?> ? 'bg-blue-600 text-white': 'bg-gray-100 dark:bg-gray-700'"
 class="rounded p-2 text-xs font-semibold">
 #<?= $j['Numero'] ?><br><?= htmlspecialchars($j['Nome']) ?>
</button>
 <?php endforeach; ?>
</div>
 <h2 class="font-bold mb-3"><?= htmlspecialchars($partida['Visitante']) ?></h2>
 <div class="grid grid-cols-3 gap-2">
 <?php foreach ($jogVisit as $j): ?>
 <button @click="selecionar(<?= $j['ID'] ?>, '<?= htmlspecialchars(addslashes($j['Nome'])) ?>')"
:class="jogadorId === <?= $j['ID'] ?> ? 'bg-orange-600 text-white': 'bg-gray-100 dark:bg-gray-700'"
 class="rounded p-2 text-xs font-semibold">
 #<?= $j['Numero'] ?><br><?= htmlspecialchars($j['Nome']) ?>
</button>
 <?php endforeach; ?>
</div>
</div>

 <!-- Botões de evento (máximo 2 toques por lançamento) -->
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
 <p class="mb-3 text-sm">Jogador selecionado:
 <span class="font-bold" x-text="jogadorNome || 'nenhum'"></span>
</p>
 <div class="grid grid-cols-2 gap-2 text-sm">
 <button @click="registrar('CESTA_2', 2)" class="bg-green-600 text-white rounded p-3">Cesta 2pts</button>
 <button @click="registrar('TENTATIVA_2', 0)" class="bg-red-500 text-white rounded p-3">Errou 2pts</button>
 <button @click="registrar('CESTA_3', 3)" class="bg-green-700 text-white rounded p-3">Cesta 3pts</button>
 <button @click="registrar('TENTATIVA_3', 0)" class="bg-red-600 text-white rounded p-3">Errou 3pts</button>
 <button @click="registrar('LANCE_LIVRE_CONVERTIDO', 1)" class="bg-green-400 text-white rounded p-3">Lance livre</button>
 <button @click="registrar('LANCE_LIVRE_TENTATIVA', 0)" class="bg-red-400 text-white rounded p-3">Errou LL</button>
 <button @click="registrar('ASSISTENCIA', 0)" class="bg-blue-600 text-white rounded p-3">Assistência</button>
 <button @click="registrar('REBOTE_OFENSIVO', 0)" class="bg-purple-600 text-white rounded p-3">Reb. Ofensivo</button>
 <button @click="registrar('REBOTE_DEFENSIVO', 0)" class="bg-purple-800 text-white rounded p-3">Reb. Defensivo</button>
 <button @click="registrar('ROUBO', 0)" class="bg-teal-600 text-white rounded p-3">Roubo</button>
 <button @click="registrar('TOCO', 0)" class="bg-indigo-600 text-white rounded p-3">Toco</button>
 <button @click="registrar('TURNOVER', 0)" class="bg-yellow-600 text-white rounded p-3">Turnover</button>
 <button @click="registrar('FALTA', 0)" class="bg-gray-600 text-white rounded p-3 col-span-2">Falta</button>
</div>
 <p class="mt-4 text-xs" x-show="statusMsg" x-text="statusMsg" :class="statusOk ? 'text-green-600' : 'text-red-600'"></p>
</div>

 <!-- Quadra para geolocalização de arremessos -->
 <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
 <h2 class="font-bold mb-3">Localização do Arremesso</h2>
 <p class="text-xs text-gray-400 mb-2">Selecione o jogador, clique no botão do arremesso e depois toque na quadra.</p>
 <svg viewBox="0 0 500 470" class="w-full border rounded dark:border-gray-700 cursor-crosshair" @click="clicouQuadra($event)">
 <rect x="0" y="0" width="500" height="470" fill="#f3f4f6" />
 <rect x="150" y="0" width="200" height="190" fill="none" stroke="#9ca3af" stroke-width="2"/>
 <circle cx="250" cy="190" r="60" fill="none" stroke="#9ca3af" stroke-width="2"/>
 <path d="M 30 0 L 30 300 A 220 220 0 0 0 470 300 L 470 0" fill="none" stroke="#9ca3af" stroke-width="2"/>
 <circle x-show="ultimoClique" :cx="ultimoClique?.x" :cy="ultimoClique?.y" r="7" fill="#2563eb" />
</svg>
 <p class="text-xs text-gray-400 mt-2">Último ponto marcado é enviado automaticamente no próximo arremesso registrado.</p>
</div>
</div>

<script>
function scouting() {
 return {
 jogadorId: null,
 jogadorNome: '',
 ultimoClique: null,
 statusMsg: '',
 statusOk: true,
 idPartida: <?= $idPartida ?>,

 selecionar(id, nome) {
 this.jogadorId = id;
 this.jogadorNome = nome;
 this.statusMsg = '';
 },

 clicouQuadra(e) {
 const svg = e.currentTarget;
 const pt = svg.createSVGPoint();
 pt.x = e.clientX; pt.y = e.clientY;
 const loc = pt.matrixTransform(svg.getScreenCTM().inverse());
 this.ultimoClique = { x: loc.x, y: loc.y };
 },

 registrar(tipo, pontos) {
 if (!this.jogadorId) {
 this.statusMsg = 'Selecione um jogador primeiro.';
 this.statusOk = false;
 return;
 }
 const isArremesso = ['CESTA_2','TENTATIVA_2','CESTA_3','TENTATIVA_3'].includes(tipo);
 const payload = {
 idPartida: this.idPartida,
 idJogador: this.jogadorId,
 tipoEvento: tipo,
 pontos: pontos,
 coordenadaX: isArremesso && this.ultimoClique ? this.ultimoClique.x: null,
 coordenadaY: isArremesso && this.ultimoClique ? this.ultimoClique.y: null
 };

 fetch('<?= caminhoRaiz() ?>api/registrar_evento.php', {
 method: 'POST',
 headers: { 'Content-Type': 'application/json' },
 body: JSON.stringify(payload)
 })
.then(r => r.json())
.then(data => {
 this.statusOk = !!data.sucesso;
 this.statusMsg = data.mensagem;
 if (data.sucesso) {
 this.ultimoClique = null;
 }
 })
.catch(() => {
 this.statusOk = false;
 this.statusMsg = 'Falha de conexão. O evento pode ser reenviado quando a rede voltar (modo offline).';
 });
 }
 }
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
