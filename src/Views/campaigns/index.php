<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar (Same as dashboard, could be extracted to partial) -->
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Campañas</h1>
                    <p class="text-sm text-gray-500">Datos sincronizados desde la base de ponche</p>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($campaigns as $campaign): ?>
                    <?php
                    $active = (int) ($campaign['active'] ?? $campaign['is_active'] ?? 1) === 1;
                    $color = $campaign['color'] ?? '#6366f1';
                    ?>
                    <div
                        class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300">
                        <div class="px-6 py-5 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">
                                    <?php echo htmlspecialchars($campaign['name']); ?>
                                </h3>
                                <span class="inline-flex items-center text-xs font-medium text-gray-600">
                                    <span class="inline-block h-2 w-2 rounded-full mr-2"
                                        style="background: <?php echo htmlspecialchars($color); ?>"></span>
                                    <?php echo htmlspecialchars($campaign['code'] ?? ''); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <?php echo htmlspecialchars($campaign['description'] ?? ''); ?>
                            </p>
                        </div>
                        <div class="px-6 py-4 bg-white">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Formularios Asignados
                            </h4>
                            <?php if (!empty($campaign['forms'])): ?>
                                <ul class="space-y-2">
                                    <?php foreach ($campaign['forms'] as $form): ?>
                                        <li class="flex items-center text-sm">
                                            <svg class="h-4 w-4 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create?id=<?php echo $form['id']; ?>"
                                                class="text-gray-700 hover:text-indigo-600 transition-colors duration-150">
                                                <?php echo htmlspecialchars($form['title']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-xs text-gray-400 italic">Sin formularios asignados</p>
                            <?php endif; ?>
                        </div>
                        <div class="px-6 py-4 bg-gray-50">
                            <div class="flex justify-between items-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $active ? 'Activa' : 'Inactiva'; ?>
                                </span>
                                <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create?campaign_id=<?php echo $campaign['id']; ?>"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                    <span class="mr-1">+</span> Añadir Formulario
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>