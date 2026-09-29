<?php

use App\Jobs\EnrichPerson;
use App\Models\Person;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => Http::preventStrayRequests());

it('stores the estimated age and gender', function () {
    Http::fake([
        'api.agify.io*' => Http::response(['count' => 1234, 'name' => 'Laura', 'age' => 42]),
        'api.genderize.io*' => Http::response(['count' => 1234, 'name' => 'Laura', 'gender' => 'female', 'probability' => 0.98]),
    ]);

    $person = Person::factory()->create(['first_name' => 'Laura']);

    EnrichPerson::dispatchSync($person);

    expect($person->refresh())
        ->estimated_age->toBe(42)
        ->estimated_gender->toBe('female')
        ->gender_probability->toBe(0.98)
        ->enriched_at->not->toBeNull();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.agify.io?name=Laura');
    Http::assertSent(fn ($request) => $request->url() === 'https://api.genderize.io?name=Laura');
});

it('accepts names the apis do not know', function () {
    Http::fake([
        'api.agify.io*' => Http::response(['count' => 0, 'name' => 'Xyzzy', 'age' => null]),
        'api.genderize.io*' => Http::response(['count' => 0, 'name' => 'Xyzzy', 'gender' => null, 'probability' => 0.0]),
    ]);

    $person = Person::factory()->create(['first_name' => 'Xyzzy']);

    EnrichPerson::dispatchSync($person);

    expect($person->refresh())
        ->estimated_age->toBeNull()
        ->estimated_gender->toBeNull()
        ->enriched_at->not->toBeNull();
});

it('throws on a failing api so the queue retries the job', function () {
    Http::fake([
        'api.agify.io*' => Http::response(['error' => 'Request limit reached'], 429),
        'api.genderize.io*' => Http::response(['gender' => 'female', 'probability' => 0.98]),
    ]);

    $person = Person::factory()->create();

    expect(fn () => (new EnrichPerson($person))->handle())->toThrow(RequestException::class)
        ->and($person->fresh()->enriched_at)->toBeNull();
});
