<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls/show?id=<?php echo $call['id']; ?>"
                    class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Detalle de Llamada
                </a>
            </div>

            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-2xl font-bold text-gray-900">Editar Llamada #<?php echo $call['id']; ?></h2>
                    <p class="mt-1 text-sm text-gray-500">Modifique los datos de la llamada.</p>
                </div>

                <form action="<?php echo \App\Config\Config::BASE_URL; ?>calls/update" method="POST"
                    enctype="multipart/form-data" class="p-8">

                    <input type="hidden" name="id" value="<?php echo $call['id']; ?>">

                    <?php if (!empty($errors)): ?>
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <ul class="list-disc list-inside text-sm">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700">Agente</label>
                            <select name="agent_id" id="agent_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un Agente...</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>" <?php echo ($old['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($agent['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700">Proyecto</label>
                            <select name="project_id" id="project_id"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un Proyecto...</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" <?php echo ($old['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-2 text-xs text-gray-500">
                                ¿No ves tu proyecto? <a class="text-indigo-600 hover:text-indigo-700" href="<?php echo \App\Config\Config::BASE_URL; ?>clients/create">Crea un cliente corporativo</a>.
                            </p>
                        </div>

                        <div>
                            <label for="campaign_id" class="block text-sm font-medium text-gray-700">Campaña</label>
                            <select name="campaign_id" id="campaign_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                   <option value="">Seleccione una Campaña...</option>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <option value="<?php echo $campaign['id']; ?>" <?php echo ($old['campaign_id'] == $campaign['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($campaign['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label for="call_type" class="block text-sm font-medium text-gray-700">Tipo de llamada</label>
                            <input type="text" name="call_type" id="call_type"
                                value="<?php echo htmlspecialchars($old['call_type']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Ej: Ventas, Soporte, Retención">
                        </div>
                        <div>
                            <label for="call_datetime" class="block text-sm font-medium text-gray-700">Fecha y hora</label>
                            <input type="datetime-local" name="call_datetime" id="call_datetime" required
                                value="<?php echo htmlspecialchars($old['call_datetime']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="duration_seconds" class="block text-sm font-medium text-gray-700">Duración (segundos)</label>
                            <input type="number" name="duration_seconds" id="duration_seconds" min="0"
                                value="<?php echo htmlspecialchars($old['duration_seconds']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Ej: 320">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700">Teléfono del cliente</label>
                            <input type="text" name="customer_phone" id="customer_phone"
                                value="<?php echo htmlspecialchars($old['customer_phone']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Ej: +1 555 000 0000">
                        </div>
                        <div>
                            <label for="lead" class="block text-sm font-medium text-gray-700">Lead</label>
                            <input type="text" name="lead" id="lead"
                                value="<?php echo htmlspecialchars($old['lead']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Ej: Juan Pérez, Renovación Seguro">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div>
                            <label for="recording" class="block text-sm font-medium text-gray-700">Grabación (audio)</label>
                            <?php if (!empty($old['recording_path'])): ?>
                                <div class="mb-2 text-sm text-gray-600">
                                    <strong>Grabación actual:</strong> <?php echo basename($old['recording_path']); ?>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="recording" id="recording" accept="audio/*"
                                class="mt-1 block w-full text-sm text-gray-700">
                            <p class="mt-1 text-xs text-gray-500">Deje vacío para mantener la grabación actual. Se permite cualquier formato de audio.</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notas de la llamada</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($old['notes']); ?></textarea>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls/show?id=<?php echo $call['id']; ?>"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-8 rounded-lg shadow-lg transition duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition duration-200 hover:scale-[1.02]">
                            Actualizar Llamada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
