<?php

namespace Database\Factories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = $this->faker->paragraph(2);
        
        return [
            'content' => $content,
            'author' => $this->faker->name(),
            'length' => str_word_count($content),
            'popularity_count' => $this->faker->numberBetween(0, 100),
        ];
    }
}
