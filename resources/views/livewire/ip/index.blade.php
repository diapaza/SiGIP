@use('App\Enums\IpType')

<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Registro de IP</flux:heading>
            <flux:text class="mt-1">Inventario de direcciones IP separado por tipo de equipo.</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreate">
            Registrar IP
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        {{-- Pestañas por tipo --}}
        <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="$set('filter', 'all')"
                    @class([
                        'inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition',
                        'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $filter === 'all',
                        'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => $filter !== 'all',
                    ])
                >
                    Todos
                    <span @class([
                        'rounded-full px-1.5 py-0.5 text-xs',
                        'bg-white/20 text-white dark:bg-zinc-900/10 dark:text-zinc-900' => $filter === 'all',
                        'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' => $filter !== 'all',
                    ])>{{ $this->totalCount }}</span>
                </button>

                @foreach (IpType::cases() as $type)
                    <button
                        type="button"
                        wire:click="$set('filter', '{{ $type->value }}')"
                        @class([
                            'inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition',
                            'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $filter === $type->value,
                            'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => $filter !== $type->value,
                        ])
                    >
                        {{ $type->label() }}
                        <span @class([
                            'rounded-full px-1.5 py-0.5 text-xs',
                            'bg-white/20 text-white dark:bg-zinc-900/10 dark:text-zinc-900' => $filter === $type->value,
                            'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' => $filter !== $type->value,
                        ])>{{ $this->typeCounts[$type->value] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    clearable
                    placeholder="Buscar por IP, hostname, equipo, DHCP, personal u oficina…"
                    class="sm:max-w-md"
                />

                <flux:text class="text-xs">
                    @if ($filter === 'all')
                        Usa las pestañas para filtrar por tipo de equipo.
                    @else
                        {{ IpType::from($filter)->requirementsLabel() }}
                    @endif
                </flux:text>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto px-4 pb-2">
            <flux:table :paginate="$this->addresses">
                <flux:table.columns>
                    <x-sortable-heading field="ip" label="IP" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <x-sortable-heading field="hostname" label="Hostname" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    @if ($filter === 'all')
                        <x-sortable-heading field="type" label="Tipo" :sort-field="$sortField" :sort-direction="$sortDirection" />
                    @endif

                    @if ($filter === 'workstation')
                        <x-sortable-heading field="office" label="Oficina" :sort-field="$sortField" :sort-direction="$sortDirection" />
                    @endif

                    <x-sortable-heading field="model" label="Equipo o Modelo" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    @if ($filter !== 'network' && $filter !== 'workstation')
                        <x-sortable-heading field="name" label="Nombre" :sort-field="$sortField" :sort-direction="$sortDirection" />
                    @endif

                    @if ($filter === 'workstation')
                        <x-sortable-heading field="staff" label="Personal" :sort-field="$sortField" :sort-direction="$sortDirection" />
                    @endif

                    <x-sortable-heading field="is_active" label="Estado" :sort-field="$sortField" :sort-direction="$sortDirection" />

                    <flux:table.column class="text-right whitespace-nowrap">Acciones</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->addresses as $address)
                        <flux:table.row :key="$address->id">
                            <flux:table.cell class="font-mono font-medium">
                                @if ($address->is_dhcp)
                                    <flux:badge size="sm" color="sky" icon="arrows-right-left">DHCP</flux:badge>
                                @else
                                    {{ $address->ip }}
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="font-mono text-xs">
                                {{ $address->hostname ?? '—' }}
                            </flux:table.cell>

                            @if ($filter === 'all')
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$address->type->color()" :icon="$address->type->icon()">
                                        {{ $address->type->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                            @endif

                            @if ($filter === 'workstation')
                                <flux:table.cell>{{ $address->office ?? '—' }}</flux:table.cell>
                            @endif

                            <flux:table.cell>{{ $address->model ?? '—' }}</flux:table.cell>

                            @if ($filter !== 'network' && $filter !== 'workstation')
                                <flux:table.cell>{{ $address->name ?? '—' }}</flux:table.cell>
                            @endif

                            @if ($filter === 'workstation')
                                <flux:table.cell class="whitespace-nowrap">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $address->staff ?? '—' }}</div>
                                    @if ($address->position)
                                        <div class="text-xs text-zinc-400">{{ $address->position }}</div>
                                    @endif
                                </flux:table.cell>
                            @endif

                            <flux:table.cell>
                                <div class="flex flex-col gap-1">
                                    <flux:badge
                                        size="sm"
                                        :color="$address->statusColor()"
                                        :icon="match ($address->is_active) {
                                            true => 'check-circle',
                                            false => 'x-circle',
                                            null => 'question-mark-circle',
                                        }"
                                    >
                                        {{ $address->statusLabel() }}
                                    </flux:badge>

                                    @if ($address->last_pinged_at)
                                        <span class="text-[11px] text-zinc-400">
                                            {{ $address->last_pinged_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        size="xs"
                                        square
                                        variant="ghost"
                                        icon="signal"
                                        :title="$address->is_dhcp ? 'No disponible: la IP es dinámica (DHCP)' : 'Hacer ping'"
                                        :disabled="$address->is_dhcp"
                                        wire:click="ping({{ $address->id }})"
                                        wire:loading.attr="disabled"
                                    />

                                    <flux:button
                                        size="xs"
                                        square
                                        variant="ghost"
                                        icon="pencil-square"
                                        title="Editar"
                                        wire:click="openEdit({{ $address->id }})"
                                    />

                                    <flux:button
                                        size="xs"
                                        square
                                        variant="ghost"
                                        icon="trash"
                                        title="Eliminar"
                                        class="text-red-500"
                                        wire:click="confirmDelete({{ $address->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="10">
                                <div class="flex flex-col items-center justify-center gap-3 py-12 text-center">
                                    <flux:icon.server-stack class="size-10 text-zinc-300 dark:text-zinc-600" />
                                    <flux:heading size="sm">Sin registros</flux:heading>
                                    <flux:text class="text-sm">
                                        @if ($search !== '')
                                            No hay resultados para “{{ $search }}”.
                                        @else
                                            Todavía no hay IP registradas en esta categoría.
                                        @endif
                                    </flux:text>
                                    <flux:button size="sm" variant="primary" icon="plus" wire:click="openCreate">
                                        Registrar IP
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
    <flux:modal name="ip-form" class="w-full max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Editar registro de IP' : 'Registrar IP' }}
                </flux:heading>
                <flux:text class="mt-1">
                    Los campos cambian según el tipo de equipo seleccionado.
                </flux:text>
            </div>

            <form wire:submit="save" class="space-y-5">
                <flux:select wire:model.live="formType" label="Tipo de equipo" :disabled="$editingId !== null">
                    @foreach (IpType::cases() as $type)
                        <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if (IpType::from($formType)->requiresWorkstationDetails())
                    <flux:checkbox
                        wire:model.live="isDhcp"
                        label="Usa DHCP (IP dinámica)"
                        description="La estación obtiene su dirección IP automáticamente, así que no se registra una IP fija."
                    />
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model.live.debounce.400ms="ip"
                        x-on:blur="$wire.validateIp()"
                        label="Dirección IP"
                        :label:badge="$isDhcp ? 'DHCP' : ($ip === '' ? 'Obligatorio' : null)"
                        :placeholder="$isDhcp ? 'Asignada automáticamente' : '192.168.1.10'"
                        :disabled="$isDhcp"
                        :required="! $isDhcp"
                    />

                    <flux:input wire:model="hostname" label="Hostname" placeholder="Opcional" />

                    <flux:input
                        wire:model.live.debounce.400ms="model"
                        label="Equipo o Modelo"
                        :label:badge="$model === '' ? 'Obligatorio' : null"
                        placeholder="Ej. Dell PowerEdge R740"
                        required
                    />

                    @if (IpType::from($formType)->requiresName())
                        <flux:input
                            wire:model.live.debounce.400ms="name"
                            label="Nombre"
                            :label:badge="$name === '' ? 'Opcional' : null"
                            placeholder="Ej. SRV-DC01"
                        />
                    @endif
                </div>

                @if (IpType::from($formType)->requiresWorkstationDetails())
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model.live.debounce.400ms="office"
                            label="Oficina"
                            :label:badge="$office === '' ? 'Obligatorio' : null"
                            placeholder="Ej. Oficina Central - Piso 2"
                            required
                        />

                        <flux:input
                            wire:model.live.debounce.400ms="staff"
                            label="Personal"
                            :label:badge="$staff === '' ? 'Obligatorio' : null"
                            placeholder="Nombre del usuario"
                            required
                        />

                        <flux:input
                            wire:model.live.debounce.400ms="position"
                            label="Puesto"
                            :label:badge="$position === '' ? 'Obligatorio' : null"
                            placeholder="Ej. Contabilidad"
                            required
                        />
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" type="button">Cancelar</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" type="submit">
                        {{ $editingId ? 'Guardar cambios' : 'Registrar IP' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Modal de eliminación --}}
    <x-confirm-deletion-modal name="delete-ip" title="Eliminar registro" action="delete">
        ¿Seguro que quieres eliminar la IP
        <span class="font-mono font-semibold">{{ $deletingIp }}</span>?
        Esta acción no se puede deshacer.
    </x-confirm-deletion-modal>
</div>
