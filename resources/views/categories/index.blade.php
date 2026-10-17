
@push('styles')
    @vite('resources/css/transactions.css')
@endpush

<x-app-layout>
    <main class="transactions">

        <div class="transactions-header">
            <div>
                <h1>My Categories</h1>
                <p class="transactions-subtitle">
                    Manage your income and expense categories.
                </p>
            </div>
        </div>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p>{{ session('error') }}</p>
        @endif

        <div class="transaction-list">
            @forelse ($categories as $category)

                <div class="category-item">

                    <div class="transaction-card">
                        <div class="transaction-info">
                            <strong>{{ $category->category_name }}</strong>
                            <p>{{ ucfirst($category->category_type) }}</p>
                        </div>

                        <div class="category-actions">
                            <button
                                type="button"
                                class="edit-category-btn"
                                onclick="toggleCategoryEdit({{ $category->id }})"
                            >
                                Edit
                            </button>

                            <form
                                method="POST"
                                action="{{ route('categories.destroy', $category->id) }}"
                                onsubmit="return confirm('Delete this category?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div
                        class="category-edit-form"
                        id="edit-category-{{ $category->id }}"
                        hidden
                    >
                        <form
                            method="POST"
                            action="{{ route('categories.update', $category->id) }}"
                        >
                            @csrf
                            @method('PUT')

                            <label>Category name</label>
                            <input
                                type="text"
                                name="category_name"
                                value="{{ $category->category_name }}"
                                required
                            >

                            <label>Category type</label>
                            <select name="category_type" required>
                                <option value="expense"
                                    {{ $category->category_type === 'expense' ? 'selected' : '' }}>
                                    Expense
                                </option>

                                <option value="income"
                                    {{ $category->category_type === 'income' ? 'selected' : '' }}>
                                    Income
                                </option>
                            </select>

                            <div class="category-edit-actions">
                                <button type="submit">Save Changes</button>

                                <button
                                    type="button"
                                    class="cancel-edit-btn"
                                    onclick="toggleCategoryEdit({{ $category->id }})"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            @empty
                <p class="no-transactions">
                    You haven't created any categories yet.
                </p>
            @endforelse
        </div>

        <a href="{{ route('transactions') }}">Back to Transactions</a>

    </main>

    <script>
        function toggleCategoryEdit(id) {
            const form = document.getElementById('edit-category-' + id);
            form.hidden = !form.hidden;
        }
    </script>
</x-app-layout>
