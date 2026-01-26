<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($client['name'] ?? 'Cliente'); ?></h1>
                    <p class="mt-1 text-sm text-gray-500">Portal corporativo con visibilidad ejecutiva de calidad.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? 'Usuario'); ?></p>
                        <p class="text-xs text-gray-500">Usuario corporativo</p>
                    </div>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>logout"
                        class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Cerrar sesión</a>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
            <?php
            $callCountValue = (int) ($callCount ?? 0);
            $totalEvaluationsValue = (int) ($totalEvaluations ?? 0);
            $pendingEvaluationsValue = (int) ($pendingEvaluations ?? max(0, $callCountValue - $totalEvaluationsValue));
            $complianceValue = isset($complianceRate) ? max(0, min(100, (float) $complianceRate)) : 0;
            $coverageValue = isset($evaluationCoverage) ? max(0, min(100, (float) $evaluationCoverage)) : 0;
            $avgScoreValue = isset($evaluationStats['avg_percentage']) ? (float) $evaluationStats['avg_percentage'] : 0;
            $avgScoreText = $avgScoreValue > 0 ? number_format($avgScoreValue, 1) . '%' : 'N/D';
            $criticalFailsValue = (int) ($criticalFails ?? 0);
            $avgDurationValue = isset($avgDuration) && $avgDuration !== null ? (int) round((float) $avgDuration) : null;
            $avgDurationText = $avgDurationValue !== null ? sprintf('%02d:%02d', floor($avgDurationValue / 60), $avgDurationValue % 60) : 'N/D';
            ?>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Graficos ejecutivos</h2>
                    <p class="text-sm text-gray-500">Vista rapida de calidad, cobertura y carga.</p>
                </div>
                <div class="text-xs text-gray-400">Actualizado con los datos actuales</div>
            </div>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Cumplimiento</h2>
                            <p class="text-sm text-gray-500">Meta >= 85%</p>
                        </div>
                        <span class="text-xs text-gray-400">Calidad</span>
                    </div>
                    <div class="mt-6 flex items-center gap-6">
                        <div class="relative w-24 h-24 rounded-full"
                            style="background: conic-gradient(#4f46e5 <?php echo $complianceValue; ?>%, #e5e7eb 0);">
                            <div class="absolute inset-3 bg-white rounded-full flex items-center justify-center">
                                <span class="text-lg font-bold text-gray-900"><?php echo number_format($complianceValue, 0); ?>%</span>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Promedio</span>
                                <span class="font-semibold text-gray-900"><?php echo $avgScoreText; ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Fallos criticos</span>
                                <span class="font-semibold text-gray-900"><?php echo $criticalFailsValue; ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Duracion media</span>
                                <span class="font-semibold text-gray-900"><?php echo $avgDurationText; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Cobertura de evaluacion</h2>
                            <p class="text-sm text-gray-500">Evaluadas vs total de llamadas</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span><?php echo $totalEvaluationsValue; ?> evaluaciones</span>
                            <span class="font-semibold text-gray-900"><?php echo number_format($coverageValue, 1); ?>%</span>
                        </div>
                        <div class="mt-3 h-3 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-blue-400"
                                style="width: <?php echo $coverageValue; ?>%"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                                <p class="text-gray-500">Total llamadas</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $callCountValue; ?></p>
                            </div>
                            <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                                <p class="text-gray-500">Pendientes</p>
                                <p class="text-lg font-semibold text-gray-900"><?php echo $pendingEvaluationsValue; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Volumen operativo</h2>
                            <p class="text-sm text-gray-500">Estado general de la carga</p>
                        </div>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span>Evaluadas</span>
                                <span class="font-semibold text-gray-900"><?php echo $totalEvaluationsValue; ?></span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full bg-emerald-500"
                                    style="width: <?php echo $callCountValue > 0 ? ($totalEvaluationsValue / $callCountValue) * 100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span>Pendientes</span>
                                <span class="font-semibold text-gray-900"><?php echo $pendingEvaluationsValue; ?></span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full bg-amber-500"
                                    style="width: <?php echo $callCountValue > 0 ? ($pendingEvaluationsValue / $callCountValue) * 100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                            <p class="text-xs uppercase tracking-widest text-indigo-400">Capacidad</p>
                            <p class="text-2xl font-bold text-indigo-700 mt-2"><?php echo $callCountValue; ?> llamadas</p>
                            <p class="text-sm text-indigo-600">Total monitoreadas</p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Campañas habilitadas</h2>
                        <p class="text-sm text-gray-500">Filtradas según el acceso configurado.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php if (empty($campaigns)): ?>
                            <span class="text-sm text-gray-400">No hay campañas asignadas.</span>
                        <?php else: ?>
                            <?php foreach ($campaigns as $campaign): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <?php echo htmlspecialchars($campaign['name']); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($metricCards)): ?>
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm md:col-span-2">
                        <h2 class="text-lg font-semibold text-gray-900">Métricas personalizadas</h2>
                        <p class="text-sm text-gray-500 mt-2">Aún no hay KPIs seleccionados para este cliente.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($metricCards as $card): ?>
                        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                            <p class="text-xs uppercase tracking-widest text-gray-400"><?php echo htmlspecialchars($card['label']); ?></p>
                            <p class="text-3xl font-bold text-gray-900 mt-3"><?php echo htmlspecialchars($card['value']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <?php if (!empty($settings['show_evaluations'])): ?>
                <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Desempeño por campaña</h2>
                            <p class="text-sm text-gray-500">Promedio de calidad y volumen de evaluaciones.</p>
                        </div>
                    </div>
                    <?php if (empty($campaignPerformance)): ?>
                        <p class="text-sm text-gray-400 mt-4">No hay evaluaciones disponibles.</p>
                    <?php else: ?>
                        <div class="mt-6 space-y-4">
                            <?php foreach ($campaignPerformance as $row): ?>
                                <?php
                                $avg = $row['avg_percentage'] !== null ? round((float) $row['avg_percentage'], 1) : 0;
                                $width = max(5, min(100, $avg));
                                ?>
                                <div>
                                    <div class="flex items-center justify-between text-sm text-gray-700">
                                        <span class="font-medium"><?php echo htmlspecialchars($row['name']); ?></span>
                                        <span class="text-xs text-gray-500"><?php echo number_format($avg, 1); ?>% - <?php echo (int) $row['total_evaluations']; ?> evals</span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-blue-400" style="width: <?php echo $width; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if (!empty($settings['show_agent_scores'])): ?>
                <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Top agentes</h2>
                    <p class="text-sm text-gray-500">Ranking por promedio de calidad.</p>
                    <?php if (empty($topAgents)): ?>
                        <p class="text-sm text-gray-400 mt-4">No hay datos suficientes.</p>
                    <?php else: ?>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agente</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($topAgents as $agent): ?>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($agent['full_name']); ?></td>
                                            <td class="px-4 py-2 text-sm text-gray-600"><?php echo number_format((float) $agent['avg_score'], 1); ?>%</td>
                                            <td class="px-4 py-2 text-sm text-gray-600"><?php echo (int) $agent['total']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if (!empty($settings['show_calls'])): ?>
                <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Llamadas recientes</h2>
                            <p class="text-sm text-gray-500">Últimas interacciones monitoreadas.</p>
                        </div>
                    </div>
                    <?php if (empty($calls)): ?>
                        <p class="text-sm text-gray-400 mt-4">No hay llamadas para mostrar.</p>
                    <?php else: ?>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agente</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaña</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                        <?php if (!empty($settings['show_ai_summary'])): ?>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resumen IA</th>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['show_recordings'])): ?>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grabación</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($calls as $call): ?>
                                        <?php
                                        $score = $call['evaluation_percentage'] !== null ? number_format((float) $call['evaluation_percentage'], 1) . '%' : 'Pendiente';
                                        $summary = $call['ai_summary'] ?? '';
                                        $shortSummary = $summary !== '' ? substr($summary, 0, 120) . (strlen($summary) > 120 ? '...' : '') : 'Sin resumen';
                                        ?>
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($call['date']); ?></td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($call['agent_name']); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($call['campaign_name']); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($call['duration']); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                <?php if ($call['evaluation_percentage'] !== null): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <?php echo $score; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-500">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (!empty($settings['show_ai_summary'])): ?>
                                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($shortSummary); ?></td>
                                            <?php endif; ?>
                                            <?php if (!empty($settings['show_recordings'])): ?>
                                                <td class="px-4 py-3 text-sm">
                                                    <?php if (!empty($call['recording_url'])): ?>
                                                        <audio controls preload="none" class="w-48">
                                                            <source src="<?php echo htmlspecialchars($call['recording_url']); ?>">
                                                            Tu navegador no soporta audio HTML5.
                                                        </audio>
                                                    <?php else: ?>
                                                        <span class="text-gray-400 text-sm">No disponible</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
