<x-app-layout>
    <div class="content-wrapper p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h5 class="text-warning mb-0">Dynamic Reports</h5>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-printer me-1"></i>Print PDF
                </button>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel (CSV)
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card bg-black border-warning mb-4 no-print">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="text-light small">Report Type</label>
                        <select name="type" class="form-select bg-black text-white border-secondary">
                            <option value="ledger" {{ $reportType == 'ledger' ? 'selected' : '' }}>Ledger Report</option>
                            <option value="float" {{ $reportType == 'float' ? 'selected' : '' }}>Table Float Report</option>
                            <option value="gameday" {{ $reportType == 'gameday' ? 'selected' : '' }}>Gaming Day Report</option>
                            <option value="table" {{ $reportType == 'table' ? 'selected' : '' }}>Table Info Report</option>
                        </select>
                    </div>

                    @if($reportType != 'table')
                    <div class="col-md-2">
                        <label class="text-light small">From Date</label>
                        <input type="date" name="from_date" class="form-control bg-black text-white border-secondary" value="{{ $fromDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="text-light small">To Date</label>
                        <input type="date" name="to_date" class="form-control bg-black text-white border-secondary" value="{{ $toDate }}">
                    </div>
                    @endif

                    @if($reportType == 'ledger' || $reportType == 'float')
                    <div class="col-md-3">
                        <label class="text-light small">Table (Optional)</label>
                        <select name="table_id" class="form-select bg-black text-white border-secondary">
                            <option value="">-- All Tables --</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" {{ $tableId == $table->id ? 'selected' : '' }}>
                                    {{ $table->table_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100">Generate</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Report Content --}}
        <div class="card bg-black border-secondary report-container">
            <div class="card-body">
                <div class="text-center mb-4 only-print">
                    <h4 class="text-warning">Report: {{ ucfirst($reportType) }}</h4>
                    <p class="text-light small">
                        Period: {{ $fromDate }} to {{ $toDate }}
                        @if($tableId) | Table: {{ $tables->find($tableId)->table_name }} @endif
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle border-secondary">
                        <thead class="text-warning border-secondary">
                            <tr>
                                @if($reportType == 'ledger')
                                    <th>ID</th>
                                    <th>Table</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Float Balance</th>
                                    <th>Gameday</th>
                                    <th>Reference</th>
                                    <th>At</th>
                                @elseif($reportType == 'float')
                                    <th>ID</th>
                                    <th>Table</th>
                                    <th>Gameday</th>
                                    <th>Open</th>
                                    <th>Close</th>
                                    <th>Opened At</th>
                                    <th>Closed At</th>
                                @elseif($reportType == 'gameday')
                                    <th>Date</th>
                                    <th>Started At</th>
                                    <th>Ended At</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                @elseif($reportType == 'table')
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Game Type</th>
                                    <th>MAC Address</th>
                                    <th>Status</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $row)
                                <tr>
                                    @if($reportType == 'ledger')
                                        <td>{{ $row->txn_id }}</td>
                                        <td>{{ $row->gameTable->table_name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($row->txn_type) {
                                                    'FILL', 'BUYIN', 'PAYOUT' => 'bg-success',
                                                    'CREDIT', 'CASHOUT', 'DROP' => 'bg-danger',
                                                    'VOID' => 'bg-secondary',
                                                    default => 'bg-info'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $row->txn_type }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ number_format($row->amount, 2) }}</td>
                                        <td class="text-warning">{{ number_format($row->float_balance, 2) }}</td>
                                        <td>{{ $row->gameday->format('Y-m-d') }}</td>
                                        <td class="small">{{ $row->reference ?? '—' }}</td>
                                        <td class="small">{{ $row->created_at->format('Y-m-d H:i') }}</td>
                                    @elseif($reportType == 'float')
                                        <td>{{ $row->float_id }}</td>
                                        <td>{{ $row->gameTable->table_name ?? 'N/A' }}</td>
                                        <td>{{ $row->gameday->format('Y-m-d') }}</td>
                                        <td>{{ number_format($row->float_open, 2) }}</td>
                                        <td class="{{ $row->float_close ? '' : 'text-muted italic' }}">
                                            {{ $row->float_close ? number_format($row->float_close, 2) : 'Open' }}
                                        </td>
                                        <td class="small">{{ $row->opened_at->format('H:i') }}</td>
                                        <td class="small">{{ $row->closed_at ? $row->closed_at->format('H:i') : '—' }}</td>
                                    @elseif($reportType == 'gameday')
                                        <td>{{ $row->gaming_date }}</td>
                                        <td class="small">{{ $row->started_at->format('Y-m-d H:i') }}</td>
                                        <td class="small">{{ $row->ended_at ? $row->ended_at->format('Y-m-d H:i') : '—' }}</td>
                                        <td>{{ $row->duration_hours ?? '—' }} hrs</td>
                                        <td>
                                            @if($row->is_closed)
                                                <span class="badge bg-secondary">Closed</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                    @elseif($reportType == 'table')
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->table_name }}</td>
                                        <td>{{ $row->gameType->name ?? 'N/A' }}</td>
                                        <td class="small font-monospace">{{ $row->active_mac ?? '—' }}</td>
                                        <td>
                                            @if($row->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No data found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            .content-wrapper { padding: 0 !important; }
            .card { border: none !important; }
            .only-print { display: block !important; }
            body { background: white !important; color: black !important; }
            .table { color: black !important; }
            .table-dark { --bs-table-bg: transparent !important; }
            .text-warning { color: #856404 !important; }
        }
        .only-print { display: none; }
    </style>
</x-app-layout>
