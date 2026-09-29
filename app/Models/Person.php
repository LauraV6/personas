<?php

namespace App\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'estimated_age',
    'estimated_gender',
    'gender_probability',
    'enriched_at',
])]
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_age' => 'integer',
            'gender_probability' => 'float',
            'enriched_at' => 'datetime',
        ];
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->first_name, 0, 1).mb_substr($this->last_name, 0, 1));
    }

    public function isEnriched(): bool
    {
        return $this->enriched_at !== null;
    }

    public function genderLabel(): string
    {
        return match ($this->estimated_gender) {
            'female' => 'Vrouw',
            'male' => 'Man',
            default => 'Onbekend',
        };
    }
}
