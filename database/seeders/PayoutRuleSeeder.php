<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayoutRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payout_rules')->insert([

            /* GAME TYPE 1 */

            ['payout_id' => 1, 'game_type_id' => 1, 'bet_name' => 'Player', 'bet_position' => 'P', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 2, 'game_type_id' => 1, 'bet_name' => 'Banker', 'bet_position' => 'B', 'payout_multiplier' => 0.95, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 3, 'game_type_id' => 1, 'bet_name' => 'Tie', 'bet_position' => 'T', 'payout_multiplier' => 8, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 4, 'game_type_id' => 1, 'bet_name' => 'Player Pair', 'bet_position' => 'PP', 'payout_multiplier' => 11, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 5, 'game_type_id' => 1, 'bet_name' => 'Banker Pair', 'bet_position' => 'BP', 'payout_multiplier' => 11, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 6, 'game_type_id' => 1, 'bet_name' => 'Lucky 6 2 Cards', 'bet_position' => 'S6*2', 'payout_multiplier' => 12, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 7, 'game_type_id' => 1, 'bet_name' => 'Lucky 6 3 Cards', 'bet_position' => 'S6*3', 'payout_multiplier' => 20, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 8, 'game_type_id' => 1, 'bet_name' => 'Baccarat 6', 'bet_position' => 'B6', 'payout_multiplier' => 0.95, 'is_active' => 1, 'is_jackpot' => 0],


            /* GAME TYPE 2 */

            ['payout_id' => 9, 'game_type_id' => 2, 'bet_name' => 'Andar', 'bet_position' => 'A', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 10, 'game_type_id' => 2, 'bet_name' => 'Bahar', 'bet_position' => 'B', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 11, 'game_type_id' => 2, 'bet_name' => 'Andar 1st Shot', 'bet_position' => 'A1', 'payout_multiplier' => 1.25, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 12, 'game_type_id' => 2, 'bet_name' => 'Bahar 1st Shot', 'bet_position' => 'B1', 'payout_multiplier' => 1.25, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 13, 'game_type_id' => 2, 'bet_name' => 'Super Andar', 'bet_position' => 'SA', 'payout_multiplier' => 11, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 14, 'game_type_id' => 2, 'bet_name' => 'Super Bahar', 'bet_position' => 'SB', 'payout_multiplier' => 11, 'is_active' => 1, 'is_jackpot' => 0],


            /* GAME TYPE 3 */

            ['payout_id' => 15, 'game_type_id' => 3, 'bet_name' => 'Dragon', 'bet_position' => 'D', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 16, 'game_type_id' => 3, 'bet_name' => 'Tiger', 'bet_position' => 'TG', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 17, 'game_type_id' => 3, 'bet_name' => 'Tie', 'bet_position' => 'T', 'payout_multiplier' => 10, 'is_active' => 1, 'is_jackpot' => 0],


            /* GAME TYPE 4 */

            ['payout_id' => 18, 'game_type_id' => 4, 'bet_name' => 'Ante', 'bet_position' => 'A', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 19, 'game_type_id' => 4, 'bet_name' => 'Play', 'bet_position' => 'PB', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],

            ['payout_id' => 20, 'game_type_id' => 4, 'bet_name' => 'Ante Bonus Mini Royale', 'bet_position' => 'A*MR', 'payout_multiplier' => 5, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 21, 'game_type_id' => 4, 'bet_name' => 'Ante Bonus Straight Flush', 'bet_position' => 'A*SF', 'payout_multiplier' => 4, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 22, 'game_type_id' => 4, 'bet_name' => 'Ante Bonus Three of a Kind', 'bet_position' => 'A*TK', 'payout_multiplier' => 3, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 23, 'game_type_id' => 4, 'bet_name' => 'Ante Bonus Straight', 'bet_position' => 'A*S', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],

            ['payout_id' => 24, 'game_type_id' => 4, 'bet_name' => 'Pair Plus Mini Royale', 'bet_position' => 'PP*MR', 'payout_multiplier' => 40, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 25, 'game_type_id' => 4, 'bet_name' => 'Pair Plus Straight Flush', 'bet_position' => 'PP*SF', 'payout_multiplier' => 40, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 26, 'game_type_id' => 4, 'bet_name' => 'Pair Plus Three of a Kind', 'bet_position' => 'PP*TK', 'payout_multiplier' => 30, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 27, 'game_type_id' => 4, 'bet_name' => 'Pair Plus Straight', 'bet_position' => 'PP*S', 'payout_multiplier' => 5, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 28, 'game_type_id' => 4, 'bet_name' => 'Pair Plus Flush', 'bet_position' => 'PP*F', 'payout_multiplier' => 4, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 29, 'game_type_id' => 4, 'bet_name' => 'Pair Plus Pair', 'bet_position' => 'PP*P', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 30, 'game_type_id' => 4, 'bet_name' => '6 Card Bonus', 'bet_position' => null, 'payout_multiplier' => null, 'is_active' => 1, 'is_jackpot' => 1],


            /* GAME TYPE 5 */

            ['payout_id' => 31, 'game_type_id' => 5, 'bet_name' => 'Bet BlackJack', 'bet_position' => 'B*BJ', 'payout_multiplier' => 1.5, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 32, 'game_type_id' => 5, 'bet_name' => 'Bet Regular Win', 'bet_position' => 'B*W', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 33, 'game_type_id' => 5, 'bet_name' => 'Bet Insurance Win', 'bet_position' => 'I', 'payout_multiplier' => 2, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 34, 'game_type_id' => 5, 'bet_name' => 'Bet Mixed Pair', 'bet_position' => 'P*MP', 'payout_multiplier' => 5, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 35, 'game_type_id' => 5, 'bet_name' => 'Bet Colored Pair', 'bet_position' => 'P*CP', 'payout_multiplier' => 12, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 36, 'game_type_id' => 5, 'bet_name' => 'Bet Perfect Pair', 'bet_position' => 'P*PP', 'payout_multiplier' => 25, 'is_active' => 1, 'is_jackpot' => 0],

            ['payout_id' => 37, 'game_type_id' => 5, 'bet_name' => 'Charlie777', 'bet_position' => null, 'payout_multiplier' => null, 'is_active' => 1, 'is_jackpot' => 1],


            /* GAME TYPE 6 */

            ['payout_id' => 38, 'game_type_id' => 6, 'bet_name' => 'Bet', 'bet_position' => 'B', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 39, 'game_type_id' => 6, 'bet_name' => 'Board', 'bet_position' => 'BB', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 40, 'game_type_id' => 6, 'bet_name' => 'Board Bonus Trail', 'bet_position' => 'BB*T', 'payout_multiplier' => 5, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 41, 'game_type_id' => 6, 'bet_name' => 'Board Bonus Straight Flush', 'bet_position' => 'BB*SF', 'payout_multiplier' => 4, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 42, 'game_type_id' => 6, 'bet_name' => 'Board Bonus Run', 'bet_position' => 'BB*R', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 43, 'game_type_id' => 6, 'bet_name' => 'Bet Trail', 'bet_position' => 'B*T', 'payout_multiplier' => 100, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 44, 'game_type_id' => 6, 'bet_name' => 'Bet Straight Flush', 'bet_position' => 'B*SF', 'payout_multiplier' => 75, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 45, 'game_type_id' => 6, 'bet_name' => 'Bet Run', 'bet_position' => 'B*R', 'payout_multiplier' => 4, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 46, 'game_type_id' => 6, 'bet_name' => 'Bet Flush', 'bet_position' => 'B*F', 'payout_multiplier' => 2, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 47, 'game_type_id' => 6, 'bet_name' => 'High', 'bet_position' => 'H', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 48, 'game_type_id' => 6, 'bet_name' => 'Low Nine Top', 'bet_position' => 'L*9', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 49, 'game_type_id' => 6, 'bet_name' => 'Low Eight Top', 'bet_position' => 'L*8', 'payout_multiplier' => 2, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 50, 'game_type_id' => 6, 'bet_name' => 'Low Seven Top', 'bet_position' => 'L*7', 'payout_multiplier' => 4, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 51, 'game_type_id' => 6, 'bet_name' => 'Low Six Top', 'bet_position' => 'L*6', 'payout_multiplier' => 15, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 52, 'game_type_id' => 6, 'bet_name' => 'Low Five Top', 'bet_position' => 'L*5', 'payout_multiplier' => 50, 'is_active' => 1, 'is_jackpot' => 0],


            /* GAME TYPE 7 */

            ['payout_id' => 53, 'game_type_id' => 7, 'bet_name' => 'Bet', 'bet_position' => 'B', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 54, 'game_type_id' => 7, 'bet_name' => 'War', 'bet_position' => 'W', 'payout_multiplier' => 1, 'is_active' => 1, 'is_jackpot' => 0],
            ['payout_id' => 55, 'game_type_id' => 7, 'bet_name' => 'Tie', 'bet_position' => 'T', 'payout_multiplier' => 10, 'is_active' => 1, 'is_jackpot' => 0],

        ]);
    }
}
