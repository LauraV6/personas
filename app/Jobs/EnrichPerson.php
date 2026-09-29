<?php

namespace App\Jobs;

use App\Models\Person;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Pool;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Http;

/**
 * Haalt de geschatte leeftijd (agify.io), het geschatte geslacht (genderize.io)
 * en de geschatte nationaliteit (nationalize.io) op basis van de voornaam op
 * en slaat die op bij de persoon.
 */
#[Tries(3)]
#[Backoff(10, 30, 60)]
class EnrichPerson implements ShouldQueue
{
    use Queueable;

    public function __construct(public Person $person)
    {
        //
    }

    public function handle(): void
    {
        $query = ['name' => $this->person->first_name];

        $responses = Http::pool(fn (Pool $pool) => [
            $pool->as('agify')->timeout(10)->get('https://api.agify.io', $query),
            $pool->as('genderize')->timeout(10)->get('https://api.genderize.io', $query),
            $pool->as('nationalize')->timeout(10)->get('https://api.nationalize.io', $query),
        ]);

        // Een mislukt verzoek gooit een exceptie, zodat de queue het opnieuw probeert.
        $age = $responses['agify']->throw()->json();
        $gender = $responses['genderize']->throw()->json();
        $nationality = $responses['nationalize']->throw()->json();

        // nationalize.io geeft een lijst landen met kansen, we bewaren het meest waarschijnlijke.
        $country = collect($nationality['country'] ?? [])->sortByDesc('probability')->first();

        $this->person->update([
            'estimated_age' => $age['age'] ?? null,
            'estimated_gender' => $gender['gender'] ?? null,
            'gender_probability' => $gender['probability'] ?? null,
            'estimated_nationality' => $country['country_id'] ?? null,
            'enriched_at' => now(),
        ]);
    }
}
