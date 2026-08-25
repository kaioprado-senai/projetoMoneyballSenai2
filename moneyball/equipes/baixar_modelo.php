<?php
/**
 * Disponibiliza a planilha modelo (CSV compatível com Excel) para download,
 * com o cabeçalho de colunas esperado pela importação de equipes.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$linhas = [
    ['Nome', 'Cidade', 'Tecnico'],
    ['Curitiba Hawks', 'Curitiba', 'Marcos Silva'],
    ['Londrina Storm', 'Londrina', 'Ana Ferreira'],
];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="modelo_importacao_equipes.csv"');

echo "\xEF\xBB\xBF";

$saida = fopen('php://output', 'w');
foreach ($linhas as $linha) {
    fputcsv($saida, $linha, ';');
}
fclose($saida);
exit;
