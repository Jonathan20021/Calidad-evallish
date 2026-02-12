<!-- Sidebar -->
<aside class="w-64 bg-[#0B1120] text-white hidden md:flex flex-col flex-shrink-0 transition-all duration-300">
    <div class="h-16 flex items-center px-6 bg-[#0B1120] border-b border-gray-800">
        <div class="flex items-center gap-3">
            <img src="<?php echo \App\Config\Config::BASE_URL; ?>logo.png" alt="Evallish BPO" class="h-10 w-auto" />
        </div>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
        <?php
        $currentUri = $_SERVER['REQUEST_URI'];
        $role = $_SESSION['user']['role'] ?? '';
        $menuItems = [
            ['label' => 'Dashboard', 'url' => 'dashboard', 'icon' => '<path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/>'],
            ['label' => 'Evaluaciones', 'url' => 'evaluations', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>'],
            ['label' => 'Campañas', 'url' => 'campaigns', 'icon' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'],
            ['label' => 'Agentes', 'url' => 'agents', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['label' => 'Formularios', 'url' => 'form-templates', 'icon' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>'],
            ['label' => 'Llamadas', 'url' => 'calls', 'icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>'],
            ['label' => 'Entrenamiento IA', 'url' => 'training', 'icon' => '<path d="M12 2l9 4-9 4-9-4 9-4z"/><path d="M3 6v6a9 9 0 0 0 18 0V6"/><path d="M7 10v4"/><path d="M17 10v4"/>'],
            ['label' => 'Reportes', 'url' => 'reports', 'icon' => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>'],
            ['label' => 'Configuración', 'url' => 'settings', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>']
        ];

        if ($role === 'client') {
            $menuItems = [
                [
                    'label' => 'Portal',
                    'url' => 'client-portal',
                    'icon' => '<path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/>'
                ]
            ];
        } else {
            // Add extra items for admin/privileged users
            if ($role === 'admin' || \App\Helpers\Auth::hasPermission('users.view') || \App\Helpers\Auth::hasPermission('users.create')) {
                array_splice($menuItems, 4, 0, [
                    [
                        'label' => 'Usuarios',
                        'url' => 'users',
                        'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/>'
                    ]
                ]);
            }
            if ($role === 'admin' || \App\Helpers\Auth::hasPermission('clients.view') || \App\Helpers\Auth::hasPermission('clients.manage')) {
                $pos = count($menuItems) > 6 ? 6 : count($menuItems);
                array_splice($menuItems, $pos, 0, [
                    [
                        'label' => 'Clientes',
                        'url' => 'clients',
                        'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>'
                    ]
                ]);
            }
            if ($role === 'admin' || \App\Helpers\Auth::hasPermission('ai_criteria.view') || \App\Helpers\Auth::hasPermission('ai_criteria.manage')) {
                $pos = count($menuItems) > 7 ? 7 : count($menuItems);
                array_splice($menuItems, $pos, 0, [
                    [
                        'label' => 'Criterios IA',
                        'url' => 'ai-criteria',
                        'icon' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/><path d="M5 9h4"/><path d="M5 13h3"/>'
                    ]
                ]);
            }

            // Filter all items by permission if not admin
            if ($role !== 'admin') {
                $menuItems = array_values(array_filter($menuItems, function ($item) {
                    switch ($item['url']) {
                        case 'dashboard':
                            return true;
                        case 'evaluations':
                            return \App\Helpers\Auth::hasPermission('evaluations.view');
                        case 'campaigns':
                            return \App\Helpers\Auth::hasPermission('campaigns.view');
                        case 'agents':
                            return \App\Helpers\Auth::hasPermission('agents.view');
                        case 'form-templates':
                            return \App\Helpers\Auth::hasPermission('forms.view');
                        case 'calls':
                            return \App\Helpers\Auth::hasPermission('calls.view');
                        case 'training':
                            return \App\Helpers\Auth::hasPermission('training.view');
                        case 'reports':
                            return \App\Helpers\Auth::hasPermission('reports.view');
                        case 'settings':
                            return \App\Helpers\Auth::hasPermission('settings.manage');
                        case 'users':
                            return \App\Helpers\Auth::hasPermission('users.view') || \App\Helpers\Auth::hasPermission('users.create');
                        case 'clients':
                            return \App\Helpers\Auth::hasPermission('clients.view') || \App\Helpers\Auth::hasPermission('clients.manage');
                        case 'ai-criteria':
                            return \App\Helpers\Auth::hasPermission('ai_criteria.view') || \App\Helpers\Auth::hasPermission('ai_criteria.manage');
                        default:
                            return false;
                    }
                }));
            }
        }

        foreach ($menuItems as $item):
            $isActive = strpos($currentUri, $item['url']) !== false;
            $bgClass = $isActive ? 'bg-[#1E293B] text-white my-1 shadow-sm' : 'text-gray-400 hover:bg-[#111827] hover:text-white';
            $iconColor = $isActive ? 'text-blue-500' : 'text-gray-500';
            $fullUrl = \App\Config\Config::BASE_URL . $item['url'];
            ?>
            <a href="<?php echo $fullUrl; ?>"
                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $bgClass; ?>">
                <svg class="mr-3 flex-shrink-0 h-5 w-5 <?php echo $iconColor; ?> group-hover:text-blue-400 transition-colors"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <?php echo $item['icon']; ?>
                </svg>
                <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="border-t border-gray-800 p-4">
        <div class="flex items-center">
            <div
                class="h-9 w-9 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                <?php echo substr($_SESSION['user']['username'] ?? 'U', 0, 1); ?>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white group-hover:text-white">
                    <?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? 'Usuario'); ?>
                </p>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>logout"
                    class="text-xs text-gray-500 hover:text-gray-300">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</aside>