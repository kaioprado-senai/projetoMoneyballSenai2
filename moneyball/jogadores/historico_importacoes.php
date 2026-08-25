<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

// Esqueleto simples: mostra os últimos jogadores cadastrados como proxy de "última importação".
// Para um histórico completo, crie uma tabela Importacao (ID, Usuario, Arquivo, DataHora, TotalRegistros, Status)
// e grave um registro nela dentro de importar.php a cada upload processado.
$ultimos = $pdo->query("SELECT ID, Nome, Numero FROM Jogador ORDER BY ID DESC LIMIT 20")->fetchAll();

$pageTitle = "Histórico de Importações";
require __DIR__ . '/../includes/header.php';
?>
<h1 class="text-2xl font-bold mb-4">Histórico de Importações</h1>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-xl">Esta é uma versão simplificada mostrando os últimos jogadores cadastrados no sistema.
 Para um log completo de cada importação (arquivo, usuário, data e total de registros),
 crie a tabela <code>Importacao</code> e grave um registro a cada upload em <code>importar.php</code>.
</p>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 max-w-xl">
<ul class="text-sm divide-y dark:divide-gray-700">
<?php foreach ($ultimos as $j): ?>
<li class="py-2">#<?= htmlspecialchars($j['Numero'] ?? '-') ?> — <?= htmlspecialchars($j['Nome']) ?></li>
<?php endforeach; ?>
</ul>
</div>
<a href="importar.php" class="inline-block mt-6 text-blue-600 hover:underline">Voltar para importação</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
