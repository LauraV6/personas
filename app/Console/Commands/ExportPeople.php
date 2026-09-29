<?php

namespace App\Console\Commands;

use App\Actions\ExportPeopleAsJson;
use App\Models\Person;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('person:export {--output= : Bestand om de export naar te schrijven, anders naar de terminal}')]
#[Description('Exporteer alle personen met hun opgehaalde gegevens als JSON')]
class ExportPeople extends Command
{
    public function handle(ExportPeopleAsJson $export): int
    {
        $people = Person::orderBy('id')->get();
        $json = $export->handle($people);

        if ($path = $this->option('output')) {
            File::put($path, $json.PHP_EOL);
            $this->info("{$people->count()} personen geëxporteerd naar {$path}.");

            return self::SUCCESS;
        }

        $this->line($json);

        return self::SUCCESS;
    }
}
