<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
                <p class="mt-1 text-sm text-gray-500">Gestiona todo desde un solo lugar.</p>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Campañas</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['campaigns']; ?></p>
                    <div class="mt-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Administrar</a>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Agentes activos</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['agents']; ?></p>
                    <div class="mt-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>agents"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Administrar</a>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Evaluadores activos</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['qas']; ?></p>
                    <div class="mt-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>users"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Gestionar usuarios</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Formularios de Evaluación</h2>
                        <p class="text-sm text-gray-500 mt-1">Personaliza los formularios por campaña.</p>
                        <p class="text-sm text-gray-500 mt-3">Total: <span class="font-semibold"><?php echo $stats['forms']; ?></span></p>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Ver formularios</a>
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>form-templates/create"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Crear nuevo</a>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Llamadas y Evaluaciones</h2>
                        <p class="text-sm text-gray-500 mt-1">Controla grabaciones y resultados.</p>
                        <p class="text-sm text-gray-500 mt-3">Llamadas: <span class="font-semibold"><?php echo $stats['calls']; ?></span></p>
                        <p class="text-sm text-gray-500">Evaluaciones: <span class="font-semibold"><?php echo $stats['evaluations']; ?></span></p>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Ver llamadas</a>
                        <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">Ver evaluaciones</a>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Accesos rápidos</h2>
                <p class="text-sm text-gray-500 mt-1">Acciones frecuentes del departamento de calidad.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>calls/create"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition">Subir llamada</a>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition">Nueva evaluación</a>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>campaigns/create"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition">Crear campaña</a>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>agents/create"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition">Crear agente</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
