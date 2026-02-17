<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Papelera de Reciclaje</h1>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

            <?php if (isset($_GET['restored'])): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">El elemento ha sido restaurado exitosamente.</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">El elemento ha sido eliminado permanentemente.</span>
                </div>
            <?php endif; ?>

            <!-- Section: Evaluations -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clipboard-check mr-2 text-indigo-500"></i> Evaluaciones Eliminadas
                </h2>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
                    <?php if (empty($deletedEvaluations)): ?>
                        <div class="p-6 text-center text-gray-500">No hay evaluaciones en la papelera.</div>
                    <?php else: ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaña</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Eliminado el
                                    </th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($deletedEvaluations as $eval): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($eval['agent_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($eval['campaign_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($eval['deleted_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>recycle-bin/restore?type=evaluation&id=<?php echo $eval['id']; ?>"
                                                class="text-indigo-600 hover:text-indigo-900">Restaurar</a>
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>recycle-bin/delete-permanently?type=evaluation&id=<?php echo $eval['id']; ?>"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar esto permanentemente? Esta acción NO se puede deshacer.')"
                                                class="text-red-600 hover:text-red-900">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section: Form Templates -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt mr-2 text-indigo-500"></i> Formularios Eliminados
                </h2>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
                    <?php if (empty($deletedTemplates)): ?>
                        <div class="p-6 text-center text-gray-500">No hay formularios en la papelera.</div>
                    <?php else: ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campañas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Eliminado el
                                    </th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($deletedTemplates as $temp): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($temp['title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">
                                            <?php echo htmlspecialchars($temp['campaign_names'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($temp['deleted_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>recycle-bin/restore?type=template&id=<?php echo $temp['id']; ?>"
                                                class="text-indigo-600 hover:text-indigo-900">Restaurar</a>
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>recycle-bin/delete-permanently?type=template&id=<?php echo $temp['id']; ?>"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar esto permanentemente? Esta acción NO se puede deshacer.')"
                                                class="text-red-600 hover:text-red-900">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section: Campaigns -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-bullhorn mr-2 text-indigo-500"></i> Campañas Eliminadas
                </h2>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
                    <?php if (empty($deletedCampaigns)): ?>
                        <div class="p-6 text-center text-gray-500">No hay campañas en la papelera.</div>
                    <?php else: ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Eliminado el
                                    </th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($deletedCampaigns as $camp): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($camp['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($camp['deleted_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>recycle-bin/restore?type=campaign&id=<?php echo $camp['id']; ?>"
                                                class="text-indigo-600 hover:text-indigo-900">Restaurar</a>
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>recycle-bin/delete-permanently?type=campaign&id=<?php echo $camp['id']; ?>"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar esto permanentemente? Esta acción NO se puede deshacer.')"
                                                class="text-red-600 hover:text-red-900">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>