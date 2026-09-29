<?php

namespace App\Actions;

use App\Actions\Concerns\ValidatesPerson;
use App\Jobs\EnrichPerson;
use App\Models\Person;
use Illuminate\Validation\ValidationException;

class CreatePerson
{
    use ValidatesPerson;

    /**
     * Slaat een nieuwe persoon op en zet het ophalen van de gegevens in de wachtrij.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function handle(array $input): Person
    {
        $person = Person::create($this->validate($input));

        EnrichPerson::dispatch($person);

        return $person;
    }
}
