<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Clientes corporativos</h1>
                    <p class="mt-1 text-sm text-gray-500">Controla accesos y contenido del portal de clientes.</p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>clients/create"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition">
                    Nuevo cliente
                </a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <?php 
                        echo htmlspecialchars($_SESSION['success_message']); 
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <?php 
                        echo htmlspecialchars($_SESSION['error_message']); 
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Industria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario portal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campanas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($clients as $client): ?>
                                <?php
                                $campaigns = $campaignMap[$client['id']] ?? [];
                                $campaignNames = array_map(fn($row) => $row['name'], $campaigns);
                                $portalUser = $client['portal_username'] ? $client['portal_username'] : 'Sin usuario';
                                $portalUserName = $client['portal_user_name'] ?? '';
                                $portalStatus = (int) ($client['portal_user_active'] ?? 0) === 1 ? 'Activo' : 'Inactivo';
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($client['name']); ?>
                                        <div class="text-xs text-gray-500 font-normal">
                                            <?php echo htmlspecialchars($client['contact_name'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($client['industry'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($portalUser); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($portalUserName); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php if (empty($campaignNames)): ?>
                                            <span class="text-xs text-gray-400">Sin campanas</span>
                                        <?php else: ?>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($campaignNames as $name): ?>
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                        <?php echo htmlspecialchars($name); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $client['active'] ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600'; ?>">
                                            <?php echo $client['active'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                        <div class="text-xs text-gray-400 mt-1">Portal: <?php echo $portalStatus; ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>clients/edit?id=<?php echo $client['id']; ?>"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium">Editar</a>
                                        
                                        <?php if (!empty($client['contact_email']) && $client['portal_username']): ?>
                                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>clients/send-credentials?id=<?php echo $client['id']; ?>"
                                                class="text-emerald-600 hover:text-emerald-900 font-medium"
                                                onclick="return confirm('¿Enviar credenciales a <?php echo htmlspecialchars($client['contact_email']); ?>?');"
                                                title="Enviar credenciales por correo">
                                                📧 Enviar credenciales
                                            </a>
                                        <?php endif; ?>
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
