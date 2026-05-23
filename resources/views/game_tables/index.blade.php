<x-app-layout>
    <div class="content-wrapper p-4">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h5 class="text-warning mb-0">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>Game Tables
            </h5>
            
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Search & Sort --}}
                <form method="GET" action="{{ route('game_tables.index') }}" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-black border-secondary text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search"
                            class="form-control bg-black text-white border-secondary"
                            placeholder="Search table..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('game_tables.index', ['status' => $status]) }}" class="btn btn-outline-secondary border-secondary">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <select name="sort_by" class="form-select form-select-sm bg-black text-white border-secondary" 
                                onchange="this.form.submit()" style="width: 130px;">
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Newest</option>
                            <option value="table_name" {{ request('sort_by') == 'table_name' ? 'selected' : '' }}>Name</option>
                            <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                        </select>

                        <select name="order" class="form-select form-select-sm bg-black text-white border-secondary" 
                                onchange="this.form.submit()" style="width: 85px;">
                            <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>DESC</option>
                            <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>ASC</option>
                        </select>
                    </div>
                </form>

                <div class="vr bg-secondary mx-1 d-none d-lg-block" style="height: 24px; opacity: 0.4;"></div>

                {{-- Status Filter --}}
                <div class="btn-group" role="group">
                    <a href="{{ route('game_tables.index', ['status' => 1, 'search' => request('search'), 'sort_by' => request('sort_by'), 'order' => request('order'), 'mac_filter' => $macFilter]) }}"
                        class="btn btn-sm {{ $status == 1 ? 'btn-warning' : 'btn-outline-warning' }}">
                        Active
                    </a>
                    <a href="{{ route('game_tables.index', ['status' => 0, 'search' => request('search'), 'sort_by' => request('sort_by'), 'order' => request('order'), 'mac_filter' => $macFilter]) }}"
                        class="btn btn-sm {{ $status == 0 ? 'btn-warning' : 'btn-outline-warning' }}">
                        Inactive
                    </a>
                </div>

                <div class="vr bg-secondary mx-1 d-none d-lg-block" style="height: 24px; opacity: 0.4;"></div>

                {{-- MAC Filter --}}
                <div class="btn-group" role="group">
                    <a href="{{ route('game_tables.index', ['mac_filter' => 'all', 'status' => $status, 'search' => request('search'), 'sort_by' => request('sort_by'), 'order' => request('order')]) }}"
                        class="btn btn-sm {{ $macFilter == 'all' ? 'btn-warning' : 'btn-outline-warning' }}">
                        All
                    </a>
                    <a href="{{ route('game_tables.index', ['mac_filter' => 'bound', 'status' => $status, 'search' => request('search'), 'sort_by' => request('sort_by'), 'order' => request('order')]) }}"
                        class="btn btn-sm {{ $macFilter == 'bound' ? 'btn-warning' : 'btn-outline-warning' }}">
                        Bound
                    </a>
                    <a href="{{ route('game_tables.index', ['mac_filter' => 'unbound', 'status' => $status, 'search' => request('search'), 'sort_by' => request('sort_by'), 'order' => request('order')]) }}"
                        class="btn btn-sm {{ $macFilter == 'unbound' ? 'btn-warning' : 'btn-outline-warning' }}">
                        Unbound
                    </a>
                </div>

                <a href="{{ route('game_tables.create') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-plus-lg me-1"></i>New Table
                </a>
            </div>
        </div>

        {{-- Session Flash --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 mb-4"
                style="background:#0f2e1a; color:#6fcf97;">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Table Grid --}}
        @forelse($tables as $table)
            @php
                $config = $table->config;
                $preset = $config?->preset;
                $chip = $preset?->chipPreset;
                $colors = ['red', 'blue', 'green', 'purple', 'gold'];
                $feltColor = $table->felt_color ?? '#006400';

                $gameBadgeColor = match ($table->gameType?->code ?? '') {
                    'BAC' => '#c9a227',
                    'BJ' => '#0047ab',
                    'DT' => '#b30000',
                    'AB' => '#006400',
                    '3CP' => '#5a007a',
                    'MF' => '#1a6b6b',
                    'CW' => '#8b0000',
                    default => '#444',
                };
            @endphp

            <div class="card mb-4 border-0 overflow-hidden"
                style="background:#111; box-shadow: 0 0 25px rgba(255,215,0,0.07); border-left: 4px solid {{ $gameBadgeColor }} !important;">
                <div class="card-body p-0">
                    <div class="row g-0">

                        {{-- ── LEFT: Felt Visual ── --}}
                        <div class="col-md-1 d-flex align-items-center justify-content-center"
                            style="background:{{ $feltColor }}22; border-right:1px solid #222; min-height:130px;">
                            <div class="text-center px-2">
                                <div
                                    style="width:40px; height:40px; border-radius:50%; background:{{ $feltColor }};
                                        border:3px dashed white; margin:auto;
                                        box-shadow: 0 0 12px {{ $feltColor }}88;">

                                    <span class="text-white fw-bold" style="font-size:10px;">{{ $table->id }}</span>

                                </div>
                                <div class="mt-2" style="font-size:10px; letter-spacing:0.05em; color:#fff;">FELT
                                </div>
                            </div>
                        </div>

                        {{-- ── CENTER: Table Info ── --}}
                        <div class="col-md-6 p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold text-white fs-6">{{ $table->table_name }}</span>
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background:{{ $gameBadgeColor }}33; color:{{ $gameBadgeColor }};
                                         border:1px solid {{ $gameBadgeColor }}; font-size:11px;">
                                    {{ $table->gameType?->name ?? 'N/A' }}
                                </span>
                                @if ($table->status == 1)
                                    <span class="badge rounded-pill"
                                        style="background:#0f2e1a; color:#6fcf97; border:1px solid #6fcf97; font-size:10px;">
                                        ● ACTIVE
                                    </span>
                                @else
                                    <span class="badge rounded-pill"
                                        style="background:#2e1010; color:#eb5757; border:1px solid #eb5757; font-size:10px;">
                                        ● INACTIVE
                                    </span>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-3 mt-2">
                                @if ($table->active_mac)
                                    <span class="text-secondary small">
                                        <i class="bi bi-pc-display me-1"></i>{{ $table->active_mac }}
                                    </span>
                                @endif
                                @php $liveFloat = $table->live_float; @endphp
                                @if (!is_null($liveFloat))
                                    <span class="text-secondary small">
                                        <i class="bi bi-cash-stack me-1"></i>Float:
                                        <span class="text-warning">{{ number_format($liveFloat, 2) }}</span>
                                    </span>
                                @elseif($table->float)
                                    {{-- fallback to reference float if no open session --}}
                                    <span class="text-secondary small">
                                        <i class="bi bi-cash-stack me-1"></i>Float:
                                        <span class="text-secondary">{{ number_format($table->float, 2) }}</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Config Bets --}}
                            @if ($preset)
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    @php
                                        $range = $table->active_bet_range;
                                    @endphp
                                    @if (!empty($range))
                                        <span class="text-secondary small">
                                            <i class="bi bi-coin me-1"></i>
                                            Min-Max: <span
                                                class="text-warning">{{ number_format($range['min'], 2) }}</span>
                                            <span class="text-secondary">–</span>
                                            <span class="text-warning">{{ number_format($range['max'], 2) }}</span>
                                            @if (count(explode('|', $table->config?->preset?->min_bet ?? '')) > 1)
                                                <span class="badge bg-secondary ms-1"
                                                    style="font-size:0.65rem;">L{{ $table->bet_index }}</span>
                                            @endif
                                        </span>
                                    @endif
                                    @if (isset($preset->side_min_bet))
                                        <span class="small" style="color:#aaa;">
                                            Side: <span
                                                class="text-warning fw-bold">{{ number_format($preset->side_min_bet, 2) }}
                                                – {{ number_format($preset->side_max_bet, 2) }}</span>
                                        </span>
                                    @endif
                                    @if (isset($preset->tie_min))
                                        <span class="small" style="color:#aaa;">
                                            Tie: <span
                                                class="text-warning fw-bold">{{ number_format($preset->tie_min, 2) }} –
                                                {{ number_format($preset->tie_max, 2) }}</span>
                                        </span>
                                    @endif
                                    @if (isset($preset->commission))
                                        <span class="small" style="color:#aaa;">
                                            Commission:
                                            <span
                                                class="{{ $preset->commission ? 'text-warning' : 'text-secondary' }} fw-bold">
                                                {{ $preset->commission ? 'Enabled (0.95x)' : 'Disabled (1x)' }}
                                            </span>
                                        </span>
                                    @endif
                                    @if (isset($preset->surrender))
                                        <span class="small" style="color:#aaa;">
                                            Surrender:
                                            <span class="text-warning fw-bold">
                                                @if ($preset->surrender == '0')
                                                    No Surrender
                                                @elseif($preset->surrender == '1')
                                                    Surrender on any card
                                                @else
                                                    Surrender on any card except Ace
                                                @endif
                                            </span>
                                        </span>
                                    @endif
                                    @if (isset($preset->insurance))
                                        <span class="small" style="color:#aaa;">
                                            Insurance: <span
                                                class="text-warning fw-bold">{{ $preset->insurance ? 'Enabled' : 'Disabled' }}</span>
                                        </span>
                                    @endif
                                    @if (isset($preset->burn_card))
                                        <span class="small" style="color:#aaa;">
                                            Burn Card: <span
                                                class="text-warning fw-bold">{{ $preset->burn_card }}</span>
                                        </span>
                                    @endif
                                    @if (isset($preset->split_type))
                                        <span class="small" style="color:#aaa;">
                                            Split Type: <span
                                                class="text-warning fw-bold">{{ $preset->split_type === 'same_rank' ? 'Same Rank' : 'Same Value' }}</span>
                                        </span>
                                    @endif
                                    @if (isset($preset->soft17rule))
                                        <span class="small" style="color:#aaa;">
                                            Soft 17 Rule: <span
                                                class="text-warning fw-bold">{{ $preset->soft17rule === 's17' ? 'Stand Soft 17' : 'Hit Soft 17' }}</span>
                                        </span>
                                    @endif

                                </div>

                                {{-- Toggle Badges --}}
                                {{-- <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach ([
        'enable_pairbets' => 'Pair Bets',
        'enable_lucky6' => 'Lucky 6',
        'enable_super_andar' => 'Super Andar',
        'enable_super_bahar' => 'Super Bahar',
        'enable_777_charlie' => '777 Charlie',
    ] as $field => $label)
                            @if (isset($preset->$field))
                            <span class="badge" style="{{ $preset->$field
                                                        ? 'background:#0f2e1a; color:#6fcf97; border:1px solid #6fcf97;'
                                                        : 'background:#1e1e1e; color:#555; border:1px solid #333;' }}
                        font-size:10px;">
                        {{ $preset->$field ? '✓' : '✕' }} {{ $label }}
                        </span>
                        @endif
                        @endforeach
                    </div> --}}
                            @else
                                <div class="text-muted small mt-2 fst-italic">No configuration assigned</div>
                            @endif
                        </div>

                        {{-- ── RIGHT: Chip Preview ── --}}
                        <div class="col-md-3 d-flex align-items-center justify-content-center p-3"
                            style="border-left:1px solid #222; border-right:1px solid #222;">
                            @if ($chip)
                                <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center">
                                    @foreach ($colors as $i => $color)
                                        @php $val = $chip->{'chip_'.($i+1).'_value'}; @endphp
                                        <div class="text-center">
                                            <div class="casino-chip chip-{{ $color }}"
                                                style="width:44px; height:44px; font-size:10px; border-width:2px;">
                                                <span class="text-white fw-bold"
                                                    style="font-size:10px;">{{ $val }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div
                                        style="width:1px; height:44px; background:linear-gradient(to bottom, transparent, #ffc107, transparent); margin:0 6px;">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-warning" style="font-size:9px; letter-spacing:0.05em;">BASE
                                        </div>
                                        <div class="text-white fw-bold" style="font-size:13px;">
                                            {{ $chip->base_value }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small fst-italic">No chip preset</span>
                            @endif
                        </div>
                        {{-- ── FAR RIGHT: Actions ── --}}
                        <div class="col-md-2 d-flex flex-column align-items-center justify-content-center gap-2 p-3">

                            <a href="{{ route('game_tables.edit', $table->id) }}"
                                class="btn btn-sm btn-outline-warning w-100">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </a>

                            {{-- Unregister MAC --}}
                            @if ($table->active_mac)
                                <form method="POST" action="{{ route('game_tables.unregister-mac', $table->id) }}"
                                    class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100"
                                        onclick="return confirm('Unregister MAC {{ $table->active_mac }} from this table?')">
                                        <i class="bi bi-pc-display me-1"></i>Unregister MAC
                                    </button>
                                </form>
                            @else
                                <span class="btn btn-sm w-100 disabled"
                                    style="border:1px dashed #444; color:#555; font-size:11px;">
                                    <i class="bi bi-pc-display me-1"></i>No MAC Bound
                                </span>
                            @endif

                            {{-- Deactivate / Restore --}}
                            @if ($table->status == 1)
                                <form method="POST" action="{{ route('game_tables.deactivate', $table->id) }}"
                                    class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                        onclick="return confirm('Deactivate this table?')">
                                        <i class="bi bi-pause-circle me-1"></i>Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('game_tables.restore', $table->id) }}"
                                    class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- ── PAYOUT RULES ── --}}
                        <div class="col-md-12 px-4 pb-3" style="border-top:1px solid #1e1e1e;">
                            <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                                <span class="text-warning small fw-bold" style="letter-spacing:0.06em;">
                                    PAYOUT RULES
                                </span>
                                <div
                                    style="flex:1; height:1px; background:linear-gradient(to right, #ffc10733, transparent);">
                                </div>
                                {{-- Active/Total count badge --}}
                                @php
                                    $totalRules = $table->payoutRules->count();
                                    $activeRules = $table->payoutRules->where('is_active', 1)->count();
                                @endphp
                                <span class="badge rounded-pill"
                                    style="background:#1a1a1a; color:#ffc107; border:1px solid #ffc10744; font-size:10px;">
                                    {{ $activeRules }}/{{ $totalRules }} active
                                </span>
                            </div>

                            @if ($table->payoutRules->isEmpty())
                                <span class="text-muted small fst-italic">No payout rules assigned</span>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($table->payoutRules as $tableRule)
                                        @php $rule = $tableRule->payoutRule; @endphp
                                        @if ($rule)
                                            <div class="d-flex flex-column gap-1">
                                                <div class="d-flex align-items-center gap-1 px-2 py-1 rounded"
                                                    style="{{ $tableRule->is_active
                                                        ? 'background:#0f1f0f; border:1px solid #2a5a2a;'
                                                        : 'background:#1a1a1a; border:1px solid #2e2e2e;' }}">

                                                    {{-- Active dot --}}
                                                    <span
                                                        style="width:6px; height:6px; border-radius:50%; flex-shrink:0;
                                 background:{{ $tableRule->is_active ? '#6fcf97' : '#555' }};"></span>

                                                    {{-- Bet name --}}
                                                    <span
                                                        style="font-size:11px; color:{{ $tableRule->is_active ? '#ddd' : '#555' }};">
                                                        {{ $rule->bet_name }}
                                                    </span>

                                                    @if ($rule->bet_position)
                                                        <span
                                                            style="font-size:10px; color:#666;">({{ $rule->bet_position }})</span>
                                                    @endif

                                                    {{-- Jackpot badge --}}
                                                    @if ($rule->is_jackpot)
                                                        <span
                                                            style="font-size:9px; padding:1px 5px; border-radius:8px;
                                     background:#1a1200; color:#ffc107; border:1px solid #ffc10744;">
                                                            JACKPOT
                                                        </span>
                                                    @endif

                                                    {{-- Divider --}}
                                                    <span
                                                        style="width:1px; height:12px; background:#333; margin:0 3px;"></span>

                                                    {{-- Multiplier or seed --}}
                                                    @if ($rule->payout_multiplier)
                                                        <span
                                                            style="font-size:11px; font-weight:bold;
                                     color:{{ $tableRule->is_active ? '#ffc107' : '#444' }};">
                                                            {{ $rule->payout_multiplier }}x
                                                        </span>
                                                    @else
                                                        <span
                                                            style="font-size:11px; font-weight:bold; color:#555;">—</span>
                                                    @endif
                                                </div>

                                                {{-- Seed value pill — only for active jackpot rules with a seed ──────── --}}
                                                @if ($rule->is_jackpot && $tableRule->is_active && $tableRule->seed_value)
                                                    <div class="d-flex align-items-center gap-1 px-2 py-1 rounded"
                                                        style="background:#1a1200; border:1px solid #ffc10733;">
                                                        <i class="bi bi-coin"
                                                            style="color:#ffc107; font-size:10px;"></i>
                                                        <span
                                                            style="font-size:10px; color:#999; letter-spacing:0.04em;">SEED</span>
                                                        <span style="font-size:11px; font-weight:bold; color:#ffc107;">
                                                            {{ number_format($tableRule->seed_value, 2) }}
                                                        </span>
                                                    </div>
                                                @endif

                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>



                    </div>
                </div>
            </div>

        @empty
            <div class="text-center py-5">
                <div style="font-size:48px; opacity:0.2;">🎰</div>
                <div class="text-muted mt-3">No game tables found.</div>
                <a href="{{ route('game_tables.create') }}" class="btn btn-warning btn-sm mt-3">
                    + Create First Table
                </a>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if ($tables->hasPages())
            <div class="d-flex justify-content-center mt-2">
                {{ $tables->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
