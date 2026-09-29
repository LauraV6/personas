<?php

use App\Jobs\EnrichPerson;
use App\Models\Person;
use Illuminate\Support\Facades\Queue;

beforeEach(fn () => Queue::fake());

it('stores a person and queues the enrichment', function () {
    $this->artisan('person:add', ['first_name' => 'Laura', 'last_name' => 'Vlasma', 'email' => 'laura@example.com'])
        ->expectsOutputToContain('Laura Vlasma is toegevoegd')
        ->assertSuccessful();

    $person = Person::sole();

    expect($person->email)->toBe('laura@example.com')
        ->and($person->estimated_age)->toBeNull();

    Queue::assertPushed(EnrichPerson::class, fn (EnrichPerson $job) => $job->person->is($person));
});

it('asks for missing values', function () {
    $this->artisan('person:add')
        ->expectsQuestion('Voornaam', 'Jan')
        ->expectsQuestion('Achternaam', 'Jansen')
        ->expectsQuestion('E-mailadres', 'jan@example.com')
        ->assertSuccessful();

    $this->assertDatabaseHas('people', ['first_name' => 'Jan', 'email' => 'jan@example.com']);
});

it('rejects an invalid email', function () {
    $this->artisan('person:add', ['first_name' => 'Jan', 'last_name' => 'Jansen', 'email' => 'geen-email'])
        ->assertFailed();

    $this->assertDatabaseCount('people', 0);
    Queue::assertNothingPushed();
});

it('rejects a duplicate email', function () {
    Person::factory()->create(['email' => 'jan@example.com']);

    $this->artisan('person:add', ['first_name' => 'Jan', 'last_name' => 'Jansen', 'email' => 'jan@example.com'])
        ->assertFailed();

    $this->assertDatabaseCount('people', 1);
    Queue::assertNothingPushed();
});
