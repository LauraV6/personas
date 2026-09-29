<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Vult de database met voorbeeldpersonen, zonder de API's aan te roepen.
     */
    public function run(): void
    {
        Person::factory(12)->enriched()->create();
    }
}
