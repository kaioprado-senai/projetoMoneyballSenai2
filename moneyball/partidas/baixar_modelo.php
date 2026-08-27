<?php
/**
 * Disponibiliza a planilha modelo (CSV compatível com Excel) para download,
 * com o cabeçalho de colunas esperado pela importação de partidas.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$linhas = [
 ['DataHora', 'Local', 'EquipeCasa', 'EquipeVisitante', 'PlacarCasa', 'PlacarVisitante', 'Status'],
 ['2026-08-19 19:00', 'Ginásio SENAI - Curitiba', 'Curitiba Hawks', 'Londrina Storm', '0', '0', 'Agendada'],
 ['2026-08-22 20:30', 'Ginásio Municipal - Londrina', 'Londrina Storm', 'Curitiba Hawks', '78', '81', 'Finalizada'],
];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="modelo_importacao_partidas.csv"');

echo "\xEF\xBB\xBF";

$saida = fopen('php://output', 'w');
foreach ($linhas as $linha) {
 fputcsv($saida, $linha, ';');
}
fclose($saida);
exit;
