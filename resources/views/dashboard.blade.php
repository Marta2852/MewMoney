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

        <div class="dashboard-chart">
        <h2>Expenses by Category</h2>

        <div class="chart-container">
            <canvas id="expensesChart"></canvas>
        </div>
    </div>

    </main>

</x-app-layout>