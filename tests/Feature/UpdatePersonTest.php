<?php

use App\Actions\UpdatePerson;
use App\Jobs\EnrichPerson;
use App\Models\Person;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => Queue::fake());

function enrichedLaura(): Person
{
    return Person::factory()->enriched()->create([
        'first_name' => 'Laura',
        'last_name' => 'Vlasma',
        'email' => 'laura@example.com',
        'estimated_age' => 42,
        'estimated_gender' => 'female',
        'estimated_nationality' => 'NL',
    ]);
}

it('keeps the estimates when only the last name or email changes', function () {
    $person = enrichedLaura();

    app(UpdatePerson::class)->handle($person, ['first_name' => 'Laura', 'last_name' => 'de Vries', 'email' => 'laura@voorbeeld.nl']);

    expect($person->fresh())
        ->last_name->toBe('de Vries')
        ->email->toBe('laura@voorbeeld.nl')
        ->estimated_age->toBe(42)
        ->enriched_at->not->toBeNull();

    Queue::assertNothingPushed();
});

it('clears the estimates and fetches them again when the first name changes', function () {
    $person = enrichedLaura();

    app(UpdatePerson::class)->handle($person, ['first_name' => 'Eva', 'last_name' => 'Vlasma', 'email' => 'laura@example.com']);

    expect($person->fresh())
        ->first_name->toBe('Eva')
        ->estimated_age->toBeNull()
        ->estimated_gender->toBeNull()
        ->estimated_nationality->toBeNull()
        ->enriched_at->toBeNull();

    Queue::assertPushed(EnrichPerson::class, fn (EnrichPerson $job) => $job->person->is($person));
});

it('allows keeping the own email but not taking someone elses', function () {
    $person = enrichedLaura();
    Person::factory()->create(['email' => 'pieter@example.com']);

    app(UpdatePerson::class)->handle($person, ['first_name' => 'Laura', 'last_name' => 'Vlasma', 'email' => 'laura@example.com']);

    expect(fn () => app(UpdatePerson::class)->handle($person, ['first_name' => 'Laura', 'last_name' => 'Vlasma', 'email' => 'pieter@example.com']))
        ->toThrow(ValidationException::class, 'Dit e-mailadres is al in gebruik.');

    expect($person->fresh()->email)->toBe('laura@example.com');
});

it('updates a person from the cli with options', function () {
    $person = enrichedLaura();

    $this->artisan('person:update', ['id' => $person->id, '--email' => 'nieuw@example.com'])
        ->expectsOutputToContain('Laura Vlasma is bijgewerkt.')
        ->assertSuccessful();

    expect($person->fresh()->email)->toBe('nieuw@example.com');
});

it('asks for the new values when no options are given', function () {
    $person = enrichedLaura();

    $this->artisan('person:update', ['id' => $person->id])
        ->expectsQuestion('Voornaam', 'Eva')
        ->expectsQuestion('Achternaam', 'Vlasma')
        ->expectsQuestion('E-mailadres', 'laura@example.com')
        ->expectsOutputToContain('worden opnieuw opgehaald')
        ->assertSuccessful();

    expect($person->fresh()->first_name)->toBe('Eva');
});

it('fails in the cli for an unknown id or invalid input', function () {
    $person = enrichedLaura();

    $this->artisan('person:update', ['id' => 999, '--email' => 'x@example.com'])
        ->expectsOutputToContain('Er is geen persoon met id 999.')
        ->assertFailed();

    $this->artisan('person:update', ['id' => $person->id, '--email' => 'geen-email'])
        ->expectsOutputToContain('Vul een geldig e-mailadres in.')
        ->assertFailed();
});
