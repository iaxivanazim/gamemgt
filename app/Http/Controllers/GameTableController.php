<?php

namespace App\Http\Controllers;

use App\Models\GameTable;
use App\Models\GameType;
use App\Models\Theme;

use Illuminate\Http\Request;

class GameTableController extends Controller
{
    public function index()
    {
        $tables = GameTable::with(['gameType', 'theme'])->latest()->get();
        return view('game_tables.index', compact('tables'));
    }

    public function create()
    {
        $gameTypes = GameType::where('status', true)->get();
        $themes = Theme::where('status', true)->get();

        return view('game_tables.create', compact('gameTypes', 'themes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_name' => 'required|string|max:255',
            'game_type_id' => 'required|exists:game_types,id',
            'active_mac' => 'required|unique:game_tables,active_mac',
            'float' => 'required|numeric|min:0',
            'theme_id' => 'nullable|exists:themes,id',
        ]);

        GameTable::create($request->all());

        return redirect()
            ->route('game_tables.index')
            ->with('success', 'Game Table created successfully.');
    }


    public function edit($id)
    {
        $table = GameTable::findOrFail($id);
        $gameTypes = GameType::where('status', true)->get();
        $themes = Theme::where('status', true)->get();

        return view('game_tables.edit', compact('table', 'gameTypes', 'themes'));
    }


    public function update(Request $request, $id)
    {
        $table = GameTable::findOrFail($id);

        $request->validate([
            'table_name' => 'required|string|max:255',
            'game_type_id' => 'required|exists:game_types,id',
            'active_mac' => 'required|unique:game_tables,active_mac,' . $id,
            'float' => 'required|numeric|min:0',
            'theme_id' => 'nullable|exists:themes,id',
        ]);

        $table->update($request->all());

        return redirect()
            ->route('game_tables.index')
            ->with('success', 'Game Table updated successfully.');
    }


    public function destroy($id)
    {
        $table = GameTable::findOrFail($id);
        $table->delete();

        return response()->json([
            'message' => 'Game Table deleted successfully.'
        ]);
    }

    // API Methods

    public function apiIndex()
    {
        $tables = GameTable::with(['gameType', 'theme'])
            ->where('status', 1)
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $tables->count(),
            'data'    => $tables
        ]);
    }

    public function apiShow($id)
    {
        $table = GameTable::with(['gameType', 'theme'])
            ->where('status', 1)
            ->find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Game table not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $table
        ]);
    }

    public function apiActive()
    {
        $tables = GameTable::with(['gameType', 'theme'])
            ->where('status', 1)
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $tables->count(),
            'data'    => $tables
        ]);
    }

    public function apiByMac($mac)
    {
        $table = GameTable::with(['gameType', 'theme'])
            ->where('active_mac', $mac)
            ->where('status', 1)
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or inactive table'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $table
        ]);
    }

    public function apiConfiguration($id)
    {
        $table = GameTable::with(['gameType', 'theme'])
            ->where('status', 1)
            ->find($id);

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Game table not found'
            ], 404);
        }

        $configuration = [
            'table_id'      => $table->id,
            'table_name'    => $table->table_name,
            'game_type'     => [
                'id'   => $table->gameType->id ?? null,
                'name' => $table->gameType->name ?? null,
            ],
            'theme'         => [
                'id'   => $table->theme->id ?? null,
                'name' => $table->theme->name ?? null,
            ],
            'mac_address'   => $table->active_mac,
            'status'        => $table->status,
        ];

        return response()->json([
            'success' => true,
            'configuration' => $configuration
        ]);
    }
}
