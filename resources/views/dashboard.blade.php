@push('styles')
    @vite(['resources/css/dashboard.css',
            'resources/js/dashboard.js'])
@endpush

<script>
    window.expenseCategoryLabels = @json($expensesByCategory->keys());
    window.expenseCategoryData = @json($expensesByCategory->values());
</script>

<x-app-layout>

    <main class="dashboard">

        <h1>Dashboard</h1>

        <p class="dashboard-subtitle">
            Welcome back, {{ Auth::user()->name }}!
        </p>

        <div class="dashboard-cards">

            <div class="dashboard-card">
                <p class="dashboard-card-title">Total Balance</p>

                <div class="dashboard-card-amount">
                    €{{ number_format($totalBalance, 2) }}
                </div>
            </div>

            <div class="dashboard-card">
                <p class="dashboard-card-title">Income This Month</p>

                <div class="dashboard-card-amount">
                    €{{ number_format($incomeThisMonth, 2) }}
                </div>
            </div>

            <div class="dashboard-card">
                <p class="dashboard-card-title">Expenses This Month</p>

                <div class="dashboard-card-amount">
                    €{{ number_format($expensesThisMonth, 2) }}
                </div>
            </div>

            <div class="dashboard-card">
                <p class="dashboard-card-title">Saved This Month</p>

                <div class="dashboard-card-amount">
                    €{{ number_format($savedThisMonth, 2) }}
                </div>
            </div>

        </div>

        <div class="dashboard-middle">

        <div class="dashboard-chart">
            <h2>Expenses by Category</h2>

            <div class="chart-container">
                <canvas id="expensesChart"></canvas>
            </div>
        </div>

        <div class="dashboard-savings">
            <h2>Your Savings Goals</h2>

            <div class="savings-goals-content">
            @if ($activeGoals->count() > 0)
                <h3 class="goals-section-title">
                    Active Goals
                </h3>

                <div class="active-goals-list">
                    @foreach ($activeGoals as $goal)
                        @php
                            $progress = $goal->target_amount > 0
                                ? ($goal->current_amount / $goal->target_amount) * 100
                                : 0;

                            $progress = min($progress, 100);
                        @endphp

                        <div class="savings-card">
                            <div class="savings-card-header">
                                <strong>
                                    {{ $goal->goal_name }}
                                </strong>

                                <span>
                                    €{{ number_format($goal->current_amount, 2) }}
                                    /
                                    €{{ number_format($goal->target_amount, 2) }}
                                </span>
                            </div>

                            <div class="progress-bar">
                                <div
                                    class="progress"
                                    style="width: {{ $progress }}%">
                                </div>
                            </div>

                            <p>
                                {{ number_format($progress, 0) }}% saved
                            </p>

                            @if ($goal->target_date)
                                <p>
                                    Target date: {{ $goal->target_date }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($activeGoals->count() === 0 && $completedGoals->count() === 0)
                <p class="no-savings">You don't have any savings goals yet.</p>
            @endif

            @if ($completedGoals->count() > 0)
                <button
                    type="button"
                    class="completed-toggle"
                    id="completedToggle"
                    aria-expanded="false"
                    aria-controls="completedGoals"
                >
                    <span>
                        Completed ({{ $completedGoals->count() }})
                    </span>

                    <span id="completedArrow" aria-hidden="true">▼</span>
                </button>

                <div
                    class="completed-goals"
                    id="completedGoals"
                    hidden
                >
                    @foreach ($completedGoals as $goal)
                        <div class="savings-card completed-card">
                            <div class="savings-card-header">
                                <strong>{{ $goal->goal_name }}</strong>
                                <span>Completed</span>
                            </div>

                            <div class="progress-bar">
                                <div class="progress" style="width: 100%"></div>
                            </div>

                            <p>
                                €{{ number_format($goal->current_amount, 2) }}
                                /
                                €{{ number_format($goal->target_amount, 2) }}
                                - 100%
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
            </div>
        </div>

    </div>

    <div class="dashboard-transactions">

    <h2>Recent Transactions</h2>

    @forelse ($recentTransactions as $transaction)

        <div class="recent-transaction">

            <div>
                <strong>{{ $transaction->description ?: 'No description' }}</strong>

                <p>
                    {{ $transaction->transaction_date }}
                    @if ($transaction->category)
                        · {{ $transaction->category->category_name }}
                    @endif
                </p>
            </div>

            <div>
                @if ($transaction->transaction_type === 'income')
                    <span>+€{{ number_format($transaction->amount, 2) }}</span>

                @elseif ($transaction->transaction_type === 'expense')
                    <span>-€{{ number_format($transaction->amount, 2) }}</span>

                @else
                    <span>€{{ number_format($transaction->amount, 2) }}</span>
                @endif
            </div>

        </div>

        @empty
            <p>No transactions yet.</p>
        @endforelse
    </div>

    </main>

</x-app-layout>