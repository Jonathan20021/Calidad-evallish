<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($exam['title']); ?></h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Agente: <?php echo htmlspecialchars($exam['agent_name']); ?> | QA: <?php echo htmlspecialchars($exam['qa_name']); ?>
                    </p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>training"
                    class="text-sm text-gray-500 hover:text-gray-700">Volver</a>
            </div>
        </header>

        <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Estado</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($exam['status']); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if (!empty($exam['public_enabled']) && !empty($exam['public_token'])): ?>
                            <?php
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                            $publicUrl = $scheme . '://' . $host . \App\Config\Config::BASE_URL . 'training/exams/public?token=' . $exam['public_token'];
                            ?>
                            <input type="text" readonly
                                value="<?php echo htmlspecialchars($publicUrl); ?>"
                                class="hidden md:block text-xs text-gray-600 border border-gray-200 rounded-lg px-2 py-1 w-72">
                            <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/public/disable">
                                <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                <button type="submit"
                                    class="border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-3 rounded-lg text-xs">
                                    Desactivar enlace
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/public/enable">
                                <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                <button type="submit"
                                    class="border border-indigo-600 text-indigo-700 hover:bg-indigo-50 font-semibold py-2 px-3 rounded-lg text-xs">
                                    Activar enlace público
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if ($exam['status'] !== 'completed'): ?>
                            <a href="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/take?exam_id=<?php echo $exam['id']; ?>"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
                                Tomar examen
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($exam['percentage'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Resultado</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo number_format((float) $exam['percentage'], 1); ?>%
                        </p>
                        <?php if (!empty($exam['ai_summary'])): ?>
                            <p class="text-sm text-gray-600 mt-2"><?php echo nl2br(htmlspecialchars($exam['ai_summary'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900">Preguntas</h2>
                <div class="mt-4 space-y-4">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-900">
                                <?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?>
                            </p>
                            <?php if ($question['question_type'] === 'mcq' && !empty($question['options_json'])): ?>
                                <?php $options = json_decode($question['options_json'], true) ?: []; ?>
                                <ul class="mt-2 text-sm text-gray-600 space-y-1">
                                    <?php foreach ($options as $option): ?>
                                        <li>- <?php echo htmlspecialchars($option); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <p class="text-xs text-gray-400 mt-2">Peso: <?php echo number_format((float) $question['weight'], 1); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
