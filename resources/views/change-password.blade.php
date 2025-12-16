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
            Enter your new password below. Make sure it's secure and memorable.
        </p>

        <!-- Form -->
        <form action="{{ route('password.change') }}" method="post" class="space-y-5 text-left">
            @csrf
            <!-- Password Field -->
            <div class="space-y-1">
                <label for="password" class="text-xs font-semibold text-gray-400 uppercase tracking-wider ml-1">New Password</label>
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-neonPink to-neonCyan rounded-xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                    <input type="password" id="password" name="password" class="relative w-full bg-deepVoid/80 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:border-neonPink focus:ring-1 focus:ring-neonPink transition-all" placeholder="••••••••" required>
                </div>
                @error('password')
                <div class="text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password Field -->
            <div class="space-y-1">
                <label for="confirm_password" class="text-xs font-semibold text-gray-400 uppercase tracking-wider ml-1">Confirm Password</label>
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-neonCyan to-neonPurple rounded-xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                    <input type="password" id="confirm_password" name="password_confirmation" class="relative w-full bg-deepVoid/80 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:border-neonCyan focus:ring-1 focus:ring-neonCyan transition-all" placeholder="••••••••" required>
                </div>
            </div>

            <input type="hidden" name="token" value="{{ request()->route('token') }}" />
            <input type="hidden" name="email" value="{{ request()->query('email') }}" />

            <!-- Reset Button -->
            <button type="submit" class="w-full py-3.5 px-4 mt-4 bg-gradient-to-r from-neonPink to-neonPurple rounded-xl text-white font-bold text-sm uppercase tracking-wider hover:opacity-90 hover:shadow-[0_0_20px_rgba(255,0,222,0.4)] transition-all duration-300 transform active:scale-95">
                Reset Password
            </button>
        </form>
    </div>
</main>

</body>
</html>