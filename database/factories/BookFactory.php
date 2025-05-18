<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Library;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'author' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'library_id' => Library::factory(),
        ];
    }
}
