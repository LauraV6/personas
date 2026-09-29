<?php

namespace App\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Locale;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'estimated_age',
    'estimated_gender',
    'gender_probability',
    'estimated_nationality',
    'enriched_at',
    'enrichment_failed_at',
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
            'enrichment_failed_at' => 'datetime',
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

    /**
     * Het ophalen is na alle pogingen mislukt en er komt geen nieuwe poging vanzelf.
     */
    public function enrichmentFailed(): bool
    {
        return ! $this->isEnriched() && $this->enrichment_failed_at !== null;
    }

    public function genderLabel(): string
    {
        return match ($this->estimated_gender) {
            'female' => 'Vrouw',
            'male' => 'Man',
            default => 'Onbekend',
        };
    }

    public function nationalityLabel(): ?string
    {
        return $this->estimated_nationality !== null ? self::countryName($this->estimated_nationality) : null;
    }

    public function nationalityFlag(): ?string
    {
        return $this->estimated_nationality !== null ? self::countryFlag($this->estimated_nationality) : null;
    }

    /**
     * Landnaam in het Nederlands, bijvoorbeeld "Nederland" voor NL.
     */
    public static function countryName(string $code): string
    {
        return Locale::getDisplayRegion("-{$code}", 'nl');
    }

    /**
     * Vlag-emoji bij de landcode, opgebouwd uit twee regionale letters.
     */
    public static function countryFlag(string $code): string
    {
        return implode('', array_map(
            fn (string $letter) => mb_chr(0x1F1E6 + ord($letter) - ord('A')),
            str_split(strtoupper($code)),
        ));
    }
}
