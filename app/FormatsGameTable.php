<?php

namespace App;

use App\Models\GameTable;
use App\Models\BaccaratPreset;
use App\Models\AndarBaharPreset;
use App\Models\DragonTigerPreset;
use App\Models\ThreeCardPokerPreset;
use App\Models\BlackjackPreset;
use App\Models\MiniFlushPreset;
use App\Models\CasinoWarPreset;

trait FormatsGameTable
{
    private function formatTableResponse(GameTable $table): array
    {
        $config = $table->config;
        $preset = $config?->preset;
        $chip   = $preset?->chipPreset;

        return [
            // ── Table Core ──────────────────────────────
            'table_id'   => $table->id,
            'table_name' => $table->table_name,
            'status'     => $table->status,
            'active_mac' => $table->active_mac,
            'float_reference' => (float) $table->float,
            'felt_color' => $table->felt_color,

            // ── Game Type ───────────────────────────────
            'game_type' => $table->gameType ? [
                'id'          => $table->gameType->id,
                'name'        => $table->gameType->name,
                'code'        => $table->gameType->code,
                'description' => $table->gameType->description,
            ] : null,

            // ── Game Config ─────────────────────────────
            'config' => $preset ? array_merge(
                [
                    'preset_id'   => $preset->id,
                    'preset_name' => $preset->name,
                    'min_bet'     => $this->parsePipeValues($preset->min_bet),
                    'max_bet'     => $this->parsePipeValues($preset->max_bet),
                    'burn_card'   => $preset->burn_card,
                ],
                $this->formatPresetFields($preset)
            ) : null,

            // ── Chip Preset ─────────────────────────────
            'chip_preset' => $chip ? [
                'id'         => $chip->id,
                'base_value' => (float) $chip->base_value,
                'chips'      => [
                    ['position' => 1, 'value' => (float) $chip->chip_1_value],
                    ['position' => 2, 'value' => (float) $chip->chip_2_value],
                    ['position' => 3, 'value' => (float) $chip->chip_3_value],
                    ['position' => 4, 'value' => (float) $chip->chip_4_value],
                    ['position' => 5, 'value' => (float) $chip->chip_5_value],
                ],
            ] : null,

            // ── Payout Rules ────────────────────────────
            'payout_rules' => $table->payoutRules
                ->filter(fn($r) => $r->payoutRule !== null)
                ->map(function ($r) use ($preset) {
                    $multiplier = $r->payoutRule->payout_multiplier
                        ? (float) $r->payoutRule->payout_multiplier
                        : null;

                    if ($preset instanceof \App\Models\BaccaratPreset) {
                        if (strtoupper($r->payoutRule->bet_position ?? '') === 'B') {
                            $multiplier = $preset->getBankerMultiplier();
                        }
                        if (strtoupper($r->payoutRule->bet_position ?? '') === 'B6' && $r->is_active) {
                            $multiplier = $preset->getBaccarat6Multiplier();
                        }
                    }

                    return [
                        'payout_id'         => $r->payoutRule->payout_id,
                        'bet_name'          => $r->payoutRule->bet_name,
                        'bet_position'      => $r->payoutRule->bet_position,
                        'payout_multiplier' => $multiplier,
                        'is_jackpot'        => (bool) $r->payoutRule->is_jackpot,
                        'seed_value'        => $r->payoutRule->is_jackpot && $r->is_active
                            ? (float) $r->seed_value
                            : null,
                        'is_active'         => (bool) $r->is_active,
                    ];
                })
                ->values(),

            'created_at' => $table->created_at?->toISOString(),
            'updated_at' => $table->updated_at?->toISOString(),
        ];
    }

    private function formatPresetFields($preset): array
    {
        return match (true) {

            $preset instanceof BaccaratPreset => [
                'side_min_bet'      => (float) $preset->side_min_bet,
                'side_max_bet'      => (float) $preset->side_max_bet,
                'commission'        => (bool)  $preset->commission,
                'banker_multiplier' => $preset->getBankerMultiplier(),
                'baccarat_6_commission' => (bool)  $preset->baccarat_6_commission,
                'baccarat_6_multiplier' => $preset->getBaccarat6Multiplier(),
                // 'enable_pairbets'   => (bool)  $preset->enable_pairbets,
                // 'enable_lucky6'     => (bool)  $preset->enable_lucky6,
            ],

            $preset instanceof AndarBaharPreset => [
                // 'enable_super_andar' => (bool) $preset->enable_super_andar,
                // 'enable_super_bahar' => (bool) $preset->enable_super_bahar,
            ],

            $preset instanceof DragonTigerPreset => [
                'tie_min' => (float) $preset->tie_min,
                'tie_max' => (float) $preset->tie_max,
            ],

            $preset instanceof ThreeCardPokerPreset => [
                'side_min'       => (float) $preset->side_min,
                'side_max'       => (float) $preset->side_max,
                // 'six_card_bonus' => (float) $preset->six_card_bonus,
            ],

            $preset instanceof BlackjackPreset => [
                'pair_min'           => (float) $preset->pair_min,
                'pair_max'           => (float) $preset->pair_max,
                'surrender'          => (bool)  $preset->surrender,
                'insurance'          => (bool)  $preset->insurance,
                // 'split_type'         =>         $preset->split_type,
                // 'rule_type'          =>         $preset->rule_type,
                // 'enable_777_charlie' => (bool)  $preset->enable_777_charlie,
            ],

            $preset instanceof MiniFlushPreset => [
                'hl_min' => (float) $preset->hl_min,
                'hl_max' => (float) $preset->hl_max,
            ],

            $preset instanceof CasinoWarPreset => [
                'tie_min' => (float) $preset->tie_min,
                'tie_max' => (float) $preset->tie_max,
            ],

            default => []
        };
    }

    // ── Helper ────────────────────────────────────────────────────────────
private function parsePipeValues(?string $value): array
{
    if (!$value) return [];
    return array_map(
        fn($v) => (float) trim($v),
        explode('|', $value)
    );
}
}
