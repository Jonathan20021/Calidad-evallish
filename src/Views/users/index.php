<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
                    <p class="mt-1 text-sm text-gray-500">Gestiona usuarios, roles y estado</p>
                </div>
                <?php if (\App\Helpers\Auth::hasPermission('users.create')): ?>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>users/create"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Usuario
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'self_disable'): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                    <p class="text-sm text-red-700">No puedes desactivar tu propio usuario.</p>
                </div>
            <?php endif; ?>

            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="userSearch" class="block text-sm font-medium text-gray-700">Buscar</label>
                        <input type="text" id="userSearch" placeholder="Nombre, usuario u origen"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="roleFilter" class="block text-sm font-medium text-gray-700">Rol</label>
                        <select id="roleFilter"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="admin" <?php echo ($filters['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="qa" <?php echo ($filters['role'] ?? '') === 'qa' ? 'selected' : ''; ?>>QA</option>
                            <option value="agent" <?php echo ($filters['role'] ?? '') === 'agent' ? 'selected' : ''; ?>>Agente</option>
                            <option value="client" <?php echo ($filters['role'] ?? '') === 'client' ? 'selected' : ''; ?>>Cliente</option>
                        </select>
                    </div>
                    <div>
                        <label for="statusFilter" class="block text-sm font-medium text-gray-700">Estado</label>
                        <select id="statusFilter"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="1" <?php echo ($filters['status'] ?? '') === '1' ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($filters['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div>
                        <label for="pageSize" class="block text-sm font-medium text-gray-700">Filas por página</label>
                        <select id="pageSize"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Listado de Usuarios</h3>
                    <div class="text-sm text-gray-500">
                        Todos los usuarios registrados en el sistema
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="usersTableBody">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No hay usuarios registrados aun.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $role = $user['role'];
                                    $roleLabel = strtoupper($role);
                                    $roleClass = 'bg-gray-100 text-gray-800';
                                    if ($role === 'admin') $roleClass = 'bg-purple-100 text-purple-800';
                                    if ($role === 'qa') $roleClass = 'bg-blue-100 text-blue-800';
                                    if ($role === 'agent') $roleClass = 'bg-green-100 text-green-800';
                                    if ($role === 'client') $roleClass = 'bg-yellow-100 text-yellow-800';
                                    $clientName = $user['client_id'] ? ($clientMap[(int) $user['client_id']] ?? ('Cliente #' . $user['client_id'])) : '-';
                                    $sourceLabel = ($user['source'] ?? '') === 'ponche' ? 'Ponche' : 'Calidad';
                                    $canManage = (bool) ($user['can_manage'] ?? true);
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150"
                                        data-search="<?php echo strtolower(htmlspecialchars($user['full_name'] . ' ' . $user['username'] . ' ' . $role . ' ' . $sourceLabel)); ?>"
                                        data-role="<?php echo htmlspecialchars($role); ?>"
                                        data-active="<?php echo (int) $user['active']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if (($user['source'] ?? '') === 'ponche'): ?>
                                                PO-<?php echo str_pad($user['id'], 3, '0', STR_PAD_LEFT); ?>
                                            <?php else: ?>
                                                US-<?php echo str_pad($user['id'], 3, '0', STR_PAD_LEFT); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                                        <?php echo substr($user['username'], 0, 1); ?>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($user['username']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleClass; ?>">
                                                <?php echo $roleLabel; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($clientName); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($sourceLabel); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ((int) $user['active'] === 1): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Activo</span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($canManage && \App\Helpers\Auth::hasPermission('users.create')): ?>
                                                <a href="<?php echo \App\Config\Config::BASE_URL; ?>users/edit?id=<?php echo $user['id']; ?>"
                                                    class="text-blue-600 hover:text-blue-900">Editar</a>
                                                <form action="<?php echo \App\Config\Config::BASE_URL; ?>users/toggle" method="POST"
                                                    class="inline">
                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                    <?php if ((int) $user['active'] === 1): ?>
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
                                            <?php else: ?>
                                                <span class="text-gray-400">Solo lectura</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="text-sm text-gray-600" id="paginationInfo">Mostrando 0 de 0</div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        Anterior
                    </button>
                    <span class="text-sm text-gray-600" id="pageIndicator">Página 1 de 1</span>
                    <button id="nextPage"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<script>
    (function () {
        const searchInput = document.getElementById('userSearch');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const pageSizeSelect = document.getElementById('pageSize');
        const tableBody = document.getElementById('usersTableBody');
        const rows = Array.from(tableBody ? tableBody.querySelectorAll('tr[data-search]') : []);
        const info = document.getElementById('paginationInfo');
        const indicator = document.getElementById('pageIndicator');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');

        if (!tableBody) {
            return;
        }

        let currentPage = 1;

        const getPageSize = () => parseInt(pageSizeSelect.value, 10) || 10;

        const getFilteredRows = () => {
            const query = (searchInput.value || '').trim().toLowerCase();
            const roleValue = (roleFilter.value || '').trim().toLowerCase();
            const statusValue = statusFilter.value;

            return rows.filter(row => {
                const searchText = (row.dataset.search || '').toLowerCase();
                const rowRole = (row.dataset.role || '').toLowerCase();
                const rowActive = row.dataset.active || '';

                if (query && !searchText.includes(query)) {
                    return false;
                }
                if (roleValue && rowRole !== roleValue) {
                    return false;
                }
                if (statusValue !== '' && rowActive !== statusValue) {
                    return false;
                }
                return true;
            });
        };

        const render = () => {
            const filtered = getFilteredRows();
            const pageSize = getPageSize();
            const total = filtered.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;

            rows.forEach(row => row.classList.add('hidden'));
            filtered.slice(start, end).forEach(row => row.classList.remove('hidden'));

            info.textContent = `Mostrando ${total === 0 ? 0 : start + 1} a ${Math.min(end, total)} de ${total}`;
            indicator.textContent = `Página ${currentPage} de ${totalPages}`;
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;
            prevBtn.classList.toggle('opacity-50', prevBtn.disabled);
            nextBtn.classList.toggle('opacity-50', nextBtn.disabled);
        };

        searchInput.addEventListener('input', () => {
            currentPage = 1;
            render();
        });

        roleFilter.addEventListener('change', () => {
            currentPage = 1;
            render();
        });

        statusFilter.addEventListener('change', () => {
            currentPage = 1;
            render();
        });

        pageSizeSelect.addEventListener('change', () => {
            currentPage = 1;
            render();
        });

        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage -= 1;
                render();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentPage < Math.max(1, Math.ceil(getFilteredRows().length / getPageSize()))) {
                currentPage += 1;
                render();
            }
        });

        render();
    })();
</script>
