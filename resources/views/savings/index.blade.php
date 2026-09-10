@push('styles')
    @vite('resources/css/savings.css')
@endpush

<x-app-layout>

    <main class="savings">

        <div class="savings-header">

            <div>
                <h1>Savings</h1>

                <p class="savings-subtitle">
                    Track your savings goals and progress.
                </p>
            </div>

        </div>


        <!-- Create savings goal -->

        <div class="savings-form">

            <h2>Create Savings Goal</h2>

            <form method="POST" action="{{ route('savings.store') }}">
                @csrf

                <input
                    type="text"
                    name="name"
                    placeholder="Goal name"
                    required
                >

                <input
                    type="number"
                    name="target_amount"
                    step="0.01"
                    min="0.01"
                    placeholder="Target amount"
                    required
                >

                <input
                    type="number"
                    name="current_amount"
                    step="0.01"
                    min="0"
                    placeholder="Starting amount"
                >

                <input
                    type="date"
                    name="target_date"
                >

                <button type="submit">
                    Create Goal
                </button>

            </form>

        </div>

        <div class="savings-form">

        <h2>Add to Savings</h2>

        <form method="POST" action="{{ route('savings.add') }}">
            @csrf

            <select name="account_id" required>

                <option value="" disabled selected>
                    Select account
                </option>

                    @foreach (auth()->user()->accounts as $account)

                    <option value="{{ $account->id }}">
                        {{ $account->account_name }}
                        (€{{ number_format($account->balance, 2) }})
                    </option>

                    @endforeach

                    </select>

                    <select name="goal_id" required>

                        <option value="" disabled selected>
                            Select savings goal
                        </option>

                        @foreach ($savingsGoals as $goal)

                            <option value="{{ $goal->id }}">
                                {{ $goal->goal_name }}
                            </option>

                        @endforeach

                        </select>

                        <input
                            type="number"
                            name="amount"
                            step="0.01"
                            min="0.01"
                            placeholder="Amount to add"
                            required
                        >

                        <button type="submit">
                            Add to Savings
                        </button>

            </form>
        </div>


        <!-- Savings goals -->

        <div class="savings-goals">

            <h2>Your Savings Goals</h2>

            @forelse ($savingsGoals as $goal)

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

                    @php
                        $progress = $goal->target_amount > 0
                            ? ($goal->current_amount / $goal->target_amount) * 100
                            : 0;

                        $progress = min($progress, 100);
                    @endphp

                    <div class="progress-bar">
                        <div
                            class="progress"
                            style="width: {{ $progress }}%"
                        ></div>
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

            @empty

                <p class="no-savings">
                    You don't have any savings goals yet.
                </p>

            @endforelse

        </div>

    </main>

</x-app-layout>