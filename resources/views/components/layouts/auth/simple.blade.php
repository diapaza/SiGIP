<!DOCTYPE html>
<html lang="es" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="mb-2 flex flex-col items-center gap-1 font-medium" wire:navigate>
                    <span class="mb-1 flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white shadow-sm">
                        <x-app-logo-icon class="size-6" />
                    </span>
                    <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'SIGIP') }}</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Registro de direcciones IP</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
