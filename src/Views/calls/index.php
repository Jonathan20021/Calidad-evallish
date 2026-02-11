<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Registro de Llamadas</h1>
                    <p class="mt-1 text-sm text-gray-500">Historial de llamadas recientes y estado de evaluación</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls/create"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Subir Llamada
                    </a>
                    <button id="filterButton"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filtrar
                    </button>
                    <!-- Simulated 'Sync' button -->
                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sincronizar
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Active Filters Display -->
            <?php if (!empty($activeFilters)): ?>
                <div class="mb-4 flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-medium text-gray-700">Filtros activos:</span>
                    <?php if (!empty($activeFilters['agent_id'])): ?>
                        <?php
                        $agentName = 'Agente';
                        foreach ($agents as $agent) {
                            if ($agent['id'] == $activeFilters['agent_id']) {
                                $agentName = $agent['full_name'];
                                break;
                            }
                        }
                        ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                            Agente: <?php echo htmlspecialchars($agentName); ?>
                            <button onclick="removeFilter('agent_id')" class="ml-2 text-indigo-600 hover:text-indigo-900">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($activeFilters['campaign_id'])): ?>
                        <?php
                        $campaignName = 'Campaña';
                        foreach ($campaigns as $campaign) {
                            if ($campaign['id'] == $activeFilters['campaign_id']) {
                                $campaignName = $campaign['name'];
                                break;
                            }
                        }
                        ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            Campaña: <?php echo htmlspecialchars($campaignName); ?>
                            <button onclick="removeFilter('campaign_id')" class="ml-2 text-purple-600 hover:text-purple-900">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($activeFilters['project_id'])): ?>
                        <?php
                        $projectName = 'Proyecto';
                        foreach ($projects as $project) {
                            if ($project['id'] == $activeFilters['project_id']) {
                                $projectName = $project['name'];
                                break;
                            }
                        }
                        ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Proyecto: <?php echo htmlspecialchars($projectName); ?>
                            <button onclick="removeFilter('project_id')" class="ml-2 text-green-600 hover:text-green-900">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($activeFilters['status'])): ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Estado: <?php echo $activeFilters['status'] === 'evaluated' ? 'Evaluada' : 'Pendiente'; ?>
                            <button onclick="removeFilter('status')" class="ml-2 text-yellow-600 hover:text-yellow-900">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($activeFilters['date_from']) || !empty($activeFilters['date_to'])): ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            Fechas:
                            <?php echo !empty($activeFilters['date_from']) ? date('d/m/Y', strtotime($activeFilters['date_from'])) : '...'; ?>
                            -
                            <?php echo !empty($activeFilters['date_to']) ? date('d/m/Y', strtotime($activeFilters['date_to'])) : '...'; ?>
                            <button onclick="removeFilter('dates')" class="ml-2 text-blue-600 hover:text-blue-900">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($activeFilters['call_type'])): ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-pink-100 text-pink-800">
                            Tipo: <?php echo htmlspecialchars($activeFilters['call_type']); ?>
                            <button onclick="removeFilter('call_type')" class="ml-2 text-pink-600 hover:text-pink-900">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    <?php endif; ?>
                    <button onclick="clearAllFilters()" class="text-sm text-red-600 hover:text-red-900 font-medium">
                        Limpiar todos
                    </button>
                </div>
            <?php endif; ?>
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 280px);">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID Llamada</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Agente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Proyecto</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Campaña</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lead</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha / Hora</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Duración</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($calls as $call): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $call['id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                <?php echo substr($call['agent'], 0, 1); ?>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($call['agent']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($call['project'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($call['campaign']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($call['call_type'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($call['lead'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $call['date']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                        <?php echo $call['duration']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($call['status'] === 'evaluated'): ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor"
                                                    viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Evaluada
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor"
                                                    viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Pendiente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if ($call['status'] === 'pending'): ?>
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create?call_id=<?php echo $call['id']; ?>"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Evaluar</a>
                                        <?php endif; ?>
                                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls/show?id=<?php echo $call['id']; ?>"
                                            class="text-indigo-600 hover:text-indigo-900">Ver Detalle</a>
                                        <form action="<?php echo \App\Config\Config::BASE_URL; ?>calls/delete" method="POST"
                                            class="inline">
                                            <input type="hidden" name="id" value="<?php echo $call['id']; ?>">
                                            <button type="submit" class="ml-3 text-red-600 hover:text-red-900"
                                                onclick="return confirm('¿Eliminar esta llamada?');">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Placeholder -->
            <div
                class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4 rounded-lg shadow-sm">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando <span class="font-medium"><?php echo count($calls); ?></span> resultado(s)
                            <?php if (!empty($activeFilters)): ?>
                                <span class="text-gray-500">(filtrado)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Filtrar Llamadas</h3>
            <button onclick="closeFilterModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="filterForm" method="GET" action="<?php echo \App\Config\Config::BASE_URL; ?>calls">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Agent Filter -->
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">Agente</label>
                    <select id="agent_id" name="agent_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los agentes</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" <?php echo (!empty($activeFilters['agent_id']) && $activeFilters['agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agent['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Campaign Filter -->
                <div>
                    <label for="campaign_id" class="block text-sm font-medium text-gray-700 mb-1">Campaña</label>
                    <select id="campaign_id" name="campaign_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todas las campañas</option>
                        <?php foreach ($campaigns as $campaign): ?>
                            <option value="<?php echo $campaign['id']; ?>" <?php echo (!empty($activeFilters['campaign_id']) && $activeFilters['campaign_id'] == $campaign['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($campaign['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Project Filter -->
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Proyecto</label>
                    <select id="project_id" name="project_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los proyectos</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo (!empty($activeFilters['project_id']) && $activeFilters['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los estados</option>
                        <option value="evaluated" <?php echo (!empty($activeFilters['status']) && $activeFilters['status'] === 'evaluated') ? 'selected' : ''; ?>>Evaluada</option>
                        <option value="pending" <?php echo (!empty($activeFilters['status']) && $activeFilters['status'] === 'pending') ? 'selected' : ''; ?>>Pendiente</option>
                    </select>
                </div>

                <!-- Date From Filter -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" id="date_from" name="date_from"
                        value="<?php echo $activeFilters['date_from'] ?? ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date To Filter -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" id="date_to" name="date_to"
                        value="<?php echo $activeFilters['date_to'] ?? ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Call Type Filter -->
                <div class="md:col-span-2">
                    <label for="call_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Llamada</label>
                    <input type="text" id="call_type" name="call_type"
                        value="<?php echo $activeFilters['call_type'] ?? ''; ?>" placeholder="Ej: Inbound, Outbound"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="clearFiltersInModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Limpiar Filtros
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Aplicar Filtros
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Open filter modal
    document.getElementById('filterButton').addEventListener('click', function () {
        document.getElementById('filterModal').classList.remove('hidden');
    });

    // Close filter modal
    function closeFilterModal() {
        document.getElementById('filterModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('filterModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeFilterModal();
        }
    });

    // Clear all filters in modal
    function clearFiltersInModal() {
        document.getElementById('filterForm').reset();
    }

    // Clear all filters and reload
    function clearAllFilters() {
        window.location.href = '<?php echo \App\Config\Config::BASE_URL; ?>calls';
    }

    // Remove individual filter
    function removeFilter(filterName) {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);

        if (filterName === 'dates') {
            params.delete('date_from');
            params.delete('date_to');
        } else {
            params.delete(filterName);
        }

        window.location.href = url.pathname + (params.toString() ? '?' + params.toString() : '');
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>