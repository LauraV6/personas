<?php

use App\Livewire\PeopleDashboard;
use App\Models\Person;
use Livewire\Livewire;

it('shows the dashboard on the homepage', function () {
    Person::factory()->create(['first_name' => 'Laura', 'last_name' => 'Vlasma']);

    $this->get('/')
        ->assertOk()
        ->assertSeeLivewire(PeopleDashboard::class)
        ->assertSee('Laura Vlasma');
});

it('shows an empty state when there are no people', function () {
    Livewire::test(PeopleDashboard::class)
        ->assertSee('Nog geen personen')
        ->assertSee('php artisan person:add');
});

it('calculates the stats', function () {
    Person::factory()->enriched()->create(['estimated_age' => 30, 'estimated_gender' => 'female']);
    Person::factory()->enriched()->create(['estimated_age' => 41, 'estimated_gender' => 'male']);
    Person::factory()->enriched()->create(['estimated_age' => null, 'estimated_gender' => null]);
    Person::factory()->create();

    expect(Livewire::test(PeopleDashboard::class)->instance()->stats)->toBe([
        'total' => 4,
        'today' => 4,
        'enriched' => 3,
        'pending' => 1,
        'average_age' => 36,
        'female' => 1,
        'male' => 1,
        'unknown' => 1,
    ]);
});

it('searches on every word of the name and email', function () {
    Person::factory()->create(['first_name' => 'Laura', 'last_name' => 'Vlasma', 'email' => 'laura@example.com']);
    Person::factory()->create(['first_name' => 'Laura', 'last_name' => 'Bakker', 'email' => 'lb@example.com']);
    Person::factory()->create(['first_name' => 'Pieter', 'last_name' => 'Jansen', 'email' => 'pieter@voorbeeld.nl']);

    Livewire::test(PeopleDashboard::class)
        ->set('search', 'laura vlasma')
        ->assertSee('Laura Vlasma')
        ->assertDontSee('Laura Bakker')
        ->set('search', 'voorbeeld')
        ->assertSee('Pieter Jansen')
        ->assertDontSee('Laura Vlasma');
});

it('filters on gender', function () {
    Person::factory()->enriched()->create(['first_name' => 'Sanne', 'estimated_gender' => 'female']);
    Person::factory()->enriched()->create(['first_name' => 'Pieter', 'estimated_gender' => 'male']);
    Person::factory()->enriched()->create(['first_name' => 'Xyzzy', 'estimated_gender' => null]);
    Person::factory()->create(['first_name' => 'Wilhelmina']);

    Livewire::test(PeopleDashboard::class)
        ->set('gender', 'female')
        ->assertSee('Sanne')
        ->assertDontSee('Pieter')
        ->set('gender', 'unknown')
        ->assertSee('Xyzzy')
        ->assertDontSee('Sanne')
        ->assertDontSee('Wilhelmina');
});

it('filters on status', function () {
    Person::factory()->enriched()->create(['first_name' => 'Sanne']);
    Person::factory()->create(['first_name' => 'Wilhelmina']);

    Livewire::test(PeopleDashboard::class)
        ->set('status', 'pending')
        ->assertSee('Wilhelmina')
        ->assertDontSee('Sanne')
        ->set('status', 'enriched')
        ->assertSee('Sanne')
        ->assertDontSee('Wilhelmina');
});

it('polls only while people are waiting for their data', function () {
    Person::factory()->enriched()->create();

    Livewire::test(PeopleDashboard::class)->assertDontSeeHtml('wire:poll');

    Person::factory()->create();

    Livewire::test(PeopleDashboard::class)->assertSeeHtml('wire:poll');
});

it('offers to clear the filters when nothing matches', function () {
    Person::factory()->create(['first_name' => 'Laura']);

    Livewire::test(PeopleDashboard::class)
        ->set('search', 'bestaatniet')
        ->assertSee('Geen personen gevonden')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSee('Laura');
});

it('switches status with the tabs', function () {
    Person::factory()->enriched()->create(['first_name' => 'Sanne']);
    Person::factory()->create(['first_name' => 'Wilhelmina']);

    Livewire::test(PeopleDashboard::class)
        ->call('setStatus', 'pending')
        ->assertSet('status', 'pending')
        ->assertSee('Wilhelmina')
        ->assertDontSee('Sanne')
        ->call('setStatus', 'onzin')
        ->assertSet('status', '');
});

it('sorts on a column and flips the direction on a second click', function () {
    Person::factory()->enriched()->create(['first_name' => 'Jong', 'estimated_age' => 20]);
    Person::factory()->enriched()->create(['first_name' => 'Oud', 'estimated_age' => 70]);

    Livewire::test(PeopleDashboard::class)
        ->call('sortBy', 'age')
        ->assertSet('direction', 'asc')
        ->assertSeeInOrder(['Jong', 'Oud'])
        ->call('sortBy', 'age')
        ->assertSet('direction', 'desc')
        ->assertSeeInOrder(['Oud', 'Jong']);
});

it('ignores unknown sort columns', function () {
    Livewire::test(PeopleDashboard::class)
        ->call('sortBy', 'email; drop table people')
        ->assertSet('sort', 'created');
});

it('shows the most common nationalities and age groups', function () {
    Person::factory()->enriched()->count(2)->create(['estimated_nationality' => 'NL', 'estimated_age' => 34]);
    Person::factory()->enriched()->create(['estimated_nationality' => 'BE', 'estimated_age' => 71]);

    $dashboard = Livewire::test(PeopleDashboard::class)->instance();

    expect($dashboard->topCountries)->toBe([
        ['code' => 'NL', 'name' => 'Nederland', 'flag' => '🇳🇱', 'count' => 2],
        ['code' => 'BE', 'name' => 'België', 'flag' => '🇧🇪', 'count' => 1],
    ])
        ->and($dashboard->ageGroups)->toMatchArray(['30' => 2, '70+' => 1, '20' => 0]);
});
