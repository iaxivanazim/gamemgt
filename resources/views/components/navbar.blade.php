<nav class="navbar navbar-dark bg-black px-4">
    <button class="btn btn-outline-warning" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>


    <!-- Navigation Items -->

    <!-- Left Navigation Links -->
    <!-- <ul class="navbar-nav me-auto">
        <li class="nav-item">
            <a class="nav-link text-white" href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
    </ul> -->

    <!-- Right User Dropdown -->
    <div class="d-flex align-items-center">
        <div class="btn-group" role="group">
            <button type="button"
                class="btn btn-sm btn-outline-warning dropdown-toggle"
                data-bs-toggle="dropdown"
                data-bs-auto-close="true"
                aria-expanded="false">
                <span >
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

</nav>