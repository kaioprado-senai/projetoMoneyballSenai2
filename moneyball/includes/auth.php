<?php
/**
 * Controle de autenticação e permissões.
 * Login de usuários, controle de acesso e perfis (Administrador, Comissão, Usuário).
 */

require_once __DIR__ . '/../config.php';

function usuarioLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function exigirLogin(): void
{
    if (!usuarioLogado()) {
        header("Location: " . caminhoRaiz() . "login.php");
        exit;
    }
}

/**
 * Restringe a página a um ou mais perfis.
 * Ex: exigirPerfil(['Administrador'])
 */
function exigirPerfil(array $perfisPermitidos): void
{
    exigirLogin();
    if (!in_array($_SESSION['usuario_perfil'], $perfisPermitidos, true)) {
        http_response_code(403);
        die("Acesso negado. Seu perfil (" . htmlspecialchars($_SESSION['usuario_perfil']) . ") não tem permissão para acessar esta página.");
    }
}

function perfilAtual(): ?string
{
    return $_SESSION['usuario_perfil'] ?? null;
}

function podeGerenciarUsuarios(): bool
{
    return perfilAtual() === 'Administrador';
}

function podeEditarDados(): bool
{
    return in_array(perfilAtual(), ['Administrador', 'Comissao'], true);
}

/**
 * Calcula o caminho relativo até a raiz do sistema a partir de qualquer subpasta,
 * para que os links/includes funcionem em usuarios/, jogadores/, etc.
 */
function caminhoRaiz(): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $partes = explode('/', trim($scriptDir, '/'));
    $ultimo = end($partes);
    $subpastas = ['usuarios', 'equipes', 'jogadores', 'partidas', 'scouting', 'estatisticas', 'api'];
    return in_array($ultimo, $subpastas, true) ? '../' : '';
}
