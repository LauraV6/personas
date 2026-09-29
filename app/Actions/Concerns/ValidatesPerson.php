<?php

namespace App\Actions\Concerns;

use App\Models\Person;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidatesPerson
{
    /**
     * Valideert de invoer voor een persoon. Bij bijwerken telt het eigen
     * e-mailadres niet als dubbel.
     *
     * @param  array<string, mixed>  $input
     * @return array{first_name: string, last_name: string, email: string}
     */
    protected function validate(array $input, ?Person $ignore = null): array
    {
        return Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('people', 'email')->ignore($ignore)],
        ])->validate();
    }
}
