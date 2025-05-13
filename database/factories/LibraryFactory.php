<?php

namespace Database\Factories;

use App\Enums\LibraryType;
use App\Models\Library;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LibraryFactory extends Factory
{
    protected $model = Library::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement([LibraryType::BOOK->value, LibraryType::MOVIE->value]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
