<?php

namespace App\Console\Commands;

use App\Actions\UpdatePerson as UpdatePersonAction;
use App\Models\Person;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\text;

#[Signature('person:update {id : Id van de persoon} {--first-name= : Nieuwe voornaam} {--last-name= : Nieuwe achternaam} {--email= : Nieuw e-mailadres}')]
#[Description('Werk een persoon bij, zonder opties vraagt hij de nieuwe waarden')]
class UpdatePerson extends Command
{
    public function handle(UpdatePersonAction $updatePerson): int
    {
        $person = Person::find($this->argument('id'));

        if ($person === null) {
            $this->error("Er is geen persoon met id {$this->argument('id')}.");

            return self::FAILURE;
        }

        $options = array_filter([
            'first_name' => $this->option('first-name'),
            'last_name' => $this->option('last-name'),
            'email' => $this->option('email'),
        ], fn ($value) => $value !== null);

        // Zonder opties vragen we elke waarde, met de huidige als standaard.
        $input = $options !== [] ? [...$person->only('first_name', 'last_name', 'email'), ...$options] : [
            'first_name' => text('Voornaam', default: $person->first_name, required: true),
            'last_name' => text('Achternaam', default: $person->last_name, required: true),
            'email' => text('E-mailadres', default: $person->email, required: true),
        ];

        $oldFirstName = $person->first_name;

        try {
            $updatePerson->handle($person, $input);
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info("{$person->fullName()} is bijgewerkt.");

        if ($person->first_name !== $oldFirstName) {
            $this->line('De voornaam is gewijzigd, dus leeftijd, geslacht en nationaliteit worden opnieuw opgehaald.');
        }

        return self::SUCCESS;
    }
}
