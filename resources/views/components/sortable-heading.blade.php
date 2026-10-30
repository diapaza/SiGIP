@props([
    'field',
    'sortField',
    'sortDirection' => 'desc',
    'label',
    'align' => 'start',
])

<flux:table.column
    {{ $attributes->class('cursor-pointer select-none') }}
    align="{{ $align }}"
    wire:click="sortBy('{{ $field }}')"
    title="Ordenar por {{ $label }}"
>
    <span class="inline-flex items-center gap-1">
        <span>{{ $label }}</span>

        @if ($sortField === $field)
            <flux:icon
                :icon="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'"
                variant="micro"
                class="text-zinc-500 dark:text-zinc-400"
            />
        @else
            <flux:icon.chevrons-up-down variant="micro" class="text-zinc-300 dark:text-zinc-600" />
        @endif
    </span>
</flux:table.column>
