<?php
/**
 * Disponibiliza a planilha modelo (CSV compatível com Excel) para download,
 * já com o cabeçalho de colunas esperado pela importação.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirPerfil(['Administrador', 'Comissao']);

$linhas = [
 ['Nome', 'Numero', 'Posicao', 'Altura', 'Peso', 'DataNascimento', 'Equipe'],
 ['Pedro Alves', '10', 'Armador', '1.88', '80.0', '2005-02-10', 'Curitiba Hawks'],
 ['Marcos Lima', '23', 'Ala', '1.96', '90.5', '2004-06-15', 'Curitiba Hawks'],
];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="modelo_importacao_jogadores.csv"');

// BOM UTF-8 para o Excel abrir os acentos corretamente
echo "\xEF\xBB\xBF";

$saida = fopen('php://output', 'w');
foreach ($linhas as $linha) {
 fputcsv($saida, $linha, ';');
}
fclose($saida);
exit;
