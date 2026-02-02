<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex justify-between items-center">
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations"
                    class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Evaluaciones
                </a>
                <div class="flex space-x-3">
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/edit?id=<?php echo $evaluation['id']; ?>"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5h2m-1-1v2m-7 7l9.586-9.586a2 2 0 012.828 0L20 6.414a2 2 0 010 2.828L10.414 19H6v-4.414z" />
                        </svg>
                        Editar
                    </a>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/export-pdf?id=<?php echo $evaluation['id']; ?>"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Exportar PDF
                    </a>
                    <button onclick="window.print()"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir
                    </button>
                </div>
            </div>

            <!-- Header Card -->
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h2 class="text-3xl font-bold">
                                <?php echo htmlspecialchars($evaluation['agent_name']); ?>
                            </h2>
                            <p class="text-indigo-100 mt-1">
                                <?php echo htmlspecialchars($evaluation['campaign_name']); ?> |
                                <?php echo htmlspecialchars($evaluation['form_title']); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="inline-block bg-white/20 rounded-lg px-4 py-2 backdrop-blur-sm">
                                <span class="block text-xs uppercase tracking-wide opacity-80">Calificación Final</span>
                                <span class="block text-4xl font-extrabold">
                                    <?php echo number_format($evaluation['percentage'], 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-8 py-6 bg-white grid grid-cols-1 md:grid-cols-3 gap-6 border-b border-gray-100">
                    <div>
                        <span
                            class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Evaluador</span>
                        <div class="flex items-center">
                            <div
                                class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs mr-2">
                                <?php echo substr($evaluation['qa_name'], 0, 1); ?>
                            </div>
                            <span class="text-gray-900 font-medium">
                                <?php echo htmlspecialchars($evaluation['qa_name']); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Fecha de
                            Evaluación</span>
                        <span class="text-gray-900 font-medium">
                            <?php echo date('d/m/Y H:i', strtotime($evaluation['created_at'])); ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Duración
                            Llamada</span>
                        <span class="text-gray-900 font-medium tracking-wide">
                            <?php echo htmlspecialchars($evaluation['call_duration_formatted'] ?? '--:--'); ?>
                        </span>
                    </div>
                </div>
            
            <?php if (!empty($evaluation['feedback_confirmed']) || !empty($evaluation['feedback_evidence_path']) || !empty($evaluation['feedback_evidence_note'])): ?>
                <div class="bg-white shadow-lg rounded-2xl overflow-hidden mb-8">
                    <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Feedback confirmado</h3>
                    </div>
                    <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Estado</span>
                            <?php if (!empty($evaluation['feedback_confirmed'])): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">Realizado</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">Pendiente</span>
                            <?php endif; ?>
                            <?php if (!empty($evaluation['feedback_confirmed_at'])): ?>
                                <div class="text-xs text-gray-500 mt-2">
                                    <?php echo date('d/m/Y H:i', strtotime($evaluation['feedback_confirmed_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Evidencia</span>
                            <?php if (!empty($evaluation['feedback_evidence_url'])): ?>
                                <a class="text-indigo-600 hover:text-indigo-800 font-medium" href="<?php echo htmlspecialchars($evaluation['feedback_evidence_url']); ?>" target="_blank" rel="noopener">
                                    <?php echo htmlspecialchars($evaluation['feedback_evidence_name'] ?? 'Ver archivo'); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">No adjunta</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Nota</span>
                            <?php if (!empty($evaluation['feedback_evidence_note'])): ?>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($evaluation['feedback_evidence_note']); ?></p>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">Sin nota</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?></div>

            <!-- Scorecard Details -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">Detalle de la Evaluación</h3>
                    <span class="text-sm text-gray-500">Desglose por ítems</span>
                </div>

                <div class="divide-y divide-gray-100">
                    <?php foreach ($answers as $answer): ?>
                        <?php $maxScore = isset($answer['max_score']) ? (float) $answer['max_score'] : 100.0; ?>
                        <?php if ($maxScore <= 0): ?>
                            <?php $maxScore = 100.0; ?>
                        <?php endif; ?>
                        <div class="px-8 py-6 hover:bg-gray-50 transition duration-150 relative group">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h4 class="text-lg font-semibold text-gray-900 mr-3">
                                            <?php echo htmlspecialchars($answer['field_label']); ?>
                                        </h4>
                                        <!-- Badge for type/weight -->
                                        <span
                                            class="px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600 border border-gray-200">
                                            Peso:
                                            <?php echo number_format($answer['field_weight'], 0); ?>
                                        </span>
                                    </div>

                                    <!-- Comment if exists -->
                                    <?php if (!empty($answer['comment'])): ?>
                                        <div
                                            class="mt-2 text-sm text-gray-600 bg-yellow-50 border-l-4 border-yellow-400 p-3 italic">
                                            "
                                            <?php echo htmlspecialchars($answer['comment']); ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="ml-6 text-right">
                                    <span class="block text-xs uppercase text-gray-500 font-semibold mb-1">Resultado</span>

                                    <?php if ($answer['field_type'] === 'yes_no'): ?>
                                        <?php if ($answer['score_given'] == 100): ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                ✅ CUMPLE
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                                ❌ NO CUMPLE
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($answer['field_type'] === 'score'): ?>
                                        <span
                                            class="text-2xl font-bold <?php echo $answer['score_given'] >= 80 ? 'text-green-600' : 'text-orange-600'; ?>">
                                            <?php echo number_format($answer['score_given'], 0); ?>
                                            <span class="text-sm text-gray-400 font-normal">/ <?php echo number_format($maxScore, 0); ?></span>
                                        </span>
                                    <?php elseif ($answer['field_type'] === 'select'): ?>
                                        <?php $displayValue = $answer['text_answer'] ?? $answer['score_given']; ?>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <?php echo htmlspecialchars($displayValue); ?>
                                        </span>
                                    <?php elseif ($answer['field_type'] === 'text'): ?>
                                        <?php $displayValue = $answer['text_answer'] ?? $answer['score_given']; ?>
                                        <div class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-md px-3 py-2 max-w-xs">
                                            <?php echo nl2br(htmlspecialchars($displayValue)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php
            $actionTypeLabel = '';
            if (!empty($evaluation['action_type'])) {
                $actionTypeLabel = $evaluation['action_type'] === 'feedback' ? 'Feedback' : 'EvaluaciÃ³n de llamada';
            }
            $hasActionDetails = !empty($evaluation['action_type']) || !empty($evaluation['improvement_areas']) || !empty($evaluation['improvement_plan']) || !empty($evaluation['tasks_commitments']);
            ?>
            <?php if ($hasActionDetails): ?>
                <div class="bg-white shadow-lg rounded-2xl overflow-hidden mt-8">
                    <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">AcciÃ³n y plan de mejora</h3>
                    </div>
                    <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Tipo de acciÃ³n</span>
                            <?php if (!empty($actionTypeLabel)): ?>
                                <span class="text-gray-900 font-medium"><?php echo $actionTypeLabel; ?></span>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">No registrado</span>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Ãreas de mejora</span>
                            <?php if (!empty($evaluation['improvement_areas'])): ?>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($evaluation['improvement_areas']); ?></p>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">No registrado</span>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Plan de mejora sugerido</span>
                            <?php if (!empty($evaluation['improvement_plan'])): ?>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($evaluation['improvement_plan']); ?></p>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">No registrado</span>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1">Tareas / compromisos</span>
                            <?php if (!empty($evaluation['tasks_commitments'])): ?>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($evaluation['tasks_commitments']); ?></p>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">No registrado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- General Feedback -->
            <?php if (!empty($evaluation['general_comments'])): ?>
                <div class="bg-white shadow-lg rounded-2xl overflow-hidden mt-8">
                    <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Feedback General</h3>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">
                            <?php echo htmlspecialchars($evaluation['general_comments']); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

