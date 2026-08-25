<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador']);

$busca = trim($_GET['busca'] ?? '');
if ($busca !== '') {
 $sql = $pdo->prepare("SELECT * FROM Usuario WHERE Nome LIKE ? OR Email LIKE ? ORDER BY Nome");
 $sql->execute(["%$busca%", "%$busca%"]);
} else {
 $sql = $pdo->query("SELECT * FROM Usuario ORDER BY Nome");
}
$usuarios = $sql->fetchAll();

$pageTitle = "Usuários";
require __DIR__ . '/../includes/header.php';
?>
<div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold">Usuários</h1>
 <a href="cadastrar.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">+ Novo Usuário</a>
</div>

<form method="GET" class="mb-4">
 <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar por nome ou e-mail..."
 class="border rounded p-2 bg-white text-gray-900 placeholder-gray-400 w-full max-w-sm">
</form>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-100 dark:bg-gray-700 text-left">
<tr><th class="p-3">Nome</th><th class="p-3">E-mail</th><th class="p-3">Perfil</th><th class="p-3">Status</th><th class="p-3">Último acesso</th><th class="p-3">Ações</th></tr>
</thead>
<tbody>
<?php foreach ($usuarios as $u): ?>
<tr class="border-t dark:border-gray-700">
 <td class="p-3"><?= htmlspecialchars($u['Nome']) ?></td>
 <td class="p-3"><?= htmlspecialchars($u['Email']) ?></td>
 <td class="p-3"><?= htmlspecialchars($u['Perfil']) ?></td>
 <td class="p-3"><?= $u['Status'] ? '<span class="text-green-600">Ativo</span>': '<span class="text-red-600">Inativo</span>' ?></td>
 <td class="p-3"><?= $u['UltimoAcesso'] ? htmlspecialchars($u['UltimoAcesso']): '-' ?></td>
 <td class="p-3 space-x-2">
 <a href="editar.php?id=<?= $u['ID'] ?>" class="text-blue-600 hover:underline">Editar</a>
 <a href="excluir.php?id=<?= $u['ID'] ?>" onclick="return confirm('Excluir este usuário?')" class="text-red-600 hover:underline">Excluir</a>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$usuarios): ?><tr><td colspan="6" class="p-4 text-center text-gray-400">Nenhum usuário encontrado.</td></tr><?php endif; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
