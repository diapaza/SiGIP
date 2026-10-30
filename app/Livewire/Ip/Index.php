<?php

namespace App\Livewire\Ip;

use App\Enums\IpType;
use App\Livewire\Concerns\WithSorting;
use App\Models\IpAddress;
use App\Services\PingService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithSorting;

    /**
     * Active tab. Kept in the query string so the sidebar can link to a type.
     */
    #[Url(as: 'tipo')]
    public string $filter = 'all';

    public string $search = '';

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $formType = 'server';

    /**
     * Workstations only: the machine gets its address automatically.
     */
    public bool $isDhcp = false;

    public string $ip = '';

    public string $hostname = '';

    public string $model = '';

    public string $name = '';

    public string $office = '';

    public string $staff = '';

    public string $position = '';

    public ?int $deletingId = null;

    public string $deletingIp = '';

    /**
     * @var list<string>
     */
    protected array $sortable = [
        'ip',
        'hostname',
        'type',
        'model',
        'name',
        'office',
        'staff',
        'is_active',
        'last_pinged_at',
        'created_at',
    ];

    public function mount(): void
    {
        if ($this->filter !== 'all' && IpType::tryFrom($this->filter) === null) {
            $this->filter = 'all';
        }
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    /**
     * DHCP only applies to workstations, so switching the type resets it.
     */
    public function updatedFormType(): void
    {
        if (! $this->typeUsesDhcp()) {
            $this->isDhcp = false;
        }
    }

    /**
     * A DHCP record has no fixed IP, so clear any typed value when enabled.
     */
    public function updatedIsDhcp(bool $value): void
    {
        if ($value) {
            $this->ip = '';
            $this->resetValidation('ip');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function validateIp(): void
    {
        if ($this->usesDhcp()) {
            return;
        }

        $this->validateOnly('ip', $this->rules());
    }

    #[Computed]
    public function addresses()
    {
        return IpAddress::query()
            ->when($this->filter !== 'all', fn ($query) => $query->where('type', $this->filter))
            ->when($this->search !== '', function ($query) {
                $term = '%'.trim($this->search).'%';

                $query->where(function ($query) use ($term) {
                    $query->where('ip', 'like', $term)
                        ->orWhere('hostname', 'like', $term)
                        ->orWhere('model', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('office', 'like', $term)
                        ->orWhere('staff', 'like', $term)
                        ->orWhere('position', 'like', $term);

                    if (str_contains(mb_strtolower($this->search), 'dhcp')) {
                        $query->orWhere('is_dhcp', true);
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('id', $this->sortDirection)
            ->paginate(10);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function typeCounts(): array
    {
        return IpAddress::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();
    }

    #[Computed]
    public function totalCount(): int
    {
        return array_sum($this->typeCounts);
    }

    public function openCreate(): void
    {
        $this->resetForm();

        $this->formType = $this->filter !== 'all' ? $this->filter : IpType::Server->value;

        Flux::modal('ip-form')->show();
    }

    public function openEdit(int $id): void
    {
        $address = IpAddress::findOrFail($id);

        $this->resetForm();

        $this->editingId = $address->id;
        $this->formType = $address->type->value;
        $this->isDhcp = (bool) $address->is_dhcp;
        $this->ip = $address->ip ?? '';
        $this->hostname = $address->hostname ?? '';
        $this->model = $address->model ?? '';
        $this->name = $address->name ?? '';
        $this->office = $address->office ?? '';
        $this->staff = $address->staff ?? '';
        $this->position = $address->position ?? '';

        Flux::modal('ip-form')->show();
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $type = IpType::from($this->formType);
        $usesDhcp = $this->usesDhcp();

        $data = [
            'ip' => $usesDhcp ? null : trim((string) $this->ip),
            'is_dhcp' => $usesDhcp,
            'hostname' => $this->clean($this->hostname),
            'type' => $type,
            'model' => $this->clean($this->model),
            'name' => $type->requiresName() ? $this->clean($this->name) : null,
            'office' => $type->requiresWorkstationDetails() ? $this->clean($this->office) : null,
            'staff' => $type->requiresWorkstationDetails() ? $this->clean($this->staff) : null,
            'position' => $type->requiresWorkstationDetails() ? $this->clean($this->position) : null,
        ];

        if ($this->editingId) {
            IpAddress::findOrFail($this->editingId)->update($data);
        } else {
            IpAddress::create($data);
        }

        $this->refreshData();

        Flux::modal('ip-form')->close();
        Flux::toast(
            text: $this->editingId ? 'La IP se actualizó correctamente.' : 'La IP se registró correctamente.',
            heading: $this->editingId ? 'Registro actualizado' : 'Registro creado',
            variant: 'success',
        );

        $this->resetForm();
    }

    public function ping(int $id): void
    {
        $address = IpAddress::findOrFail($id);

        if (! $address->canBePinged()) {
            Flux::toast(
                text: 'Esta estación de trabajo obtiene su IP por DHCP, por lo que no tiene una dirección fija a la que hacer ping.',
                heading: 'Sin IP fija',
                variant: 'warning',
            );

            return;
        }

        $isActive = app(PingService::class)->ping($address->ip);

        $address->update([
            'is_active' => $isActive,
            'last_pinged_at' => now(),
        ]);

        $this->refreshData();

        Flux::toast(
            text: $isActive
                ? "La IP {$address->ip} respondió al ping correctamente."
                : "La IP {$address->ip} no respondió al ping.",
            heading: $isActive ? 'Activo' : 'Inactivo',
            variant: $isActive ? 'success' : 'danger',
        );
    }

    public function confirmDelete(int $id): void
    {
        $address = IpAddress::findOrFail($id);

        $this->deletingId = $address->id;
        $this->deletingIp = $address->ipLabel();

        Flux::modal('delete-ip')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        IpAddress::findOrFail($this->deletingId)->delete();

        $this->refreshData();

        $this->deletingId = null;
        $this->deletingIp = '';

        Flux::modal('delete-ip')->close();
        Flux::toast(text: 'El registro de IP se eliminó.', heading: 'Registro eliminado', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.ip.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $type = IpType::from($this->formType);
        $usesDhcp = $this->usesDhcp();

        return [
            'ip' => $usesDhcp
                ? ['nullable', 'string', 'max:45']
                : ['required', 'string', 'max:45', 'ip', Rule::unique('ip_addresses', 'ip')->ignore($this->editingId)],
            'hostname' => ['nullable', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'office' => [$type->requiresWorkstationDetails() ? 'required' : 'nullable', 'string', 'max:255'],
            'staff' => [$type->requiresWorkstationDetails() ? 'required' : 'nullable', 'string', 'max:255'],
            'position' => [$type->requiresWorkstationDetails() ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Whether the form is registering a DHCP workstation.
     */
    protected function usesDhcp(): bool
    {
        return $this->isDhcp && $this->typeUsesDhcp();
    }

    protected function typeUsesDhcp(): bool
    {
        return IpType::tryFrom($this->formType)?->requiresWorkstationDetails() ?? false;
    }

    protected function clean(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'isDhcp', 'ip', 'hostname', 'model', 'name', 'office', 'staff', 'position']);
    }

    protected function refreshData(): void
    {
        unset($this->addresses, $this->typeCounts, $this->totalCount);
    }
}

