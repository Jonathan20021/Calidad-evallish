<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Agentes</h1>
                    <p class="mt-1 text-sm text-gray-500">Gestiona agentes y QA del sistema de ponche</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>agents/create"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Agente
                    </a>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Lista de Agentes</h3>
                    <div class="text-sm text-gray-500">
                        Agentes y QA sincronizados con ponche
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rol</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($agents)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No hay agentes registrados aún.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agents as $agent): ?>
                                    <?php
                                    $role = $agent['role'] ?? 'agent';
                                    $roleLabel = strtoupper($role);
                                    $roleClass = $role === 'qa' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                                    $isActive = (int) ($agent['active'] ?? 1) === 1;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            AG-
                                            <?php echo str_pad($agent['id'], 3, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                                        <?php echo substr($agent['username'], 0, 1); ?>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($agent['full_name']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($agent['username']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleClass; ?>">
                                                <?php echo $roleLabel; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($isActive): ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Activo
                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>agents/edit?id=<?php echo $agent['id']; ?>"
                                                class="text-blue-600 hover:text-blue-900">Editar</a>
                                            <form action="<?php echo \App\Config\Config::BASE_URL; ?>agents/toggle" method="POST"
                                                class="inline">
                                                <input type="hidden" name="id" value="<?php echo $agent['id']; ?>">
                                                <?php if ($isActive): ?>
                                                    <input type="hidden" name="active" value="0">
                                                    <button type="submit"
                                                        class="ml-4 text-red-600 hover:text-red-900"
                                                        onclick="return confirm('¿Deseas desactivar este usuario?');">
                                                        Desactivar
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="active" value="1">
                                                    <button type="submit"
                                                        class="ml-4 text-green-600 hover:text-green-900">
                                                        Activar
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
