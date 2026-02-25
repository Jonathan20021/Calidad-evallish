<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php
function formatDuration($seconds)
{
    if (!$seconds) {
        return 'N/A';
    }
    $seconds = (int) round($seconds);
    $hours = (int) floor($seconds / 3600);
    $minutes = (int) floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    if ($hours > 0) {
        return sprintf('%dh %02dm', $hours, $minutes);
    }
    return sprintf('%02dm %02ds', $minutes, $secs);
}

function pct($value, $total)
{
    if ($total <= 0) {
        return 0;
    }
    return ($value / $total) * 100;
}

$totalEvals = (int) ($overallStats['total_evaluations'] ?? 0);
$avgScore = (float) ($overallStats['avg_score'] ?? 0);
$minScore = (float) ($overallStats['min_score'] ?? 0);
$maxScore = (float) ($overallStats['max_score'] ?? 0);
$passRate = (float) ($overallStats['pass_rate'] ?? 0);
$avgDuration = formatDuration($overallStats['avg_duration'] ?? null);

$bucket95 = (int) ($scoreDistribution['bucket_95'] ?? 0);
$bucket90 = (int) ($scoreDistribution['bucket_90'] ?? 0);
$bucket80 = (int) ($scoreDistribution['bucket_80'] ?? 0);
$bucket70 = (int) ($scoreDistribution['bucket_70'] ?? 0);
$bucket0 = (int) ($scoreDistribution['bucket_0'] ?? 0);
$distributionTotal = $bucket95 + $bucket90 + $bucket80 + $bucket70 + $bucket0;
$selectedCampaignId = (int) ($selectedCampaignId ?? 0);
$selectedCampaignName = $selectedCampaignName ?? null;
$filterQuery = $selectedCampaignId > 0 ? ('?campaign_id=' . $selectedCampaignId) : '';
?>

