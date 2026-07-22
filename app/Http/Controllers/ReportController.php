<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableFloat;
use App\Models\TableLedger;
use App\Models\GameTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('type', 'ledger');
        $fromDate   = $request->input('from_date', now()->format('Y-m-d'));
        $toDate     = $request->input('to_date', now()->format('Y-m-d'));
        $tableId    = $request->input('table_id');
        $tabId      = $request->input('tab_id');
        $gameday    = $request->input('gameday', now()->format('Y-m-d'));

        $tables = GameTable::where('status', 1)->get();
        $data   = $this->getReportData($reportType, $fromDate, $toDate, $tableId, $tabId, $gameday);

        // Distinct tab IDs for the dropdown — only meaningful for ledger + specific table
        $tabIds = [];
        if ($reportType === 'ledger' && $tableId) {
            $tabIds = TableLedger::where('table_id', $tableId)
                ->whereNotNull('tab_id')
                ->where('tab_id', '!=', '')
                ->distinct()
                ->orderBy('tab_id')
                ->pluck('tab_id')
                ->toArray();
        }

        if ($request->has('export')) {
            return $this->exportReport($reportType, $data, $gameday);
        }

        return view('reports.index', compact(
            'tables', 'data', 'reportType',
            'fromDate', 'toDate', 'tableId', 'tabId', 'tabIds', 'gameday'
        ));
    }

    private function getReportData($type, $from, $to, $tableId = null, $tabId = null, $gameday = null)
    {
        return match ($type) {
            'float'   => $this->getFloatReport($from, $to, $tableId),
            'gameday' => $this->getGameDayReport($gameday),
            'ledger'  => $this->getLedgerReport($from, $to, $tableId, $tabId),
            'table'   => $this->getTableReport(),
            default   => collect(),
        };
    }

    private function getFloatReport($from, $to, $tableId)
    {
        $query = TableFloat::with('gameTable')
            ->whereBetween('gameday', [$from, $to]);

        if ($tableId) {
            $query->where('table_id', $tableId);
        }

        return $query->get();
    }

    private function getGameDayReport($gameday)
    {
        // 1. Load all active game tables with their game type
        $gameTables = GameTable::with('gameType')
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        // 2. Aggregate float records per table for this gameday (sum across multiple open/close sessions)
        $floatsByTable = DB::table('table_floats')
            ->select(
                'table_id',
                DB::raw('SUM(float_open) AS total_opening_float'),
                DB::raw('SUM(float_close) AS total_closing_float'),
                DB::raw('MAX(CASE WHEN closed_at IS NULL THEN 1 ELSE 0 END) AS has_open_session')
            )
            ->where('gameday', $gameday)
            ->groupBy('table_id')
            ->get()
            ->keyBy('table_id');

        // 3. Aggregate ledger entries per (table_id, tab_id) for this gameday
        $ledgerAggregates = DB::table('table_ledgers')
            ->select(
                'table_id',
                'tab_id',
                DB::raw("SUM(CASE WHEN txn_type = 'FILL'   THEN amount ELSE 0 END) AS total_fills"),
                DB::raw("SUM(CASE WHEN txn_type = 'CREDIT' THEN amount ELSE 0 END) AS total_credits"),
                DB::raw("SUM(CASE WHEN txn_type = 'BUYIN'  THEN amount ELSE 0 END) AS total_buy"),
                DB::raw("SUM(CASE WHEN txn_type = 'CASHOUT' THEN amount ELSE 0 END) AS total_cashout")
            )
            ->where('gameday', $gameday)
            ->whereNotNull('tab_id')
            ->where('tab_id', '!=', '')
            ->groupBy('table_id', 'tab_id')
            ->get()
            ->groupBy('table_id');

        // 4. Build the structured result
        $result = collect();

        foreach ($gameTables as $table) {
            $float = $floatsByTable->get($table->id);

            // Only include tables that had activity on this gameday
            $tabRows = $ledgerAggregates->get($table->id, collect())->values();

            // Skip tables with no float and no ledger activity on this day
            if (is_null($float) && $tabRows->isEmpty()) {
                continue;
            }

            $result->push((object) [
                'table_id'       => $table->id,
                'table_name'     => $table->table_name,
                'game_type'      => $table->gameType->name ?? 'N/A',
                'opening_float'  => $float ? $float->total_opening_float : null,
                'closing_float'  => $float ? $float->total_closing_float : null,
                'float_status'   => $float
                    ? ($float->has_open_session ? 'Open' : 'Closed')
                    : 'No Float',
                'tabs'           => $tabRows,
            ]);
        }

        return $result;
    }

    private function getLedgerReport($from, $to, $tableId, $tabId = null)
    {
        $query = TableLedger::with('gameTable')
            ->whereBetween('gameday', [$from, $to]);

        if ($tableId) {
            $query->where('table_id', $tableId);

            if ($tabId) {
                $query->where('tab_id', $tabId);
            }
        }

        return $query->get();
    }

    private function getTableReport()
    {
        return GameTable::with('gameType')->get();
    }

    private function exportReport($type, $data, $gameday = null)
    {
        $filename = "report_{$type}_" . now()->format('Ymd_His') . ".csv";

        $headers = match ($type) {
            'float'   => ['Float ID', 'Table', 'Gameday', 'Opening Float', 'Closing Float', 'Opened At', 'Closed At'],
            'gameday' => ['Table ID', 'Table Name', 'Game Type', 'Opening Float', 'Closing Float', 'Float Status',
                          'Tab ID', 'Total Fills', 'Total Credits', 'Total Buy-In', 'Total Cash-Out'],
            'ledger'  => ['Txn ID', 'Table', 'Tab ID', 'Type', 'Amount', 'Float Balance', 'Gameday', 'Reference', 'At'],
            'table'   => ['ID', 'Name', 'Game Type', 'MAC Address', 'Status'],
            default   => [],
        };

        $callback = function () use ($type, $data, $headers, $gameday) {
            $file = fopen('php://output', 'w');

            if ($type === 'gameday') {
                fputcsv($file, ['Gaming Day Report — Date: ' . $gameday]);
                fputcsv($file, []);
            }

            fputcsv($file, $headers);

            if ($type === 'gameday') {
                foreach ($data as $block) {
                    if ($block->tabs->isEmpty()) {
                        fputcsv($file, [
                            $block->table_id,
                            $block->table_name,
                            $block->game_type,
                            $block->opening_float ?? 'N/A',
                            $block->closing_float ?? 'Open',
                            $block->float_status,
                            '— No Tab Activity —', '', '', '', '',
                        ]);
                    } else {
                        foreach ($block->tabs as $tab) {
                            fputcsv($file, [
                                $block->table_id,
                                $block->table_name,
                                $block->game_type,
                                $block->opening_float ?? 'N/A',
                                $block->closing_float ?? 'Open',
                                $block->float_status,
                                $tab->tab_id,
                                number_format((float) $tab->total_fills, 2),
                                number_format((float) $tab->total_credits, 2),
                                number_format((float) $tab->total_buy, 2),
                                number_format((float) $tab->total_cashout, 2),
                            ]);
                        }
                    }
                    fputcsv($file, []); 
                }
            } else {
                foreach ($data as $row) {
                    $line = match ($type) {
                        'float' => [
                            $row->float_id,
                            $row->gameTable->table_name ?? 'N/A',
                            $row->gameday->format('Y-m-d'),
                            $row->float_open,
                            $row->float_close ?? 'Open',
                            $row->opened_at,
                            $row->closed_at ?? 'N/A',
                        ],
                        'ledger' => [
                            $row->txn_id,
                            $row->gameTable->table_name ?? 'N/A',
                            $row->tab_id ?? '—',
                            $row->txn_type,
                            $row->amount,
                            $row->float_balance,
                            $row->gameday->format('Y-m-d'),
                            $row->reference,
                            $row->created_at,
                        ],
                        'table' => [
                            $row->id,
                            $row->table_name,
                            $row->gameType->name ?? 'N/A',
                            $row->active_mac ?? 'N/A',
                            $row->status ? 'Active' : 'Inactive',
                        ],
                        default => [],
                    };
                    fputcsv($file, $line);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
