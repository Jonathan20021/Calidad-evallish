<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Entrenamiento IA</h1>
                <p class="mt-1 text-sm text-gray-500">Roleplay inteligente y examenes personalizados</p>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
            <?php if (!empty($_GET['success'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php elseif (!empty($_GET['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (($role ?? '') === 'agent'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Iniciar roleplay</h2>
                        <p class="text-sm text-gray-500 mt-1">Selecciona un guion y empieza a practicar.</p>
                        <?php if (!empty($scripts)): ?>
                            <form class="mt-4" method="get"
                                action="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/start">
                                <label class="block text-sm font-medium text-gray-700">Guion disponible</label>
                                <select name="script_id" class="mt-1 w-full rounded-lg border-gray-300">
                                    <?php foreach ($scripts as $script): ?>
                                        <option value="<?php echo $script['id']; ?>">
                                            <?php echo htmlspecialchars($script['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit"
                                    class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                                    Comenzar roleplay
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mt-3">No hay guiones disponibles.</p>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Mis examenes</h2>
                        <p class="text-sm text-gray-500 mt-1">Examenes asignados por QA.</p>
                        <div class="mt-4 space-y-3">
                            <?php if (!empty($agentExams)): ?>
                                <?php foreach ($agentExams as $exam): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($exam['title']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Estado: <?php echo htmlspecialchars($exam['status']); ?>
                                            </p>
                                        </div>
                                        <a class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                            href="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/take?exam_id=<?php echo $exam['id']; ?>">
                                            Abrir
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No tienes examenes asignados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Mis roleplays</h2>
                    <div class="mt-4 space-y-3">
                        <?php if (!empty($agentRoleplays)): ?>
                            <?php foreach ($agentRoleplays as $roleplay): ?>
                                <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($roleplay['script_title'] ?? 'Roleplay'); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Estado: <?php echo htmlspecialchars($roleplay['status']); ?>
                                        </p>
                                    </div>
                                    <a class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                        href="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay?session_id=<?php echo $roleplay['id']; ?>">
                                        Continuar
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Aun no tienes roleplays activos.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <?php
                    $roleplayCompletion = !empty($roleplayStats['total']) ? round(($roleplayStats['completed'] / $roleplayStats['total']) * 100) : 0;
                    $examCompletion = !empty($examStats['total']) ? round(($examStats['completed'] / $examStats['total']) * 100) : 0;
                    ?>
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase text-gray-500">Roleplays</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo (int) ($roleplayStats['total'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Completados: <?php echo $roleplayCompletion; ?>%</p>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase text-gray-500">Score promedio</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo $roleplayStats['avg_score'] !== null ? number_format((float) $roleplayStats['avg_score'], 1) : 'N/A'; ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">IA Coach</p>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase text-gray-500">Examenes</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo (int) ($examStats['total'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Completados: <?php echo $examCompletion; ?>%</p>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase text-gray-500">Promedio examen</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo $examStats['avg_percentage'] !== null ? number_format((float) $examStats['avg_percentage'], 1) . '%' : 'N/A'; ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Global</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 xl:col-span-2">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Mejores llamadas QA</h2>
                            <span class="text-xs text-gray-500">Top evaluadas</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            <?php if (!empty($topCalls)): ?>
                                <?php foreach ($topCalls as $call): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($call['agent_name']); ?> - <?php echo htmlspecialchars($call['campaign_name']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Score: <?php echo number_format((float) $call['percentage'], 1); ?>%
                                                | <?php echo date('d/m/Y', strtotime($call['call_datetime'])); ?>
                                            </p>
                                        </div>
                                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/scripts/from-best-call">
                                            <input type="hidden" name="call_id" value="<?php echo $call['call_id']; ?>">
                                            <button type="submit"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Generar guion IA
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No hay llamadas evaluadas con grabacion.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Subir guion</h2>
                        <form class="mt-4 space-y-3" method="post"
                            action="<?php echo \App\Config\Config::BASE_URL; ?>training/scripts/upload"
                            enctype="multipart/form-data">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Titulo</label>
                                <input name="title" class="mt-1 w-full rounded-lg border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Campana (opcional)</label>
                                <select name="campaign_id" class="mt-1 w-full rounded-lg border-gray-300">
                                    <option value="">Sin campana</option>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <option value="<?php echo $campaign['id']; ?>">
                                            <?php echo htmlspecialchars($campaign['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Escenario (opcional)</label>
                                <textarea name="scenario_text" rows="3"
                                    class="mt-1 w-full rounded-lg border-gray-300"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guion</label>
                                <textarea name="script_text" rows="4"
                                    class="mt-1 w-full rounded-lg border-gray-300"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Archivo (txt)</label>
                                <input type="file" name="script_file" class="mt-1 w-full text-sm text-gray-600">
                            </div>
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                                Guardar guion
                            </button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Generar examen IA</h2>
                        <form class="mt-4 space-y-3" method="post"
                            action="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/generate">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Agente</label>
                                <select name="agent_id" class="mt-1 w-full rounded-lg border-gray-300" required>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>">
                                            <?php echo htmlspecialchars($agent['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Campana</label>
                                <select name="campaign_id" class="mt-1 w-full rounded-lg border-gray-300">
                                    <option value="">Sin campana</option>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <option value="<?php echo $campaign['id']; ?>">
                                            <?php echo htmlspecialchars($campaign['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Preguntas</label>
                                    <input type="number" name="num_questions" min="4" max="20" value="8"
                                        class="mt-1 w-full rounded-lg border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Dificultad</label>
                                    <select name="difficulty" class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="baja">Baja</option>
                                        <option value="media" selected>Media</option>
                                        <option value="alta">Alta</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                                Generar examen
                            </button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Guiones disponibles</h2>
                        <p class="text-sm text-gray-500 mt-1">Asigna roleplays a agentes.</p>
                        <div class="mt-4 space-y-3">
                            <?php if (!empty($scripts)): ?>
                                <?php foreach ($scripts as $script): ?>
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($script['title']); ?></p>
                                        <form class="mt-2 space-y-2" method="get"
                                            action="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay/start">
                                            <input type="hidden" name="script_id" value="<?php echo $script['id']; ?>">
                                            <select name="agent_id" class="w-full rounded-lg border-gray-300 text-sm">
                                                <?php foreach ($agents as $agent): ?>
                                                    <option value="<?php echo $agent['id']; ?>">
                                                        <?php echo htmlspecialchars($agent['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="rubric_id" class="w-full rounded-lg border-gray-300 text-sm">
                                                <option value="">Rubrica (opcional)</option>
                                                <?php foreach ($rubrics as $rubric): ?>
                                                    <option value="<?php echo $rubric['id']; ?>">
                                                        <?php echo htmlspecialchars($rubric['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <textarea name="objectives_text" rows="2"
                                                class="w-full rounded-lg border-gray-300 text-xs"
                                                placeholder="Objetivos del coach (saludo, verificacion, cierre, etc)"></textarea>
                                            <input name="tone_text"
                                                class="w-full rounded-lg border-gray-300 text-xs"
                                                placeholder="Tono del cliente (ej: impaciente, curioso, molesto)">
                                            <input name="obstacles_text"
                                                class="w-full rounded-lg border-gray-300 text-xs"
                                                placeholder="Obstaculos (ej: poco tiempo, no confia, compara precios)">
                                            <button type="submit"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Iniciar roleplay
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No hay guiones cargados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Rubricas QA</h2>
                        <p class="text-sm text-gray-500 mt-1">Crea criterios por campaña para coaching.</p>
                        <form class="mt-4 space-y-3" method="post"
                            action="<?php echo \App\Config\Config::BASE_URL; ?>training/rubrics/create">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Titulo</label>
                                <input name="title" class="mt-1 w-full rounded-lg border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Campana</label>
                                <select name="campaign_id" class="mt-1 w-full rounded-lg border-gray-300">
                                    <option value="">Sin campana</option>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <option value="<?php echo $campaign['id']; ?>">
                                            <?php echo htmlspecialchars($campaign['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Items (uno por linea)</label>
                                <textarea name="items" rows="4" class="mt-1 w-full rounded-lg border-gray-300"
                                    placeholder="Saludo profesional&#10;Validacion de datos&#10;Empatia&#10;Cierre efectivo"
                                    required></textarea>
                            </div>
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                                Crear rubrica
                            </button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Rubricas activas</h2>
                        <div class="mt-4 space-y-3">
                            <?php if (!empty($rubrics)): ?>
                                <?php foreach ($rubrics as $rubric): ?>
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($rubric['title']); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($rubric['campaign_name'] ?? 'General'); ?> ·
                                            Por <?php echo htmlspecialchars($rubric['created_by_name'] ?? 'QA'); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">Aun no hay rubricas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Roleplays recientes</h2>
                        <div class="mt-4 space-y-3">
                            <?php if (!empty($recentRoleplays)): ?>
                                <?php foreach ($recentRoleplays as $roleplay): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($roleplay['agent_name']); ?> - <?php echo htmlspecialchars($roleplay['script_title'] ?? 'Roleplay'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">Estado: <?php echo htmlspecialchars($roleplay['status']); ?></p>
                                        </div>
                                        <a class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                            href="<?php echo \App\Config\Config::BASE_URL; ?>training/roleplay?session_id=<?php echo $roleplay['id']; ?>">
                                            Ver
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No hay sesiones registradas.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Examenes recientes</h2>
                        <div class="mt-4 space-y-3">
                            <?php if (!empty($recentExams)): ?>
                                <?php foreach ($recentExams as $exam): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($exam['agent_name']); ?> - <?php echo htmlspecialchars($exam['title']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">Estado: <?php echo htmlspecialchars($exam['status']); ?></p>
                                        </div>
                                        <a class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                            href="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/view?exam_id=<?php echo $exam['id']; ?>">
                                            Ver
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No hay examenes registrados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
