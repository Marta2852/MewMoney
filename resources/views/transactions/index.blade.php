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

        </div>


        <!-- Expense filters -->
        <div class="expense-section">

            <h2>Expenses</h2>

            <div class="expense-filters">
                <button type="button">Day</button>
                <button type="button">Week</button>
                <button type="button">Month</button>
            </div>

        </div>


        <!-- Transaction list -->
        <div class="transaction-list">

            @forelse ($transactions as $transaction)

                <div class="transaction-card">

                    <div class="transaction-info">

                        <strong>
                            {{ $transaction->description ?: 'No description' }}
                        </strong>

                        <p>
                            {{ $transaction->transaction_date }}
                            · {{ $transaction->account->account_name }}
                            · {{ $transaction->category->category_name }}
                        </p>

                    </div>

                    <div class="transaction-amount">
                        €{{ number_format($transaction->amount, 2) }}
                    </div>

                </div>

            @empty

                <p class="no-transactions">
                    No transactions yet.
                </p>

            @endforelse

        </div>

    </main>


    <!-- Category filtering -->
    <script>
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
```
