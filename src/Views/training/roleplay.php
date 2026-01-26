<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Roleplay IA</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php echo htmlspecialchars($roleplay['script_title'] ?? 'Sesion'); ?>
                    </p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>training"
                    class="text-sm text-gray-500 hover:text-gray-700">Volver</a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white shadow-sm rounded-xl border border-gray-200 flex flex-col h-[70vh]">
                <div id="chat-container" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <?php
                            $isAgent = $message['sender'] === 'agent';
                            $isAi = $message['sender'] === 'ai';
                            $bubbleClass = $isAgent ? 'bg-indigo-600 text-white ml-auto' : ($isAi ? 'bg-gray-100 text-gray-800' : 'bg-blue-50 text-blue-800');
                            ?>
                            <div class="max-w-md <?php echo $isAgent ? 'ml-auto text-right' : ''; ?>">
                                <div class="text-xs text-gray-400 mb-1">
                                    <?php echo $message['sender'] === 'ai' ? 'Cliente IA' : ($message['sender'] === 'qa' ? 'QA' : 'Agente'); ?>
                                </div>
                                <div class="rounded-xl px-4 py-2 text-sm <?php echo $bubbleClass; ?>">
                                    <?php echo nl2br(htmlspecialchars($message['message_text'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Inicia la conversacion con un saludo.</p>
                    <?php endif; ?>
                </div>
                <form id="chat-form" class="border-t border-gray-200 p-3">
                    <input type="hidden" name="session_id" value="<?php echo $roleplay['id']; ?>">
                    <div class="flex items-center gap-2">
                        <input id="chat-input" name="message" required
                            class="flex-1 rounded-lg border-gray-300 text-sm" placeholder="Escribe tu mensaje...">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
                            Enviar
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Coach IA</h2>
                        <?php if ($roleplay['status'] !== 'completed'): ?>
                            <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/end">
                                <input type="hidden" name="session_id" value="<?php echo $roleplay['id']; ?>">
                                <button type="submit"
                                    class="border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-3 rounded-lg text-xs">
                                    Finalizar roleplay
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 text-sm text-gray-700 space-y-2">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Score promedio</span>
                            <span><?php echo $feedbackAverage !== null ? number_format((float) $feedbackAverage, 1) : 'N/A'; ?></span>
                        </div>
                        <?php if (!empty($roleplay['ai_summary'])): ?>
                            <div class="text-sm text-gray-700">
                                <p class="font-semibold text-gray-900">Resumen final</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['ai_summary'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['ai_actions_json'])): ?>
                            <?php $actions = json_decode($roleplay['ai_actions_json'], true) ?: []; ?>
                            <?php if (!empty($actions)): ?>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold text-gray-900">Acciones sugeridas</p>
                                    <ul class="mt-2 list-disc list-inside text-sm text-gray-700">
                                        <?php foreach ($actions as $action): ?>
                                            <li><?php echo htmlspecialchars((string) $action); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Feedback por turno</h2>
                    <div id="feedback-container" class="mt-4 space-y-3 text-sm text-gray-700">
                        <?php if (!empty($feedbackItems)): ?>
                            <?php foreach ($feedbackItems as $feedback): ?>
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <p class="text-xs text-gray-500">
                                        Score IA: <?php echo number_format((float) $feedback['score'], 1); ?>
                                        <?php if ($feedback['qa_score'] !== null): ?>
                                            · Score QA: <?php echo number_format((float) $feedback['qa_score'], 1); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($feedback['feedback'])): ?>
                                        <p class="mt-1"><?php echo nl2br(htmlspecialchars($feedback['feedback'])); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($feedback['qa_feedback'])): ?>
                                        <p class="mt-2 text-xs text-gray-500">QA</p>
                                        <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($feedback['qa_feedback'])); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($feedback['checklist_json'])): ?>
                                        <?php $checklist = json_decode($feedback['checklist_json'], true) ?: []; ?>
                                        <?php if (!empty($checklist)): ?>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <?php foreach ($checklist as $item): ?>
                                                    <?php
                                                    if (is_array($item)) {
                                                        $label = $item['label'] ?? json_encode($item, JSON_UNESCAPED_UNICODE);
                                                    } else {
                                                        $label = (string) $item;
                                                    }
                                                    ?>
                                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                                        <?php echo htmlspecialchars($label); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (in_array($_SESSION['user']['role'] ?? '', ['qa', 'admin'], true)): ?>
                                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/feedback/update"
                                            class="mt-3 space-y-2">
                                            <input type="hidden" name="session_id" value="<?php echo $roleplay['id']; ?>">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                            <div class="grid grid-cols-2 gap-2">
                                                <input name="qa_score" type="number" step="0.1" min="0" max="100"
                                                    value="<?php echo htmlspecialchars($feedback['qa_score'] ?? ''); ?>"
                                                    class="w-full rounded-lg border-gray-300 text-xs" placeholder="Score QA">
                                                <input name="qa_feedback"
                                                    value="<?php echo htmlspecialchars($feedback['qa_feedback'] ?? ''); ?>"
                                                    class="w-full rounded-lg border-gray-300 text-xs" placeholder="Feedback QA">
                                            </div>
                                            <?php if (!empty($rubricItems)): ?>
                                                <?php $qaChecklist = json_decode($feedback['qa_checklist_json'] ?? '', true) ?: []; ?>
                                                <div class="flex flex-wrap gap-2">
                                                    <?php foreach ($rubricItems as $item): ?>
                                                        <?php $checked = in_array($item['label'], $qaChecklist, true); ?>
                                                        <label class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                                            <input type="checkbox" name="qa_checklist[]"
                                                                value="<?php echo htmlspecialchars($item['label']); ?>"
                                                                <?php echo $checked ? 'checked' : ''; ?>>
                                                            <?php echo htmlspecialchars($item['label']); ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <button type="submit"
                                                class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">
                                                Guardar QA
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Aun no hay feedback.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Coaching QA</h2>
                    <div class="mt-3 space-y-2 text-sm text-gray-700">
                        <?php if (!empty($coachNotes)): ?>
                            <?php foreach ($coachNotes as $note): ?>
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($note['qa_name']); ?></p>
                                    <p><?php echo nl2br(htmlspecialchars($note['note_text'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Aun no hay notas del QA.</p>
                        <?php endif; ?>
                    </div>
                    <?php if (in_array($_SESSION['user']['role'] ?? '', ['qa', 'admin'], true)): ?>
                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/coach-note"
                            class="mt-3 space-y-2">
                            <input type="hidden" name="session_id" value="<?php echo $roleplay['id']; ?>">
                            <textarea name="note_text" rows="2" class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Tip privado para el agente"></textarea>
                            <button type="submit"
                                class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">
                                Enviar tip
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Plan de mejora</h2>
                    <?php if (in_array($_SESSION['user']['role'] ?? '', ['qa', 'admin'], true)): ?>
                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/plan/save"
                            class="mt-3 space-y-2">
                            <input type="hidden" name="session_id" value="<?php echo $roleplay['id']; ?>">
                            <textarea name="qa_plan_text" rows="3" class="w-full rounded-lg border-gray-300 text-sm"
                                placeholder="Acciones acordadas con el agente"><?php echo htmlspecialchars($roleplay['qa_plan_text'] ?? ''); ?></textarea>
                            <button type="submit"
                                class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">
                                Guardar plan
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-sm text-gray-700">
                            <?php echo nl2br(htmlspecialchars($roleplay['qa_plan_text'] ?? 'El QA aun no ha definido un plan.')); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Comparativo</h2>
                    <div class="mt-3 space-y-3 text-sm text-gray-700">
                        <div>
                            <p class="font-semibold text-gray-900">Ultimos roleplays</p>
                            <?php if (!empty($recentAgentRoleplays)): ?>
                                <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                    <?php foreach ($recentAgentRoleplays as $item): ?>
                                        <li>
                                            <?php echo date('d/m/Y', strtotime($item['created_at'])); ?> ·
                                            <?php echo htmlspecialchars($item['script_title'] ?? 'Roleplay'); ?> ·
                                            Score: <?php echo $item['score'] !== null ? number_format((float) $item['score'], 1) : 'N/A'; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">Sin historial.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Ultimas evaluaciones</p>
                            <?php if (!empty($recentEvaluations)): ?>
                                <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                    <?php foreach ($recentEvaluations as $item): ?>
                                        <li>
                                            <?php echo date('d/m/Y', strtotime($item['created_at'])); ?> ·
                                            <?php echo htmlspecialchars($item['campaign_name']); ?> ·
                                            <?php echo $item['percentage'] !== null ? number_format((float) $item['percentage'], 1) . '%' : 'N/A'; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">Sin evaluaciones recientes.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Contexto</h2>
                    <div class="mt-4 space-y-3 text-sm text-gray-700">
                        <?php if (!empty($roleplay['objectives_text'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Objetivos</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['objectives_text'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['rubric_title'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Rubrica</p>
                                <p><?php echo htmlspecialchars($roleplay['rubric_title']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['tone_text'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Tono del cliente</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['tone_text'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['obstacles_text'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Obstaculos</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['obstacles_text'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['scenario_text'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Escenario</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['scenario_text'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['persona_json'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Cliente</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['persona_json'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($roleplay['script_text'])): ?>
                            <div>
                                <p class="font-semibold text-gray-900">Guion base</p>
                                <p><?php echo nl2br(htmlspecialchars($roleplay['script_text'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const container = document.getElementById('chat-container');
    const feedbackContainer = document.getElementById('feedback-container');

    const appendMessage = (sender, text) => {
        const wrapper = document.createElement('div');
        const isAgent = sender === 'agent';
        const isAi = sender === 'ai';
        const bubbleClass = isAgent ? 'bg-indigo-600 text-white ml-auto' : (isAi ? 'bg-gray-100 text-gray-800' : 'bg-blue-50 text-blue-800');
        wrapper.className = `max-w-md ${isAgent ? 'ml-auto text-right' : ''}`;
        wrapper.innerHTML = `
            <div class="text-xs text-gray-400 mb-1">${sender === 'ai' ? 'Cliente IA' : (sender === 'qa' ? 'QA' : 'Agente')}</div>
            <div class="rounded-xl px-4 py-2 text-sm ${bubbleClass}"></div>
        `;
        wrapper.querySelector('div:last-child').textContent = text;
        container.appendChild(wrapper);
        container.scrollTop = container.scrollHeight;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        appendMessage('agent', message);
        input.value = '';

        const formData = new FormData(form);
        formData.set('message', message);

        try {
            const response = await fetch('<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/message', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (!data.success) {
                appendMessage('qa', data.error || 'Error IA');
                return;
            }
            appendMessage('ai', data.data.reply);
            if (data.data.feedback && feedbackContainer) {
                const item = document.createElement('div');
                item.className = 'border border-gray-200 rounded-lg p-3';
                const score = typeof data.data.feedback.score !== 'undefined' ? data.data.feedback.score : 'N/A';
                const feedback = data.data.feedback.feedback || '';
                const checklist = Array.isArray(data.data.feedback.checklist) ? data.data.feedback.checklist : [];
                const pills = checklist.length
                    ? `<div class="mt-2 flex flex-wrap gap-2">${checklist.map(item => `<span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">${item}</span>`).join('')}</div>`
                    : '';
                item.innerHTML = `<p class="text-xs text-gray-500">Score: ${score}</p>` +
                    (feedback ? `<p class="mt-1">${feedback}</p>` : '') + pills;
                feedbackContainer.appendChild(item);
            }
        } catch (err) {
            appendMessage('qa', 'Error de red.');
        }
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
