<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Criterios IA de Evaluación</h1>
                <p class="text-sm text-gray-500 mt-1">Ajusta los criterios por proyecto, campaña y tipo de llamada.</p>
            </div>

            <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <?php echo !empty($editing) ? 'Editar criterio' : 'Nuevo criterio'; ?>
                    </h2>
                </div>
                <form action="<?php echo \App\Config\Config::BASE_URL; ?>ai-criteria/store" method="POST" class="p-6">
                    <?php if (!empty($editing)): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editing['id']; ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Proyecto</label>
                            <select name="project_id"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm">
                                <option value="">Todos</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" <?php echo (!empty($editing) && (int) $editing['project_id'] === (int) $project['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Campaña</label>
                            <select name="campaign_id"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm">
                                <option value="">Todas</option>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <option value="<?php echo $campaign['id']; ?>" <?php echo (!empty($editing) && (int) $editing['campaign_id'] === (int) $campaign['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($campaign['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de llamada</label>
                            <input type="text" name="call_type"
                                value="<?php echo htmlspecialchars($editing['call_type'] ?? ''); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm"
                                placeholder="Ej: Ventas, Soporte, Retención">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Criterios de evaluación</label>
                        <textarea name="criteria_text" rows="4" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm"
                            placeholder="Describe criterios específicos para la IA."><?php echo htmlspecialchars($editing['criteria_text'] ?? ''); ?></textarea>
                    </div>

                    <div class="flex items-center mb-6">
                        <input type="checkbox" id="active" name="active" class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            <?php echo empty($editing) || !empty($editing['active']) ? 'checked' : ''; ?>>
                        <label for="active" class="ml-2 text-sm text-gray-600">Activo</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg">
                            <?php echo !empty($editing) ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                        <?php if (!empty($editing)): ?>
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>ai-criteria"
                                class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Criterios existentes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyecto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaña</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criterios</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($criteria as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($row['project_name'] ?? 'Todos'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($row['campaign_name'] ?? 'Todas'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($row['call_type'] ?? 'Todos'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xl">
                                        <?php echo nl2br(htmlspecialchars($row['criteria_text'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if (!empty($row['active'])): ?>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Activo</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-600 rounded-full">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>ai-criteria/edit?id=<?php echo (int) $row['id']; ?>"
                                            class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form action="<?php echo \App\Config\Config::BASE_URL; ?>ai-criteria/toggle" method="POST" class="inline">
                                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                            <input type="hidden" name="active" value="<?php echo !empty($row['active']) ? 0 : 1; ?>">
                                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                <?php echo !empty($row['active']) ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($criteria)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-sm text-gray-500 text-center">
                                        Aún no hay criterios configurados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
