<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an admin user
        User::factory()->admin()->create();
        
        
        // Create 10 regular users
        $users = User::factory()->count(10)->create();
        
        // Create 25 quotes assigned to random users
        foreach ($users as $user) {
            // Each user creates 2-5 quotes
            $quoteCount = rand(2, 5);
            Quote::factory()->count($quoteCount)->byUser($user)->create();
        }
        
        // Create 5 popular quotes
        Quote::factory()->count(5)->popular()->create();
        
        // Create quotes with different lengths
        Quote::factory()->count(3)->short()->create();
        Quote::factory()->count(3)->long()->create();
        
        // Seed likes and favorites
        $this->call([
            QuoteLikeSeeder::class,
            QuoteFavoriteSeeder::class,
        ]);
    }
}
