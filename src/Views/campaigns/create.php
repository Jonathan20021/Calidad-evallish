<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col shadow-xl">
        <!-- Sidebar content same as others -->
        <div class="p-6">
            <h1 class="text-2xl font-bold tracking-wider text-indigo-400">EVALLISH</h1>
            <p class="text-xs text-slate-400">Quality Control</p>
        </div>
        <nav class="flex-1 px-4 space-y-2 mt-4">
            <a href="<?php echo \App\Config\Config::BASE_URL; ?>dashboard"
                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all duration-200">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                Dashboard
            </a>
            <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns"
                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 transition-all duration-200">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                Campañas
            </a>
        </nav>
    </aside>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <div class="px-6 py-8 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-2xl font-bold text-gray-900">Crear Nueva Campaña</h2>
                    <p class="mt-1 text-sm text-gray-500">Configure los detalles básicos de la campaña.</p>
                </div>
                <form action="<?php echo \App\Config\Config::BASE_URL; ?>campaigns/store" method="POST"
                    class="p-8 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Campaña</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Ej: Ventas Outbound 2024">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Descripción breve de la campaña..."></textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns"
                            class="mr-4 text-gray-600 hover:text-gray-900 font-medium">Cancelar</a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200">
                            Crear Campaña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>