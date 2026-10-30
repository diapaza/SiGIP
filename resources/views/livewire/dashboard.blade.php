<?php

use App\Enums\IpType;
use App\Models\IpAddress;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $total = IpAddress::query()->count();
        $active = IpAddress::query()->where('is_active', true)->count();
        $inactive = IpAddress::query()->where('is_active', false)->count();
        $unchecked = IpAddress::query()->whereNull('is_active')->count();

        $rows = collect(IpType::cases())->map(function (IpType $type) use ($total) {
            $query = IpAddress::query()->where('type', $type->value);

            $count = (clone $query)->count();

            return [
                'type' => $type,
                'total' => $count,
                'active' => (clone $query)->where('is_active', true)->count(),
                'inactive' => (clone $query)->where('is_active', false)->count(),
                'unchecked' => (clone $query)->whereNull('is_active')->count(),
                'percentage' => $total > 0 ? round($count / $total * 100) : 0,
            ];
        });

        // Build the SVG stroke segments for the donut chart.
        $donut = [];
        $offset = 0.0;

        foreach ($rows as $row) {
            if ($total === 0 || $row['total'] === 0) {
                continue;
            }

            $dash = $row['total'] / $total * 100;

            $donut[] = [
                'color' => $row['type']->chartColor(),
                'dash' => $dash,
                'offset' => 25 - $offset,
            ];

            $offset += $dash;
        }

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'unchecked' => $unchecked,
            'rows' => $rows,
            'donut' => $donut,
            'recent' => IpAddress::query()->latest('updated_at')->take(5)->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Panel de control</flux:heading>
            <flux:text class="mt-1">Resumen del inventario de direcciones IP de SIGIP.</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" :href="route('ip.index')" wire:navigate>
            Gestionar IP
        </flux:button>
    </div>

    {{-- Tarjetas informativas --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-sm">Total de IPs</flux:text>
                    <div class="mt-2 text-3xl font-semibold tracking-tight">{{ $total }}</div>
                    <flux:text class="mt-1 text-xs">Registros en las 4 categorías</flux:text>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <flux:icon.globe-alt class="size-5" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-sm">Activas</flux:text>
                    <div class="mt-2 text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $active }}</div>
                    <flux:text class="mt-1 text-xs">Respondieron al último ping</flux:text>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <flux:icon.check-circle class="size-5" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-sm">Inactivas</flux:text>
                    <div class="mt-2 text-3xl font-semibold tracking-tight text-red-600 dark:text-red-400">{{ $inactive }}</div>
                    <flux:text class="mt-1 text-xs">No respondieron al último ping</flux:text>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <flux:icon.x-circle class="size-5" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-sm">Sin verificar</flux:text>
                    <div class="mt-2 text-3xl font-semibold tracking-tight text-zinc-500 dark:text-zinc-400">{{ $unchecked }}</div>
                    <flux:text class="mt-1 text-xs">Todavía no se les hizo ping</flux:text>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.question-mark-circle class="size-5" />
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen por tipo --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($rows as $row)
            <a
                href="{{ route('ip.index', ['tipo' => $row['type']->value]) }}"
                wire:navigate
                class="group rounded-xl border border-zinc-200 bg-white p-4 shadow-xs transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
            >
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 transition group-hover:bg-zinc-900 group-hover:text-white dark:bg-zinc-800 dark:text-zinc-300 dark:group-hover:bg-white dark:group-hover:text-zinc-900">
                        <flux:icon :icon="$row['type']->icon()" class="size-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium">{{ $row['type']->label() }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $row['total'] }} {{ $row['total'] === 1 ? 'registro' : 'registros' }}
                        </div>
                    </div>
                    <div class="ml-auto text-lg font-semibold">{{ $row['total'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Gráficos --}}
    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Distribución por tipo (dona) --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <flux:heading size="sm">Distribución por tipo</flux:heading>
                    <flux:text class="mt-1 text-xs">Porcentaje de IPs registradas en cada categoría.</flux:text>
                </div>
                <flux:icon.chart-pie class="size-5 text-zinc-400" />
            </div>

            <div class="mt-6 flex flex-col items-center gap-8 sm:flex-row">
                <div class="relative size-40 shrink-0">
                    <svg viewBox="0 0 42 42" class="size-40">
                        <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="currentColor" class="text-zinc-100 dark:text-zinc-800" stroke-width="6" />

                        @foreach ($donut as $segment)
                            <circle
                                cx="21"
                                cy="21"
                                r="15.9155"
                                fill="transparent"
                                stroke="{{ $segment['color'] }}"
                                stroke-width="6"
                                stroke-dasharray="{{ $segment['dash'] }} {{ 100 - $segment['dash'] }}"
                                stroke-dashoffset="{{ $segment['offset'] }}"
                            />
                        @endforeach
                    </svg>

                    <div class="absolute inset-[18%] flex flex-col items-center justify-center rounded-full bg-white dark:bg-zinc-900">
                        <span class="text-2xl font-semibold">{{ $total }}</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400">IPs</span>
                    </div>
                </div>

                <div class="w-full space-y-3">
                    @foreach ($rows as $row)
                        <div class="flex items-center gap-3">
                            <span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $row['type']->chartColor() }}"></span>
                            <span class="min-w-0 flex-1 truncate text-sm">{{ $row['type']->label() }}</span>
                            <span class="text-sm font-medium">{{ $row['total'] }}</span>
                            <span class="w-10 text-right text-xs text-zinc-500 dark:text-zinc-400">{{ $row['percentage'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Estado por tipo (barras) --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <div>
                    <flux:heading size="sm">Estado de conectividad por tipo</flux:heading>
                    <flux:text class="mt-1 text-xs">Resultado del último ping de cada registro.</flux:text>
                </div>
                <flux:icon.chart-bar class="size-5 text-zinc-400" />
            </div>

            <div class="mt-5 flex flex-wrap gap-4 text-xs">
                <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald-500"></span> Activo</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-red-500"></span> Inactivo</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-600"></span> Sin verificar</span>
            </div>

            <div class="mt-6 space-y-5">
                @foreach ($rows as $row)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium">{{ $row['type']->label() }}</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row['total'] }} IPs</span>
                        </div>

                        <div class="mt-2 flex h-3 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            @if ($row['total'] > 0)
                                <div class="bg-emerald-500" style="width: {{ $row['active'] / $row['total'] * 100 }}%"></div>
                                <div class="bg-red-500" style="width: {{ $row['inactive'] / $row['total'] * 100 }}%"></div>
                                <div class="bg-zinc-300 dark:bg-zinc-600" style="width: {{ $row['unchecked'] / $row['total'] * 100 }}%"></div>
                            @endif
                        </div>

                        <div class="mt-1 flex items-center gap-4 text-[11px] text-zinc-500 dark:text-zinc-400">
                            <span>{{ $row['active'] }} activas</span>
                            <span>{{ $row['inactive'] }} inactivas</span>
                            <span>{{ $row['unchecked'] }} sin verificar</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Últimos movimientos --}}
    <div id="ultimos-registros" class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-700">
            <div>
                <flux:heading size="sm">Últimos registros actualizados</flux:heading>
                <flux:text class="mt-1 text-xs">Los 5 movimientos más recientes del inventario.</flux:text>
            </div>

            <flux:button size="sm" variant="ghost" :href="route('ip.index')" wire:navigate>Ver todos</flux:button>
        </div>

        <div class="overflow-x-auto px-5 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>IP</flux:table.column>
                    <flux:table.column>Tipo</flux:table.column>
                    <flux:table.column>Equipo o Modelo</flux:table.column>
                    <flux:table.column>Detalle</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($recent as $address)
                        <flux:table.row :key="$address->id">
                            <flux:table.cell class="font-mono font-medium">{{ $address->ip }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$address->type->color()" :icon="$address->type->icon()">
                                    {{ $address->type->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $address->model ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                                {{ $address->type->requiresUserDetails() ? $address->staff : $address->name }}
                                @if ($address->hostname)
                                    <span class="font-mono text-xs">· {{ $address->hostname }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$address->statusColor()">{{ $address->statusLabel() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <flux:text class="py-6 text-center text-sm">Todavía no hay registros de IP.</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
