<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BaccaratHistory;
use App\Models\AndarbaharHistory;
use App\Models\DragontigerHistory;
use App\Models\ThreecardpokerHistory;
use App\Models\BlackjackHistory;
use App\Models\MiniflushHistory;
use App\Models\CasinowarHistory;
use App\Models\GameTable;
use App\Models\GameType;

class GameHistoryController extends Controller
{
    private array $gameMap = [
        'BACCARAT'       => [BaccaratHistory::class,       'baccarat_history',       'baccarat'],
        'BAC'            => [BaccaratHistory::class,       'baccarat_history',       'baccarat'],
        'ANDARBAHAR'     => [AndarbaharHistory::class,     'andarbahar_history',     'andarbahar'],
        'AB'             => [AndarbaharHistory::class,     'andarbahar_history',     'andarbahar'],
        'DRAGONTIGER'    => [DragontigerHistory::class,    'dragontiger_history',    'dragontiger'],
        'DT'             => [DragontigerHistory::class,    'dragontiger_history',    'dragontiger'],
        'THREECARDPOKER' => [ThreecardpokerHistory::class, 'threecardpoker_history', 'threecardpoker'],
        '3CP'            => [ThreecardpokerHistory::class, 'threecardpoker_history', 'threecardpoker'],
        'BLACKJACK'      => [BlackjackHistory::class,      'blackjack_history',      'blackjack'],
        'BJ'             => [BlackjackHistory::class,      'blackjack_history',      'blackjack'],
        'MINIFLUSH'      => [MiniflushHistory::class,      'miniflush_history',      'miniflush'],
        'MF'             => [MiniflushHistory::class,      'miniflush_history',      'miniflush'],
        'CASINOWAR'      => [CasinowarHistory::class,      'casinowar_history',      'casinowar'],
        'CW'             => [CasinowarHistory::class,      'casinowar_history',      'casinowar'],
    ];

    public function index(Request $request)
    {
        $tables    = GameTable::with('gameType')->where('status', 1)->get();
        $gameTypes = GameType::where('status', 1)->get();
        $records   = [];
        $selectedTable = null;
        $selectedGame  = $request->input('game');
        $tableId       = $request->input('table_id');
        $normalizedGame = null;

        if ($selectedGame && isset($this->gameMap[strtoupper($selectedGame)])) {
            $normalizedGame = $this->gameMap[strtoupper($selectedGame)][2];
        }

        if ($selectedGame && $tableId) {
            $req = Request::create('', 'GET', $request->all());
            $response      = $this->byTable($req, $selectedGame, (int) $tableId);
            $records       = $response->getData(true);
            $selectedTable = GameTable::with('gameType')->find($tableId);
        }

        return view('history.index', compact(
            'tables',
            'gameTypes',
            'records',
            'selectedTable',
            'selectedGame',
            'normalizedGame'
        ));
    }

    private function resolveModel(string $game): string
    {
        $key = strtoupper($game);
        abort_if(!isset($this->gameMap[$key]), 404, 'Unknown game type: ' . $game);
        return $this->gameMap[$key][0];
    }

    public function store(Request $request, string $game)
    {
        $model = $this->resolveModel($game);
        $record = $model::create($request->all());
        return response()->json(['success' => true, 'id' => $record->id], 201);
    }

    public function byTable(Request $request, string $game, int $id)
    {
        $model = $this->resolveModel($game);
        $data = $model::where('table_id', $id)
            ->when($request->tab_id,    fn($q) => $q->where('tab_id', $request->tab_id))
            ->when($request->date,      fn($q) => $q->whereDate('date_time', $request->date))
            ->when($request->from,      fn($q) => $q->where('date_time', '>=', $request->from))
            ->when($request->to,        fn($q) => $q->where('date_time', '<=', $request->to))
            ->when($request->winner,    fn($q) => $q->where('winner', $request->winner))
            ->orderBy('date_time', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($data);
    }

    public function byTab(Request $request, string $game, string $tabId)
    {
        $model = $this->resolveModel($game);
        $data = $model::where('tab_id', $tabId)
            ->when($request->table_id, fn($q) => $q->where('table_id', $request->table_id))
            ->orderBy('date_time', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($data);
    }

    public function show(string $game, int $recordId)
    {
        $model = $this->resolveModel($game);
        return response()->json($model::findOrFail($recordId));
    }
}
