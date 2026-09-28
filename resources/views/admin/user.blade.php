@vite(['resources/css/admin.css'])
<x-app-layout>

    <main class="admin">

        <h1>User Details</h1>

        <p class="admin-subtitle">
            View information and activity for this user.
        </p>

        <div class="user-details">

            <div class="user-info-card">

                <h2>{{ $user->name }}</h2>

                <div class="user-info">

                    <div>
                        <span>Email</span>
                        <strong>{{ $user->email }}</strong>
                    </div>

                    <div>
                        <span>Role</span>

                        <strong>
                            {{ $user->is_admin ? 'Admin' : 'User' }}
                        </strong>

                        @if ($user->id !== auth()->id())

                            <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" style="margin-top: 10px;">
                                @csrf
                                @method('PATCH')

                                <button type="submit">
                                    {{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}
                                </button>
                            </form>

                        @endif
                    </div>

                    <div>
                        <span>Registered</span>
                        <strong>
                            {{ $user->created_at->format('d.m.Y') }}
                        </strong>
                    </div>

                </div>

            </div>

            <div class="admin-cards">

                <div class="admin-card">
                    <p>Accounts</p>
                    <strong>{{ $accountsCount }}</strong>
                    <span>Created accounts</span>
                </div>

                <div class="admin-card">
                    <p>Transactions</p>
                    <strong>{{ $transactionsCount }}</strong>
                    <span>Total transactions</span>
                </div>

                <div class="admin-card">
                    <p>Savings Goals</p>
                    <strong>{{ $savingsGoalsCount }}</strong>
                    <span>Created goals</span>
                </div>

            </div>

            <form
                method="POST"
                action="{{ route('admin.users.destroy', $user) }}"
                onsubmit="return confirm('Are you sure you want to delete this user? This will permanently delete their accounts, transactions, categories and savings goals.')"
            >
                @csrf
                @method('DELETE')

                <button type="submit" class="delete-user-button">
                    Delete User
                </button>
            </form>

            <a href="{{ route('admin.index') }}" class="back-link">
                ← Back to User Management
            </a>

        </div>

    </main>

</x-app-layout>