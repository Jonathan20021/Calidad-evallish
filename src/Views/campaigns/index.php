<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar (Same as dashboard, could be extracted to partial) -->
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Gestión de Campañas</h1>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns/create"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200">
                    + Nueva Campaña
                </a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($campaigns as $campaign): ?>
                    <div
                        class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300">
                        <div class="px-6 py-5 border-b border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900">
                                <?php echo htmlspecialchars($campaign['name']); ?>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <?php echo htmlspecialchars($campaign['description']); ?>
                            </p>
                        </div>
                        <div class="px-6 py-4 bg-gray-50">
                            <div class="flex justify-between items-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $campaign['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $campaign['active'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
                                <div class="space-x-2">
                                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create?campaign_id=<?php echo $campaign['id']; ?>"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">Editar
                                        Formulario</a>
                                    <span class="text-gray-300">|</span>
                                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns/edit?id=<?php echo $campaign['id']; ?>"
                                        class="text-gray-400 hover:text-gray-600 text-sm">Editar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>