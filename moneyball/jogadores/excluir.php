<?php
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$id = (int)($_GET['id'] ?? 0);
$sql = $pdo->prepare("DELETE FROM Jogador WHERE ID = ?");
$sql->execute([$id]);

header("Location: listar.php");
exit;
