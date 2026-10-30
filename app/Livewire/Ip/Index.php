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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function validateIp(): void
    {
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
        $this->ip = $address->ip;
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
        $validated = $this->validate($this->rules());

        $type = IpType::from($this->formType);

        $data = [
            'ip' => trim($validated['ip']),
            'hostname' => $this->clean($this->hostname),
            'type' => $type,
            'model' => $this->clean($this->model),
            'name' => $type->requiresName() ? $this->clean($this->name) : null,
            'office' => $type->requiresUserDetails() ? $this->clean($this->office) : null,
            'staff' => $type->requiresUserDetails() ? $this->clean($this->staff) : null,
            'position' => $type->requiresUserDetails() ? $this->clean($this->position) : null,
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
        $this->deletingIp = $address->ip;

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

        return [
            'ip' => ['required', 'string', 'max:45', 'ip', Rule::unique('ip_addresses', 'ip')->ignore($this->editingId)],
            'hostname' => ['nullable', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'name' => [$type->requiresName() ? 'required' : 'nullable', 'string', 'max:255'],
            'office' => [$type->requiresUserDetails() ? 'required' : 'nullable', 'string', 'max:255'],
            'staff' => [$type->requiresUserDetails() ? 'required' : 'nullable', 'string', 'max:255'],
            'position' => [$type->requiresUserDetails() ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    protected function clean(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'ip', 'hostname', 'model', 'name', 'office', 'staff', 'position']);
    }

    protected function refreshData(): void
    {
        unset($this->addresses, $this->typeCounts, $this->totalCount);
    }
}

