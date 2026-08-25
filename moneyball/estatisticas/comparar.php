<?php
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

/*
|--------------------------------------------------------------------------
| LISTA DE JOGADORES
|--------------------------------------------------------------------------
*/
$jogadores = $pdo->query("
    SELECT 
        j.ID,
        j.Nome,
        j.Numero,
        j.Posicao,
        e.Nome AS EquipeNome
    FROM Jogador j
    LEFT JOIN Equipe e ON e.ID = j.idEquipe
    ORDER BY j.Nome
")->fetchAll();

/*
|--------------------------------------------------------------------------
| JOGADORES SELECIONADOS
|--------------------------------------------------------------------------
*/
$id1 = (int)($_GET['j1'] ?? 0);
$id2 = (int)($_GET['j2'] ?? 0);
$id3 = (int)($_GET['j3'] ?? 0);

/*
|--------------------------------------------------------------------------
| BUSCA DADOS DO JOGADOR
|--------------------------------------------------------------------------
*/
function dadosJogador(PDO $pdo, int $id): ?array
{
    if (!$id) {
        return null;
    }

    $sql = $pdo->prepare("
        SELECT
            j.ID,
            j.Nome,
            j.Numero,
            j.Posicao,
            j.Altura,
            j.Peso,
            e.Nome AS EquipeNome,

            ROUND(AVG(es.Pontos), 1) AS Pontos,
            ROUND(AVG(es.Assistencias), 1) AS Assistencias,
            ROUND(AVG(es.Rebotes), 1) AS Rebotes,
            ROUND(AVG(es.Roubos), 1) AS Roubos,
            ROUND(AVG(es.Tocos), 1) AS Tocos,
            ROUND(AVG(es.Turnovers), 1) AS Turnovers,
            ROUND(AVG(es.Eficiencia), 1) AS Eficiencia,
            ROUND(AVG(es.eFG), 1) AS eFG,
            ROUND(AVG(es.TS), 1) AS TS,
            ROUND(AVG(es.Regularidade), 1) AS Regularidade

        FROM Jogador j

        LEFT JOIN Equipe e
            ON e.ID = j.idEquipe

        LEFT JOIN Estatisticas es
            ON es.idJogador = j.ID

        WHERE j.ID = ?

        GROUP BY
            j.ID,
            j.Nome,
            j.Numero,
            j.Posicao,
            j.Altura,
            j.Peso,
            e.Nome
    ");

    $sql->execute([$id]);

    return $sql->fetch() ?: null;
}

$jogadoresSelecionados = [];

foreach ([$id1, $id2, $id3] as $id) {
    if ($id) {
        $jogador = dadosJogador($pdo, $id);

        if ($jogador) {
            $jogadoresSelecionados[] = $jogador;
        }
    }
}

/*
|--------------------------------------------------------------------------
| FUNÇÃO PARA TRANSFORMAR OS DADOS EM NOTAS DE 0 A 100
|--------------------------------------------------------------------------
|
| O gráfico usa uma escala de 0 a 100.
| Cada estatística é comparada entre os jogadores selecionados.
|--------------------------------------------------------------------------
*/
function valorRadar(?float $valor, array $valores): float
{
    if ($valor === null) {
        return 0;
    }

    $valoresValidos = array_values(
        array_filter($valores, fn($v) => $v !== null)
    );

    if (!$valoresValidos) {
        return 0;
    }

    $maior = max($valoresValidos);

    if ($maior <= 0) {
        return 0;
    }

    return round(($valor / $maior) * 100, 1);
}

/*
|--------------------------------------------------------------------------
| MÉTRICAS DO RADAR
|--------------------------------------------------------------------------
*/
$metricas = [
    'Pontos' => 'Pontuação',
    'Assistencias' => 'Assistências',
    'Rebotes' => 'Rebotes',
    'Roubos' => 'Roubos',
    'Tocos' => 'Tocos',
    'Eficiencia' => 'Eficiência',
    'eFG' => 'eFG%',
    'Regularidade' => 'Regularidade'
];

/*
|--------------------------------------------------------------------------
| PREPARA OS DADOS DOS GRÁFICOS
|--------------------------------------------------------------------------
*/
$dadosRadar = [];

foreach ($jogadoresSelecionados as $index => $jogador) {

    $valores = [];

    foreach ($metricas as $campo => $label) {

        $todosValores = array_map(
            function ($j) use ($campo) {
                return isset($j[$campo]) ? (float)$j[$campo] : null;
            },
            $jogadoresSelecionados
        );

        $valorAtual = isset($jogador[$campo])
            ? (float)$jogador[$campo]
            : null;

        $valores[$campo] = valorRadar(
            $valorAtual,
            $todosValores
        );
    }

    $dadosRadar[] = [
        'nome' => $jogador['Nome'],
        'posicao' => $jogador['Posicao'] ?? '-',
        'equipe' => $jogador['EquipeNome'] ?? 'Sem equipe',
        'numero' => $jogador['Numero'] ?? '-',
        'dados' => array_values($valores)
    ];
}

$pageTitle = "Comparar Jogadores";

require __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold mb-2">
        Comparação de Jogadores
    </h1>

    <p class="text-gray-500 dark:text-gray-400">
        Compare até 3 jogadores utilizando um gráfico de desempenho.
    </p>
</div>

<!--
|--------------------------------------------------------------------------
| SELEÇÃO DOS JOGADORES
|--------------------------------------------------------------------------
-->
<form
    method="GET"
    class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mb-8"
>

    <div class="grid md:grid-cols-3 gap-4">

        <!-- Jogador 1 -->
        <div>
            <label class="block text-sm font-semibold mb-2">
                Jogador 1
            </label>

            <select
                name="j1"
                class="w-full border border-gray-300 rounded-lg p-3
                       bg-white text-gray-900"
            >
                <option value="">Selecione</option>

                <?php foreach ($jogadores as $j): ?>

                    <option
                        value="<?= $j['ID'] ?>"
                        <?= $id1 == $j['ID'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($j['Nome']) ?>
                    </option>

                <?php endforeach; ?>
            </select>
        </div>

        <!-- Jogador 2 -->
        <div>
            <label class="block text-sm font-semibold mb-2">
                Jogador 2
            </label>

            <select
                name="j2"
                class="w-full border border-gray-300 rounded-lg p-3
                       bg-white text-gray-900"
            >
                <option value="">Selecione</option>

                <?php foreach ($jogadores as $j): ?>

                    <option
                        value="<?= $j['ID'] ?>"
                        <?= $id2 == $j['ID'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($j['Nome']) ?>
                    </option>

                <?php endforeach; ?>
            </select>
        </div>

        <!-- Jogador 3 -->
        <div>
            <label class="block text-sm font-semibold mb-2">
                Jogador 3
            </label>

            <select
                name="j3"
                class="w-full border border-gray-300 rounded-lg p-3
                       bg-white text-gray-900"
            >
                <option value="">Selecione</option>

                <?php foreach ($jogadores as $j): ?>

                    <option
                        value="<?= $j['ID'] ?>"
                        <?= $id3 == $j['ID'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($j['Nome']) ?>
                    </option>

                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <div class="mt-5">
        <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700
                   text-white font-semibold
                   px-6 py-3 rounded-lg transition"
        >
            Comparar jogadores
        </button>
    </div>

</form>


<?php if (count($jogadoresSelecionados) >= 2): ?>

<!--
|--------------------------------------------------------------------------
| ÁREA DOS GRÁFICOS
|--------------------------------------------------------------------------
-->

<div class="grid xl:grid-cols-3 gap-6">

    <?php foreach ($jogadoresSelecionados as $index => $jogador): ?>

        <div
            class="bg-white dark:bg-gray-800
                   rounded-2xl shadow p-6"
        >

            <!-- Cabeçalho -->
            <div class="text-center mb-4">

                <h2 class="text-xl font-bold">
                    <?= htmlspecialchars($jogador['Nome']) ?>
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <?= htmlspecialchars($jogador['Posicao'] ?? '-') ?>
                    ·
                    <?= htmlspecialchars($jogador['EquipeNome'] ?? 'Sem equipe') ?>
                </p>

                <span
                    class="inline-block mt-2
                           bg-blue-100 text-blue-700
                           dark:bg-blue-900 dark:text-blue-200
                           px-3 py-1 rounded-full text-xs font-semibold"
                >
                    #<?= htmlspecialchars($jogador['Numero'] ?? '-') ?>
                </span>

            </div>

            <!-- Gráfico -->
            <div class="relative w-full max-w-md mx-auto">

                <canvas
                    id="radar<?= $index ?>"
                ></canvas>

            </div>

        </div>

    <?php endforeach; ?>

</div>


<!--
|--------------------------------------------------------------------------
| COMPARAÇÃO GERAL
|--------------------------------------------------------------------------
-->

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 mt-8">

    <h2 class="text-xl font-bold mb-6">
        Comparação geral
    </h2>

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead>
                <tr
                    class="border-b dark:border-gray-700
                           text-left text-gray-500"
                >

                    <th class="py-3 px-3">
                        Métrica
                    </th>

                    <?php foreach ($jogadoresSelecionados as $jogador): ?>

                        <th class="py-3 px-3 text-center">
                            <?= htmlspecialchars($jogador['Nome']) ?>
                        </th>

                    <?php endforeach; ?>

                </tr>
            </thead>

            <tbody>

            <?php foreach ($metricas as $campo => $label): ?>

                <?php
                $valores = [];

                foreach ($jogadoresSelecionados as $jogador) {
                    $valores[] =
                        $jogador[$campo] !== null
                            ? (float)$jogador[$campo]
                            : null;
                }

                $validos = array_filter(
                    $valores,
                    fn($v) => $v !== null
                );

                $maiorValor = !empty($validos)
                    ? max($validos)
                    : null;
                ?>

                <tr class="border-b dark:border-gray-700">

                    <td class="py-3 px-3 font-semibold">
                        <?= $label ?>
                    </td>

                    <?php foreach ($valores as $valor): ?>

                        <td class="py-3 px-3 text-center
                            <?= (
                                $valor !== null &&
                                $maiorValor !== null &&
                                $valor == $maiorValor
                            )
                                ? 'text-green-600 font-bold'
                                : ''
                            ?>"
                        >

                            <?= $valor !== null
                                ? number_format($valor, 1, ',', '.')
                                : '-'
                            ?>

                        </td>

                    <?php endforeach; ?>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| CHART.JS
|--------------------------------------------------------------------------
-->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels = <?= json_encode(array_values($metricas), JSON_UNESCAPED_UNICODE) ?>;

const jogadores = <?= json_encode(
    $dadosRadar,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?>;


/*
|--------------------------------------------------------------------------
| CRIA UM GRÁFICO PARA CADA JOGADOR
|--------------------------------------------------------------------------
*/

jogadores.forEach((jogador, index) => {

    const canvas =
        document.getElementById('radar' + index);

    if (!canvas) {
        return;
    }

    new Chart(canvas, {

        type: 'radar',

        data: {

            labels: labels,

            datasets: [
                {
                    label: jogador.nome,

                    data: jogador.dados,

                    borderWidth: 2,

                    pointRadius: 3,

                    pointHoverRadius: 5,

                    backgroundColor:
                        index === 0
                            ? 'rgba(37, 99, 235, 0.20)'
                            : index === 1
                                ? 'rgba(16, 185, 129, 0.20)'
                                : 'rgba(239, 68, 68, 0.20)',

                    borderColor:
                        index === 0
                            ? 'rgb(37, 99, 235)'
                            : index === 1
                                ? 'rgb(16, 185, 129)'
                                : 'rgb(239, 68, 68)',

                    pointBackgroundColor:
                        index === 0
                            ? 'rgb(37, 99, 235)'
                            : index === 1
                                ? 'rgb(16, 185, 129)'
                                : 'rgb(239, 68, 68)',

                    pointBorderColor: '#ffffff',

                    pointBorderWidth: 1
                }
            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: true,

            scales: {

                r: {

                    min: 0,

                    max: 100,

                    beginAtZero: true,

                    ticks: {
                        stepSize: 20,
                        display: true
                    },

                    grid: {
                        color: 'rgba(148, 163, 184, 0.35)'
                    },

                    angleLines: {
                        color: 'rgba(148, 163, 184, 0.35)'
                    },

                    pointLabels: {
                        font: {
                            size: 11,
                            weight: '600'
                        }
                    }

                }

            },

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    callbacks: {

                        label: function(context) {

                            return (
                                'Nota: ' +
                                context.parsed.r.toFixed(1)
                            );

                        }

                    }

                }

            }

        }

    });

});

</script>


<?php elseif (count($jogadoresSelecionados) === 1): ?>

    <div
        class="bg-yellow-50 border border-yellow-200
               text-yellow-800 rounded-xl p-5"
    >
        Selecione pelo menos <strong>2 jogadores</strong>
        para gerar a comparação.
    </div>

<?php else: ?>

    <div
        class="bg-white dark:bg-gray-800
               rounded-2xl shadow p-10 text-center"
    >

        <div class="text-5xl mb-4">
            📊
        </div>

        <h2 class="text-xl font-bold mb-2">
            Nenhum jogador selecionado
        </h2>

        <p class="text-gray-500 dark:text-gray-400">
            Escolha de 2 a 3 jogadores acima para visualizar
            os gráficos de comparação.
        </p>

    </div>

<?php endif; ?>


<?php require __DIR__ . '/../includes/footer.php'; ?>