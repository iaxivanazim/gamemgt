<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameDay;
use App\Models\TableFloat;
use App\Models\TableLedger;
use App\Models\GameTable;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('type', 'ledger');
        $fromDate = $request->input('from_date', now()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $tableId = $request->input('table_id');

        $tables = GameTable::where('status', 1)->get();
        $data = $this->getReportData($reportType, $fromDate, $toDate, $tableId);

        if ($request->has('export')) {
            return $this->exportReport($reportType, $data);
        }

        return view('reports.index', compact('tables', 'data', 'reportType', 'fromDate', 'toDate', 'tableId'));
    }

    private function getReportData($type, $from, $to, $tableId = null)
    {
        return match ($type) {
            'float' => $this->getFloatReport($from, $to, $tableId),
            'gameday' => $this->getGameDayReport($from, $to),
            'ledger' => $this->getLedgerReport($from, $to, $tableId),
            'table' => $this->getTableReport(),
            default => collect(),
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

    private function getGameDayReport($from, $to)
    {
        return GameDay::whereBetween('gaming_date', [$from, $to])->get();
    }

    private function getLedgerReport($from, $to, $tableId)
    {
        $query = TableLedger::with('gameTable')
            ->whereBetween('gameday', [$from, $to]);

        if ($tableId) {
            $query->where('table_id', $tableId);
        }

        return $query->get();
    }

    private function getTableReport()
    {
        return GameTable::with('gameType')->get();
    }

    private function exportReport($type, $data)
    {
        $filename = "report_{$type}_" . now()->format('Ymd_His') . ".csv";
        $handle = fopen('php://output', 'w');

        // Headers
        $headers = match ($type) {
            'float' => ['ID', 'Table', 'Gameday', 'Open', 'Close', 'Opened At', 'Closed At'],
            'gameday' => ['Date', 'Started At', 'Ended At', 'Duration', 'Status'],
            'ledger' => ['ID', 'Table', 'Type', 'Amount', 'Float Balance', 'Gameday', 'Reference', 'At'],
            'table' => ['ID', 'Name', 'Game Type', 'MAC Address', 'Status'],
            default => [],
        };

        $callback = function() use ($type, $data, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

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
                    'gameday' => [
                        $row->gaming_date,
                        $row->started_at,
                        $row->ended_at ?? 'N/A',
                        $row->duration_hours ?? 'N/A',
                        $row->is_closed ? 'Closed' : 'Active',
                    ],
                    'ledger' => [
                        $row->txn_id,
                        $row->gameTable->table_name ?? 'N/A',
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
            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }
}
