@push('styles')
    @vite('resources/css/accounts.css')
@endpush

<x-app-layout>

    <main class="accounts">

        <h1>Accounts</h1>

        <p class="accounts-subtitle">
            Manage your accounts and balances.
        </p>

        <div class="account-form">

            <h2>Add Account</h2>

            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf

                <input
                    type="text"
                    name="account_name"
                    placeholder="Account name"
                    required
                >

                <input
                    type="number"
                    name="balance"
                    step="0.01"
                    placeholder="Starting balance"
                    required
                >

                <button type="submit">
                    Add Account
                </button>

            </form>

        </div>

        <div class="account-list">

            @forelse ($accounts as $account)

                <div class="account-card">

                    <div class="account-name">
                        {{ $account->account_name }}
                    </div>

                    <div class="account-balance">
                        €{{ number_format($account->balance, 2) }}
                    </div>

                </div>

            @empty

                <p>You don't have any accounts yet.</p>

            @endforelse

        </div>

    </main>

</x-app-layout>