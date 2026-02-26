<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo \App\Config\Config::APP_NAME; ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        @media (max-width: 768px) {
            main {
                padding-top: 4rem !important;
                /* Make room for floating topbar */
            }

            .mobile-padding {
                padding-bottom: 2rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ sidebarOpen: false }">