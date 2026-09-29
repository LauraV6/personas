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
        <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:py-14">
            <livewire:people-dashboard />
        </main>

        @livewireScripts
    </body>
</html>
