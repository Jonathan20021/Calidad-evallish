<?php require __DIR__ . '/../layouts/header.php';
$selectedQaId = $selectedQaId ?? null;
$selectedQa = $selectedQa ?? null;
?>

<div
    class="min-h-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-50 via-white to-blue-50 flex">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1">
        <section class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600 text-white">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-blue-100">Reportes Avanzados</p>
                        <h1 class="text-3xl font-bold sm:text-4xl">Top Evaluadores (QA)</h1>
                        <p class="mt-2 text-blue-100 max-w-2xl">
                            Ranking de desempeño y consistencia para el equipo de control de calidad.
                        </p>
                    </div>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>reports"
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20">
                        <span>← Volver a Reportes</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-6 py-8 space-y-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_2fr]">
                <!-- Ranking List -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold text-gray-900 px-2">Ranking de Desempeño</h2>
                    <div class="grid gap-4">
                        <?php if (!empty($qaRanking)): ?>
                            <?php foreach ($qaRanking as $index => $qa): ?>
                                <a href="?qa_id=<?php echo $qa['qa_id']; ?>"
                                    class="block group relative rounded-2xl bg-white p-5 shadow-sm border <?php echo ($selectedQaId == $qa['qa_id']) ? 'border-blue-500 ring-2 ring-blue-500/10' : 'border-slate-200 hover:border-blue-300'; ?> transition-all duration-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full <?php echo $index < 3 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'; ?> text-base font-bold">
                                                <?php echo $index + 1; ?>
                                            </div>
                                            <div>
                                                <h3
                                                    class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                                    <?php echo htmlspecialchars($qa['qa_name']); ?>
                                                </h3>
                                                <p class="text-xs text-slate-500">
                                                    <?php echo (int) $qa['total_evaluations']; ?> evaluaciones realizadas
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-blue-600">
                                                <?php echo number_format($qa['avg_score'], 1); ?>%
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php
                                            echo $qa['consistency_label'] === 'Excelente' ? 'bg-green-100 text-green-800' :
                                                ($qa['consistency_label'] === 'Buena' ? 'bg-blue-100 text-blue-800' :
                                                    ($qa['consistency_label'] === 'Regular' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'));
                                            ?>">
                                                <?php echo $qa['consistency_label']; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500"
                                            style="width: <?php echo $qa['avg_score']; ?>%"></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-2xl bg-slate-50 p-8 text-center border-2 border-dashed border-slate-200">
                                <p class="text-slate-500 font-medium">No hay suficientes datos para generar el ranking.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detailed View -->
                <div class="space-y-6">
                    <?php if ($selectedQa): ?>
                        <div
                            class="rounded-3xl bg-white shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                            <div class="bg-slate-900 p-8 text-white relative">
                                <div class="relative z-10">
                                    <h2 class="text-2xl font-bold">
                                        <?php echo htmlspecialchars($selectedQa['full_name']); ?>
                                    </h2>
                                    <p class="text-slate-400 mt-1 uppercase tracking-widest text-xs font-semibold">Historial
                                        de Evaluaciones</p>

                                    <div class="grid grid-cols-3 gap-6 mt-8">
                                        <div class="bg-white/5 backdrop-blur rounded-2xl p-4 border border-white/10">
                                            <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Promedio
                                            </p>
                                            <p class="text-2xl font-bold mt-1 text-blue-400">
                                                <?php
                                                $qaStat = array_filter($qaRanking, fn($q) => $q['qa_id'] == $selectedQaId);
                                                $qaStat = reset($qaStat);
                                                echo number_format($qaStat['avg_score'] ?? 0, 1);
                                                ?>%
                                            </p>
                                        </div>
                                        <div class="bg-white/5 backdrop-blur rounded-2xl p-4 border border-white/10">
                                            <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Máximo</p>
                                            <p class="text-2xl font-bold mt-1 text-emerald-400">
                                                <?php echo number_format($qaStat['max_score'] ?? 0, 1); ?>%
                                            </p>
                                        </div>
                                        <div class="bg-white/5 backdrop-blur rounded-2xl p-4 border border-white/10">
                                            <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Mínimo</p>
                                            <p class="text-2xl font-bold mt-1 text-rose-400">
                                                <?php echo number_format($qaStat['min_score'] ?? 0, 1); ?>%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="overflow-hidden overflow-x-auto rounded-2xl border border-slate-100">
                                    <table class="min-w-full divide-y divide-slate-100">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                                    Agente / Campaña</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 text-center">
                                                    Puntaje</th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 text-center">
                                                    Fecha</th>
                                                <th
                                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                                    Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <?php if (!empty($qaEvaluations)): ?>
                                                <?php foreach ($qaEvaluations as $eval): ?>
                                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                                        <td class="px-6 py-4">
                                                            <div class="font-bold text-slate-900">
                                                                <?php echo htmlspecialchars($eval['agent_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($eval['campaign_name']); ?>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 text-center">
                                                            <span
                                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?php echo $eval['percentage'] >= 80 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'; ?>">
                                                                <?php echo number_format($eval['percentage'], 1); ?>%
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 text-center text-sm text-slate-500 font-medium">
                                                            <?php echo date('d/m/Y', strtotime($eval['created_at'])); ?>
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/show?id=<?php echo $eval['id']; ?>"
                                                                class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                                                                <span>Ver Trabajo</span>
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                                </svg>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">No hay
                                                        evaluaciones registradas.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div
                            class="h-full flex flex-col items-center justify-center p-12 text-center rounded-3xl bg-slate-50/50 border-2 border-dashed border-slate-200">
                            <div
                                class="h-20 w-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">Selecciona un Evaluador</h3>
                            <p class="text-slate-500 mt-2 max-w-xs mx-auto">
                                Haz clic en un QA del ranking para ver el detalle de su trabajo y comportamiento.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>