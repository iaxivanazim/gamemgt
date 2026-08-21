<?php

namespace App\Http\Controllers;

use App\Models\GameTable;
use App\Models\TableFloat;
use App\Models\TableLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Render the dashboard view.
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Live dashboard data endpoint (polled every N seconds).
     * GET /dashboard/live-data
     */
    public function liveData()
    {
        $today = today()->toDateString();

        // ── Active tables with open float ─────────────────────────────────────
        $tables = GameTable::with([
            'gameType',
            'currentFloat',
        ])
            ->where('status', 1)
            ->orderByRaw('
                CASE WHEN EXISTS (
                    SELECT 1 FROM table_floats
                    WHERE table_floats.table_id = game_tables.id
                      AND table_floats.closed_at IS NULL
                ) THEN 0 ELSE 1 END ASC
            ')
            ->get();

        // Collect all gamedates for currently-open floats (may include past dates)
        $openGamedays = $tables
            ->map(fn($t) => $t->currentFloat?->gameday?->toDateString())
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Fall back to today if no open tables exist
        $globalGamedays = count($openGamedays) ? $openGamedays : [$today];

        $tableData = $tables->map(function ($table) use ($today) {
            $float = $table->currentFloat;
            $isOpen = !is_null($float);

            // Use the gameday from the table's own open float, not today's date
            $gameday = $isOpen ? $float->gameday->toDateString() : $today;

            // Ledger summary for this table's gameday
            $txns = TableLedger::where('table_id', $table->id)
                ->where('gameday', $gameday)
                ->get();

            $totalBuyin      = (float) $txns->where('txn_type', 'BUYIN')->sum('amount');                          // all buy-ins
            $totalBuyinChips = (float) $txns->where('txn_type', 'BUYIN')->where('payment_medium', 'CHIPS')->sum('amount'); // chips buy-ins only
            $totalBuyinCash  = (float) $txns->where('txn_type', 'BUYIN')->where('payment_medium', 'CASH')->sum('amount');  // cash buy-ins (Drop)
            $totalCashout    = (float) $txns->where('txn_type', 'CASHOUT')->sum('amount');
            $totalFill       = (float) $txns->where('txn_type', 'FILL')->sum('amount');
            $totalCredit     = (float) $txns->where('txn_type', 'CREDIT')->sum('amount');
            $totalDrop       = (float) $txns->where('txn_type', 'DROP')->sum('amount');
            $totalPayout     = (float) $txns->where('txn_type', 'PAYOUT')->sum('amount');
            $txnCount        = $txns->count();

            // Float = open + chip_buyins + fills + credits (signed: negative credit = deduction)
            // Cash buyins (DROP) go in the drop box and do NOT affect the table float.
            // $totalCredit is signed (e.g. -10,000 means chips left the table),
            // so use + $totalCredit, NOT - $totalCredit (which would double-negate).
            $openingFloat = $isOpen ? (float) ($float->float_open ?? 0) : 0;
            $liveFloat    = round($openingFloat + $totalBuyinChips + $totalFill + $totalCredit, 2);

            // Use the recalculated value — do NOT read float_balance from DB,
            // as stored values may include cash buyins erroneously.
            $currentFloat = $isOpen ? $liveFloat : null;
            $statFloat    = $liveFloat;

            // Recent transactions (last 5) for activity feed
            $recentTxns = $txns->sortByDesc('txn_id')->take(5)->map(fn($t) => [
                'txn_id'       => $t->txn_id,
                'txn_type'     => $t->txn_type,
                // BUYIN+CASH displayed as DROP everywhere
                'display_type' => ($t->txn_type === 'BUYIN' && $t->payment_medium === 'CASH') ? 'DROP' : $t->txn_type,
                'amount'       => (float) $t->amount,
                'tab_id'       => $t->tab_id,
                'at'           => $t->created_at?->format('H:i:s'),
            ])->values();

            // Build 6 player seats mapped by tab_id number directly to seat position.
            // tab_id is numeric (1–6); each player sits at the seat matching their tab number,
            // so tab_id 3 always occupies seat 3 regardless of transaction order.
            $activeTabMap = $txns->whereNotNull('tab_id')
                ->pluck('tab_id')
                ->unique()
                ->mapWithKeys(fn($tab) => [(int) $tab => $tab]); // key by numeric tab_id

            $players = collect(range(1, 6))->map(function ($seat) use ($activeTabMap, $txns) {
                $tab = $activeTabMap->get($seat); // seat == tab_id number
                if (!$tab) {
                    return ['seat' => $seat, 'active' => false, 'tab_id' => null, 'balance' => null, 'last_action' => null];
                }
                $tabTxns    = $txns->where('tab_id', $tab)->sortByDesc('txn_id');
                $lastTabTxn = $tabTxns->first();
                return [
                    'seat'        => $seat,
                    'active'      => true,
                    'tab_id'      => $tab,
                    'balance'     => $lastTabTxn ? (float) $lastTabTxn->tab_balance : 0,
                    'last_action' => $lastTabTxn ? $lastTabTxn->txn_type : null,
                    'last_amount' => $lastTabTxn ? (float) $lastTabTxn->amount : null,
                    'last_at'     => $lastTabTxn?->created_at?->format('H:i:s'),
                ];
            });

            return [
                'id'              => $table->id,
                'name'            => $table->table_name,
                'game_type'       => $table->gameType?->name ?? 'Unknown',
                'game_code'       => $table->gameType?->code ?? '??',
                'felt_color'      => $table->felt_color ?? '#1a5c2e',
                'is_open'         => $isOpen,
                'float_open'      => $isOpen ? (float) ($float->float_open ?? 0) : null,
                'float_current'   => $currentFloat,
                'opened_at'       => $isOpen ? $float->opened_at?->format('H:i') : null,
                'txn_count'       => $txnCount,
                // ── Stats bar fields ──────────────────────────────────────────
                'stat_float'      => $isOpen ? round($statFloat, 2) : null,    // opening + chip buyins + fills - credits
                'total_buyin'     => round($totalBuyinChips, 2),               // chips buy-ins only (cash shown in Drop)
                'total_drop'      => round($totalBuyinCash, 2),                // cash buy-ins only
                // ── Other financials ─────────────────────────────────────────
                'total_cashout'   => $totalCashout,
                'total_fill'      => $totalFill,
                'total_credit'    => $totalCredit,
                'total_payout'    => $totalPayout,
                'net_revenue'     => round($totalDrop - $totalFill, 2),
                'recent_txns'     => $recentTxns,
                'players'         => $players,
                'active_players'  => $activeTabMap->count(),
            ];
        });

        // ── Global KPIs ────────────────────────────────────────────────────────
        $openTables    = $tables->filter(fn($t) => !is_null($t->currentFloat))->count();
        $allTxnsToday  = TableLedger::whereIn('gameday', $globalGamedays)->get();
        $totalFloat    = $tableData->sum('float_current');
        $totalRevenue  = $tableData->sum('net_revenue');
        $totalTxns     = $allTxnsToday->count();
        $totalBuyins   = (float) $allTxnsToday->where('txn_type', 'BUYIN')->sum('amount');
        $pendingTxns   = (int)   $allTxnsToday->where('processed', 0)->count();

        // Hourly transaction volume (last 12 hours)
        $hourlyVolume = TableLedger::whereIn('gameday', $globalGamedays)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as volume'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour')
            ->map(fn($r) => ['count' => $r->count, 'volume' => (float) $r->volume]);

        return response()->json([
            'success'    => true,
            'timestamp'  => now()->toISOString(),
            'gameday'    => count($globalGamedays) === 1 ? $globalGamedays[0] : $globalGamedays,
            'kpis'       => [
                'open_tables'   => $openTables,
                'total_tables'  => $tables->count(),
                'total_float'   => round($totalFloat, 2),
                'total_revenue' => round($totalRevenue, 2),
                'total_txns'    => $totalTxns,
                'total_buyins'  => round($totalBuyins, 2),
                'pending_txns'  => $pendingTxns,
            ],
            'hourly_volume' => $hourlyVolume,
            'tables'     => $tableData->values(),
        ]);
    }
}
