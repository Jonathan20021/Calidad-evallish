<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Editar Agente</h1>
            </div>
        </header>

        <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Información del Agente</h3>
                    <p class="mt-1 text-sm text-gray-500">Modifique los datos del agente.</p>
                </div>

                <form action="<?php echo \App\Config\Config::BASE_URL; ?>agents/update" method="POST"
                    class="p-8 space-y-6">
                    <input type="hidden" name="id" value="<?php echo $agent['id']; ?>">

                    <?php if (isset($_GET['error'])): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <?php
                                        if ($_GET['error'] === 'invalid_role') {
                                            echo 'El rol seleccionado no es valido.';
                                        } else {
                                            echo 'Por favor completa los campos requeridos.';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Usuario</label>
                            <input type="text" value="<?php echo htmlspecialchars($agent['username']); ?>" disabled
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 text-gray-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">El nombre de usuario no se puede cambiar.</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                            <select id="role" name="role" required
                                class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="AGENT" <?php echo $agent['role'] === 'agent' ? 'selected' : ''; ?>>Agente</option>
                                <option value="QA" <?php echo $agent['role'] === 'qa' ? 'selected' : ''; ?>>QA</option>
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Nombre
                                Completo</label>
                            <input type="text" name="full_name" id="full_name" required
                                value="<?php echo htmlspecialchars($agent['full_name']); ?>"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="inline-flex items-center mt-8">
                                <input type="checkbox" name="active" <?php echo (int) $agent['active'] === 1 ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Activo</span>
                            </label>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="password" class="block text-sm font-medium text-gray-700">Nueva
                                Contraseña</label>
                            <input type="password" name="password" id="password"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Dejar en blanco para mantener la actual">
                        </div>
                    </div>

                    <div class="pt-5 border-t border-gray-200 flex justify-end">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>agents"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Actualizar Agente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
