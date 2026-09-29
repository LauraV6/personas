<?php

namespace App\Actions;

use App\Actions\Concerns\ValidatesPerson;
use App\Jobs\EnrichPerson;
use App\Models\Person;
use Illuminate\Validation\ValidationException;

class UpdatePerson
{
    use ValidatesPerson;

    /**
     * Werkt een persoon bij. De schattingen horen bij de voornaam, dus bij een
     * nieuwe voornaam worden ze gewist en opnieuw opgehaald.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function handle(Person $person, array $input): Person
    {
        $person->fill($this->validate($input, $person));

        $firstNameChanged = $person->isDirty('first_name');

        if ($firstNameChanged) {
            $person->fill([
                'estimated_age' => null,
                'estimated_gender' => null,
                'gender_probability' => null,
                'estimated_nationality' => null,
                'enriched_at' => null,
            ]);
        }

        $person->save();

        if ($firstNameChanged) {
            EnrichPerson::dispatch($person);
        }

        return $person;
    }
}
