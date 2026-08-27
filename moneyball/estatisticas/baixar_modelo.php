<?php
/**
 * Disponibiliza a planilha modelo (CSV compatível com Excel) para download,
 * com o cabeçalho de colunas esperado pela importação de estatísticas.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$linhas = [
    ['NomeJogador', 'EquipeCasa', 'EquipeVisitante', 'DataHora', 'Pontos', 'Rebotes', 'Assistencias',
     'Roubos', 'Tocos', 'Turnovers', 'Faltas', 'PlusMinus', 'Cestas2Convertidas', 'Cestas2Tentadas',
     'Cestas3Convertidas', 'Cestas3Tentadas', 'LancesConvertidos', 'LancesTentados'],
    ['João Silva', 'Curitiba Hawks', 'Londrina Storm', '2026-08-19 19:00', '18', '5', '4',
     '2', '0', '1', '3', '6', '5', '9', '2', '4', '2', '2'],
    ['Pedro Souza', 'Londrina Storm', 'Curitiba Hawks', '2026-08-19 19:00', '12', '8', '2',
     '1', '2', '2', '4', '-3', '3', '6', '1', '3', '4', '5'],
];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="modelo_importacao_estatisticas.csv"');

echo "\xEF\xBB\xBF";

$saida = fopen('php://output', 'w');
foreach ($linhas as $linha) {
    fputcsv($saida, $linha, ';');
}
fclose($saida);
exit;
