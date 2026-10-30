<div {{ $attributes->class('flex items-center') }}>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-br from-indigo-500 to-sky-500 text-white shadow-sm shrink-0">
        <x-app-logo-icon class="size-5" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm leading-tight in-data-flux-sidebar-collapsed-desktop:hidden">
        <span class="truncate font-semibold">{{ config('app.name', 'SIGIP') }}</span>
        <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">Registro de IP</span>
    </div>
</div>
