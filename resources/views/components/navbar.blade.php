<nav class="navbar navbar-dark bg-black py-2 px-3">
    <div class="container-fluid d-flex align-items-center gap-3">
        <button class="btn btn-outline-warning btn-sm d-flex align-items-center justify-content-center" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <!-- <div class="d-flex align-items-center flex-grow-1">
            <ul class="navbar-nav d-flex flex-row align-items-center gap-3 mb-0">
                <li class="nav-item d-flex align-items-center">
                    @if($currentGameDay)
                    <span >
                       <strong>Game Day: {{ $currentGameDay->gaming_date }}</strong>
                    </span>
                    @else
                    <span >
                        No Active Game Day
                    </span>
                    @endif
                </li>

                @if(auth()->user()->hasPermission('game-day-start') && !$currentGameDay)
                <li class="nav-item">
                    <button class="btn btn-sm btn-success px-2 py-1" onclick="startGameDay()">
                        Start Game Day
                    </button>
                </li>
                @endif

                @if(auth()->user()->hasPermission('game-day-close') && $currentGameDay)
                <li class="nav-item">
                    <button class="btn btn-sm btn-danger px-2 py-1" onclick="closeGameDay({{ $currentGameDay->id }})">
                        Close Game Day
                    </button>
                </li>
                @endif

                @if($currentGameDay)
                <li class="nav-item">
                    <span id="gameDayTimer" class="text-light small ms-2"></span>
                </li>
                @endif
            </ul>
        </div> -->

        <div class="d-flex align-items-center">
            <div class="btn-group" role="group">
                <button type="button"
                    class="btn btn-sm btn-outline-warning dropdown-toggle px-3 py-1"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="true"
                    aria-expanded="false">
                    <span class="me-2">
                        {{ auth()->user()->username ?? 'Guest' }}
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark mt-2">
                    <li>
                        <a class="dropdown-item text-white" href="{{ route('profile.edit') }}">
                            {{ __('Profile') }}
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
