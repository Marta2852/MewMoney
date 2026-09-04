@push('styles')
    @vite('resources/css/dashboard.css')
@endpush

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
                    €0.00
                </div>
            </div>

            <div class="dashboard-card">
                <p class="dashboard-card-title">Expenses This Month</p>

                <div class="dashboard-card-amount">
                    €0.00
                </div>
            </div>

        </div>

    </main>

</x-app-layout>