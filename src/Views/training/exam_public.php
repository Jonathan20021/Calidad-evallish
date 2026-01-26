<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examen de entrenamiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($exam['title']); ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                Agente: <?php echo htmlspecialchars($exam['agent_name']); ?>
                <?php if (!empty($exam['campaign_name'])): ?>
                    | Campana: <?php echo htmlspecialchars($exam['campaign_name']); ?>
                <?php endif; ?>
            </p>
        </div>

        <form method="post" action="<?php echo \App\Config\Config::BASE_URL; ?>training/exams/public/submit"
            class="mt-6 space-y-4">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($exam['public_token']); ?>">
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
</body>
</html>
