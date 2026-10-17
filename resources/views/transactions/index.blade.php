@push('styles')
    @vite('resources/css/transactions.css')
@endpush

<x-app-layout>

    <main class="transactions">

        <!-- Page header -->
        <div class="transactions-header">

            <div>
                <h1>Transactions</h1>

                <p class="transactions-subtitle">
                    Manage your income and expenses.
                </p>
            </div>

        </div>


        <!-- Create category -->
        <div class="category-form">

            <h2>Create Category</h2>

            <form method="POST" action="{{ route('categories.store') }}">
                @csrf

                <input
                    type="text"
                    name="category_name"
                    placeholder="Category name"
                    required
                >

                <select name="category_type" required>
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                </select>

                <button type="submit">
                    Create Category
                </button>

                <a href="{{ route('categories.index') }}" class="view-categories-btn">
                    View Categories
                </a>

            </form>

        </div>


        <!-- Add transaction -->
        <div class="transaction-form">

            <h2>Add Transaction</h2>

            

            <form method="POST" action="{{ route('transactions.store') }}">
                @csrf

                <select name="transaction_type" id="transaction_type" required>
                    <option value="">Transaction type</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>

                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    placeholder="Amount"
                    required
                >

                <input
                    type="text"
                    name="description"
                    placeholder="Description"
                >

                <input
                    type="date"
                    name="transaction_date"
                    required
                >

                <select name="account_id" required>
                    <option value="">Select account</option>

                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">
                            {{ $account->account_name }}
                            (€{{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach

                </select>

                <select name="category_id" id="category" required>
                    <option value="">Select category</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            data-type="{{ $category->category_type }}"
                        >
                            {{ $category->category_name }}
                        </option>
                    @endforeach

                </select>

                <button type="submit">
                    Add Transaction
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

        
@if (session('success'))
    <p class="form-success">{{ session('success') }}</p>
@endif



        <!-- Expense filters -->
        <div class="expense-filters">

            <a href="{{ route('transactions', ['period' => 'day']) }}"
            class="{{ $period === 'day' ? 'active' : '' }}">
                Day
            </a>

            <a href="{{ route('transactions', ['period' => 'week']) }}"
            class="{{ $period === 'week' ? 'active' : '' }}">
                Week
            </a>

            <a href="{{ route('transactions', ['period' => 'month']) }}"
            class="{{ $period === 'month' ? 'active' : '' }}">
                Month
            </a>

            @if ($period === 'day')
                <form method="GET" action="{{ route('transactions') }}">
                    <input type="hidden" name="period" value="day">

                    <input
                        type="date"
                        name="date"
                        value="{{ $date }}"
                        onchange="this.form.submit()"
                    >
                </form>
            @endif

            @if ($period === 'week')
                <form method="GET" action="{{ route('transactions') }}">
                    <input type="hidden" name="period" value="week">

                    <input
                        type="date"
                        name="date"
                        value="{{ $date }}"
                        onchange="this.form.submit()"
                    >
                </form>
            @endif

            @if ($period === 'month')
                <form method="GET" action="{{ route('transactions') }}">
                    <input type="hidden" name="period" value="month">

                    <input
                        type="month"
                        name="date"
                        value="{{ $date }}"
                        onchange="this.form.submit()"
                    >
                </form>
            @endif

        </div>

        <div class="transaction-type-filters">
            <a href="{{ route('transactions', ['period' => $period, 'date' => $date, 'type' => 'all']) }}"
            class="{{ $type === 'all' ? 'active' : '' }}">
                All
            </a>

            <a href="{{ route('transactions', ['period' => $period, 'date' => $date, 'type' => 'income']) }}"
            class="{{ $type === 'income' ? 'active' : '' }}">
                Income
            </a>

            <a href="{{ route('transactions', ['period' => $period, 'date' => $date, 'type' => 'expense']) }}"
            class="{{ $type === 'expense' ? 'active' : '' }}">
                Expense
            </a>

            <a href="{{ route('transactions', ['period' => $period, 'date' => $date, 'type' => 'transfer']) }}"
            class="{{ $type === 'transfer' ? 'active' : '' }}">
                Transfer
            </a>
        </div>


        <!-- Transaction list -->
        <div class="transaction-list">

            @forelse ($transactions as $transaction)

                <div class="transaction-item">

                    <div class="transaction-card transaction-card--{{ $transaction->transaction_type }}">

                        <div class="transaction-info">
                            <strong>
                                {{ $transaction->description ?: 'No description' }}
                            </strong>

                            <p>
                                {{ $transaction->transaction_date }}
                                · {{ $transaction->account->account_name }}
                                · {{ $transaction->category->category_name ?? 'Transfer' }}
                            </p>
                        </div>

                        <div class="transaction-actions">
                            <div class="transaction-amount">
                                €{{ number_format($transaction->amount, 2) }}
                            </div>

                            <button
                                type="button"
                                class="edit-transaction-btn"
                                onclick="toggleTransactionEdit({{ $transaction->id }})"
                            >
                                Edit
                            </button>

                            <form
                                method="POST"
                                action="{{ route('transactions.destroy', $transaction->id) }}"
                                onsubmit="return confirm('Are you sure you want to delete this transaction?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="delete-transaction-btn">
                                    Delete
                                </button>
                            </form>
                        </div>

                    </div>

                    <div
                        class="transaction-edit-form"
                        id="edit-transaction-{{ $transaction->id }}"
                        hidden
                    >
                        <h2>Edit Transaction</h2>

                        <form
                            method="POST"
                            action="{{ route('transactions.update', $transaction->id) }}"
                        >
                            @csrf
                            @method('PUT')

                            @if ($transaction->transaction_type === 'transfer')
                                <input type="hidden" name="transaction_type" value="transfer">

                                <label>Transfer amount (€)</label>
                                <input
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="0.01"
                                    value="{{ $transaction->amount }}"
                                    required
                                >

                                <label>Description</label>
                                <input
                                    type="text"
                                    name="description"
                                    value="{{ $transaction->description }}"
                                >

                                <label>Date</label>
                                <input
                                    type="date"
                                    name="transaction_date"
                                    value="{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}"
                                    required
                                >

                                <label>Account</label>
                                <select name="account_id" required>
                                    @foreach ($accounts as $account)
                                        <option
                                            value="{{ $account->id }}"
                                            {{ $transaction->account_id == $account->id ? 'selected' : '' }}
                                        >
                                            {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label>Savings goal</label>
                                <select name="savings_goal_id" required>
                                    @foreach ($savingsGoals as $goal)
                                        <option
                                            value="{{ $goal->id }}"
                                            {{ $transaction->savings_goal_id == $goal->id ? 'selected' : '' }}
                                        >
                                            {{ $goal->goal_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <label>Transaction type</label>
                                <select name="transaction_type" class="transaction-edit-type" required>
                                    <option value="income" {{ $transaction->transaction_type === 'income' ? 'selected' : '' }}>
                                        Income
                                    </option>
                                    <option value="expense" {{ $transaction->transaction_type === 'expense' ? 'selected' : '' }}>
                                        Expense
                                    </option>
                                </select>

                                <label>Amount (€)</label>
                                <input
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="0.01"
                                    value="{{ $transaction->amount }}"
                                    required
                                >

                                <label>Description</label>
                                <input
                                    type="text"
                                    name="description"
                                    value="{{ $transaction->description }}"
                                >

                                <label>Date</label>
                                <input
                                    type="date"
                                    name="transaction_date"
                                    value="{{ $transaction->transaction_date instanceof \Carbon\Carbon
                                        ? $transaction->transaction_date->format('Y-m-d')
                                        : substr($transaction->transaction_date, 0, 10) }}"
                                    required
                                >

                                <label>Account</label>
                                <select name="account_id" required>
                                    @foreach ($accounts as $account)
                                        <option
                                            value="{{ $account->id }}"
                                            {{ $transaction->account_id == $account->id ? 'selected' : '' }}
                                        >
                                            {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label>Category</label>
                                <select name="category_id" class="transaction-edit-category" required>
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        @if ($category->category_type === $transaction->transaction_type)
                                            <option
                                                value="{{ $category->id }}"
                                                data-type="{{ $category->category_type }}"
                                                {{ $transaction->category_id == $category->id ? 'selected' : '' }}
                                            >
                                                {{ $category->category_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            @endif

                            <div class="transaction-edit-actions">
                                <button type="submit">Save Changes</button>

                                <button
                                    type="button"
                                    class="cancel-transaction-edit"
                                    onclick="toggleTransactionEdit({{ $transaction->id }})"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            @empty

                <p class="no-transactions">
                    No transactions yet.
                </p>

            @endforelse

        </div>

        @if ($transactions->hasPages())
            <nav class="transaction-pagination" aria-label="Transaction pages">
                {{ $transactions->links() }}
            </nav>
        @endif

    </main>


    <!-- Category filtering -->
    <script>
        window.toggleTransactionEdit = function (id) {
            const editForm = document.getElementById(`edit-transaction-${id}`);

            if (editForm) {
                editForm.hidden = !editForm.hidden;
            }
        };

        document.querySelectorAll('.transaction-edit-form').forEach(editForm => {
            const typeSelect = editForm.querySelector('.transaction-edit-type');
            const categorySelect = editForm.querySelector('.transaction-edit-category');

            const filterCategories = () => {
                const selectedType = typeSelect.value;
                const selectedCategory = categorySelect.selectedOptions[0];

                if (selectedCategory?.dataset.type && selectedCategory.dataset.type !== selectedType) {
                    categorySelect.value = '';
                }

                Array.from(categorySelect.options).forEach(option => {
                    option.hidden = Boolean(option.dataset.type && option.dataset.type !== selectedType);
                });
            };

            typeSelect.addEventListener('change', filterCategories);
            filterCategories();
        });

        const typeSelect = document.getElementById('transaction_type');
        const categorySelect = document.getElementById('category');

        typeSelect.addEventListener('change', function () {

            const selectedType = this.value;

            categorySelect.value = '';

            Array.from(categorySelect.options).forEach(option => {

                if (!option.dataset.type) {
                    option.hidden = false;
                    return;
                }

                option.hidden = option.dataset.type !== selectedType;

            });

            

        });
    </script>

</x-app-layout>

