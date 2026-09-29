<?php

namespace App\Actions;

use App\Http\Resources\PersonResource;
use App\Models\Person;
use Illuminate\Support\Collection;

/**
 * Zet personen om naar het JSON-exportformaat dat het dashboard en person:export delen.
 */
class ExportPeopleAsJson
{
    /**
     * @param  Collection<int, Person>  $people
     */
    public function handle(Collection $people): string
    {
        return json_encode([
            'exported_at' => now()->toIso8601String(),
            'count' => $people->count(),
            'people' => PersonResource::collection($people)->resolve(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
