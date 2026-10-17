@push('styles')
    @vite([
        'resources/css/savings.css',
        'resources/js/savings.js'
        ])
@endpush

<script>
    window.savingsGrowth = @json($savingsGrowth);

    window.activeGoals = @json(
        $activeGoals->pluck('goal_name')->values()
    );
</script>

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

                <select name="starting_account_id" id="starting_account_id">
                    <option value="">Select an account</option>

                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">
                            {{ $account->account_name }} (€{{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach
                </select>

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

                        @foreach ($activeGoals as $goal)
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

            @if ($errors->any())
            <div class="form-errors">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif
        </div>


        <!-- Savings goals -->
    <div class="savings-layout">
        <div class="savings-growth">
        <h2>Savings Activity</h2>

        <div class="savings-chart-container">
            <canvas id="savingsGrowthChart"></canvas>
        </div>
    </div>

    <div class="savings-goals">
        <h2>Your Savings Goals</h2>

        <!-- Active Goals -->
         @if ($activeGoals->count() > 0)
            <h3 class="goals-section-title">
                Active Goals
            </h3>

            @foreach ($activeGoals as $goal)

            <div class="savings-card">

                <div class="savings-card-header">
                    <strong>
                        {{ $goal->goal_name }}
                    </strong>

                    <div class="savings-card-actions">
                        <button
                            type="button"
                            class="edit-goal-btn"
                            onclick="document.getElementById('edit-goal-{{ $goal->id }}').hidden = false"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="delete-goal-btn"
                            onclick="document.getElementById('delete-goal-{{ $goal->id }}').hidden = false"
                        >
                            Delete
                        </button>
                    </div>

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

                <div id="edit-goal-{{ $goal->id }}" hidden class="goal-edit-form">
                    <h3>Edit Savings Goal</h3>

                    <form method="POST" action="{{ route('savings.update', $goal->id) }}">
                        @csrf
                        @method('PUT')

                        <input
                            type="text"
                            name="goal_name"
                            value="{{ $goal->goal_name }}"
                            placeholder="Goal name"
                            required
                        >

                        <input
                            type="number"
                            name="target_amount"
                            value="{{ $goal->target_amount }}"
                            step="0.01"
                            min="0.01"
                            placeholder="Target amount"
                            required
                        >

                        <input
                            type="date"
                            name="target_date"
                            value="{{ $goal->target_date }}"
                        >

                        <button type="submit">Save Changes</button>

                        <button
                            type="button"
                            onclick="document.getElementById('edit-goal-{{ $goal->id }}').hidden = true"
                        >
                            Cancel
                        </button>
                    </form>
                </div>

                <div id="delete-goal-{{ $goal->id }}" hidden class="goal-delete-form">
                    <p>
                        Are you sure you want to delete
                        <strong>{{ $goal->goal_name }}</strong>?
                    </p>

                    <form method="POST" action="{{ route('savings.destroy', $goal->id) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="delete-goal-btn">
                            Yes, Delete
                        </button>

                        <button
                            type="button"
                            onclick="document.getElementById('delete-goal-{{ $goal->id }}').hidden = true"
                        >
                            Cancel
                        </button>
                    </form>
                </div>

            </div>
            @endforeach
        @endif

        <!-- Completed Goals -->
        @if ($completedGoals->count() > 0)
            <button
                type="button"
                class="completed-toggle"
                id="completedToggle"
            >

            <span>
                Completed ({{ $completedGoals->count() }})
            </span>

            <span id="completedArrow">
                   ▼
                </span>
            </button>

            <div
                class="completed-goals"
                id="completedGoals"
            >

            @foreach ($completedGoals as $goal)
                <div class="savings-card completed-card">
                    <div class="savings-card-header">
                        <strong>
                            {{ $goal->goal_name }}
                        </strong>

                        <div class="savings-card-actions">
                            <button
                                type="button"
                                class="edit-goal-btn"
                                onclick="document.getElementById('edit-goal-{{ $goal->id }}').hidden = false"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="delete-goal-btn"
                                onclick="document.getElementById('delete-goal-{{ $goal->id }}').hidden = false"
                            >
                                Delete
                            </button>
                        </div>

                        <span>
                            Completed
                        </span>
                    </div>

                    <div class="progress-bar">
                        <div
                            class="progress"
                            style="width: 100%">
                        </div>
                    </div>

                    <p>
                        €{{ number_format($goal->current_amount, 2) }}
                        /
                        €{{ number_format($goal->target_amount, 2) }}
                        - 100%
                    </p>

                    <button
                        type="button"
                        class="see-growth-btn"
                        data-goal="{{ $goal->goal_name }}"
                    >
                        See Growth
                    </button>

                    <div id="edit-goal-{{ $goal->id }}" hidden class="goal-edit-form">
                        <h3>Edit Savings Goal</h3>

                        <form method="POST" action="{{ route('savings.update', $goal->id) }}">
                            @csrf
                            @method('PUT')

                            <input
                                type="text"
                                name="goal_name"
                                value="{{ $goal->goal_name }}"
                                placeholder="Goal name"
                                required
                            >

                            <input
                                type="number"
                                name="target_amount"
                                value="{{ $goal->target_amount }}"
                                step="0.01"
                                min="0.01"
                                placeholder="Target amount"
                                required
                            >

                            <input
                                type="date"
                                name="target_date"
                                value="{{ $goal->target_date }}"
                            >

                            <button type="submit">Save Changes</button>

                            <button
                                type="button"
                                onclick="document.getElementById('edit-goal-{{ $goal->id }}').hidden = true"
                            >
                                Cancel
                            </button>
                        </form>
                    </div>

                    <div id="delete-goal-{{ $goal->id }}" hidden class="goal-delete-form">
                        <p>
                            Are you sure you want to delete
                            <strong>{{ $goal->goal_name }}</strong>?
                        </p>

                        <form method="POST" action="{{ route('savings.destroy', $goal->id) }}">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="delete-goal-btn">
                                Yes, Delete
                            </button>

                            <button
                                type="button"
                                onclick="document.getElementById('delete-goal-{{ $goal->id }}').hidden = true"
                            >
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
            </div>
            @endif

            @if ($activeGoals->count() === 0 && $completedGoals->count() === 0)
            <p class="no-savings">
                You don't have any savings goals yet.
            </p>
        @endif
        </div>

    </div>

    </main>

</x-app-layout>