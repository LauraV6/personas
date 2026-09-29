<?php

use App\Jobs\EnrichPerson;
use App\Livewire\PeopleDashboard;
use App\Models\Person;
use Illuminate\Support\Facades\Queue;
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
        'failed' => 0,
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

it('edits a person in the dashboard', function () {
    Queue::fake();
    $person = Person::factory()->enriched()->create(['first_name' => 'Laura', 'last_name' => 'Vlasma', 'email' => 'laura@example.com']);

    Livewire::test(PeopleDashboard::class)
        ->call('edit', $person->id)
        ->assertSet('editingId', $person->id)
        ->assertSet('form.email', 'laura@example.com')
        ->assertSee('Persoon bewerken')
        ->set('form.last_name', 'de Vries')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('editingId', null)
        ->assertDispatched('notify', message: 'Laura de Vries is bijgewerkt.', type: 'success');

    expect($person->fresh()->last_name)->toBe('de Vries');
    Queue::assertNothingPushed();
});

it('shows validation errors in the edit dialog', function () {
    $person = Person::factory()->create();

    Livewire::test(PeopleDashboard::class)
        ->call('edit', $person->id)
        ->set('form.email', 'geen-email')
        ->set('form.first_name', '')
        ->call('save')
        ->assertHasErrors(['form.email', 'form.first_name'])
        ->assertSee('Vul een geldig e-mailadres in.')
        ->assertSee('Voornaam is verplicht.')
        ->assertSet('editingId', $person->id);
});

it('cancels editing without saving', function () {
    $person = Person::factory()->create(['last_name' => 'Vlasma']);

    Livewire::test(PeopleDashboard::class)
        ->call('edit', $person->id)
        ->set('form.last_name', 'Anders')
        ->call('cancelEdit')
        ->assertSet('editingId', null)
        ->assertDontSee('Persoon bewerken');

    expect($person->fresh()->last_name)->toBe('Vlasma');
});

it('deletes a person after confirming in the dashboard', function () {
    $person = Person::factory()->create(['first_name' => 'Sanne', 'last_name' => 'Visser']);

    Livewire::test(PeopleDashboard::class)
        ->call('confirmDelete', $person->id)
        ->assertSee('Sanne Visser verwijderen?')
        ->call('delete')
        ->assertSet('deletingId', null)
        ->assertDispatched('notify', message: 'Sanne Visser is verwijderd.')
        ->assertDontSee('Sanne Visser');

    $this->assertModelMissing($person);
});

it('handles a person that was deleted elsewhere', function () {
    $person = Person::factory()->create();
    $component = Livewire::test(PeopleDashboard::class)->call('edit', $person->id);

    $person->delete();

    $component->call('save')
        ->assertSet('editingId', null)
        ->assertDispatched('notify', message: 'Deze persoon bestaat niet meer.', type: 'error');
});

it('goes back a page when the last person on a page is deleted', function () {
    Person::factory()->count(11)->create();
    $last = Person::orderBy('created_at')->orderBy('id')->first();

    Livewire::withQueryParams(['page' => 2])
        ->test(PeopleDashboard::class)
        ->assertSee($last->fullName())
        ->call('confirmDelete', $last->id)
        ->call('delete')
        ->assertSet('paginators.page', 1);
});

it('pauses polling while a dialog is open', function () {
    $person = Person::factory()->create();

    Livewire::test(PeopleDashboard::class)
        ->assertSeeHtml('wire:poll')
        ->call('edit', $person->id)
        ->assertDontSeeHtml('wire:poll');
});

it('shows failed enrichments separately from waiting ones', function () {
    Person::factory()->create(['first_name' => 'Wilhelmina']);
    Person::factory()->create(['first_name' => 'Mislukte', 'enrichment_failed_at' => now()]);

    $component = Livewire::test(PeopleDashboard::class);

    expect($component->instance()->stats)->toMatchArray(['pending' => 1, 'failed' => 1]);

    $component
        ->assertSee('1 mislukt')
        ->call('setStatus', 'failed')
        ->assertSee('Mislukte')
        ->assertDontSee('Wilhelmina')
        ->call('setStatus', 'pending')
        ->assertSee('Wilhelmina')
        ->assertDontSee('Mislukte');
});

it('does not poll when the only unfinished people have failed', function () {
    Person::factory()->create(['enrichment_failed_at' => now()]);

    Livewire::test(PeopleDashboard::class)->assertDontSeeHtml('wire:poll');
});

it('retries a failed enrichment', function () {
    Queue::fake();
    $person = Person::factory()->create(['first_name' => 'Sanne', 'last_name' => 'Visser', 'enrichment_failed_at' => now()]);

    Livewire::test(PeopleDashboard::class)
        ->assertSeeHtml('title="Opnieuw proberen"')
        ->call('retry', $person->id)
        ->assertDispatched('notify', message: 'De gegevens van Sanne Visser worden opnieuw opgehaald.')
        ->assertDontSeeHtml('title="Opnieuw proberen"');

    expect($person->fresh()->enrichment_failed_at)->toBeNull();
    Queue::assertPushed(EnrichPerson::class, fn (EnrichPerson $job) => $job->person->is($person));
});

it('ignores a retry for a person that did not fail', function () {
    Queue::fake();
    $person = Person::factory()->enriched()->create();

    Livewire::test(PeopleDashboard::class)->call('retry', $person->id)->assertNotDispatched('notify');

    Queue::assertNothingPushed();
});
