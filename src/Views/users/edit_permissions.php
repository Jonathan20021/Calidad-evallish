<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Configurar Permisos</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php echo htmlspecialchars($user['full_name']); ?> 
                        <span class="text-gray-400">(<?php echo htmlspecialchars($user['username']); ?>)</span>
                    </p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>users" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Volver
                </a>
            </div>
        </header>

        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4">
                    <p class="text-sm text-green-700">Permisos actualizados correctamente.</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo \App\Config\Config::BASE_URL; ?>users/permissions/<?php echo $user['id']; ?>">
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Permisos del Usuario</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Configura los permisos específicos para este usuario de Ponche
                        </p>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <!-- Gestión de Usuarios -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Gestión de Usuarios</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_users" value="1" 
                                           <?php echo ($permissions['can_view_users'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver usuarios</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_create_users" value="1" 
                                           <?php echo ($permissions['can_create_users'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Crear y editar usuarios</span>
                                </label>
                            </div>
                        </div>

                        <!-- Agentes -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Agentes</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_agents" value="1" 
                                           <?php echo ($permissions['can_view_agents'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver agentes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_agents" value="1" 
                                           <?php echo ($permissions['can_manage_agents'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Crear y editar agentes</span>
                                </label>
                            </div>
                        </div>

                        <!-- Gestión de Clientes -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Gestión de Clientes</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_clients" value="1" 
                                           <?php echo ($permissions['can_view_clients'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver clientes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_clients" value="1" 
                                           <?php echo ($permissions['can_manage_clients'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Crear y editar clientes</span>
                                </label>
                            </div>
                        </div>

                        <!-- Campañas -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Campañas</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_campaigns" value="1" 
                                           <?php echo ($permissions['can_view_campaigns'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver campañas</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_campaigns" value="1" 
                                           <?php echo ($permissions['can_manage_campaigns'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Crear y editar campañas</span>
                                </label>
                            </div>
                        </div>

                        <!-- Formularios -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Formularios</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_forms" value="1" 
                                           <?php echo ($permissions['can_view_forms'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver formularios</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_forms" value="1" 
                                           <?php echo ($permissions['can_manage_forms'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Gestionar formularios electrónicos</span>
                                </label>
                            </div>
                        </div>

                        <!-- Evaluaciones -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Evaluaciones</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_evaluations" value="1" 
                                           <?php echo ($permissions['can_view_evaluations'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver evaluaciones</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_create_evaluations" value="1" 
                                           <?php echo ($permissions['can_create_evaluations'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Crear evaluaciones</span>
                                </label>
                            </div>
                        </div>

                        <!-- Llamadas -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Llamadas</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_calls" value="1" 
                                           <?php echo ($permissions['can_view_calls'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver llamadas</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_calls" value="1" 
                                           <?php echo ($permissions['can_manage_calls'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Gestionar carga y análisis de llamadas</span>
                                </label>
                            </div>
                        </div>

                        <!-- Reportes -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Reportes</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_reports" value="1" 
                                           <?php echo ($permissions['can_view_reports'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver reportes</span>
                                </label>
                            </div>
                        </div>

                        <!-- Entrenamiento IA -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Entrenamiento IA</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_training" value="1" 
                                           <?php echo ($permissions['can_view_training'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver entrenamiento IA</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_training" value="1" 
                                           <?php echo ($permissions['can_manage_training'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Gestionar entrenamiento IA</span>
                                </label>
                            </div>
                        </div>

                        <!-- Criterios IA -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Criterios IA</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_view_ai_criteria" value="1" 
                                           <?php echo ($permissions['can_view_ai_criteria'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Ver criterios IA</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_ai_criteria" value="1" 
                                           <?php echo ($permissions['can_manage_ai_criteria'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Gestionar criterios de inteligencia artificial</span>
                                </label>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="pb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Configuración</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="can_manage_settings" value="1" 
                                           <?php echo ($permissions['can_manage_settings'] ?? 0) === 1 ? 'checked' : ''; ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">Gestionar configuración del sistema</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>users" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition duration-200">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                            Guardar Permisos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
