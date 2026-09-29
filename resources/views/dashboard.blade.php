<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Personas</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        {{-- Zachte gloed achter de kop --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-0 h-96 bg-[radial-gradient(60%_100%_at_50%_0%,rgb(99_102_241/0.10),transparent)] dark:bg-[radial-gradient(60%_100%_at_50%_0%,rgb(99_102_241/0.18),transparent)]" aria-hidden="true"></div>

        <nav class="sticky top-0 z-20 border-b border-zinc-200/70 bg-white/70 backdrop-blur-md dark:border-zinc-800/70 dark:bg-zinc-950/70">
            <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-linear-to-br from-indigo-500 to-violet-600 text-white shadow-sm shadow-indigo-500/30">
                        <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" />
                        </svg>
                    </span>
                    <span class="font-semibold tracking-tight">Personas</span>
                </a>

                <a href="https://github.com/LauraV6/personas" target="_blank" rel="noopener" class="text-sm text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                    GitHub
                </a>
            </div>
        </nav>

        <main class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-12">
            <livewire:people-dashboard />
        </main>

        @livewireScripts
    </body>
</html>
