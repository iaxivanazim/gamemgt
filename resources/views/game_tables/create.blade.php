<x-app-layout>
    <div class="container">

        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Create Game Table</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('game_tables.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Table Name</label>
                            <input type="text" name="table_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Game Type</label>
                            <select name="game_type_id" class="form-control" required>
                                <option value="">Select Game Type</option>
                                @foreach($gameTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Active MAC Address</label>
                            <input type="text" name="active_mac" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Float</label>
                            <input type="number" name="float" step="0.01" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Theme</label>
                            <select name="theme_id" class="form-control">
                                <option value="">Select Theme</option>
                                @foreach($themes as $theme)
                                <option value="{{ $theme->id }}">
                                    {{ $theme->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-end">
                        <a href="{{ route('game_tables.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Table
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</x-app-layout>