<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col shadow-xl">
        <div class="p-6">
            <h1 class="text-2xl font-bold tracking-wider text-indigo-400">EVALLISH</h1>
            <p class="text-xs text-slate-400">Quality Control</p>
        </div>
        <nav class="flex-1 px-4 space-y-2 mt-4">
            <a href="<?php echo \App\Config\Config::BASE_URL; ?>dashboard"
                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all duration-200">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                Dashboard
            </a>
            <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns"
                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 transition-all duration-200">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                Campañas
            </a>
        </nav>
    </aside>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            <?php echo !empty($isEditing) ? 'Editar Formulario' : 'Diseñador de Formulario'; ?>
                        </h2>
                        <?php if (!empty($campaign)): ?>
                            <p class="mt-1 text-sm text-gray-500">Campaña: <span class="font-semibold text-indigo-600">
                                    <?php echo htmlspecialchars($campaign['name']); ?>
                                </span></p>
                        <?php else: ?>
                            <p class="mt-1 text-sm text-gray-500">Seleccione una campaña para comenzar.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($campaign)): ?>
                    <form action="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create" method="GET" class="p-8">
                        <div class="mb-6">
                            <label for="campaign_id" class="block text-sm font-medium text-gray-700">Campaña</label>
                            <select name="campaign_id" id="campaign_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione una campaña...</option>
                                <?php foreach ($campaigns as $item): ?>
                                    <option value="<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200">
                                Continuar
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <form action="<?php echo \App\Config\Config::BASE_URL; ?><?php echo !empty($isEditing) ? 'form-templates/update' : 'form-templates/store'; ?>" method="POST"
                        id="builderForm" class="p-8">
                        <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
                        <?php if (!empty($isEditing) && !empty($template)): ?>
                            <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                        <?php endif; ?>
                        <input type="hidden" name="items_json" id="itemsJson">

                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título del Formulario</label>
                            <input type="text" name="title" id="title"
                                value="<?php echo $template['title'] ?? ('Evaluación de Calidad ' . date('Y')); ?>" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div class="space-y-4 mb-8" id="fieldsContainer">
                            <!-- Fields will be added here via JS -->
                        </div>

                        <button type="button" id="addFieldBtn"
                            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 font-semibold hover:border-indigo-500 hover:text-indigo-600 transition duration-200 flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Agregar Nuevo Ítem de Evaluación
                        </button>

                        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end items-center">
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates"
                                class="mr-4 text-gray-600 hover:text-gray-900 font-medium">Cancelar</a>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200">
                                <?php echo !empty($isEditing) ? 'Guardar Cambios' : 'Guardar Formulario'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php if (!empty($campaign)): ?>
    <template id="fieldTemplate">
        <div
            class="field-item bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm relative group transition duration-200 hover:shadow-md">
            <button type="button"
                class="remove-field absolute top-4 right-4 text-gray-400 hover:text-red-500 transition duration-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pregunta /
                        Ítem</label>
                    <input type="text"
                        class="field-label block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Ej: Saludo inicial acorde al script">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipo de
                        Respuesta</label>
                    <select
                        class="field-type block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="this.closest('.field-item').querySelector('.options-container').style.display = this.value === 'select' ? 'block' : 'none'">
                        <option value="yes_no">Sí / No (Cumple/No Cumple)</option>
                        <option value="score">Puntuación (Escala 1-100)</option>
                        <option value="text">Texto Libre</option>
                        <option value="select">Selección Múltiple</option>
                    </select>
                </div>
                <div class="col-span-2 options-container" style="display: none;">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Opciones (Separadas por coma)</label>
                    <input type="text"
                        class="field-options block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Opción 1, Opción 2, Opción 3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Peso (%)</label>
                    <input type="number"
                        class="field-weight block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        value="1" min="0" step="0.1">
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('fieldsContainer');
            const template = document.getElementById('fieldTemplate');
            const form = document.getElementById('builderForm');

            const existingFields = <?php echo json_encode($fields ?? []); ?>;

            function addField(data = null) {
                const clone = template.content.cloneNode(true);
                const item = clone.querySelector('.field-item');

                if (data) {
                    item.querySelector('.field-label').value = data.label;
                    item.querySelector('.field-type').value = data.field_type;
                    item.querySelector('.field-weight').value = data.weight;
                    if (data.field_type === 'select') {
                        item.querySelector('.options-container').style.display = 'block';
                        item.querySelector('.field-options').value = data.options;
                    }
                }

                item.querySelector('.remove-field').addEventListener('click', () => {
                    item.remove();
                });

                container.appendChild(clone);
            }

            document.getElementById('addFieldBtn').addEventListener('click', () => addField());

            if (existingFields.length > 0) {
                existingFields.forEach(field => addField(field));
            } else {
                addField();
            }

            form.addEventListener('submit', () => {
                const items = [];
                container.querySelectorAll('.field-item').forEach(item => {
                    items.push({
                        label: item.querySelector('.field-label').value,
                        type: item.querySelector('.field-type').value,
                        weight: item.querySelector('.field-weight').value,
                        options: item.querySelector('.field-options').value,
                        max_score: 100
                    });
                });
                document.getElementById('itemsJson').value = JSON.stringify(items);
            });
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
