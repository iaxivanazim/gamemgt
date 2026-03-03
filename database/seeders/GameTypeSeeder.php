<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('game_types')->insert([

            [
                'name' => 'Baccarat',
                'code' => 'BAC',
                'description' => 'A card game where players bet on the outcome of the player\'s hand, banker\'s hand, or a tie.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],

            [
                'name' => 'Andar Bahar',
                'code' => 'AB',
                'description' => 'A simple card game where players bet on which side (Andar or Bahar) a card matching the value of the first card will appear.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],

            [
                'name' => 'Dragon Tiger',
                'code' => 'DT',
                'description' => 'A fast-paced card game where players bet on whether the Dragon or Tiger hand will have the higher card, or if it will be a tie.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],

            [
                'name' => '3 Card Poker',
                'code' => '3CP',
                'description' => 'A poker variant where players are dealt three cards and compete against the dealer\'s hand, with various betting options and payouts based on the hand rankings.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],

            [
                'name' => 'Blackjack',
                'code' => 'BJ',
                'description' => 'A popular card game where players aim to have a hand value of 21 or less, while beating the dealer\'s hand without going over.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],

            [
                'name' => 'Mini Flush',
                'code' => 'MF',
                'description' => 'A card game where players bet on the outcome of a flush hand, with various betting options and payouts based on the hand rankings.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],

            [
                'name' => 'Casino War',
                'code' => 'CW',
                'description' => 'A simple card game where players bet on whether their card will be higher than the dealer\'s card, with a "war" option if there is a tie.',
                'created_at'=>now(),
                'updated_at'=>now()
            ]

        ]);
    }
}
