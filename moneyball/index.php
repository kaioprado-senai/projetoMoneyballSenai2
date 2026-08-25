<?php
require_once __DIR__ . '/includes/auth.php';
header("Location: " . (usuarioLogado() ? "dashboard.php" : "login.php"));
exit;
