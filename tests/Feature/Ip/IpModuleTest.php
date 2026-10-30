<?php

use App\Enums\IpType;
use App\Livewire\Ip\Index as IpIndex;
use App\Models\IpAddress;
use Livewire\Livewire;

test('invalid ip addresses are rejected on blur validation', function () {
    Livewire::test(IpIndex::class)
        ->set('formType', IpType::Server->value)
        ->set('ip', '999.999.999.999')
        ->call('validateIp')
        ->assertHasErrors(['ip' => 'ip']);
});

test('the ip form marks required fields as mandatory while empty', function () {
    Livewire::test(IpIndex::class)
        ->set('formType', IpType::Server->value)
        ->assertSee('Dirección IP')
        ->assertSee('Equipo o Modelo')
        ->assertSee('Obligatorio');
});

test('valid ip addresses pass blur validation', function () {
    Livewire::test(IpIndex::class)
        ->set('formType', IpType::Server->value)
        ->set('ip', '10.99.0.1')
        ->call('validateIp')
        ->assertHasNoErrors(['ip']);
});

test('ip sorting toggles between ascending and descending', function () {
    Livewire::test(IpIndex::class)
        ->call('sortBy', 'ip')
        ->assertSet('sortField', 'ip')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'ip')
        ->assertSet('sortField', 'ip')
        ->assertSet('sortDirection', 'desc');
});

test('ip sorting ignores unknown columns', function () {
    Livewire::test(IpIndex::class)
        ->call('sortBy', 'drop table users')
        ->assertSet('sortField', 'created_at')
        ->assertSet('sortDirection', 'desc');
});

test('the ip list defaults to the newest records first', function () {
    $old = IpAddress::create([
        'ip' => '10.50.0.1',
        'type' => IpType::Server,
        'model' => 'Old server',
        'name' => 'OLD',
    ]);
    $old->forceFill(['created_at' => now()->subDay()])->save();

    $new = IpAddress::create([
        'ip' => '10.50.0.2',
        'type' => IpType::Server,
        'model' => 'New server',
        'name' => 'NEW',
    ]);

    $component = Livewire::test(IpIndex::class);

    $orderedIds = $component->instance()->addresses->pluck('id')->all();

    expect($orderedIds[0])->toBe($new->id)
        ->and($orderedIds)->toContain($old->id);
});
