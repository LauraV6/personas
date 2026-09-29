<?php

use App\Actions\ExportPeopleAsJson;
use App\Livewire\PeopleDashboard;
use App\Models\Person;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

it('exports every person with the enriched data', function () {
    Person::factory()->enriched()->create([
        'first_name' => 'Laura',
        'last_name' => 'Vlasma',
        'email' => 'laura@example.com',
        'estimated_age' => 42,
        'estimated_gender' => 'female',
        'estimated_nationality' => 'NL',
    ]);
    Person::factory()->create(['first_name' => 'Wachtend']);

    Artisan::call('person:export');
    $export = json_decode(Artisan::output(), true);

    expect($export['count'])->toBe(2)
        ->and($export['people'][0])->toMatchArray([
            'first_name' => 'Laura',
            'last_name' => 'Vlasma',
            'email' => 'laura@example.com',
            'estimated_age' => 42,
            'estimated_gender' => 'female',
            'estimated_nationality' => 'NL',
        ])
        ->and($export['people'][0])->not->toHaveKey('gender_probability')
        ->and($export['people'][1]['enriched_at'])->toBeNull();
});

it('writes the export to a file', function () {
    Person::factory()->count(3)->create();
    $path = tempnam(sys_get_temp_dir(), 'personas');

    $this->artisan('person:export', ['--output' => $path])
        ->expectsOutputToContain("3 personen geëxporteerd naar {$path}")
        ->assertSuccessful();

    expect(json_decode(file_get_contents($path), true)['count'])->toBe(3);

    unlink($path);
});

it('downloads only the filtered people from the dashboard', function () {
    $this->freezeTime();

    $sanne = Person::factory()->enriched()->create(['first_name' => 'Sanne', 'estimated_gender' => 'female']);
    Person::factory()->enriched()->create(['first_name' => 'Pieter', 'estimated_gender' => 'male']);

    $expected = app(ExportPeopleAsJson::class)->handle(collect([$sanne->fresh()]));

    Livewire::test(PeopleDashboard::class)
        ->set('gender', 'female')
        ->call('export')
        ->assertFileDownloaded('personen-'.now()->format('Y-m-d').'.json', $expected);
});
