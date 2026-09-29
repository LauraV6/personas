<?php

namespace App\Actions;

use App\Jobs\EnrichPerson;
use App\Models\Person;

class RetryEnrichment
{
    /**
     * Haalt de gegevens van een persoon waarbij het ophalen mislukte opnieuw op.
     */
    public function handle(Person $person): void
    {
        $person->update(['enrichment_failed_at' => null]);

        EnrichPerson::dispatch($person);
    }
}
