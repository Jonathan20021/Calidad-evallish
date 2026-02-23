<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sistema de Reentrenamiento QA</h1>
                    <p class="mt-1 text-sm text-gray-500">Quiz por modulo, simulaciones obligatorias y examen final con minimo de aprobacion</p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>training" class="text-sm text-gray-500 hover:text-gray-700">Volver a Entrenamiento</a>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <?php if (!empty($_GET['success'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (!empty($_GET['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <?php if (($role ?? '') === 'agent'): ?>
                <div class="space-y-6">
                    <?php foreach (($retrainings ?? []) as $retraining): ?>
                        <?php
                        $modules = $retraining['modules'] ?? [];
                        $progressMap = $retraining['progress_map'] ?? [];
                        $simulations = $retraining['simulations'] ?? [];
                        $finalExam = $retraining['final_exam'] ?? null;

                        $requiredModules = 0;
                        $completedModules = 0;
                        foreach ($modules as $m) {
                            if ((int) ($m['is_required'] ?? 1) === 1) {
                                $requiredModules++;
                                $mStatus = $progressMap[(int) $m['id']]['status'] ?? 'pending';
                                if ($mStatus === 'completed') {
                                    $completedModules++;
                                }
                            }
                        }
                        $modulesCompleted = $requiredModules > 0 && $completedModules >= $requiredModules;

                        $totalSims = count($simulations);
                        $completedSims = 0;
                        foreach ($simulations as $s) {
                            if (($s['status'] ?? 'pending') === 'completed') {
                                $completedSims++;
                            }
                        }
                        $simulationsCompleted = $totalSims > 0 && $completedSims >= $totalSims;
                        $finalExamPassed = !empty($finalExam) && (($finalExam['status'] ?? 'pending') === 'passed');
                        ?>
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($retraining['campaign_name'] ?? 'Campana'); ?></h2>
                                    <p class="text-sm text-gray-500">Estado: <?php echo htmlspecialchars($retraining['status']); ?> | Progreso global: <?php echo number_format((float) ($retraining['progress_percent'] ?? 0), 0); ?>%</p>
                                </div>
                                <?php if ($retraining['status'] === 'assigned'): ?>
                                    <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/start">
                                        <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">Iniciar</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="text-sm font-semibold text-gray-900">1) Quiz por modulo</p>
                                <p class="text-xs text-gray-500">Debes aprobar cada modulo para desbloquear el siguiente.</p>
                                <div class="mt-3 space-y-3">
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
                                                $prev = $progressMap[(int) $prevModule['id']] ?? null;
                                                if (!$prev || ($prev['status'] ?? 'pending') !== 'completed') {
                                                    $blocked = true;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <div class="border border-gray-200 rounded-lg p-3 <?php echo $blocked ? 'opacity-60' : ''; ?>">
                                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($module['title']); ?></p>
                                            <p class="text-xs text-gray-500">Estado: <?php echo htmlspecialchars($status); ?> | Minimo: <?php echo number_format((float) $module['pass_score'], 0); ?>%</p>
                                            <?php if (!empty($module['lesson_text'])): ?>
                                                <p class="text-sm text-gray-700 mt-2 whitespace-pre-wrap"><?php echo htmlspecialchars($module['lesson_text']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($module['quiz_question'])): ?>
                                                <p class="text-xs text-indigo-700 mt-2"><strong>Quiz:</strong> <?php echo htmlspecialchars($module['quiz_question']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!$completed && !$blocked && !in_array($retraining['status'], ['failed', 'active_in_production'], true)): ?>
                                                <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/module/submit" class="mt-2 space-y-2">
                                                    <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                                    <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                                    <textarea name="answer_text" rows="3" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="Respuesta del quiz"></textarea>
                                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-sm">Enviar quiz</button>
                                                </form>
                                            <?php elseif ($blocked): ?>
                                                <p class="text-xs text-amber-700 mt-2">Bloqueado por orden secuencial.</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-4 <?php echo !$modulesCompleted ? 'opacity-60' : ''; ?>">
                                <p class="text-sm font-semibold text-gray-900">2) Simulaciones obligatorias</p>
                                <p class="text-xs text-gray-500">Cliente molesto, upselling y error operativo/proceso. Incluye checklist y feedback auto/manual.</p>
                                <div class="mt-3 space-y-3">
                                    <?php foreach ($simulations as $sim): ?>
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($sim['title']); ?></p>
                                            <p class="text-xs text-gray-500">Modo feedback: <?php echo htmlspecialchars($sim['feedback_mode']); ?> | Estado: <?php echo htmlspecialchars($sim['status']); ?> | Minimo: <?php echo number_format((float) ($sim['min_score'] ?? 80), 0); ?>%</p>
                                            <p class="text-sm text-gray-700 mt-2"><?php echo htmlspecialchars($sim['scenario_text'] ?? ''); ?></p>
                                            <?php if (!empty($sim['feedback_text'])): ?>
                                                <p class="text-xs text-indigo-700 mt-2">Feedback: <?php echo htmlspecialchars($sim['feedback_text']); ?></p>
                                            <?php endif; ?>

                                            <?php if ($modulesCompleted && !in_array($sim['status'], ['completed'], true) && !in_array($retraining['status'], ['failed', 'active_in_production'], true)): ?>
                                                <?php $availableChecklist = json_decode((string) ($sim['checklist_json'] ?? '[]'), true); if (!is_array($availableChecklist)) { $availableChecklist = []; } ?>
                                                <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/simulation/submit" class="mt-3 space-y-2">
                                                    <input type="hidden" name="simulation_id" value="<?php echo $sim['id']; ?>">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                        <?php foreach ($availableChecklist as $item): ?>
                                                            <label class="text-xs text-gray-700 flex items-center gap-1">
                                                                <input type="checkbox" name="checklist[]" value="<?php echo htmlspecialchars($item); ?>"> <?php echo htmlspecialchars($item); ?>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <textarea name="transcript_text" rows="3" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="Escribe tu simulacion (dialogo/roleplay)"></textarea>
                                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-sm">Enviar simulacion</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-4 <?php echo (!$modulesCompleted || !$simulationsCompleted) ? 'opacity-60' : ''; ?>">
                                <p class="text-sm font-semibold text-gray-900">3) Examen final obligatorio</p>
                                <?php if (!empty($finalExam)): ?>
                                    <p class="text-xs text-gray-500">Estado: <?php echo htmlspecialchars($finalExam['status']); ?> | Minimo: <?php echo number_format((float) ($finalExam['min_score'] ?? 80), 0); ?>% <?php if (!empty($finalExam['score'])): ?>| Score: <?php echo number_format((float) $finalExam['score'], 1); ?>%<?php endif; ?></p>
                                    <?php $questions = json_decode((string) ($finalExam['question_payload_json'] ?? '[]'), true); if (!is_array($questions)) { $questions = []; } ?>
                                    <?php if ($modulesCompleted && $simulationsCompleted && !$finalExamPassed && !in_array($retraining['status'], ['failed', 'active_in_production'], true)): ?>
                                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/final-exam/submit" class="mt-3 space-y-3">
                                            <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                            <?php foreach ($questions as $idx => $q): ?>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700"><?php echo ($idx + 1) . '. ' . htmlspecialchars($q['question'] ?? 'Pregunta'); ?></label>
                                                    <textarea name="final_answer_<?php echo $idx; ?>" rows="2" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"></textarea>
                                                </div>
                                            <?php endforeach; ?>
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-sm">Enviar examen final</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 xl:col-span-2">
                        <h2 class="text-lg font-semibold text-gray-900">Crear Reentrenamiento QA</h2>
                        <p class="text-sm text-gray-500 mt-1">Incluye quiz por modulo, simulaciones obligatorias y examen final. Las simulaciones usan las plantillas configuradas por campana.</p>
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
                                    <label class="block text-sm font-medium text-gray-700">Feedback simulaciones</label>
                                    <select name="simulation_feedback_mode" class="mt-1 w-full rounded-lg border-gray-300">
                                        <option value="auto">Automatico</option>
                                        <option value="manual">Manual (Supervisor)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Minimo aprobacion final (%)</label>
                                    <input type="number" name="final_min_score" min="50" max="100" value="80" class="mt-1 w-full rounded-lg border-gray-300">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
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

                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">Crear reentrenamiento</button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Recordatorios</h2>
                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/reminders" class="mt-3">
                            <button type="submit" class="w-full border border-indigo-600 text-indigo-700 hover:bg-indigo-50 font-semibold py-2 px-4 rounded-lg">Enviar recordatorios</button>
                        </form>
                        <div class="mt-4 space-y-2">
                            <?php foreach (array_slice(($pendingReminders ?? []), 0, 8) as $item): ?>
                                <div class="border border-gray-200 rounded-lg p-2">
                                    <p class="text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($item['agent_name'] ?? 'Agente'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['campaign_name'] ?? 'Campana'); ?> | <?php echo htmlspecialchars((string) ($item['due_date'] ?? '')); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Plantillas de Simulacion (UI)</h2>
                    <p class="text-sm text-gray-500 mt-1">Administra checklist, escenarios, feedback y puntaje minimo por campana.</p>
                    <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/templates/save" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Campana</label>
                            <select name="campaign_id" class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="">Global (todas)</option>
                                <?php foreach (($campaigns ?? []) as $campaign): ?>
                                    <option value="<?php echo $campaign['id']; ?>"><?php echo htmlspecialchars($campaign['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="simulation_type" required class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="angry_client">Cliente molesto</option>
                                <option value="upselling">Upselling</option>
                                <option value="process_error">Error operativo/proceso</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Modo feedback</label>
                            <select name="feedback_mode" class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="auto">Automatico</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Titulo</label>
                            <input name="title" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="Simulacion obligatoria: cliente molesto">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Puntaje minimo</label>
                            <input type="number" name="min_score" min="50" max="100" value="80" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Escenario</label>
                            <textarea name="scenario_text" rows="2" required class="mt-1 w-full rounded-lg border-gray-300"></textarea>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Checklist (uno por linea)</label>
                            <textarea name="checklist_text" rows="3" class="mt-1 w-full rounded-lg border-gray-300" placeholder="saludo_profesional&#10;identificacion_cliente&#10;escucha_activa&#10;empatia&#10;resolucion_clara&#10;cierre_efectivo"></textarea>
                        </div>
                        <div class="md:col-span-3 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="active" checked> Activa
                            </label>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-4 rounded-lg">Guardar plantilla</button>
                        </div>
                    </form>

                    <div class="mt-5 space-y-2">
                        <?php foreach (($simulationTemplates ?? []) as $tpl): ?>
                            <?php $tplChecklist = json_decode((string) ($tpl['checklist_json'] ?? '[]'), true); if (!is_array($tplChecklist)) { $tplChecklist = []; } ?>
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($tpl['title']); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($tpl['campaign_name'] ?? 'Global'); ?> |
                                            <?php echo htmlspecialchars($tpl['simulation_type']); ?> |
                                            Feedback: <?php echo htmlspecialchars($tpl['feedback_mode']); ?> |
                                            Min: <?php echo number_format((float) ($tpl['min_score'] ?? 80), 0); ?>% |
                                            Estado: <?php echo (int) ($tpl['active'] ?? 0) === 1 ? 'Activa' : 'Inactiva'; ?>
                                        </p>
                                    </div>
                                    <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/templates/toggle">
                                        <input type="hidden" name="template_id" value="<?php echo $tpl['id']; ?>">
                                        <button type="submit" class="text-xs border border-gray-300 rounded px-2 py-1 hover:bg-gray-50">
                                            <?php echo (int) ($tpl['active'] ?? 0) === 1 ? 'Desactivar' : 'Activar'; ?>
                                        </button>
                                    </form>
                                </div>
                                <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/templates/save" class="mt-2 grid grid-cols-1 md:grid-cols-4 gap-2">
                                    <input type="hidden" name="template_id" value="<?php echo $tpl['id']; ?>">
                                    <input type="hidden" name="active" value="<?php echo (int) ($tpl['active'] ?? 0) === 1 ? '1' : ''; ?>">
                                    <select name="campaign_id" class="rounded-lg border-gray-300 text-xs">
                                        <option value="">Global</option>
                                        <?php foreach (($campaigns ?? []) as $campaign): ?>
                                            <option value="<?php echo $campaign['id']; ?>" <?php echo ((int) ($tpl['campaign_id'] ?? 0) === (int) $campaign['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($campaign['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="simulation_type" class="rounded-lg border-gray-300 text-xs">
                                        <option value="angry_client" <?php echo ($tpl['simulation_type'] ?? '') === 'angry_client' ? 'selected' : ''; ?>>Cliente molesto</option>
                                        <option value="upselling" <?php echo ($tpl['simulation_type'] ?? '') === 'upselling' ? 'selected' : ''; ?>>Upselling</option>
                                        <option value="process_error" <?php echo ($tpl['simulation_type'] ?? '') === 'process_error' ? 'selected' : ''; ?>>Error proceso</option>
                                    </select>
                                    <select name="feedback_mode" class="rounded-lg border-gray-300 text-xs">
                                        <option value="auto" <?php echo ($tpl['feedback_mode'] ?? '') === 'auto' ? 'selected' : ''; ?>>Auto</option>
                                        <option value="manual" <?php echo ($tpl['feedback_mode'] ?? '') === 'manual' ? 'selected' : ''; ?>>Manual</option>
                                    </select>
                                    <input type="number" name="min_score" min="50" max="100" value="<?php echo number_format((float) ($tpl['min_score'] ?? 80), 0, '.', ''); ?>" class="rounded-lg border-gray-300 text-xs">
                                    <input name="title" value="<?php echo htmlspecialchars($tpl['title']); ?>" class="md:col-span-2 rounded-lg border-gray-300 text-xs">
                                    <input name="scenario_text" value="<?php echo htmlspecialchars($tpl['scenario_text']); ?>" class="md:col-span-2 rounded-lg border-gray-300 text-xs">
                                    <textarea name="checklist_text" rows="2" class="md:col-span-3 rounded-lg border-gray-300 text-xs"><?php echo htmlspecialchars(implode("\n", $tplChecklist)); ?></textarea>
                                    <div class="md:col-span-1 flex items-center justify-end">
                                        <button type="submit" class="bg-gray-900 hover:bg-black text-white font-semibold py-1.5 px-3 rounded text-xs">Actualizar</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Seguimiento y evaluacion</h2>
                    <div class="mt-4 space-y-3">
                        <?php foreach (($retrainings ?? []) as $retraining): ?>
                            <?php
                            $simulations = $retraining['simulations'] ?? [];
                            $finalExam = $retraining['final_exam'] ?? null;
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($retraining['agent_name'] ?? 'Agente'); ?> - <?php echo htmlspecialchars($retraining['campaign_name'] ?? 'Campana'); ?></p>
                                        <p class="text-xs text-gray-500">Estado: <?php echo htmlspecialchars($retraining['status']); ?> | Progreso: <?php echo number_format((float) ($retraining['progress_percent'] ?? 0), 0); ?>%</p>
                                        <p class="text-xs text-gray-500">Examen final: <?php echo htmlspecialchars($finalExam['status'] ?? 'N/A'); ?> <?php if (!empty($finalExam['score'])): ?>| <?php echo number_format((float) $finalExam['score'], 1); ?>%<?php endif; ?></p>
                                    </div>
                                    <?php if (in_array($retraining['status'], ['approved', 'in_progress'], true)): ?>
                                        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/approve">
                                            <input type="hidden" name="retraining_id" value="<?php echo $retraining['id']; ?>">
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">Activar produccion</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <?php foreach ($simulations as $sim): ?>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                        <p class="text-xs font-semibold text-gray-800"><?php echo htmlspecialchars($sim['title']); ?> | Estado: <?php echo htmlspecialchars($sim['status']); ?> | Modo: <?php echo htmlspecialchars($sim['feedback_mode']); ?></p>
                                        <?php if (($sim['status'] ?? '') === 'pending_review' || (($sim['feedback_mode'] ?? '') === 'manual' && in_array(($sim['status'] ?? ''), ['pending', 'failed'], true))): ?>
                                            <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/retraining/simulation/review" class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                                                <input type="hidden" name="simulation_id" value="<?php echo $sim['id']; ?>">
                                                <input type="number" name="score" min="0" max="100" step="0.1" required class="rounded-lg border-gray-300 text-sm" placeholder="Score">
                                                <input type="text" name="feedback_text" class="rounded-lg border-gray-300 text-sm" placeholder="Feedback manual">
                                                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-sm">Guardar evaluacion</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
