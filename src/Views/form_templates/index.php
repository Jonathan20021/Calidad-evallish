<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Formularios de Evaluación</h1>
                    <p class="mt-1 text-sm text-gray-500">Crea y gestiona formularios personalizables para cada campaña
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Usually created via Campaign, but maybe a direct link if they select campaign later? 
                          For now, flow is Campaign -> Edit Form. So no direct "New" button here without campaign selection logic. 
                          We can link to Campaigns page. -->
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear Formulario
                    </a>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($templates as $template): ?>
                    <div
                        class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition duration-200 flex flex-col overflow-hidden">
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $template['active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo $template['active'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    Actualizado:
                                    <?php echo date('d/m/Y', strtotime($template['updated_at'])); ?>
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">
                                <?php echo htmlspecialchars($template['title']); ?>
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                <?php
                                if (!empty($template['campaign_names'])) {
                                    $campaigns = explode(', ', $template['campaign_names']);
                                    if (count($campaigns) > 2) {
                                        echo htmlspecialchars(implode(', ', array_slice($campaigns, 0, 2))) . ' +' . (count($campaigns) - 2);
                                    } else {
                                        echo htmlspecialchars($template['campaign_names']);
                                    }
                                } else {
                                    echo 'Sin campañas asignadas';
                                }
                                ?>
                            </p>

                            <div class="border-t border-gray-100 pt-4 mt-2">
                                <div class="grid grid-cols-3 gap-2 text-center text-xs text-gray-500">
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <div class="font-bold text-gray-900 text-sm">--</div>
                                        <div>Secciones</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <div class="font-bold text-gray-900 text-sm">--</div>
                                        <div>Criterios</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <div class="font-bold text-gray-900 text-sm">100</div>
                                        <div>Puntos</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/edit?id=<?php echo $template['id']; ?>"
                                class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar
                            </a>
                            <div class="flex space-x-2">
                                <form action="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/toggle"
                                    method="POST">
                                    <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                    <input type="hidden" name="active" value="<?php echo $template['active'] ? 0 : 1; ?>">
                                    <button type="submit" class="text-gray-400 hover:text-gray-600"
                                        title="<?php echo $template['active'] ? 'Inactivar' : 'Activar'; ?>">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m-7 8h8a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </form>
                                <form action="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/duplicate"
                                    method="POST">
                                    <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                    <button type="submit" class="text-gray-400 hover:text-gray-600" title="Duplicar">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </form>
                                <form action="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/delete"
                                    method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este formulario? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                    <button type="submit" class="text-red-400 hover:text-red-600" title="Eliminar">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Placeholder for visual balance if needed or empty state -->
                <?php if (empty($templates)): ?>
                    <div class="col-span-3 text-center py-12 text-gray-500">
                        No hay formularios creados. Crea uno y asígnalo a una campaña.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>