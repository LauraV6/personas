<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('person:list')]
#[Description('Toon alle personen met hun opgehaalde gegevens')]
class ListPeople extends Command
{
    public function handle(): int
    {
        $this->table(
            ['Id', 'Naam', 'E-mailadres', 'Leeftijd', 'Geslacht', 'Nationaliteit', 'Opgehaald op'],
            Person::orderBy('id')->get()->map(fn (Person $person) => [
                $person->id,
                $person->fullName(),
                $person->email,
                $person->estimated_age ?? '-',
                $person->estimated_gender ?? '-',
                $person->nationalityLabel() ?? '-',
                $person->enriched_at?->format('d-m-Y H:i') ?? 'nog niet',
            ]),
        );

        return self::SUCCESS;
    }
}
