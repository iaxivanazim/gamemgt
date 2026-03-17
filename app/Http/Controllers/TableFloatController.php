<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameTable;
use App\Models\TableFloat;

class TableFloatController extends Controller
{
    public function open(Request $request, $id)
    {
        $request->validate([
            'opened_by' => 'required|string|max:255',
            'gameday'   => 'required|date_format:Y-m-d',
        ]);

        try {
            // 1. Fetch the game table
            $gameTable = GameTable::with([
                'gameType',
                'config.preset.chipPreset',
                'payoutRules.payoutRule'
            ])->findOrFail($id);

            // 2. Table must be active
            if ($gameTable->status == 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Table '{$gameTable->table_name}' is inactive and cannot be opened.",
                ], 422);
            }

            // 3. Check if already open today
            $existing = TableFloat::where('table_id', $id)
                ->where('gameday', $request->gameday)
                ->first();

            if ($existing && $existing->status === 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Table '{$gameTable->table_name}' is already open for today's gameday.",
                    'session' => $this->formatSession($existing),
                ], 422);
            }

            // 4. If closed session exists for today, prevent re-opening
            if ($existing && $existing->status === 1) {
                return response()->json([
                    'success' => false,
                    'message' => "Table '{$gameTable->table_name}' has already been opened and closed today. Cannot reopen.",
                ], 422);
            }

            // 5. Open the table — float_open pulled from game_table
            $session = TableFloat::create([
                'table_id'   => $gameTable->id,
                'float_open' => $gameTable->float,   // from game_tables.float
                'opened_by'  => $request->opened_by,
                'status'     => 0, // 0 = open, 1 = closed
                'gameday'    => $request->gameday,
                'opened_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Table '{$gameTable->table_name}' opened successfully.",
                'session' => $this->formatSession($session),
                'table'   => $this->formatTableSnapshot($gameTable),
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open table.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // CLOSE TABLE
    // POST /api/v1/tables/{id}/close
    // Body: { "closed_by": "dealer_name_or_id", "float_close": 49500.00 }
    // ══════════════════════════════════════════════════════════════
    public function close(Request $request, $id)
    {
        $request->validate([
            'closed_by'   => 'required|string|max:255',
            'float_close' => 'required|numeric|min:0',
        ]);

        try {
            $gameTable = GameTable::findOrFail($id);

            // 1. Find today's open session
            $session = TableFloat::where('table_id', $id)
                ->where('gameday', $request->gameday)
                ->where('status', 0)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => "No open session found for table '{$gameTable->table_name}' today.",
                ], 422);
            }

            // 2. Close the session
            $session->update([
                'float_close' => $request->float_close,
                'closed_by'   => $request->closed_by,
                'status'      => 1,
                'closed_at'   => now(),
            ]);

            // 3. Calculate variance
            $variance = $request->float_close - $session->float_open;

            return response()->json([
                'success'  => true,
                'message'  => "Table '{$gameTable->table_name}' closed successfully.",
                'session'  => $this->formatSession($session->fresh()),
                'summary'  => [
                    'float_open'   => (float) $session->float_open,
                    'float_close'  => (float) $request->float_close,
                    'variance'     => round($variance, 2),
                    'variance_pct' => $session->float_open > 0
                        ? round(($variance / $session->float_open) * 100, 2)
                        : 0,
                    'duration'     => $session->opened_at->diffForHumans(now(), true),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close table.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // CURRENT SESSION
    // GET /api/v1/tables/{id}/session
    // ══════════════════════════════════════════════════════════════
    public function currentSession(Request $request,$id)
    {
        try {
            $gameTable = GameTable::findOrFail($id);

            $session = TableFloat::where('table_id', $id)
                ->where('gameday', $request->gameday)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => true,
                    'status'  => 'not_opened',
                    'message' => "Table '{$gameTable->table_name}' has not been opened today.",
                    'session' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status'  => $session->status === 0 ? 'open' : 'closed',
                'session' => $this->formatSession($session),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SESSION HISTORY
    // GET /api/v1/tables/{id}/history?from=2026-01-01&to=2026-03-01
    // ══════════════════════════════════════════════════════════════
    public function history(Request $request, $id)
    {
        try {
            $gameTable = GameTable::findOrFail($id);

            $query = TableFloat::where('table_id', $id)->latest('gameday');

            if ($request->from) {
                $query->whereDate('gameday', '>=', $request->from);
            }
            if ($request->to) {
                $query->whereDate('gameday', '<=', $request->to);
            }

            $sessions = $query->paginate(20);

            return response()->json([
                'success'    => true,
                'table_id'   => $gameTable->id,
                'table_name' => $gameTable->table_name,
                'total'      => $sessions->total(),
                'sessions'   => $sessions->map(fn($s) => $this->formatSession($s)),
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'last_page'    => $sessions->lastPage(),
                    'per_page'     => $sessions->perPage(),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // Formatters
    // ══════════════════════════════════════════════════════════════
    private function formatSession(TableFloat $session): array
    {
        return [
            'float_id'    => $session->float_id,
            'table_id'    => $session->table_id,
            'gameday'     => $session->gameday->toDateString(),
            'status'      => $session->status === 0 ? 'open' : 'closed',
            'float_open'  => (float) $session->float_open,
            'float_close' => $session->float_close ? (float) $session->float_close : null,
            'variance'    => $session->float_close
                ? round($session->float_close - $session->float_open, 2)
                : null,
            'opened_by'   => $session->opened_by,
            'closed_by'   => $session->closed_by,
            'opened_at'   => $session->opened_at?->toISOString(),
            'closed_at'   => $session->closed_at?->toISOString(),
        ];
    }

    private function formatTableSnapshot(GameTable $table): array
    {
        $preset = $table->config?->preset;
        $chip   = $preset?->chipPreset;

        return [
            'table_id'     => $table->id,
            'table_name'   => $table->table_name,
            'game_type'    => $table->gameType?->name,
            'game_code'    => $table->gameType?->code,
            'felt_color'   => $table->felt_color,
            'min_bet'      => (float) $preset?->min_bet,
            'max_bet'      => (float) $preset?->max_bet,
            'chip_preset'  => $chip ? [
                'base_value' => (float) $chip->base_value,
                'chips'      => [
                    (float) $chip->chip_1_value,
                    (float) $chip->chip_2_value,
                    (float) $chip->chip_3_value,
                    (float) $chip->chip_4_value,
                    (float) $chip->chip_5_value,
                ],
            ] : null,
            'payout_rules' => $table->payoutRules
                ->filter(fn($r) => $r->payoutRule && $r->is_active)
                ->map(fn($r) => [
                    'bet_name'          => $r->payoutRule->bet_name,
                    'bet_position'      => $r->payoutRule->bet_position,
                    'payout_multiplier' => (float) $r->payoutRule->payout_multiplier,
                ])->values(),
        ];
    }
}
