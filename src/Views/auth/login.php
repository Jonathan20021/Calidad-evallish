<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login -
        <?php echo \App\Config\Config::APP_NAME; ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --brand: #3B5998;
            --brand-dark: #2E5C9A;
            --accent: #4A90E2;
            --card: #ffffff;
            --shell: #EFF3F8;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
        }

        .page-bg {
            background:
                radial-gradient(1200px 600px at 80% -10%, rgba(59, 89, 152, 0.15), transparent 60%),
                radial-gradient(900px 600px at -10% 10%, rgba(74, 144, 226, 0.15), transparent 55%),
                linear-gradient(135deg, #EFF3F8 0%, #E0E7FF 100%);
        }

        .auth-card {
            box-shadow:
                0 24px 60px rgba(15, 23, 42, 0.12),
                0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .brand-panel {
            background:
                linear-gradient(135deg, rgba(59, 89, 152, 0.95), rgba(46, 92, 154, 0.98)),
                radial-gradient(260px 140px at 20% 20%, rgba(74, 144, 226, 0.25), transparent 70%);
        }

        .brand-title {
            font-family: 'Fraunces', serif;
        }

        .input-soft {
            background-color: #EFF6FF;
            border-color: #DBEAFE;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
            filter: brightness(0) invert(1);
        }
    </style>
</head>

<body class="page-bg min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-6 lg:p-10">
        <div
            class="auth-card w-full max-w-4xl overflow-hidden rounded-3xl bg-white/80 backdrop-blur-md border border-white/70">
            <div class="grid md:grid-cols-2">
                <div class="brand-panel p-8 md:p-10 text-white flex flex-col justify-between">
                    <div class="space-y-6">
                        <div class="logo-container">
                            <img src="<?php echo \App\Config\Config::BASE_URL; ?>logo.png" alt="Evallish BPO Logo" />
                        </div>
                        <h1 class="brand-title text-3xl md:text-4xl leading-tight text-center">
                            Plataforma de Calidad
                        </h1>
                        <p class="text-white/90 text-sm md:text-base text-center">
                            Controla evaluaciones, auditorías y entrenamiento con una vista clara y profesional.
                        </p>
                    </div>
                    <div class="mt-8 space-y-3 text-xs text-white/80">
                        <div class="flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            <span>Reportes en tiempo real</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            <span>Auditorías con evidencia y feedback</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            <span>Entrenamiento guiado por IA</span>
                        </div>
                    </div>
                </div>

                <div class="p-8 md:p-10 bg-white">
                    <div class="mb-8">
                        <div class="flex justify-center mb-4">
                            <img src="<?php echo \App\Config\Config::BASE_URL; ?>logo.png" alt="Evallish BPO"
                                class="h-12" />
                        </div>
                        <p class="text-sm font-semibold text-blue-600 text-center">Bienvenido</p>
                        <h2 class="text-2xl md:text-3xl font-bold text-slate-900 text-center">Acceso al sistema</h2>
                        <p class="text-sm text-slate-500 mt-2 text-center">
                            Ingresa tus credenciales para continuar.
                        </p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6" role="alert">
                            <p class="font-semibold">Error</p>
                            <p class="text-sm">
                                <?php echo $error; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo \App\Config\Config::BASE_URL; ?>login" method="POST" class="space-y-5">
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Usuario</label>
                            <input type="text" id="username" name="username" required
                                class="input-soft w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out outline-none"
                                placeholder="usuario@empresa">
                        </div>

                        <div>
                            <label for="password"
                                class="block text-sm font-medium text-slate-700 mb-2">Contrasena</label>
                            <input type="password" id="password" name="password" required
                                class="input-soft w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out outline-none"
                                placeholder="********">
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-lg shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Iniciar sesión
                        </button>
                    </form>

                    <div class="mt-6 text-center text-xs text-slate-400">
                        &copy;
                        <?php echo date('Y'); ?> Evallish BPO. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>