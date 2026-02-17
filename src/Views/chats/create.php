<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Registrar Nuevo Chat</h1>
            </div>
        </header>

        <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <form action="<?php echo \App\Config\Config::BASE_URL; ?>chats/store" method="POST"
                    enctype="multipart/form-data" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Agent Selection -->
                        <div>
                            <label for="agent_id" class="block text-sm font-semibold text-gray-700 mb-2">Agente
                                Evaluado</label>
                            <select name="agent_id" id="agent_id" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                                <option value="">Seleccione un agente...</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>">
                                        <?php echo htmlspecialchars($agent['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Campaign Selection -->
                        <div>
                            <label for="campaign_id"
                                class="block text-sm font-semibold text-gray-700 mb-2">Campaña</label>
                            <select name="campaign_id" id="campaign_id" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                                <option value="">Seleccione campaña...</option>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <option value="<?php echo $campaign['id']; ?>">
                                        <?php echo htmlspecialchars($campaign['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Project Selection -->
                        <div>
                            <label for="project_id" class="block text-sm font-semibold text-gray-700 mb-2">Cliente /
                                Proyecto (Opcional)</label>
                            <select name="project_id" id="project_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                                <option value="">N/A</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>">
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Chat Date -->
                        <div>
                            <label for="chat_date" class="block text-sm font-semibold text-gray-700 mb-2">Fecha y Hora
                                del Chat</label>
                            <input type="datetime-local" name="chat_date" id="chat_date" required
                                value="<?php echo date('Y-m-d\TH:i'); ?>"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                        </div>

                        <!-- Customer Identifier -->
                        <div class="md:col-span-2">
                            <label for="customer_identifier"
                                class="block text-sm font-semibold text-gray-700 mb-2">Identificador del Cliente
                                (Teléfono, Usuario, etc.)</label>
                            <input type="text" name="customer_identifier" id="customer_identifier"
                                placeholder="Ej: +57 321 4567890 o @username"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200">
                        </div>

                        <!-- Screenshot Upload -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Captura de Pantalla del
                                Chat</label>
                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 transition duration-200 bg-gray-50">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48" aria-hidden="true">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="screenshot"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Subir archivo</span>
                                            <input id="screenshot" name="screenshot" type="file" class="sr-only"
                                                accept="image/*">
                                        </label>
                                        <p class="pl-1">o arrastrar y soltar</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 10MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Notas /
                                Observaciones Iniciales</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                                placeholder="Cualquier aclaración sobre este chat..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 space-x-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>chats"
                            class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition duration-300 underline-offset-4 active:scale-95">
                            Guardar y Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>