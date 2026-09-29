<?php

use App\Jobs\EnrichPerson;
use App\Models\Person;
use Illuminate\Support\Facades\Http;

it('deletes a person from the cli after confirmation', function () {
    $person = Person::factory()->create(['first_name' => 'Laura', 'last_name' => 'Vlasma']);

    $this->artisan('person:delete', ['id' => $person->id])
        ->expectsConfirmation('Weet je zeker dat je Laura Vlasma wilt verwijderen?', 'yes')
        ->expectsOutputToContain('Laura Vlasma is verwijderd.')
        ->assertSuccessful();

    $this->assertModelMissing($person);
});

it('keeps the person when the confirmation is declined', function () {
    $person = Person::factory()->create();

    $this->artisan('person:delete', ['id' => $person->id])
        ->expectsConfirmation("Weet je zeker dat je {$person->fullName()} wilt verwijderen?", 'no')
        ->expectsOutputToContain('Niets verwijderd.')
        ->assertSuccessful();

    $this->assertModelExists($person);
});

it('deletes without asking when forced', function () {
    $person = Person::factory()->create();

    $this->artisan('person:delete', ['id' => $person->id, '--force' => true])->assertSuccessful();

    $this->assertModelMissing($person);
});

it('fails for an unknown id', function () {
    $this->artisan('person:delete', ['id' => 999, '--force' => true])
        ->expectsOutputToContain('Er is geen persoon met id 999.')
        ->assertFailed();
});

it('drops a queued enrichment when the person was deleted in the meantime', function () {
    Http::preventStrayRequests();
    Http::fake();

    $person = Person::factory()->create();
    $person->delete();

    // De sync-queue serialiseert de job net als een echte worker.
    EnrichPerson::dispatch($person);

    Http::assertNothingSent();
});
