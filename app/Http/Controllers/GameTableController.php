<?php

namespace App\Http\Controllers;

use App\Models\GameTable;
use App\Models\GameType;
use App\Models\Chip;
use App\Models\GameTableConfig;
use App\Models\BaccaratPreset;
use App\Models\AndarBaharPreset;
use App\Models\DragonTigerPreset;
use App\Models\ThreeCardPokerPreset;
use App\Models\BlackjackPreset;
use App\Models\MiniFlushPreset;
use App\Models\CasinoWarPreset;
use App\Models\PayoutRule;
use App\Models\GameTablePayoutRule;
use App\Models\TableLedger;
use App\Models\TableFloat;
use Illuminate\Support\Facades\DB;

use App\FormatsGameTable;


use Illuminate\Http\Request;

class GameTableController extends Controller
{
    private function resolvePresetModel(int $gameTypeId): string
    {
        return match (GameType::findOrFail($gameTypeId)->code) {
            'BAC'       => BaccaratPreset::class,
            'AB'     => AndarBaharPreset::class,
            'DT'    => DragonTigerPreset::class,
            '3CP' => ThreeCardPokerPreset::class,
            'BJ'      => BlackjackPreset::class,
            'MF'      => MiniFlushPreset::class,
            'CW'      => CasinoWarPreset::class,
            default          => throw new \Exception("Unknown game type: " . $gameTypeId)
        };
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 1);

        $query = GameTable::with(['gameType', 'config.preset.chipPreset', 'payoutRules.payoutRule'])
            ->where('status', $status)
            ->latest();

        if ($request->search) {
            $query->where('table_name', 'like', "%{$request->search}%");
        }

        $tables = $query->paginate(10)->appends($request->only(['status', 'search']));

