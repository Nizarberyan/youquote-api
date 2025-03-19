<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
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
        $famousQuotes = [
            "Be yourself; everyone else is already taken." => "Oscar Wilde",
            "Two things are infinite: the universe and human stupidity; and I'm not sure about the universe." => "Albert Einstein",
            "Be the change that you wish to see in the world." => "Mahatma Gandhi",
            "In three words I can sum up everything I've learned about life: it goes on." => "Robert Frost",
            "If you tell the truth, you don't have to remember anything." => "Mark Twain",
            "To be yourself in a world that is constantly trying to make you something else is the greatest accomplishment." => "Ralph Waldo Emerson",
            "I've learned that people will forget what you said, people will forget what you did, but people will never forget how you made them feel." => "Maya Angelou"
        ];
        
        // 20% chance to use a famous quote
        if ($this->faker->boolean(20)) {
            $keys = array_keys($famousQuotes);
            $randomKey = $keys[array_rand($keys)];
            $content = $randomKey;
            $author = $famousQuotes[$randomKey];
        } else {
            $content = $this->faker->paragraph(2);
            $author = $this->faker->name();
        }
            
        return [
            'content' => $content,
            'author' => $author,
            'length' => str_word_count($content),
            'popularity_count' => $this->faker->numberBetween(0, 100),
            'user_id' => User::factory(), // By default, create a new user for each quote
        ];
    }
    
    /**
     * Set a specific user for the quote
     */
    public function byUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
    
    /**
     * Create a quote with high popularity
     */
    public function popular()
    {
        return $this->state(function (array $attributes) {
            return [
                'popularity_count' => $this->faker->numberBetween(500, 1000),
            ];
        });
    }
    
    /**
     * Create a quote with specific length characteristics
     */
    public function short()
    {
        return $this->state(function (array $attributes) {
            $content = $this->faker->realText(80);
            return [
                'content' => $content,
                'length' => str_word_count($content),
            ];
        });
    }
    
    /**
     * Create a quote with specific length characteristics
     */
    public function long()
    {
        return $this->state(function (array $attributes) {
            $content = $this->faker->realText(300);
            return [
                'content' => $content,
                'length' => str_word_count($content),
            ];
        });
    }
}
