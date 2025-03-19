<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteFavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and quotes
        $users = User::all();
        $quotes = Quote::all();
        
        // Each user favorites some random quotes
        foreach ($users as $user) {
            // Favorite 0-3 random quotes (less than likes)
            $quotesToFavorite = $quotes->random(rand(0, min(3, $quotes->count())));
            
            foreach ($quotesToFavorite as $quote) {
                DB::table('quote_favorites')->insertOrIgnore([
                    'user_id' => $user->id,
                    'quote_id' => $quote->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}