        return view('game_tables.index', compact('tables', 'status'));
    }

    public function create()
    {
        $gameTypes  = GameType::where('status', 1)->get();
        $chipPresets = Chip::where('status', 1)->get();
        return view('game_tables.create', compact('gameTypes', 'chipPresets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_name'     => 'required|string|max:255|unique:game_tables,table_name',
            'game_type_id'   => 'required|exists:game_types,id',
            'active_mac'     => 'nullable|string|max:255',
            'float'          => 'nullable|numeric',
            'felt_color'     => 'nullable|string|max:50',
            'chip_preset_id' => 'required|exists:chips,id',
            'config.name'    => 'required|string|max:255',
            'config.min_bet' => 'required|numeric|min:0',
            'config.max_bet' => 'required|numeric|gt:config.min_bet',
            'config.burn_card' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {

            // 1. Create game table
            $table = GameTable::create([
                'table_name'   => $request->table_name,
                'game_type_id' => $request->game_type_id,
                'active_mac'   => $request->active_mac,
                'float'        => $request->float,
                'felt_color'   => $request->felt_color,
                'status'       => 1,
            ]);

            // 2. Resolve preset model
            $presetModel = $this->resolvePresetModel($request->game_type_id);

            // 3. Create game-specific preset
            $configData = array_merge(
                $request->input('config', []),
                ['chip_preset_id' => $request->chip_preset_id]
            );
            $preset = $presetModel::create($configData);

            // 4. Link to pivot
            GameTableConfig::create([
                'table_id'    => $table->id,
                'preset_type' => $preset::class,
                'preset_id'   => $preset->id,
                'assigned_by' => auth()->guard('web')->user()->id,
                'assigned_at' => now(),
            ]);

            // ── Sync payout rules ──────────────────────────────────────────
            $allRules = PayoutRule::where('game_type_id', $request->game_type_id)
                ->pluck('payout_id');

            $overrides = $request->input('payout_overrides', []); // checked = active

            $payoutData = $allRules->map(function ($payoutId) use ($overrides, $table) {
                return [
                    'table_id'   => $table->id,
                    'payout_id'  => $payoutId,
                    'is_active'  => isset($overrides[$payoutId]) ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            GameTablePayoutRule::insert($payoutData);

            $seedValues = $request->input('seed_values', []);

            foreach ($seedValues as $payoutId => $seedValue) {
                if ($seedValue === null || $seedValue === '') continue;

                GameTablePayoutRule::where('table_id', $table->id)
                    ->where('payout_id', $payoutId)
                    ->update(['seed_value' => $seedValue]);
            }
        });

        return redirect()->route('game_tables.index')
            ->with('success', 'Game table configured successfully.');
    }

    public function edit(GameTable $gameTable)
    {
        $gameTable->load(['gameType', 'config.preset.chipPreset']);
        $gameTypes    = GameType::where('status', 1)->get();
        $chipPresets  = Chip::where('status', 1)->get();

        // Get global payout rules merged with this table's saved overrides
        $payoutRules = PayoutRule::where('game_type_id', $gameTable->game_type_id)
            ->get()
            ->map(function ($rule) use ($gameTable) {
                // Override is_active with table-specific saved value if it exists
                $saved = GameTablePayoutRule::where('table_id', $gameTable->id)
                    ->where('payout_id', $rule->payout_id)
                    ->first();
                $rule->is_active = $saved ? $saved->is_active : $rule->is_active;
                return $rule;
            });

        return view('game_tables.edit', compact(
            'gameTable',
            'gameTypes',
            'chipPresets',
            'payoutRules'
        ));
    }

    public function update(Request $request, GameTable $gameTable)
    {
        $request->validate([
            'table_name'     => 'required|string|max:255|unique:game_tables,table_name,' . $gameTable->id,
            'game_type_id'   => 'required|exists:game_types,id',
            'active_mac'     => 'nullable|string|max:255',
            'float'          => 'nullable|numeric',
            'felt_color'     => 'nullable|string|max:50',
            'chip_preset_id' => 'required|exists:chips,id',
            'config.name'    => 'required|string|max:255',
            'config.min_bet' => 'required|numeric|min:0',
            'config.max_bet' => 'required|numeric|gt:config.min_bet',
            'config.burn_card' => 'nullable|integer|min:0',
        ]);
        DB::transaction(function () use ($request, $gameTable) {

            // 1. Update game table
            $gameTable->update([
                'table_name' => $request->table_name,
                'active_mac' => $request->active_mac,
                'float'      => $request->float,
                'felt_color' => $request->felt_color,
            ]);

            // 2. Update preset
            $config  = $gameTable->config;
            $preset  = $config->preset;
            $preset->update(array_merge(
                $request->input('config', []),
                ['chip_preset_id' => $request->chip_preset_id]
            ));

            // ── Sync payout rules ──────────────────────────────────────────
            $allRules = PayoutRule::where('game_type_id', $gameTable->game_type_id)
                ->pluck('payout_id');

            $overrides = $request->input('payout_overrides', []);

            foreach ($allRules as $payoutId) {
                GameTablePayoutRule::updateOrCreate(
                    [
                        'table_id'  => $gameTable->id,
                        'payout_id' => $payoutId,
                    ],
                    [
                        'is_active' => isset($overrides[$payoutId]) ? 1 : 0,
                    ]
                );
            }
            $seedValues = $request->input('seed_values', []);

            foreach ($seedValues as $payoutId => $seedValue) {
                GameTablePayoutRule::where('table_id', $gameTable->id)
                    ->where('payout_id', $payoutId)
                    ->update([
                        'seed_value' => ($seedValue !== '' && $seedValue !== null)
                            ? $seedValue
                            : null
                    ]);
            }
        });

        return redirect()->route('game_tables.index')
            ->with('success', 'Configuration updated successfully.');
    }

    public function deactivate(GameTable $gameTable)
    {
        $gameTable->update(['status' => 0]);

        return redirect()
            ->route('game_tables.index', ['status' => 1])
            ->with('success', "Table '{$gameTable->table_name}' deactivated successfully.");
    }

    public function restore(GameTable $gameTable)
    {
        $gameTable->update(['status' => 1]);

        return redirect()
            ->route('game_tables.index', ['status' => 1])
            ->with('success', "Table '{$gameTable->table_name}' restored successfully.");
    }

    // ══════════════════════════════════════════════════════
    // API — Fetch all game tables
    // GET /api/v1/game-tables
    // ══════════════════════════════════════════════════════
    public function apiIndex(Request $request)
    {
        try {
            $tables = GameTable::with([
                'gameType',
                'config.preset.chipPreset',
                'payoutRules.payoutRule'
            ])
                ->where('status', 1)
                ->latest()
                ->get()
                ->map(fn($table) => $this->formatTableResponse($table));

            return response()->json([
                'success' => true,
                'count'   => $tables->count(),
                'data'    => $tables
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch game tables.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    // API — Fetch single game table by ID
    // GET /api/v1/game-tables/{id}
    // ══════════════════════════════════════════════════════
    public function apiShow($id)
    {
        try {
            $table = GameTable::with([
                'gameType',
                'config.preset.chipPreset',
                'payoutRules.payoutRule'
            ])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $this->formatTableResponse($table)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch game table.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════
    // Shared response formatter
    // ══════════════════════════════════════════════════════
    use FormatsGameTable;

    public function registerMac(Request $request, $id)
    {
        $request->validate([
            'mac_address' => [
                'required',
                'string',
                'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/' // valid MAC format
            ],
        ]);

        try {
            $table      = GameTable::findOrFail($id);
            $incomingMac = strtoupper($request->mac_address);

            // ── Table is inactive ────────────────────────────────────────
            if ($table->status !== true) {
                return response()->json([
                    'success' => false,
                    'code'    => 'TABLE_INACTIVE',
                    'message' => "Table '{$table->table_name}' is inactive and cannot be registered.",
                ], 422);
            }

            // ── No MAC registered yet → register it ─────────────────────
            if (is_null($table->active_mac)) {
                $table->update(['active_mac' => $incomingMac]);

                return response()->json([
                    'success' => true,
                    'code'    => 'MAC_REGISTERED',
                    'message' => "MAC address registered successfully for table '{$table->table_name}'.",
                    'data'    => [
                        'table_id'   => $table->id,
                        'table_name' => $table->table_name,
                        'active_mac' => $incomingMac,
                        'registered_at' => now()->toISOString(),
                    ],
                ], 200);
            }

            $registeredMac = strtoupper($table->active_mac);

            // ── Same MAC → already bound to this device ──────────────────
            if ($registeredMac === $incomingMac) {
                return response()->json([
                    'success' => true,
                    'code'    => 'MAC_ALREADY_REGISTERED',
                    'message' => "This device is already registered to table '{$table->table_name}'.",
                    'data'    => [
                        'table_id'   => $table->id,
                        'table_name' => $table->table_name,
                        'active_mac' => $registeredMac,
                    ],
                ], 200);
            }

            // ── Different MAC → table bound to another device ────────────
            return response()->json([
                'success' => false,
                'code'    => 'MAC_CONFLICT',
                'message' => "Table '{$table->table_name}' is already bound to a different device. Contact administrator to reassign.",
                'data'    => [
                    'table_id'      => $table->id,
                    'table_name'    => $table->table_name,
                    'registered_mac' => $registeredMac,
                    'incoming_mac'  => $incomingMac,
                ],
            ], 409); // 409 Conflict

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'code'    => 'TABLE_NOT_FOUND',
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'code'    => 'SERVER_ERROR',
                'message' => 'Failed to process MAC registration.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function unregisterMac(GameTable $gameTable)
    {
        $old = $gameTable->active_mac;
        $gameTable->update(['active_mac' => null]);

        return redirect()
            ->route('game_tables.index')
            ->with('success', "MAC address ({$old}) unregistered from '{$gameTable->table_name}'. Device can now be reassigned.");
    }

    public function currentFloat(Request $request, $id)
    {
        $request->validate([
            'gameday' => 'required|date_format:Y-m-d',
        ]);

        try {
            $table   = GameTable::findOrFail($id);
            $gameday = $request->gameday;

            // 1. Check active session for this gameday
            $session = TableFloat::where('table_id', $id)
                ->where('gameday', $gameday)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NO_SESSION',
                    'message' => "No session found for table '{$table->table_name}' on gameday {$gameday}.",
                    'data'    => null,
                ], 404);
            }

            // 2. Get latest ledger entry for current float balance
            //    If no transactions yet, float_open is the current float
            $lastTxn = TableLedger::where('table_id', $id)
                ->where('gameday', $gameday)
                ->latest('txn_id')
                ->first();

            $currentFloat = $lastTxn
                ? (float) $lastTxn->float_balance
                : (float) $session->float_open;

            // 3. Build movement summary for context
            $txns      = TableLedger::where('table_id', $id)
                ->where('gameday', $gameday)
                ->get();

            $totalIn  = (float) $txns->whereIn('txn_type', ['FILL', 'CREDIT', 'BUYIN'])
                ->sum('amount');
            $totalOut = (float) $txns->whereIn('txn_type', ['DROP', 'CASHOUT'])
                ->sum('amount');

            return response()->json([
                'success' => true,
                'code'    => 'FLOAT_FETCHED',
                'data'    => [
                    'table_id'      => $table->id,
                    'table_name'    => $table->table_name,
                    'gameday'       => $gameday,
                    'session_status' => $session->status === 1 ? 'open' : 'closed',

                    'float' => [
                        'open'    => (float) $session->float_open,
                        'current' => $currentFloat,
                        'close'   => $session->float_close
                            ? (float) $session->float_close
                            : null,
                        'movement' => [
                            'total_in'  => $totalIn,
                            'total_out' => $totalOut,
                            'net'       => round($totalIn - $totalOut, 2),
                        ],
                    ],

                    'last_txn' => $lastTxn ? [
                        'txn_id'   => $lastTxn->txn_id,
                        'txn_type' => $lastTxn->txn_type,
                        'amount'   => (float) $lastTxn->amount,
                        'at'       => $lastTxn->created_at?->toISOString(),
                    ] : null,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'code'    => 'TABLE_NOT_FOUND',
                'message' => "Game table with ID {$id} not found.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'code'    => 'SERVER_ERROR',
                'message' => 'Failed to fetch float balance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
