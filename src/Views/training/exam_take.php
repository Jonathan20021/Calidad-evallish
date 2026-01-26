<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($exam['title']); ?></h1>
                    <p class="mt-1 text-sm text-gray-500">Responde con claridad y detalle.</p>
                </div>
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>training"
                    class="text-sm text-gray-500 hover:text-gray-700">Volver</a>
            </div>
        </header>

        <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/submit"
                class="space-y-4">
                <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <p class="text-sm font-semibold text-gray-900">
                            <?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?>
                        </p>
                        <div class="mt-3">
                            <?php if ($question['question_type'] === 'mcq' && !empty($question['options_json'])): ?>
                                <?php $options = json_decode($question['options_json'], true) ?: []; ?>
                                <div class="space-y-2">
                                    <?php foreach ($options as $option): ?>
                                        <label class="flex items-center gap-2 text-sm text-gray-700">
                                            <input type="radio" name="answer_<?php echo $question['id']; ?>"
                                                value="<?php echo htmlspecialchars($option); ?>" required>
                                            <span><?php echo htmlspecialchars($option); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <textarea name="answer_<?php echo $question['id']; ?>" rows="4" required
                                    class="mt-2 w-full rounded-lg border-gray-300 text-sm"></textarea>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Peso: <?php echo number_format((float) $question['weight'], 1); ?></p>
                    </div>
                <?php endforeach; ?>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Enviar examen
                </button>
            </form>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
