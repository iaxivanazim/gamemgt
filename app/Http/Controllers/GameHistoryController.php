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
        $data = $request->all();

        // Support multiple bet positions in format "banker:100,tie:200" or as array
        if (isset($data['bet_position']) && is_string($data['bet_position'])) {
            $data['bet_position'] = $this->parseKeyValueString($data['bet_position']);
        }

        // Support side_win in format "player_pair,lucky6" or as array
        if (isset($data['side_win']) && is_string($data['side_win'])) {
            $data['side_win'] = array_map('trim', explode(',', $data['side_win']));
        }

        $record = $model::create($data);
        return response()->json(['success' => true, 'id' => $record->id], 201);
    }

    private function parseKeyValueString(string $str): array
    {
        if (empty($str)) return [];
        
        $parts = explode(',', $str);
        $result = [];
        foreach ($parts as $part) {
            if (str_contains($part, ':')) {
                [$key, $val] = explode(':', $part, 2);
                $result[trim($key)] = trim($val);
            } else {
                $result[] = trim($part);
            }
        }
        return $result;
    }

    public function byTable(Request $request, string $game, int $id)
    {
        $model = $this->resolveModel($game);
        
        // If tab_id or date filters are used, we might want to fall back to traditional pagination
        // or just apply them within the game_no set. 
        // For now, let's implement the game_no grouping as requested.

        $gameNo = $request->input('game_no');
        
        // If no game_no, get the latest one from the table
        if (!$gameNo) {
            $latest = $model::where('table_id', $id)->orderBy('date_time', 'desc')->first();
            $gameNo = $latest ? $latest->game_no : null;
        }

        $query = $model::where('table_id', $id)
            ->when($gameNo, fn($q) => $q->where('game_no', $gameNo))
            ->when($request->tab_id, fn($q) => $q->where('tab_id', $request->tab_id))
            ->when($request->date,   fn($q) => $q->whereDate('date_time', $request->date))
            ->orderBy('date_time', 'desc');

        $data = $query->get();

        // Get navigation list (distinct game_nos ordered by time)
        $gameNos = $model::where('table_id', $id)
            ->select('game_no')
            ->groupBy('game_no')
            ->orderByRaw('MAX(date_time) DESC')
            ->pluck('game_no')
            ->toArray();

        $currentIndex = array_search($gameNo, $gameNos);
        $prevGameNo = ($currentIndex !== false && isset($gameNos[$currentIndex + 1])) ? $gameNos[$currentIndex + 1] : null;
        $nextGameNo = ($currentIndex !== false && $currentIndex > 0) ? $gameNos[$currentIndex - 1] : null;

        // Wrap in a structure similar to pagination for compatibility
        return response()->json([
            'data'         => $data,
            'current_page' => $currentIndex !== false ? $currentIndex + 1 : 1,
            'last_page'    => count($gameNos),
            'total'        => count($gameNos), // Total number of games
            'from'         => $currentIndex !== false ? $currentIndex + 1 : 0,
            'to'           => $currentIndex !== false ? $currentIndex + 1 : 0,
            'per_page'     => 1,
            'game_no'      => $gameNo,
            'prev_game_no' => $prevGameNo,
            'next_game_no' => $nextGameNo,
            'all_game_nos' => $gameNos
        ]);
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