<div
    class="min-h-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-50 via-white to-sky-50 flex">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1">
        <section class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-500 text-white">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-indigo-100">Control de calidad</p>
                        <h1 class="text-3xl font-semibold sm:text-4xl">Centro de Reportes</h1>
                        <p class="mt-2 text-indigo-100 max-w-2xl">
                            Panorama en tiempo real sobre el rendimiento de agentes, campañas y evaluadores.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>reports/export-pdf<?php echo $filterQuery; ?>"
                            class="inline-flex items-center gap-2 rounded-full bg-white/15 px-5 py-2 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/25">
                            <span>Exportar PDF</span>
                            <span class="text-lg">↗</span>
                        </a>
                        <div
                            class="rounded-full bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-100">
                            Última actualización: <?php echo date('d/m/Y H:i'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 py-8 space-y-8">
            <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <form method="GET" action="<?php echo \App\Config\Config::BASE_URL; ?>reports"
                        class="grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-end w-full lg:max-w-3xl">
                        <div>
                            <label for="campaign_id" class="block text-xs font-semibold uppercase tracking-widest text-gray-500">
                                CampaÃ±a
                            </label>
                            <select id="campaign_id" name="campaign_id"
                                class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="0">Todas las campaÃ±as</option>
                                <?php foreach (($campaigns ?? []) as $campaign): ?>
                                    <?php $campaignId = (int) ($campaign['id'] ?? 0); ?>
                                    <option value="<?php echo $campaignId; ?>" <?php echo $campaignId === $selectedCampaignId ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($campaign['name'] ?? ('CampaÃ±a #' . $campaignId)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            Aplicar filtros
                        </button>
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>reports"
                            class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Limpiar
                        </a>
                    </form>
                    <div class="text-sm text-gray-500">
                        <?php if ($selectedCampaignId > 0): ?>
                            Filtro activo: <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($selectedCampaignName ?: ('CampaÃ±a #' . $selectedCampaignId)); ?></span>
                        <?php else: ?>
                            Mostrando datos globales (todas las campaÃ±as)
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Evaluaciones</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900"><?php echo number_format($totalEvals); ?></p>
                    <p class="mt-1 text-sm text-gray-500">Total registradas</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Promedio global</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900"><?php echo number_format($avgScore, 1); ?>%</p>
                    <p class="mt-1 text-sm text-gray-500">Calificación media</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Tasa de cumplimiento</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900"><?php echo number_format($passRate, 1); ?>%</p>
                    <p class="mt-1 text-sm text-gray-500">Meta ≥ 80%</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Mejor puntuación</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900"><?php echo number_format($maxScore, 1); ?>%</p>
                    <p class="mt-1 text-sm text-gray-500">Record actual</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Duración promedio</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900"><?php echo $avgDuration; ?></p>
                    <p class="mt-1 text-sm text-gray-500">Tiempo de llamada</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Tendencia mensual (6 meses)</h2>
                            <p class="text-sm text-gray-500">Evaluaciones y promedio por periodo</p>
                        </div>
                    </div>
                    <div class="mt-6 grid gap-4 sm:grid-cols-6">
                        <?php if (!empty($monthlyTrend)): ?>
                            <?php foreach ($monthlyTrend as $trend): ?>
                                <?php $height = max(10, min(100, (float) $trend['avg_score'])); ?>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex h-28 w-10 items-end rounded-full bg-indigo-50">
                                        <div class="w-10 rounded-full bg-gradient-to-t from-indigo-500 to-sky-400"
                                            style="height: <?php echo $height; ?>%;"></div>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-600">
                                        <?php echo htmlspecialchars($trend['period']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?php echo number_format($trend['avg_score'], 1); ?>%</p>
                                    <p class="text-xs text-gray-400"><?php echo (int) $trend['total_evaluations']; ?> eval.</p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full rounded-xl bg-gray-50 p-6 text-sm text-gray-500">
                                No hay datos suficientes para mostrar la tendencia.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <h2 class="text-lg font-semibold text-gray-900">Distribución de puntuaciones</h2>
                    <p class="text-sm text-gray-500">Concentración por rangos de calidad</p>
                    <div class="mt-6 space-y-4">
                        <?php
                        $buckets = [
                            ['label' => '95 - 100%', 'value' => $bucket95, 'color' => 'from-emerald-500 to-emerald-400'],
                            ['label' => '90 - 94%', 'value' => $bucket90, 'color' => 'from-sky-500 to-sky-400'],
                            ['label' => '80 - 89%', 'value' => $bucket80, 'color' => 'from-indigo-500 to-indigo-400'],
                            ['label' => '70 - 79%', 'value' => $bucket70, 'color' => 'from-amber-500 to-amber-400'],
                            ['label' => '< 70%', 'value' => $bucket0, 'color' => 'from-rose-500 to-rose-400'],
                        ];
                        ?>
                        <?php foreach ($buckets as $bucket): ?>
                            <?php $percent = pct($bucket['value'], $distributionTotal); ?>
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                                    <span><?php echo $bucket['label']; ?></span>
                                    <span><?php echo $bucket['value']; ?>
                                        (<?php echo number_format($percent, 1); ?>%)</span>
                                </div>
                                <div class="mt-2 h-2 w-full rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-gradient-to-r <?php echo $bucket['color']; ?>"
                                        style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div
                    class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100 xl:col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Rendimiento por campaña</h2>
                            <p class="text-sm text-gray-500">Promedio y volumen de evaluaciones</p>
                        </div>
                    </div>
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Campaña</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Evaluaciones</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Promedio</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Mín</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Máx</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (!empty($campaignStats)): ?>
                                    <?php foreach ($campaignStats as $stat): ?>
                                        <tr class="hover:bg-indigo-50/40">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($stat['campaign_name']); ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                <?php echo (int) $stat['total_evaluations']; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                <?php echo number_format($stat['avg_score'], 1); ?>%
                                            </td>
                                            <td class="px-4 py-3 text-sm text-rose-600">
                                                <?php echo number_format($stat['min_score'], 1); ?>%
                                            </td>
                                            <td class="px-4 py-3 text-sm text-emerald-600">
                                                <?php echo number_format($stat['max_score'], 1); ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                            Sin evaluaciones registradas todavía.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">QA destacados</h2>
                        <?php if (\App\Helpers\Auth::hasPermission('reports.top_evaluators')): ?>
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>reports/top-evaluators"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                Ver Ranking Detallado →
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="mt-6 space-y-4">
                        <?php if (!empty($qaStats)): ?>
                            <?php foreach ($qaStats as $qa): ?>
                                <div class="rounded-xl border border-gray-100 p-4">
                                    <p class="text-sm font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($qa['qa_name']); ?>
                                    </p>
                                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                        <span><?php echo (int) $qa['total_evaluations']; ?> evaluaciones</span>
                                        <span
                                            class="font-semibold text-indigo-600"><?php echo number_format($qa['avg_score'], 1); ?>%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-sky-400"
                                            style="width: <?php echo number_format($qa['avg_score'], 1); ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sin datos de QA disponibles.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <h2 class="text-lg font-semibold text-gray-900">Top 5 campañas</h2>
                    <p class="text-sm text-gray-500">Mejor desempeño promedio</p>
                    <div class="mt-6 space-y-4">
                        <?php if (!empty($topCampaigns)): ?>
                            <?php foreach ($topCampaigns as $index => $camp): ?>
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 text-ellipsis overflow-hidden whitespace-nowrap max-w-[120px]"
                                                title="<?php echo htmlspecialchars($camp['campaign_name']); ?>">
                                                <?php echo htmlspecialchars($camp['campaign_name']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?php echo (int) $camp['total_evaluations']; ?>
                                                evaluaciones</p>
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-indigo-600">
                                        <?php echo number_format($camp['avg_score'], 1); ?>%
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sin datos suficientes.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <h2 class="text-lg font-semibold text-gray-900">Top 5 agentes</h2>
                    <p class="text-sm text-gray-500">Mejor desempeño promedio</p>
                    <div class="mt-6 space-y-4">
                        <?php if (!empty($topAgents)): ?>
                            <?php foreach ($topAgents as $index => $agent): ?>
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 text-ellipsis overflow-hidden whitespace-nowrap max-w-[120px]"
                                                title="<?php echo htmlspecialchars($agent['agent_name']); ?>">
                                                <?php echo htmlspecialchars($agent['agent_name']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?php echo (int) $agent['total_evaluations']; ?>
                                                evaluaciones</p>
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-emerald-600">
                                        <?php echo number_format($agent['avg_score'], 1); ?>%
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sin datos suficientes.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                    <h2 class="text-lg font-semibold text-gray-900">Agentes en riesgo</h2>
                    <p class="text-sm text-gray-500">Promedio más bajo (≥ 3 eval.)</p>
                    <div class="mt-6 space-y-4">
                        <?php if (!empty($bottomAgents)): ?>
                            <?php foreach ($bottomAgents as $index => $agent): ?>
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-rose-100 text-sm font-bold text-rose-700">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 text-ellipsis overflow-hidden whitespace-nowrap max-w-[120px]"
                                                title="<?php echo htmlspecialchars($agent['agent_name']); ?>">
                                                <?php echo htmlspecialchars($agent['agent_name']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?php echo (int) $agent['total_evaluations']; ?>
                                                evaluaciones</p>
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-rose-600">
                                        <?php echo number_format($agent['avg_score'], 1); ?>%
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sin datos suficientes.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-lg shadow-indigo-100 border border-indigo-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Evaluaciones recientes</h2>
                        <p class="text-sm text-gray-500">Últimos 10 registros</p>
                    </div>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        Ver todo →
                    </a>
                </div>
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                    ID</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                    Agente</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                    Campaña</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                    QA</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                    Puntuación</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-500">
                                    Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($recentEvaluations)): ?>
                                <?php foreach ($recentEvaluations as $evaluation): ?>
                                    <tr class="hover:bg-indigo-50/40">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                            #<?php echo (int) $evaluation['id']; ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <?php echo htmlspecialchars($evaluation['agent_name']); ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <?php echo htmlspecialchars($evaluation['campaign_name']); ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <?php echo htmlspecialchars($evaluation['qa_name']); ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-indigo-600">
                                            <?php echo number_format($evaluation['percentage'], 1); ?>%
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($evaluation['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No hay evaluaciones recientes.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
