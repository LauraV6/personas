<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

#[Signature('person:delete {id : Id van de persoon} {--force : Verwijder zonder te vragen}')]
#[Description('Verwijder een persoon')]
class DeletePerson extends Command
{
    public function handle(): int
    {
        $person = Person::find($this->argument('id'));

        if ($person === null) {
            $this->error("Er is geen persoon met id {$this->argument('id')}.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! confirm("Weet je zeker dat je {$person->fullName()} wilt verwijderen?", default: false)) {
            $this->line('Niets verwijderd.');

            return self::SUCCESS;
        }

        $person->delete();

        $this->info("{$person->fullName()} is verwijderd.");

        return self::SUCCESS;
    }
}
