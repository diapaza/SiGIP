@props([
    'name',
    'title' => 'Confirmar eliminación',
    'action' => 'delete',
])

<flux:modal :name="$name" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:text class="mt-2">
                {{ $slot }}
            </flux:text>
        </div>

        <div class="flex items-center justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost" type="button">Cancelar</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" :wire:click="$action">Eliminar</flux:button>
        </div>
    </div>
</flux:modal>

