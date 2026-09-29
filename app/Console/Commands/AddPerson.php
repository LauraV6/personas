<?php

namespace App\Console\Commands;

use App\Actions\CreatePerson;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\text;

#[Signature('person:add {first_name? : Voornaam} {last_name? : Achternaam} {email? : E-mailadres}')]
#[Description('Voeg een nieuw persoon toe en haal op de achtergrond leeftijd, geslacht en nationaliteit op')]
class AddPerson extends Command
{
    public function handle(CreatePerson $createPerson): int
    {
        $data = [
            'first_name' => $this->argument('first_name') ?? text('Voornaam', required: true),
            'last_name' => $this->argument('last_name') ?? text('Achternaam', required: true),
            'email' => $this->argument('email') ?? text('E-mailadres', required: true),
        ];

        try {
            $person = $createPerson->handle($data);
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info("{$person->fullName()} is toegevoegd (id {$person->id}).");
        $this->line('Leeftijd, geslacht en nationaliteit worden op de achtergrond opgehaald. Start de worker met: php artisan queue:work');

        return self::SUCCESS;
    }
}
