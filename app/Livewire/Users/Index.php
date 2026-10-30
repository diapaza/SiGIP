<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Livewire\Concerns\WithSorting;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithSorting;

    public string $search = '';

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $role = 'user';

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @var list<string>
     */
    protected array $sortable = [
        'first_name',
        'last_name',
        'email',
        'role',
        'is_active',
        'created_at',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.trim($this->search).'%';

                $query->where(function ($query) use ($term) {
                    $query->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('id', $this->sortDirection)
            ->paginate(10);
    }

    public function openCreate(): void
    {
        $this->resetForm();

        Flux::modal('user-form')->show();
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->resetForm();

        $this->editingId = $user->id;
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->is_active = $user->is_active;

        Flux::modal('user-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
            'password' => [
                $this->editingId ? 'nullable' : 'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ], $this->validationMessages(), $this->validationAttributes());

        $role = UserRole::from($validated['role']);

        if ($this->violatesAdminGuardrails($role)) {
            return;
        }

        $data = [
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $role,
            'is_active' => $this->is_active,
        ];

        if ($this->password !== '') {
            $data['password'] = $this->password;
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
        } else {
            $data['email_verified_at'] = now();
            User::create($data);
        }

        $this->refreshData();
        Flux::modal('user-form')->close();
        Flux::toast(
            text: $this->editingId ? 'El usuario se actualizó correctamente.' : 'El usuario se registró correctamente.',
            heading: $this->editingId ? 'Usuario actualizado' : 'Usuario creado',
            variant: 'success',
        );

        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $user = User::findOrFail($id);

        $this->deletingId = $user->id;
        $this->deletingName = $user->fullName();

        Flux::modal('delete-user')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        if ($this->deletingId === Auth::id()) {
            Flux::toast(text: 'No puedes eliminar tu propia cuenta.', heading: 'Acción no permitida', variant: 'danger');
            Flux::modal('delete-user')->close();

            return;
        }

        $user = User::findOrFail($this->deletingId);

        if ($user->isAdmin() && User::query()->active()->where('role', UserRole::Admin->value)->count() <= 1) {
            Flux::toast(text: 'Debe existir al menos un administrador activo.', heading: 'Acción no permitida', variant: 'danger');
            Flux::modal('delete-user')->close();

            return;
        }

        $user->delete();

        $this->refreshData();
        $this->deletingId = null;
        $this->deletingName = '';

        Flux::modal('delete-user')->close();
        Flux::toast(text: 'El usuario se eliminó.', heading: 'Usuario eliminado', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.users.index');
    }

    protected function violatesAdminGuardrails(UserRole $role): bool
    {
        $activeAdmins = User::query()
            ->active()
            ->where('role', UserRole::Admin->value)
            ->count();

        if ($this->editingId === Auth::id()) {
            if ($role !== UserRole::Admin) {
                $this->addError('role', 'No puedes quitarte el rol de administrador.');

                return true;
            }

            if (! $this->is_active) {
                $this->addError('is_active', 'No puedes desactivar tu propia cuenta.');

                return true;
            }
        }

        if ($this->editingId !== null) {
            $target = User::findOrFail($this->editingId);

            if ($target->isAdmin() && $activeAdmins <= 1) {
                if ($role !== UserRole::Admin) {
                    $this->addError('role', 'Debe existir al menos un administrador activo.');

                    return true;
                }

                if (! $this->is_active) {
                    $this->addError('is_active', 'Debe existir al menos un administrador activo.');

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function validationMessages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un correo válido.',
            'unique' => 'El valor de :attribute ya está en uso.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'first_name' => 'nombres',
            'last_name' => 'apellidos',
            'email' => 'correo electrónico',
            'role' => 'rol',
            'password' => 'contraseña',
        ];
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'first_name', 'last_name', 'email', 'password', 'password_confirmation']);

        $this->role = UserRole::User->value;
        $this->is_active = true;
    }

    protected function refreshData(): void
    {
        unset($this->users);
    }
}

