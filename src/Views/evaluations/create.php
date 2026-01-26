<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-2xl font-bold text-gray-900">Nueva Evaluación</h2>
                    <p class="mt-1 text-sm text-gray-500">Complete los datos para iniciar la evaluación.</p>
                </div>

                <form
                    action="<?php echo \App\Config\Config::BASE_URL; ?><?php echo isset($selectedCampaignId) ? 'evaluations/store' : 'evaluations/create'; ?>"
                    method="<?php echo isset($selectedCampaignId) ? 'POST' : 'GET'; ?>" class="p-8">
                    <?php if (isset($callId) && $callId): ?>
                        <input type="hidden" name="call_id" value="<?php echo htmlspecialchars($callId); ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700">Agente</label>
                            <?php if (isset($lockedCall) && $lockedCall): ?>
                                <input type="hidden" name="agent_id"
                                    value="<?php echo htmlspecialchars($selectedAgentId); ?>">
                            <?php endif; ?>
                            <select name="agent_id" id="agent_id" required <?php echo (isset($lockedCall) && $lockedCall) ? 'disabled' : ''; ?>
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
                            <?php if (isset($lockedCall) && $lockedCall): ?>
                                <input type="hidden" name="campaign_id"
                                    value="<?php echo htmlspecialchars($selectedCampaignId); ?>">
                            <?php endif; ?>
                            <select name="campaign_id" id="campaign_id"
                                onchange="submitEvaluationSelection(this.form)" required <?php echo (isset($lockedCall) && $lockedCall) ? 'disabled' : ''; ?>
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
                            <select name="form_template_id" id="form_template_id" required
                                onchange="submitEvaluationSelection(this.form)"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?php echo $tpl['id']; ?>" <?php echo ($template && $template['id'] == $tpl['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tpl['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($formFields) && !empty($formFields)): ?>
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <?php echo htmlspecialchars($template['title']); ?>
                            </h3>

                            <div class="space-y-6">
                                <?php foreach ($formFields as $field): ?>
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
                                                    <input type="radio" name="answers[<?php echo $field['id']; ?>]" value="100"
                                                        required class="form-radio h-5 w-5 text-indigo-600">
                                                    <span class="ml-2 text-gray-700">Cumple</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="answers[<?php echo $field['id']; ?>]" value="0"
                                                        required class="form-radio h-5 w-5 text-red-600">
                                                    <span class="ml-2 text-gray-700">No Cumple</span>
                                                </label>
                                            </div>
                                        <?php elseif ($field['field_type'] === 'score'): ?>
                                            <div class="w-full max-w-xs">
                                                <input type="number" name="answers[<?php echo $field['id']; ?>]" min="0" max="100"
                                                    required
                                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Puntaje (0-100)">
                                            </div>
                                        <?php elseif ($field['field_type'] === 'text'): ?>
                                            <textarea name="answers[<?php echo $field['id']; ?>]" rows="2"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                        <?php elseif ($field['field_type'] === 'select'): ?>
                                            <select name="answers[<?php echo $field['id']; ?>]"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Seleccione una opción...</option>
                                                <?php
                                                $options = explode(',', $field['options']);
                                                foreach ($options as $opt):
                                                    ?>
                                                    <option value="<?php echo trim($opt); ?>"><?php echo trim($opt); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>

                                        <div class="mt-3">
                                            <input type="text" name="field_comments[<?php echo $field['id']; ?>]"
                                                placeholder="Observación (opcional)"
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
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition duration-200 hover:scale-[1.02]">
                                Finalizar Evaluación
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
