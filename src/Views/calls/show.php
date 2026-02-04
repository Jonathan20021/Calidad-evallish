<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex justify-between items-center">
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls"
                    class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Llamadas
                </a>
            </div>

            <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-8">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Detalle de Llamada #
                            <?php echo $call['id']; ?>
                        </h2>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 <?php echo $call['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                            <?php echo $call['status'] === 'pending' ? 'Pendiente de Evaluación' : 'Evaluada'; ?>
                        </span>
                    </div>
                    <?php if ($call['status'] === 'pending'): ?>
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create?call_id=<?php echo $call['id']; ?>"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow transition duration-200">
                            Evaluar Ahora
                        </a>
                    <?php endif; ?>
                </div>

                <div class="p-8">
                    <div class="bg-gray-900 rounded-xl p-6 mb-8 text-white shadow-inner">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-mono text-gray-400">Audio</span>
                            <span class="text-xs font-mono text-gray-400">
                                <?php echo $call['duration']; ?>
                            </span>
                        </div>
                        <?php if (!empty($call['recording_url'])): ?>
                            <audio controls class="w-full">
                                <source src="<?php echo htmlspecialchars($call['recording_url']); ?>">
                                Tu navegador no soporta audio HTML5.
                            </audio>
                        <?php else: ?>
                            <div class="text-sm text-gray-300">Sin grabación disponible.</div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Información
                                del Agente</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-500 block">Nombre</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($call['agent']); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Proyecto</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($call['project'] ?? ''); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Campaña</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($call['campaign']); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Tipo de llamada</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($call['call_type'] ?? ''); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Fecha / Hora</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo $call['date']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Datos del
                                Cliente</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-500 block">Teléfono</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($call['customer_phone'] ?? ''); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Lead</span>
                                    <span class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($call['lead'] ?? ''); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Notas de la llamada</span>
                                    <p
                                        class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 mt-1">
                                        "<?php echo htmlspecialchars($call['notes'] ?? ''); ?>"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">Evaluación</h3>
                        <?php if (!empty($call['evaluation_id'])): ?>
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/show?id=<?php echo $call['evaluation_id']; ?>"
                                class="text-indigo-600 hover:text-indigo-900 font-medium">Ver evaluación
                                #<?php echo $call['evaluation_id']; ?></a>
                        <?php else: ?>
                            <div class="text-sm text-gray-600 mb-2">Esta llamada aún no tiene evaluación.</div>
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create?call_id=<?php echo $call['id']; ?>"
                                class="text-indigo-600 hover:text-indigo-900 font-medium">Crear evaluación</a>
                    <?php endif; ?>
                    </div>

                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">Analiticas de Calidad (IA)</h3>
                        <?php if (!empty($aiAnalyticsError)): ?>
                            <div class="text-sm text-red-600" id="ai-analytics-error">
                                No se pudo generar el analisis IA: <?php echo htmlspecialchars($aiAnalyticsError); ?>
                            </div>
                        <?php elseif (!empty($aiAnalytics)): ?>
                            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-4">
                                <div class="text-sm text-gray-500">Modelo</div>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars(\App\Config\Config::$GEMINI_MODEL); ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="ai-analytics-summary">
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500">Puntaje general</div>
                                    <div class="text-2xl font-bold text-indigo-600">
                                        <span id="ai-analytics-score"><?php echo htmlspecialchars($aiAnalytics['overall_score'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500">Sentimiento</div>
                                    <div class="text-lg font-semibold text-gray-900">
                                        <span id="ai-analytics-sentiment"><?php echo htmlspecialchars($aiAnalytics['sentiment'] ?? 'desconocido'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 bg-white border border-gray-100 rounded-xl p-4">
                                <div class="text-sm text-gray-500 mb-2">Resumen</div>
                                <div class="text-sm text-gray-800">
                                    <span id="ai-analytics-summary-text"><?php echo htmlspecialchars($aiAnalytics['summary'] ?? 'Sin resumen disponible.'); ?></span>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Punto de vista</div>
                                    <div class="text-sm text-gray-800">
                                        <span id="ai-analytics-point"><?php echo htmlspecialchars($aiAnalytics['punto_de_vista'] ?? 'Sin punto de vista disponible.'); ?></span>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">An�lisis</div>
                                    <div class="text-sm text-gray-800">
                                        <span id="ai-analytics-analysis"><?php echo htmlspecialchars($aiAnalytics['analisis'] ?? 'Sin an�lisis disponible.'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($aiAnalytics['compliance']) && is_array($aiAnalytics['compliance'])): ?>
                                <div class="mt-4 bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Cumplimiento</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm" id="ai-analytics-compliance">
                                        <?php foreach ($aiAnalytics['compliance'] as $key => $value): ?>
                                            <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-lg px-3 py-2">
                                                <span class="text-gray-600"><?php echo htmlspecialchars((string) $key); ?></span>
                                                <span class="font-semibold text-gray-900"><?php echo htmlspecialchars((string) $value); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mt-4 bg-white border border-gray-100 rounded-xl p-4 hidden" id="ai-analytics-compliance-wrap">
                                    <div class="text-sm text-gray-500 mb-2">Cumplimiento</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm" id="ai-analytics-compliance"></div>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Fortalezas</div>
                                    <?php if (!empty($aiAnalytics['agent_strengths']) && is_array($aiAnalytics['agent_strengths'])): ?>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1" id="ai-analytics-strengths">
                                            <?php foreach ($aiAnalytics['agent_strengths'] as $item): ?>
                                                <li><?php echo htmlspecialchars((string) $item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-600" id="ai-analytics-strengths-empty">Sin datos.</div>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1 hidden" id="ai-analytics-strengths"></ul>
                                    <?php endif; ?>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Oportunidades</div>
                                    <?php if (!empty($aiAnalytics['agent_opportunities']) && is_array($aiAnalytics['agent_opportunities'])): ?>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1" id="ai-analytics-opportunities">
                                            <?php foreach ($aiAnalytics['agent_opportunities'] as $item): ?>
                                                <li><?php echo htmlspecialchars((string) $item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-600" id="ai-analytics-opportunities-empty">Sin datos.</div>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1 hidden" id="ai-analytics-opportunities"></ul>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Issues criticos</div>
                                    <?php if (!empty($aiAnalytics['critical_issues']) && is_array($aiAnalytics['critical_issues'])): ?>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1" id="ai-analytics-critical">
                                            <?php foreach ($aiAnalytics['critical_issues'] as $item): ?>
                                                <li><?php echo htmlspecialchars((string) $item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-600" id="ai-analytics-critical-empty">Sin issues criticos.</div>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1 hidden" id="ai-analytics-critical"></ul>
                                    <?php endif; ?>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Coaching</div>
                                    <?php if (!empty($aiAnalytics['coaching_tips']) && is_array($aiAnalytics['coaching_tips'])): ?>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1" id="ai-analytics-coaching">
                                            <?php foreach ($aiAnalytics['coaching_tips'] as $item): ?>
                                                <li><?php echo htmlspecialchars((string) $item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-600" id="ai-analytics-coaching-empty">Sin recomendaciones.</div>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1 hidden" id="ai-analytics-coaching"></ul>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Tags</div>
                                    <?php if (!empty($aiAnalytics['call_tags']) && is_array($aiAnalytics['call_tags'])): ?>
                                        <div class="flex flex-wrap gap-2" id="ai-analytics-tags">
                                            <?php foreach ($aiAnalytics['call_tags'] as $item): ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    <?php echo htmlspecialchars((string) $item); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-600" id="ai-analytics-tags-empty">Sin tags.</div>
                                        <div class="flex flex-wrap gap-2 hidden" id="ai-analytics-tags"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl p-4">
                                    <div class="text-sm text-gray-500 mb-2">Next Best Actions</div>
                                    <?php if (!empty($aiAnalytics['next_best_actions']) && is_array($aiAnalytics['next_best_actions'])): ?>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1" id="ai-analytics-actions">
                                            <?php foreach ($aiAnalytics['next_best_actions'] as $item): ?>
                                                <li><?php echo htmlspecialchars((string) $item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-600" id="ai-analytics-actions-empty">Sin acciones sugeridas.</div>
                                        <ul class="text-sm text-gray-800 list-disc list-inside space-y-1 hidden" id="ai-analytics-actions"></ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-600 mb-3" id="ai-analytics-empty">Sin analiticas IA disponibles.</div>
                        <?php endif; ?>
                        <?php if (!empty($call['recording_url'])): ?>
                            <div class="mt-4">
                                <a id="ai-analytics-button" href="<?php echo \App\Config\Config::BASE_URL; ?>calls/analyze?id=<?php echo $call['id']; ?>"
                                    class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                                    data-loading="0">
                                    <span id="ai-analytics-button-text"><?php echo !empty($aiAnalytics) ? 'Actualizar analisis IA' : 'Generar analisis IA'; ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
<script>
    (function () {
        var button = document.getElementById('ai-analytics-button');
        if (!button) return;

        button.addEventListener('click', function (e) {
            e.preventDefault();
            if (button.getAttribute('data-loading') === '1') return;

            var baseUrl = "<?php echo \App\Config\Config::BASE_URL; ?>";
            var callId = "<?php echo $call['id']; ?>";
            var targetUrl = baseUrl + "calls/analyze?id=" + encodeURIComponent(callId);

            button.setAttribute('data-loading', '1');
            var buttonText = document.getElementById('ai-analytics-button-text');
            if (buttonText) {
                buttonText.textContent = 'Generando...';
            }

            var errorEl = document.getElementById('ai-analytics-error');
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }

            fetch(targetUrl, { credentials: 'same-origin' })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    if (!payload || payload.success !== true) {
                        throw new Error(payload && payload.error ? payload.error : 'No se pudo generar el analisis IA.');
                    }

                    var data = payload.data || {};
                    var scoreEl = document.getElementById('ai-analytics-score');
                    var sentimentEl = document.getElementById('ai-analytics-sentiment');
                    var summaryEl = document.getElementById('ai-analytics-summary-text');
                    var pointEl = document.getElementById('ai-analytics-point');
                    var analysisEl = document.getElementById('ai-analytics-analysis');
                    if (scoreEl) scoreEl.textContent = (data.overall_score ?? 'N/A');
                    if (sentimentEl) sentimentEl.textContent = (data.sentiment ?? 'desconocido');
                    if (summaryEl) summaryEl.textContent = (data.summary ?? 'Sin resumen disponible.');
                    if (pointEl) pointEl.textContent = (data.punto_de_vista ?? 'Sin punto de vista disponible.');
                    if (analysisEl) analysisEl.textContent = (data.analisis ?? 'Sin an�lisis disponible.');

                    renderList('ai-analytics-strengths', 'ai-analytics-strengths-empty', data.agent_strengths);
                    renderList('ai-analytics-opportunities', 'ai-analytics-opportunities-empty', data.agent_opportunities);
                    renderList('ai-analytics-critical', 'ai-analytics-critical-empty', data.critical_issues, 'Sin issues criticos.');
                    renderList('ai-analytics-coaching', 'ai-analytics-coaching-empty', data.coaching_tips, 'Sin recomendaciones.');
                    renderList('ai-analytics-actions', 'ai-analytics-actions-empty', data.next_best_actions, 'Sin acciones sugeridas.');
                    renderTags('ai-analytics-tags', 'ai-analytics-tags-empty', data.call_tags);
                    renderCompliance(data.compliance);

                    var emptyEl = document.getElementById('ai-analytics-empty');
                    if (emptyEl) emptyEl.classList.add('hidden');
                })
                .catch(function (err) {
                    var errorTarget = document.getElementById('ai-analytics-error');
                    if (!errorTarget) {
                        errorTarget = document.createElement('div');
                        errorTarget.id = 'ai-analytics-error';
                        errorTarget.className = 'text-sm text-red-600 mt-2';
                        button.parentNode.insertBefore(errorTarget, button.parentNode.firstChild);
                    }
                    errorTarget.textContent = 'No se pudo generar el analisis IA: ' + err.message;
                    errorTarget.classList.remove('hidden');
                })
                .finally(function () {
                    button.setAttribute('data-loading', '0');
                    if (buttonText) {
                        buttonText.textContent = 'Actualizar analisis IA';
                    }
                });
        });

        function renderList(listId, emptyId, items, emptyText) {
            var listEl = document.getElementById(listId);
            var emptyEl = document.getElementById(emptyId);
            if (!listEl || !emptyEl) return;
            listEl.innerHTML = '';
            if (Array.isArray(items) && items.length > 0) {
                items.forEach(function (item) {
                    var li = document.createElement('li');
                    li.textContent = String(item);
                    listEl.appendChild(li);
                });
                listEl.classList.remove('hidden');
                emptyEl.classList.add('hidden');
            } else {
                emptyEl.textContent = emptyText || emptyEl.textContent;
                emptyEl.classList.remove('hidden');
                listEl.classList.add('hidden');
            }
        }

        function renderTags(containerId, emptyId, items) {
            var container = document.getElementById(containerId);
            var emptyEl = document.getElementById(emptyId);
            if (!container || !emptyEl) return;
            container.innerHTML = '';
            if (Array.isArray(items) && items.length > 0) {
                items.forEach(function (item) {
                    var tag = document.createElement('span');
                    tag.className = 'px-2 py-1 text-xs rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100';
                    tag.textContent = String(item);
                    container.appendChild(tag);
                });
                container.classList.remove('hidden');
                emptyEl.classList.add('hidden');
            } else {
                emptyEl.classList.remove('hidden');
                container.classList.add('hidden');
            }
        }

        function renderCompliance(compliance) {
            var wrap = document.getElementById('ai-analytics-compliance-wrap');
            var container = document.getElementById('ai-analytics-compliance');
            if (!container) return;
            container.innerHTML = '';
            if (compliance && typeof compliance === 'object') {
                Object.keys(compliance).forEach(function (key) {
                    var row = document.createElement('div');
                    row.className = 'flex items-center justify-between bg-gray-50 border border-gray-100 rounded-lg px-3 py-2';
                    var left = document.createElement('span');
                    left.className = 'text-gray-600';
                    left.textContent = key;
                    var right = document.createElement('span');
                    right.className = 'font-semibold text-gray-900';
                    right.textContent = String(compliance[key]);
                    row.appendChild(left);
                    row.appendChild(right);
                    container.appendChild(row);
                });
                if (wrap) wrap.classList.remove('hidden');
            } else {
                if (wrap) wrap.classList.add('hidden');
            }
        }
    })();
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>




