@push('styles')
	@vite('resources/css/accounts.css')
@endpush

<x-app-layout>
	<main class="accounts">
		<h1>Edit Accounts</h1>

		<p class="accounts-subtitle">
			Update account names. Balances remain tied to transaction history.
		</p>

		<div class="account-list account-management-list">
			@forelse ($accounts as $account)
				<section class="account-card account-edit-card">
					<form method="POST" action="{{ route('accounts.update', $account->id) }}">
						@csrf
						@method('PUT')

						<label for="account-name-{{ $account->id }}">Account name</label>
						<input
							id="account-name-{{ $account->id }}"
							type="text"
							name="account_name"
							value="{{ old('account_name', $account->account_name) }}"
							maxlength="255"
							required
						>

						@error('account_name')
							<p class="account-form-error">{{ $message }}</p>
						@enderror

						<p class="account-balance">
							€{{ number_format($account->balance, 2) }}
						</p>

						<button type="submit">Save Changes</button>
					</form>
				</section>
			@empty
				<p>You don't have any accounts to edit.</p>
			@endforelse
		</div>

		<a href="{{ route('accounts') }}" class="edit-accounts-link">Back to Accounts</a>
	</main>
</x-app-layout>
