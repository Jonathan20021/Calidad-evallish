<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">

    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard de Calidad</h1>
                    <p class="text-xs text-gray-500 mt-1">Visión general del rendimiento del BPO</p>
                </div>

                <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200">
                    + Nueva Evaluación
                </a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Evaluations -->
                <div
                    class="bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Evaluaciones</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_evaluations']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compliance Rate -->
                <div
                    class="bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Tasa de Cumplimiento</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    <?php echo number_format($complianceRate, 1); ?>%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Quality -->
                <div
                    class="bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Promedio Calidad</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    <?php echo number_format($stats['avg_percentage'] ?? 0, 1); ?>%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Critical Fails -->
                <div
                    class="bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 rounded-xl border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Alertas Críticas</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $criticalFails; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Context Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Top Performer -->
                <div
                    class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl">
                    </div>
                    <h3 class="text-indigo-100 font-semibold text-sm uppercase tracking-wider mb-4">Mejor Agente (Top
                        Performer)</h3>

                    <?php if ($topAgent): ?>
                        <div class="flex items-center">
                            <div
                                class="h-16 w-16 bg-white rounded-full flex items-center justify-center text-indigo-700 font-bold text-2xl shadow-lg border-4 border-indigo-400">
                                <?php echo substr($topAgent['full_name'], 0, 1); ?>
                            </div>
                            <div class="ml-6">
                                <p class="text-2xl font-bold"><?php echo htmlspecialchars($topAgent['full_name']); ?></p>
                                <p class="text-indigo-200">Promedio: <span
                                        class="text-white font-bold"><?php echo number_format($topAgent['avg_score'], 1); ?>%</span>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-indigo-200">No hay suficientes datos aún.</p>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions or Placeholder Chart -->
                <div class="bg-white rounded-2xl shadow-md p-6 col-span-2 border border-gray-100">
                    <h3 class="text-gray-800 font-bold mb-4">Acciones Rápidas</h3>
                    <div class="flex gap-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create"
                            class="flex-1 bg-gray-50 hover:bg-indigo-50 border border-gray-200 hover:border-indigo-200 rounded-xl p-4 flex flex-col items-center justify-center transition group cursor-pointer">
                            <div
                                class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mb-2 group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700 group-hover:text-indigo-700">Nueva
                                Evaluación</span>
                        </a>
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>reports"
                            class="flex-1 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 rounded-xl p-4 flex flex-col items-center justify-center transition group cursor-pointer">
                            <div
                                class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mb-2 group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700 group-hover:text-blue-700">Ver Reportes</span>
                        </a>
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls"
                            class="flex-1 bg-gray-50 hover:bg-green-50 border border-gray-200 hover:border-green-200 rounded-xl p-4 flex flex-col items-center justify-center transition group cursor-pointer">
                            <div
                                class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-2 group-hover:scale-110 transition-transform">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700 group-hover:text-green-700">Gestionar
                                Llamadas</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Evaluations -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-900">Evaluaciones Recientes</h3>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Ver todas &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Agente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Campaña</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Puntaje</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    QA</th>
                                <th class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentEvaluations as $eval): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($eval['agent_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($eval['campaign_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full <?php echo $eval['percentage'] >= 90 ? 'bg-green-100 text-green-800' : ($eval['percentage'] >= 70 ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo number_format($eval['percentage'], 1); ?>%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($eval['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($eval['qa_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/show?id=<?php echo $eval['id']; ?>"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold">Ver Detalles</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>