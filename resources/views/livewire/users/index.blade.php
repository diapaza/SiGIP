@use('App\Enums\UserRole')

<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Usuarios</flux:heading>
            <flux:text class="mt-1">Gestiona quién puede acceder y administrar el inventario de IP.</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreate">
            Registrar usuario
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        {{-- Búsqueda --}}
        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                clearable
                placeholder="Buscar por nombres, apellidos o correo…"
                class="sm:max-w-md"
            />
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto px-4 pb-2">
            <flux:table :paginate="$this->users">
                <flux:table.columns>
                    <x-sortable-heading field="first_name" label="Nombres" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <x-sortable-heading field="last_name" label="Apellidos" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <x-sortable-heading field="email" label="Correo electrónico" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <x-sortable-heading field="role" label="Rol" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <x-sortable-heading field="is_active" label="Estado" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <flux:table.column class="text-right whitespace-nowrap">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell class="font-medium">
                                {{ $user->first_name ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="font-medium">
                                {{ $user->last_name ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="font-mono text-xs">
                                {{ $user->email }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$user->role->color()" :icon="$user->role->icon()">
                                    {{ $user->role->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge
                                    size="sm"
                                    :color="$user->is_active ? 'emerald' : 'red'"
                                    :icon="$user->is_active ? 'check-circle' : 'x-circle'"
                                >
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        size="xs"
                                        square
                                        variant="ghost"
                                        icon="pencil-square"
                                        title="Editar"
                                        wire:click="openEdit({{ $user->id }})"
                                    />

                                    <flux:button
                                        size="xs"
                                        square
                                        variant="ghost"
                                        icon="trash"
                                        title="Eliminar"
                                        class="text-red-500"
                                        wire:click="confirmDelete({{ $user->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6">
                                <div class="flex flex-col items-center justify-center gap-3 py-12 text-center">
                                    <flux:icon.users class="size-10 text-zinc-300 dark:text-zinc-600" />
                                    <flux:heading size="sm">Sin usuarios</flux:heading>
                                    <flux:text class="text-sm">
                                        @if ($search !== '')
                                            No hay resultados para “{{ $search }}”.
                                        @else
                                            Todavía no hay usuarios registrados.
                                        @endif
                                    </flux:text>
                                    <flux:button size="sm" variant="primary" icon="plus" wire:click="openCreate">
                                        Registrar usuario
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- Modal de alta / edición --}}
    <flux:modal name="user-form" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Editar usuario' : 'Registrar usuario' }}
                </flux:heading>
                <flux:text class="mt-1">
                    {{ $editingId ? 'Actualiza los datos y permisos del usuario.' : 'Los campos marcados como obligatorios son necesarios para crear la cuenta.' }}
                </flux:text>
            </div>

            <form wire:submit="save" class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model.live.debounce.400ms="first_name"
                        label="Nombres"
                        :label:badge="$first_name === '' ? 'Obligatorio' : null"
                        placeholder="Ej. María Fernanda"
                        required
                    />

                    <flux:input
                        wire:model.live.debounce.400ms="last_name"
                        label="Apellidos"
                        :label:badge="$last_name === '' ? 'Obligatorio' : null"
                        placeholder="Ej. López García"
                        required
                    />
                </div>

                <flux:input
                    wire:model.live.debounce.400ms="email"
                    type="email"
                    label="Correo electrónico"
                    :label:badge="$email === '' ? 'Obligatorio' : null"
                    placeholder="correo@empresa.com"
                    required
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="role" label="Rol">
                        @foreach (UserRole::cases() as $roleOption)
                            <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:switch wire:model="is_active" label="Cuenta activa" description="Los usuarios inactivos no pueden iniciar sesión." />
                </div>

                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3">
                        {{ $editingId ? 'Cambiar contraseña' : 'Contraseña' }}
                    </flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model.live.debounce.400ms="password"
                            type="password"
                            label="Contraseña"
                            :label:badge="$editingId === null && $password === '' ? 'Obligatorio' : null"
                            :description="$editingId ? 'Déjala en blanco para conservar la actual.' : null"
                            autocomplete="new-password"
                            :required="$editingId === null"
                        />

                        <flux:input
                            wire:model.live.debounce.400ms="password_confirmation"
                            type="password"
                            label="Confirmar contraseña"
                            :label:badge="$editingId === null && $password_confirmation === '' ? 'Obligatorio' : null"
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" type="button">Cancelar</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" type="submit">
                        {{ $editingId ? 'Guardar cambios' : 'Registrar usuario' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Modal de eliminación --}}
    <x-confirm-deletion-modal name="delete-user" title="Eliminar usuario" action="delete">
        ¿Seguro que quieres eliminar a
        <span class="font-semibold">{{ $deletingName }}</span>?
        Esta acción no se puede deshacer.
    </x-confirm-deletion-modal>
</div>
