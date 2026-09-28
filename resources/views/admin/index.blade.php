@vite(['resources/css/admin.css'])
<x-app-layout>

    <main class="admin">

        <h1>Admin Panel</h1>

        <p class="admin-subtitle">
            Manage and monitor your MewMoney system.
        </p>

        <div class="admin-cards">

            <div class="admin-card">
                <p>Total Users</p>
                <strong>{{ $totalUsers }}</strong>
                <span>Registered</span>
            </div>

            <div class="admin-card">
                <p>New Users This Month</p>
                <strong>{{ $newUsersThisMonth }}</strong>
                <span>Registered this month</span>
            </div>

            <div class="admin-card">
                <p>Active Users</p>
                <strong>{{ $activeUsers }}</strong>
                <span>Users</span>
            </div>

        </div>

        <div class="user-management">

            <form method="GET" action="{{ route('admin.index') }}" class="user-search">

                <input
                    type="text"
                    name="search"
                    placeholder="Search users..."
                    value="{{ request('search') }}"
                >

                <button type="submit">
                    Search
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.index') }}" class="clear-search">
                        Clear
                    </a>
                @endif

            </form>

            <h2>User Management</h2>

            <table>

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($users as $user)

                        <tr>
                            <td>{{ $user->name }}</td>

                            <td>{{ $user->email }}</td>

                            <td>
                                {{ $user->is_admin ? 'Admin' : 'User' }}
                            </td>

                            <td>
                                {{ $user->created_at->format('d.m.Y') }}
                            </td>

                            <td>
                                <a href="{{ route('admin.users.show', $user) }}">
                                    View
                                </a>
                            </td>
                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="admin-bottom">

            <div class="admin-chart">

                <h2>User Registrations</h2>

                <canvas id="userRegistrationsChart"></canvas>

            </div>

            <div class="system-activity">

                <h2>System Activity</h2>

                <div class="activity-list">

                    @foreach ($recentUsers as $user)

                        <div class="activity-item">

                            <div>
                                <strong>New user registered</strong>
                                <span>{{ $user->name }}</span>
                            </div>

                            <small>
                                {{ $user->created_at->format('d.m.Y H:i') }}
                            </small>

                        </div>

                    @endforeach

                    @foreach ($recentTransactions as $transaction)

                        <div class="activity-item">

                            <div>
                                <strong>New transaction</strong>
                                <span>{{ $transaction->user->name }}</span>
                            </div>

                            <small>
                                {{ $transaction->created_at->format('d.m.Y H:i') }}
                            </small>

                        </div>

                    @endforeach

                    @foreach ($recentSavingsGoals as $goal)

                        <div class="activity-item">

                            <div>
                                <strong>New savings goal</strong>
                                <span>{{ $goal->user->name }}</span>
                            </div>

                            <small>
                                {{ $goal->created_at->format('d.m.Y H:i') }}
                            </small>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>

        <script>
            window.userRegistrations = @json($registrations);
        </script>

        @vite(['resources/js/admin.js'])

    </main>

</x-app-layout>