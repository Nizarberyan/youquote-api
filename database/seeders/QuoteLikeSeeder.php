<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and quotes
        $users = User::all();
        $quotes = Quote::all();
        
        // Each user likes some random quotes
        foreach ($users as $user) {
            // Like 1-5 random quotes
            $quotesToLike = $quotes->random(rand(1, min(5, $quotes->count())));
            
            foreach ($quotesToLike as $quote) {
                DB::table('quote_likes')->insertOrIgnore([
                    'user_id' => $user->id,
                    'quote_id' => $quote->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}