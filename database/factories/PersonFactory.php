<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }

    /**
     * Een persoon waarvoor leeftijd en geslacht al zijn opgehaald.
     */
    public function enriched(): static
    {
        return $this->state(function () {
            $gender = fake()->randomElement(['female', 'male']);

            return [
                'first_name' => fake()->firstName($gender),
                'estimated_age' => fake()->numberBetween(18, 80),
                'estimated_gender' => $gender,
                'gender_probability' => fake()->randomFloat(2, 0.6, 1),
                'enriched_at' => now(),
            ];
        });
    }
}
