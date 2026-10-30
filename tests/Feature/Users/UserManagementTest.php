<?php

use App\Enums\UserRole;
use App\Livewire\Users\Index as UsersIndex;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guests are redirected from the users module', function () {
    $this->get('/users')->assertRedirect('/login');
});

test('non admin users can not access the users module', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/users')
        ->assertForbidden();
});

test('admins can access the users module', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/users')
        ->assertOk();
});

test('the users table shows the required fields and sortable headings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('openCreate')
        ->assertSee('Nombres')
        ->assertSee('Apellidos')
        ->assertSee('Correo electrónico')
        ->assertSee('Rol')
        ->assertSee('Cuenta activa')
        ->assertSee('Obligatorio');
});

test('the users list defaults to the newest records first', function () {
    $admin = User::factory()->admin()->create();
    $older = User::factory()->create();
    $newer = User::factory()->create();

    $older->forceFill(['created_at' => now()->subDay()])->save();

    $this->actingAs($admin);

    $orderedIds = Volt::test('users.index')->instance()->users->pluck('id')->all();

    expect($orderedIds[0])->toBe($newer->id)
        ->and($orderedIds)->toContain($older->id);
});

test('admins can register a new user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->set('first_name', 'Ana')
        ->set('last_name', 'Pérez')
        ->set('email', 'ana@example.com')
        ->set('role', UserRole::User->value)
        ->set('is_active', true)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors();

    $user = User::where('email', 'ana@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Ana Pérez')
        ->and($user->role)->toBe(UserRole::User)
        ->and($user->is_active)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();
});

test('email must be unique when registering a user', function () {
    $admin = User::factory()->admin()->create();
    $existing = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->set('first_name', 'Ana')
        ->set('last_name', 'Pérez')
        ->set('email', $existing->email)
        ->set('role', UserRole::User->value)
        ->set('is_active', true)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('admins can update a user without changing the password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['first_name' => 'Old', 'last_name' => 'Name']);

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('openEdit', $user->id)
        ->set('first_name', 'New')
        ->set('last_name', 'Name')
        ->set('email', $user->email)
        ->set('role', UserRole::User->value)
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->first_name)->toBe('New')
        ->and($user->name)->toBe('New Name')
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

test('admins can delete another user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('confirmDelete', $user->id)
        ->call('delete');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admins can not delete their own account from the users module', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('confirmDelete', $admin->id)
        ->call('delete');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('the last active admin can not be demoted', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('openEdit', $admin->id)
        ->set('first_name', $admin->first_name)
        ->set('last_name', $admin->last_name)
        ->set('email', $admin->email)
        ->set('role', UserRole::User->value)
        ->set('is_active', true)
        ->call('save')
        ->assertHasErrors(['role']);

    expect($admin->refresh()->isAdmin())->toBeTrue();
});
