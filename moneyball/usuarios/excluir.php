<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador']);

$id = (int)($_GET['id'] ?? 0);

if ($id === (int)$_SESSION['usuario_id']) {
 die("Você não pode excluir o próprio usuário logado.");
}

$sql = $pdo->prepare("DELETE FROM Usuario WHERE ID = ?");
$sql->execute([$id]);

header("Location: listar.php");
exit;
