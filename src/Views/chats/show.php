<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden bg-gray-50">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <a href="<?php echo \App\Config\Config::BASE_URL; ?>chats"
                    class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center transition duration-150">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al listado
                </a>
                <?php if (!$chat['evaluation_id']): ?>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/create?chat_id=<?php echo $chat['id']; ?>"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition duration-200">
                        Iniciar Auditoría
                    </a>
                <?php else: ?>
                    <a href="<?php echo \App\Config\Config::BASE_URL; ?>evaluations/show?id=<?php echo $chat['evaluation_id']; ?>"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition duration-200">
                        Ver Auditoría Realizada
                    </a>
                <?php endif; ?>
            </div>

            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-10 text-white">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-6">
                        <div>
                            <span class="text-indigo-100 text-xs font-bold uppercase tracking-widest bg-white/20 px-3 py-1 rounded-full">Detalle de Interacción</span>
                            <h1 class="text-4xl font-extrabold mt-3 tracking-tight">Chat Wasapi</h1>
                            <p class="text-indigo-100 mt-2 flex items-center opacity-90">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <?php echo date('d \d\e F, Y - H:i', strtotime($chat['chat_date'])); ?>
                            </p>
                        </div>
                        <div class="text-right flex flex-col items-start md:items-end">
                             <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20">
                                <p class="text-xs text-indigo-100 font-medium">Identificador:</p>
                                <p class="text-xl font-bold"><?php echo htmlspecialchars($chat['customer_identifier']); ?></p>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 md:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                        <!-- Information Column -->
                        <div class="lg:col-span-1 space-y-8">
                            <div>
                                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Datos del Agente</h3>
                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xl">
                                            <?php echo substr($chat['agent_name'], 0, 1); ?>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($chat['agent_name']); ?></p>
                                            <p class="text-sm text-gray-500 font-medium">Campaña: <?php echo htmlspecialchars($chat['campaign_name']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($chat['project_name']): ?>
                            <div>
                                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Cliente / Proyecto</h3>
                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                    <p class="text-lg font-bold text-gray-800 tracking-tight"><?php echo htmlspecialchars($chat['project_name']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($chat['notes']): ?>
                            <div>
                                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Notas QA</h3>
                                <div class="bg-indigo-50/50 rounded-2xl p-5 border border-indigo-100">
                                    <p class="text-gray-700 text-sm italic leading-relaxed">"<?php echo nl2br(htmlspecialchars($chat['notes'])); ?>"</p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <form action="<?php echo \App\Config\Config::BASE_URL; ?>chats/delete" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este registro?')">
                                <input type="hidden" name="id" value="<?php echo $chat['id']; ?>">
                                <button type="submit" class="w-full text-red-500 hover:text-red-700 text-sm font-medium transition duration-150 py-2 border border-red-100 rounded-xl hover:bg-red-50">
                                    Eliminar registro de chat
                                </button>
                            </form>
                        </div>

                        <!-- Screenshot Column -->
                        <div class="lg:col-span-2">
                             <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Captura de Pantalla / Evidencia</h3>
                             <div class="bg-gray-100 rounded-3xl overflow-hidden shadow-inner border border-gray-200">
                                <?php if ($chat['screenshot_path']): ?>
                                    <img src="<?php echo \App\Config\Config::BASE_URL . $chat['screenshot_path']; ?>" 
                                         alt="Captura de chat" 
                                         class="w-full h-auto max-h-[700px] object-contain mx-auto cursor-zoom-in"
                                         onclick="window.open(this.src, '_blank')">
                                <?php else: ?>
                                    <div class="py-20 text-center text-gray-400">
                                        <svg class="mx-auto h-16 w-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <p class="text-lg font-medium">Sin imagen adjunta</p>
                                    </div>
                                <?php endif; ?>
                             </div>
                             <div class="mt-4 text-center">
                                <p class="text-xs text-gray-400 font-medium">Haga clic en la imagen para ampliar</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
