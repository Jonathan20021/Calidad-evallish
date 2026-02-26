<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Evaluaciones Realizadas</h1>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200">
                    + Nueva Evaluación
                </a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">La evaluación ha sido movida a la papelera exitosamente.</span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col">
                <div class="mb-4">
                    <form method="GET" action="" class="flex items-center space-x-2">
                        <label for="campaign_id" class="text-sm font-medium text-gray-700">Filtrar por Campaña:</label>
                        <select name="campaign_id" id="campaign_id" onchange="this.form.submit()"
                            class="block w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                            <option value="">Todas las campañas</option>
                            <?php foreach ($campaigns as $campaign): ?>
                                <option value="<?php echo $campaign['id']; ?>" <?php echo (isset($selectedCampaignId) && $selectedCampaignId == $campaign['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($campaign['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 240px);">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Agente</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Campaña</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Formulario</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Puntuación</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tipo</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Evaluador</th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Ver</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($evaluations as $eval): ?>
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <span
                                                                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold">
                                                                <?php echo substr($eval['agent_name'], 0, 1); ?>
                                                            </span>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                <?php echo htmlspecialchars($eval['agent_name']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($eval['campaign_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($eval['form_title']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $eval['percentage'] >= 90 ? 'bg-green-100 text-green-800' : ($eval['percentage'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                        <?php echo number_format($eval['percentage'], 1); ?>%
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                                    <?php echo htmlspecialchars($eval['evaluation_type'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('d/m/Y', strtotime($eval['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($eval['qa_name']); ?>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end space-x-3">
                                                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/show?id=<?php echo $eval['id']; ?>"
                                                        class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                                    <form
                                                        action="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/delete"
                                                        method="POST" class="inline"
                                                        onsubmit="return confirm('¿Estás seguro de que deseas mover esta evaluación a la papelera? Podrás restaurarla desde la papelera de reciclaje.');">
                                                        <input type="hidden" name="id" value="<?php echo $eval['id']; ?>">
                                                        <button type="submit"
                                                            class="text-red-600 hover:text-red-900">Borrar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>