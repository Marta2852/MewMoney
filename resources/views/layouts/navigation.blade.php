
<nav class="navigation">

    <div class="nav-container">

        <a href="{{ route('dashboard') }}" class="mewmoney-logo">
            <img src="{{ asset('images/Mewmoney.png') }}" alt="MewMoney">
            MewMoney
        </a>

        <div class="nav-links">

            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('transactions') }}"
               class="{{ request()->routeIs('transactions') ? 'active' : '' }}">
                Transactions
            </a>

            <a href="{{ route('accounts') }}"
               class="{{ request()->routeIs('accounts') ? 'active' : '' }}">
                Accounts
            </a>

            <a href="{{ route('savings') }}"
               class="{{ request()->routeIs('savings') ? 'active' : '' }}">
                Savings
            </a>

            @if (Auth::user()->is_admin)
                <a href="{{ route('admin.index') }}"
                   class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    Admin
                </a>
            @endif

        </div>

        <div class="nav-user">

            <span>{{ Auth::user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit">
                    Log Out
                </button>
            </form>

        </div>

    </div>

</nav>

