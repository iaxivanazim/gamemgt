<x-app-layout>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Game Tables</h4>

            <div class="d-flex gap-2">

                {{-- Add Game Table --}}
                @if(auth()->user()->hasPermission('create-game_tables'))
                <a href="{{ route('game_tables.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Game Table
                </a>
                @endif

                {{-- Add Game Type --}}
                <!-- @if(auth()->user()->hasPermission('create-game_types'))
                <a href="{{ route('game_types.index') }}" class="btn btn-success">
                    <i class="bi bi-controller"></i> Add Game Type
                </a>
                @endif -->

                {{-- Add Theme --}}
                <!-- @if(auth()->user()->hasPermission('create-themes'))
                <a href="{{ route('themes.index') }}" class="btn btn-info text-white">
                    <i class="bi bi-palette"></i> Add Theme
                </a>
                @endif -->

            </div>
        </div>


        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Game Type</th>
                    <th>MAC Address</th>
                    <th>Float</th>
                    <th>Felt Color</th>
                    <th>Status</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tables as $table)
                <tr>
                    <td>{{ $table->table_name }}</td>
                    <td>{{ $table->gameType->name ?? '-' }}</td>
                    <td>{{ $table->active_mac }}</td>
                    <td>{{ number_format($table->float, 2) }}</td>
                    <td>{{ $table->felt_color ?? '-' }} <span class="badge" style="background-color: {{ $table->felt_color ?? '#000000' }};">felt</span></td>
                    <td>
                        @if($table->status)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if(auth()->user()->hasPermission('edit-game_tables'))
                        <a href="{{ route('game_tables.edit', $table->id) }}" class="btn btn-sm btn-warning">
                            Edit
                        </a>
                        @endif

                        @if(auth()->user()->hasPermission('delete-game_tables'))
                        <button onclick="deleteTable({{ $table->id }})" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    {{-- Pagination --}}
        @if($tables->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $tables->links() }}
            </div>
        @endif
    </div>
</x-app-layout>