<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Nuevo cliente corporativo</h1>
                <p class="mt-1 text-sm text-gray-500">Crea el portal y decide que informacion vera el cliente.</p>
            </div>
        </header>

        <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
                    <p class="font-semibold">Revisa los campos:</p>
                    <ul class="mt-2 text-sm list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo \App\Config\Config::BASE_URL; ?>clients/store" method="POST" class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Datos del cliente</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nombre del cliente</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($old['name']); ?>" required
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Industria</label>
                            <input type="text" name="industry" value="<?php echo htmlspecialchars($old['industry']); ?>"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Contacto principal</label>
                            <input type="text" name="contact_name" value="<?php echo htmlspecialchars($old['contact_name']); ?>"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Email de contacto</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($old['contact_email']); ?>"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="active" <?php echo $old['active'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Cliente activo
                        </label>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Usuario del portal</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Usuario</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($old['username']); ?>" required
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nombre completo</label>
                            <input type="text" name="user_full_name" value="<?php echo htmlspecialchars($old['user_full_name']); ?>" required
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Contrasena</label>
                            <input type="password" name="password" required
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="user_active" <?php echo $old['user_active'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Usuario activo
                        </label>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Campanas visibles</h2>
                    <p class="text-sm text-gray-500">Selecciona las campanas que el cliente podra ver.</p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($campaigns as $campaign): ?>
                            <?php $checked = in_array($campaign['id'], $selectedCampaignIds, true); ?>
                            <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                                <input type="checkbox" name="campaign_ids[]"
                                    value="<?php echo $campaign['id']; ?>" <?php echo $checked ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($campaign['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Contenido del portal</h2>
                    <p class="text-sm text-gray-500">Define que modulos estaran disponibles.</p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                            <input type="checkbox" name="show_calls" <?php echo $settings['show_calls'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Llamadas recientes</span>
                        </label>
                        <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                            <input type="checkbox" name="show_evaluations" <?php echo $settings['show_evaluations'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Resumen de evaluaciones</span>
                        </label>
                        <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                            <input type="checkbox" name="show_agent_scores" <?php echo $settings['show_agent_scores'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Top agentes</span>
                        </label>
                        <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                            <input type="checkbox" name="show_ai_summary" <?php echo $settings['show_ai_summary'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Resumen IA</span>
                        </label>
                        <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                            <input type="checkbox" name="show_recordings" <?php echo $settings['show_recordings'] ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Links de grabaciones</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Metricas visibles</h2>
                    <p class="text-sm text-gray-500">El CEO decide que KPIs se muestran en el portal.</p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($metricOptions as $key => $label): ?>
                            <?php $checked = in_array($key, $selectedMetrics, true); ?>
                            <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
                                <input type="checkbox" name="metrics[]"
                                    value="<?php echo htmlspecialchars($key); ?>" <?php echo $checked ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>clients"
                        class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700">Cancelar</a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Crear cliente</button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
