<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sistema de Reentrenamiento QA</h1>
                    <p class="mt-1 text-sm text-gray-500">Campanas, modulos por error, bloqueo por leccion y activacion a produccion</p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>training" class="text-sm text-gray-500 hover:text-gray-700">Volver a Entrenamiento</a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
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
                <div class="grid grid-cols-1 gap-6">
                    <?php if (!empty($retrainings)): ?>
                        <?php foreach ($retrainings as $retraining): ?>
                            <?php
                            $modules = $retraining['modules'] ?? [];
                            $progressMap = $retraining['progress_map'] ?? [];
                            ?>
                            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($retraining['campaign_name'] ?? 'Campana'); ?>
                                        </h2>
                                        <p class="text-sm text-gray-500">Estado: <?php echo htmlspecialchars($retraining['status']); ?> | Progreso: <?php echo number_format((float) ($retraining['progress_percent'] ?? 0), 0); ?>%</p>
                                        <?php if (!empty($retraining['due_date'])): ?>
                                            <p class="text-xs text-gray-500 mt-1">Fecha limite: <?php echo htmlspecialchars($retraining['due_date']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($retraining['status'] === 'assigned'): ?>
                                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/start">
                                            <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">Iniciar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <?php foreach ($modules as $module): ?>
                                        <?php
                                        $progress = $progressMap[(int) $module['id']] ?? null;
                                        $status = $progress['status'] ?? 'pending';
                                        $completed = $status === 'completed';

                                        $blocked = false;
                                        foreach ($modules as $prevModule) {
                                            if ((int) $prevModule['sequence_order'] >= (int) $module['sequence_order']) {
                                                break;
                                            }
                                            if ((int) $prevModule['is_required'] === 1) {
                                                $prevProgress = $progressMap[(int) $prevModule['id']] ?? null;
                                                if (!$prevProgress || ($prevProgress['status'] ?? 'pending') !== 'completed') {
                                                    $blocked = true;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <div class="border border-gray-200 rounded-lg p-4 <?php echo $blocked ? 'opacity-60' : ''; ?>">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($module['title']); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1">Estado modulo: <?php echo htmlspecialchars($status); ?> | Minimo: <?php echo number_format((float) $module['pass_score'], 0); ?>%</p>
                                                </div>
                                                <?php if (!empty($progress['score'])): ?>
                                                    <span class="text-xs font-semibold px-2 py-1 rounded bg-gray-100 text-gray-700">Score: <?php echo number_format((float) $progress['score'], 1); ?>%</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($module['lesson_text'])): ?>
                                                <p class="text-sm text-gray-700 mt-3 whitespace-pre-wrap"><?php echo htmlspecialchars($module['lesson_text']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!$completed && !$blocked && !in_array($retraining['status'], ['failed', 'active_in_production'], true)): ?>
                                                <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/module/submit" class="mt-3 space-y-2">
                                                    <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                                    <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                                    <label class="block text-xs text-gray-600">Respuesta de leccion / quiz</label>
                                                    <textarea name="answer_text" rows="3" required class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-sm">Enviar modulo</button>
                                                </form>
                                            <?php elseif ($blocked): ?>
                                                <p class="text-xs text-amber-700 mt-3">Bloqueado: completa lecciones anteriores para avanzar.</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                            <p class="text-sm text-gray-500">No tienes reentrenamientos asignados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 xl:col-span-2">
                        <h2 class="text-lg font-semibold text-gray-900">Crear Reentrenamiento QA</h2>
                        <p class="text-sm text-gray-500 mt-1">1 reentrenamiento por campana/agente. Los modulos se crean por cada error detectado.</p>
                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/create" class="mt-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Campana</label>
                                    <select name="campaign_id" required class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="">Selecciona</option>
                                        <?php foreach (($campaigns ?? []) as $campaign): ?>
                                            <option value="<?php echo $campaign['id']; ?>"><?php echo htmlspecialchars($campaign['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Agente</label>
                                    <select name="agent_id" required class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="">Selecciona</option>
                                        <?php foreach (($agents ?? []) as $agent): ?>
                                            <option value="<?php echo $agent['id']; ?>"><?php echo htmlspecialchars($agent['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Supervisor</label>
                                    <select name="supervisor_id" class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="">Asignar a mi usuario</option>
                                        <?php foreach (($supervisors ?? []) as $supervisor): ?>
                                            <option value="<?php echo $supervisor['id']; ?>"><?php echo htmlspecialchars($supervisor['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Evaluacion origen (opcional)</label>
                                    <select name="evaluation_id" class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="">Sin evaluacion</option>
                                        <?php foreach (($recentEvaluations ?? []) as $evaluation): ?>
                                            <option value="<?php echo $evaluation['id']; ?>">#<?php echo $evaluation['id']; ?> - <?php echo htmlspecialchars($evaluation['agent_name'] ?? 'Agente'); ?> (<?php echo number_format((float) ($evaluation['percentage'] ?? 0), 1); ?>%)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha limite</label>
                                    <input type="date" name="due_date" class="mt-1 w-full rounded-lg border-gray-300">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Errores detectados (uno por linea)</label>
                                <textarea name="detected_errors" rows="5" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="No valida identidad\nNo aplica escucha activa\nNo hace cierre con recap"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
                                <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300"></textarea>
                            </div>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">Crear reentrenamiento</button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Recordatorios</h2>
                        <p class="text-sm text-gray-500 mt-1">Pendientes con fecha limite vencida o del dia.</p>
                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/reminders" class="mt-4">
                            <button type="submit" class="w-full border border-indigo-600 text-indigo-700 hover:bg-indigo-50 font-semibold py-2 px-4 rounded-lg">Enviar recordatorios</button>
                        </form>
                        <div class="mt-4 space-y-2">
                            <?php if (!empty($pendingReminders)): ?>
                                <?php foreach (array_slice($pendingReminders, 0, 8) as $item): ?>
                                    <div class="border border-gray-200 rounded-lg p-2">
                                        <p class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($item['agent_name'] ?? 'Agente'); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['campaign_name'] ?? 'Campana'); ?> | <?php echo htmlspecialchars((string) ($item['due_date'] ?? '')); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">Sin pendientes para recordar.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Seguimiento de Reentrenamientos</h2>
                    <div class="mt-4 space-y-3">
                        <?php if (!empty($retrainings)): ?>
                            <?php foreach ($retrainings as $retraining): ?>
                                <?php
                                $modules = $retraining['modules'] ?? [];
                                $progressMap = $retraining['progress_map'] ?? [];
                                $completedModules = 0;
                                foreach ($modules as $module) {
                                    $p = $progressMap[(int) $module['id']] ?? null;
                                    if (($p['status'] ?? 'pending') === 'completed') {
                                        $completedModules++;
                                    }
                                }
                                ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($retraining['agent_name'] ?? 'Agente'); ?> - <?php echo htmlspecialchars($retraining['campaign_name'] ?? 'Campana'); ?></p>
                                            <p class="text-xs text-gray-500">Estado: <?php echo htmlspecialchars($retraining['status']); ?> | Supervisor: <?php echo htmlspecialchars($retraining['supervisor_name'] ?? 'N/A'); ?></p>
                                            <p class="text-xs text-gray-500">Modulos: <?php echo $completedModules; ?>/<?php echo count($modules); ?> | Progreso: <?php echo number_format((float) ($retraining['progress_percent'] ?? 0), 0); ?>%</p>
                                        </div>
                                        <?php if (in_array($retraining['status'], ['approved', 'in_progress'], true)): ?>
                                            <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/approve">
                                                <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">Aprobar y activar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">No hay reentrenamientos registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
