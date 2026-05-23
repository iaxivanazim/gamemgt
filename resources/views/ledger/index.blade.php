<x-app-layout>
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="text-warning mb-0"><i class="bi bi-wallet me-2"></i>Table Ledger</h4>
                <small class="text-secondary">Financial transactions and float tracking</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('ledger.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Reset Filters
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card bg-black border-secondary mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('ledger.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label text-secondary small mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm bg-black text-white border-secondary" 
                                   placeholder="Txn ID, Tab ID..." value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-secondary small mb-1">Type</label>
                            <select name="txn_type" class="form-select form-select-sm bg-black text-white border-secondary">
                                <option value="">All Types</option>
                                @foreach($txnTypes as $type)
                                    <option value="{{ $type }}" {{ request('txn_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-secondary small mb-1">Table</label>
                            <select name="table_id" class="form-select form-select-sm bg-black text-white border-secondary">
                                <option value="">All Tables</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>{{ $table->table_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-secondary small mb-1">Game Day</label>
                            <input type="date" name="gameday" class="form-control form-control-sm bg-black text-white border-secondary" 
                                   value="{{ request('gameday') }}">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label text-secondary small mb-1">Status</label>
                            <select name="processed" class="form-select form-select-sm bg-black text-white border-secondary">
                                <option value="">All</option>
                                <option value="0" {{ request('processed') === '0' ? 'selected' : '' }}>New</option>
                                <option value="2" {{ request('processed') === '2' ? 'selected' : '' }}>Claimed</option>
                                <option value="1" {{ request('processed') === '1' ? 'selected' : '' }}>Complete</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label text-secondary small mb-1">Sort</label>
                            <select name="sort" class="form-select form-select-sm bg-black text-white border-secondary">
                                <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>DESC</option>
                                <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>ASC</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary Strip (Optional, based on filtered data) --}}
        @php
            $totalAmount = $ledgers->sum('amount');
        @endphp
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="card bg-black border-secondary text-center py-2">
                    <div class="text-secondary small">Total Entries</div>
                    <div class="text-white fw-bold">{{ $ledgers->total() }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-black border-secondary text-center py-2">
                    <div class="text-secondary small">Page Total Amount</div>
                    <div class="text-warning fw-bold">{{ number_format($ledgers->sum('amount'), 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Results Table --}}
        <div class="card bg-black border-secondary">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-sm mb-0 align-middle" style="font-size:.85rem">
                    <thead class="border-secondary">
                        <tr class="text-secondary text-uppercase" style="font-size:.75rem">
                            <th>TXN ID</th>
                            <th>Game Day</th>
                            <th>Table</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Float Bal</th>
                            <th class="text-end">Tab Bal</th>
                            <th>Tab ID</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgers as $ledger)
                            <tr>
                                <td class="text-secondary">#{{ $ledger->txn_id }}</td>
                                <td class="text-white">{{ $ledger->gameday->format('d/m/Y') }}</td>
                                <td class="text-warning fw-bold">{{ $ledger->gameTable->table_name }}</td>
                                <td>
                                    @php
                                        $typeColor = match($ledger->txn_type) {
                                            'FILL', 'BUYIN', 'CREDIT' => 'success',
                                            'DROP', 'CASHOUT' => 'danger',
                                            'PAYOUT' => 'primary',
                                            'VOID' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $typeColor }} {{ $ledger->txn_type == 'VOID' ? 'text-dark' : '' }}" style="font-size:.7rem">
                                        {{ $ledger->txn_type }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold {{ $ledger->amount < 0 ? 'text-danger' : 'text-white' }}">
                                    {{ number_format($ledger->amount, 2) }}
                                </td>
                                <td class="text-end text-info">{{ number_format($ledger->float_balance, 2) }}</td>
                                <td class="text-end text-warning">{{ number_format($ledger->tab_balance, 2) }}</td>
                                <td>
                                    @if($ledger->tab_id)
                                        <span class="badge bg-secondary">{{ $ledger->tab_id }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusLabel = match($ledger->processed) {
                                            0 => ['label' => 'NEW', 'class' => 'text-info'],
                                            2 => ['label' => 'CLAIMED', 'class' => 'text-warning'],
                                            1 => ['label' => 'COMPLETE', 'class' => 'text-success'],
                                            default => ['label' => 'UNKNOWN', 'class' => 'text-secondary']
                                        };
                                    @endphp
                                    <span class="small fw-bold {{ $statusLabel['class'] }}">{{ $statusLabel['label'] }}</span>
                                </td>
                                <td class="text-secondary small text-truncate" style="max-width: 120px;">
                                    {{ $ledger->reference ?: '—' }}
                                </td>
                                <td class="text-secondary small">
                                    {{ $ledger->created_at->format('H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-secondary">
                                    <i class="bi bi-inbox" style="font-size:2rem"></i>
                                    <p class="mt-2">No ledger entries found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($ledgers->hasPages())
                <div class="card-footer bg-black border-secondary d-flex justify-content-between align-items-center py-2">
                    <small class="text-secondary">
                        Showing {{ $ledgers->firstItem() }}–{{ $ledgers->lastItem() }} of {{ $ledgers->total() }}
                    </small>
                    <div class="d-flex gap-1">
                        {{ $ledgers->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
