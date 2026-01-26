<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login -
        <?php echo \App\Config\Config::APP_NAME; ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-login {
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="bg-login min-h-screen flex items-center justify-center p-4">

    <div class="bg-white/95 backdrop-blur-sm w-full max-w-md p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Evallish BPO</h1>
            <p class="text-gray-500 mt-2 text-sm">Sistema de Control de Calidad</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r" role="alert">
                <p class="font-bold">Error</p>
                <p class="text-sm">
                    <?php echo $error; ?>
                </p>
            </div>
        <?php endif; ?>

        <form action="<?php echo \App\Config\Config::BASE_URL; ?>login" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out outline-none bg-gray-50"
                    placeholder="Ingrese su usuario">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 ease-in-out outline-none bg-gray-50"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transform transition duration-200 hover:scale-[1.02] active:scale-[0.98] shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                Iniciar Sesión
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-400">
            &copy;
            <?php echo date('Y'); ?> Evallish BPO. Todos los derechos reservados.
        </div>
    </div>

</body>

</html>