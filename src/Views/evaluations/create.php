<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?php echo !empty($isEdit) ? 'Editar Evaluación' : 'Nueva Evaluación'; ?>
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php echo !empty($isEdit) ? 'Actualice los datos y guarde los cambios.' : 'Complete los datos para iniciar la evaluación.'; ?>
                    </p>
                </div>

                <form
                    action="<?php echo \App\Config\Config::BASE_URL; ?><?php echo $formAction ?? (isset($selectedCampaignId) ? 'evaluations/store' : 'evaluations/create'); ?>"
                    method="<?php echo $formMethod ?? (isset($selectedCampaignId) ? 'POST' : 'GET'); ?>" class="p-8" enctype="multipart/form-data">
                    <?php if (!empty($isEdit)): ?>
                        <input type="hidden" name="evaluation_id" value="<?php echo htmlspecialchars($evaluationId); ?>">
                    <?php endif; ?>
                    <?php if (isset($callId) && $callId): ?>
                        <input type="hidden" name="call_id" value="<?php echo htmlspecialchars($callId); ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700">Agente</label>
                            <?php if (!empty($isEdit) || (isset($lockedCall) && $lockedCall)): ?>
                                <input type="hidden" name="agent_id"
                                    value="<?php echo htmlspecialchars($selectedAgentId); ?>">
                            <?php endif; ?>
                            <select name="agent_id" id="agent_id" required <?php echo (!empty($isEdit) || (isset($lockedCall) && $lockedCall)) ? 'disabled' : ''; ?>
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un Agente...</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>" <?php echo ((isset($selectedAgentId) && $selectedAgentId == $agent['id']) || (isset($_GET['agent_id']) && $_GET['agent_id'] == $agent['id'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($agent['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="campaign_id" class="block text-sm font-medium text-gray-700">Campaña</label>
                            <?php if (!empty($isEdit) || (isset($lockedCall) && $lockedCall)): ?>
                                <input type="hidden" name="campaign_id"
                                    value="<?php echo htmlspecialchars($selectedCampaignId); ?>">
                            <?php endif; ?>
                            <select name="campaign_id" id="campaign_id"
                                onchange="<?php echo !empty($isEdit) ? '' : 'submitEvaluationSelection(this.form)'; ?>" required <?php echo (!empty($isEdit) || (isset($lockedCall) && $lockedCall)) ? 'disabled' : ''; ?>
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione una Campaña...</option>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <option value="<?php echo $campaign['id']; ?>" <?php echo ($selectedCampaignId == $campaign['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($campaign['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!isset($selectedCampaignId)): ?>
                                <p class="mt-2 text-xs text-gray-500">Seleccione una campaña para cargar el formulario.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($templates)): ?>
                        <div class="mb-6">
                            <label for="form_template_id" class="block text-sm font-medium text-gray-700">Formulario</label>
                            <?php if (!empty($isEdit)): ?>
                                <input type="hidden" name="form_template_id" value="<?php echo htmlspecialchars($template['id']); ?>">
                            <?php endif; ?>
                            <select name="form_template_id" id="form_template_id" required
                                onchange="<?php echo !empty($isEdit) ? '' : 'submitEvaluationSelection(this.form)'; ?>" <?php echo !empty($isEdit) ? 'disabled' : ''; ?>
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?php echo $tpl['id']; ?>" <?php echo ($template && $template['id'] == $tpl['id']) ? 'selected' : ''; ?> <?php echo !empty($isEdit) ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($tpl['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php elseif (!empty($selectedCampaignId)): ?>
                        <div class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700">
                            Esta campaña no tiene formulario de evaluación. Cree uno para poder continuar.
                            <a class="font-semibold underline ml-1"
                                href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create?campaign_id=<?php echo (int) $selectedCampaignId; ?>">
                                Crear formulario
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($formValues['feedback_evidence_url'])): ?>
                        <div class="mb-6 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                            Evidencia actual:
                            <a class="font-semibold underline" href="<?php echo htmlspecialchars($formValues['feedback_evidence_url']); ?>" target="_blank" rel="noopener">
                                <?php echo htmlspecialchars($formValues['feedback_evidence_name'] ?? 'Ver archivo'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($recordingUrl)): ?>
                        <div class="mb-6">
                            <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-indigo-900">Grabacion de la llamada</p>
                                        <p class="text-xs text-indigo-700">Escucha la llamada mientras completas la evaluacion.</p>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($recordingUrl); ?>" target="_blank" rel="noopener"
                                        class="inline-flex items-center text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                        Abrir audio
                                    </a>
                                </div>
                                <audio controls class="mt-3 w-full">
                                    <source src="<?php echo htmlspecialchars($recordingUrl); ?>">
                                    Tu navegador no soporta audio HTML5.
                                </audio>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($formFields) && !empty($formFields)): ?>
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <?php echo htmlspecialchars($template['title']); ?>
                            </h3>

                            <div class="space-y-6">
                                <?php foreach ($formFields as $field): ?>
                                    <?php
                                    $currentAnswer = $prefillAnswers[$field['id']] ?? null;
                                    $currentScore = $currentAnswer['score_given'] ?? null;
                                    $currentText = $currentAnswer['text_answer'] ?? null;
                                    $currentComment = $prefillComments[$field['id']] ?? '';
                                    ?>
                                    <?php $maxScore = isset($field['max_score']) ? (float) $field['max_score'] : 100.0; ?>
                                    <?php if ($maxScore <= 0): ?>
                                        <?php $maxScore = 100.0; ?>
                                    <?php endif; ?>
                                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                        <div class="flex justify-between items-start mb-4">
                                            <label class="block text-md font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($field['label']); ?>
                                                <?php if ($field['required']): ?><span class="text-red-500">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <span
                                                class="text-xs font-mono bg-indigo-100 text-indigo-800 px-2 py-1 rounded">Peso:
                                                <?php echo $field['weight']; ?>%
                                            </span>
                                        </div>

                                        <?php if ($field['field_type'] === 'yes_no'): ?>
                                            <div class="flex space-x-6">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="answers[<?php echo $field['id']; ?>]" value="<?php echo $maxScore; ?>"
                                                        <?php echo ($currentScore !== null && (float) $currentScore === (float) $maxScore) ? 'checked' : ''; ?>
                                                        required class="form-radio h-5 w-5 text-indigo-600">
                                                    <span class="ml-2 text-gray-700">Cumple</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="answers[<?php echo $field['id']; ?>]" value="0"
                                                        <?php echo ($currentScore !== null && (float) $currentScore === 0.0) ? 'checked' : ''; ?>
                                                        required class="form-radio h-5 w-5 text-red-600">
                                                    <span class="ml-2 text-gray-700">No Cumple</span>
                                                </label>
                                            </div>
                                        <?php elseif ($field['field_type'] === 'score'): ?>
                                            <div class="w-full max-w-xs">
                                                <input type="number" name="answers[<?php echo $field['id']; ?>]" min="0" max="<?php echo $maxScore; ?>"
                                                    value="<?php echo $currentScore !== null ? htmlspecialchars($currentScore) : ''; ?>" required
                                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Puntaje (0-<?php echo $maxScore; ?>)">
                                            </div>
                                        <?php elseif ($field['field_type'] === 'text'): ?>
                                            <textarea name="answers[<?php echo $field['id']; ?>]" rows="2"
                                                <?php echo $field['required'] ? 'required' : ''; ?>
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($currentText ?? ''); ?></textarea>
                                        <?php elseif ($field['field_type'] === 'select'): ?>
                                            <select name="answers[<?php echo $field['id']; ?>]"
                                                <?php echo $field['required'] ? 'required' : ''; ?>
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Seleccione una opción...</option>
                                                <?php
                                                $options = explode(',', $field['options']);
                                                foreach ($options as $opt):
                                                    ?>
                                                    <?php $optionValue = trim($opt); ?>
                                                    <option value="<?php echo $optionValue; ?>" <?php echo ($currentText !== null && $currentText === $optionValue) ? 'selected' : ''; ?>><?php echo $optionValue; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>

                                        <div class="mt-3">
                                            <input type="text" name="field_comments[<?php echo $field['id']; ?>]"
                                                placeholder="Observación (opcional)"
                                                value="<?php echo htmlspecialchars($currentComment); ?>"
                                                class="block w-full border-gray-200 rounded-md text-xs py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <label for="general_comments" class="block text-sm font-medium text-gray-700">Comentarios
                                Generales / Feedback</label>
                            <textarea name="general_comments" id="general_comments" rows="4"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formValues['general_comments'] ?? ''); ?></textarea>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">DocumentaciÃ³n de acciÃ³n y mejora</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="action_type" class="block text-sm font-medium text-gray-700">Tipo de acciÃ³n realizada</label>
                                    <select name="action_type" id="action_type"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Seleccione una opciÃ³n...</option>
                                        <option value="feedback" <?php echo (($formValues['action_type'] ?? '') === 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                                        <option value="call_evaluation" <?php echo (($formValues['action_type'] ?? '') === 'call_evaluation') ? 'selected' : ''; ?>>EvaluaciÃ³n de llamada</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="improvement_areas" class="block text-sm font-medium text-gray-700">Ãreas de mejora identificadas</label>
                                    <textarea name="improvement_areas" id="improvement_areas" rows="3"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formValues['improvement_areas'] ?? ''); ?></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="improvement_plan" class="block text-sm font-medium text-gray-700">Plan de mejora sugerido</label>
                                    <textarea name="improvement_plan" id="improvement_plan" rows="3"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formValues['improvement_plan'] ?? ''); ?></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="tasks_commitments" class="block text-sm font-medium text-gray-700">Tareas a realizar / compromisos</label>
                                    <textarea name="tasks_commitments" id="tasks_commitments" rows="3"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formValues['tasks_commitments'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Confirmacion de feedback</h4>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="feedback_confirmed" id="feedback_confirmed"
                                    <?php echo !empty($formValues['feedback_confirmed']) ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <label for="feedback_confirmed" class="text-sm text-gray-700">
                                    Feedback realizado con el agente
                                </label>
                            </div>
                            <div class="mt-4">
                                <label for="feedback_evidence" class="block text-sm font-medium text-gray-700">Evidencia
                                    (audio, nota, documento, etc.)</label>
                                <input type="file" name="feedback_evidence" id="feedback_evidence"
                                    class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                                <p class="mt-1 text-xs text-gray-500">Maximo 50MB. Opcional.</p>
                            </div>
                            <div class="mt-4">
                                <label for="feedback_evidence_note" class="block text-sm font-medium text-gray-700">Nota
                                    de evidencia (opcional)</label>
                                <textarea name="feedback_evidence_note" id="feedback_evidence_note" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formValues['feedback_evidence_note'] ?? ''); ?></textarea>
                            </div>
                        </div><div class="mt-8 flex justify-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition duration-200 hover:scale-[1.02]">
                                <?php echo !empty($isEdit) ? 'Guardar cambios' : 'Finalizar Evaluación'; ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    function submitEvaluationSelection(form) {
        form.action = "<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create";
        form.method = "GET";
        form.submit();
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

