<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GV LIVE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        deepVoid: '#050505',
                        neonPink: '#FF00DE',
                        neonCyan: '#00F0FF',
                        neonPurple: '#7B00FF',
                    },
                    boxShadow: {
                        'neon': '0 0 20px rgba(0, 240, 255, 0.3)',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-deepVoid min-h-screen flex items-center justify-center relative overflow-hidden font-sans text-white">

<!-- Simple Ambient Glow (Static) -->
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-neonPink/10 rounded-full blur-[100px] pointer-events-none"></div>

<!-- Main Card -->
<main class="relative z-10 w-full max-w-sm mx-4">

    <!-- Clean Container -->
    <div class="bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-8 md:p-10 text-center shadow-2xl">

        <!-- Logo Area (Similar to Verified Page) -->
        <div class="relative flex items-center justify-center w-48 h-48 mx-auto mb-6 group">
            <!-- Static Glow Ring -->
            <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-neonPink to-neonCyan blur-md opacity-40 group-hover:opacity-60 transition-opacity duration-500"></div>

            <!-- Logo Image (No Circle Container) -->
            <img src="/logo.png" alt="GV LIVE Logo" class="relative w-full h-full object-contain drop-shadow-[0_0_15px_rgba(0,240,255,0.5)]" />
        </div>

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2 tracking-wide text-white">
            Reset Password
        </h1>

        <!-- Subtext -->
        <p class="text-gray-400 text-sm mb-8 leading-relaxed">
            You have successfully change your password.
        </p>
    </div>
</main>

</body>
</html>