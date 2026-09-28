@extends('layouts.app')

@section('title', 'Student Accounts - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin Portal</a></li>
<li class="breadcrumb-item active">Student Accounts</li>
@endsection

@section('content')
<div class="admin-users-page">
    <section class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="admin-stat-icon admin-stat-green" style="width:48px;height:48px;font-size:1.15rem;">
                <i class="fas fa-users" aria-hidden="true"></i>
            </span>
            <div>
                <p class="text-success small fw-bold text-uppercase mb-1" style="letter-spacing:.11em;">Campus Coin administration</p>
                <h1 class="h3 fw-bold mb-1">Student accounts</h1>
                <p class="text-secondary small mb-0">Find accounts, manage access, and export student financial reports.</p>
            </div>
        </div>
        <form action="{{ route('admin.users.reports.export') }}" method="GET" class="d-flex flex-wrap align-items-end gap-2">
            <div>
                <label for="report-month" class="form-label small text-secondary mb-1">Report month</label>
                <input id="report-month" type="month" name="month" value="{{ $month }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-success fw-bold">
                <i class="fas fa-download me-2" aria-hidden="true"></i>Download all reports
            </button>
        </form>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Student accounts</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($studentCount) }}</div></div>
                    <span class="admin-stat-icon admin-stat-green"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Registered across Campus Coin</span>
            </article>
        </div>
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Active students</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($activeStudentCount) }}</div></div>
                    <span class="admin-stat-icon admin-stat-blue"><i class="fas fa-user-check" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Accounts currently able to sign in</span>
            </article>
        </div>
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Transactions this month</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($monthTransactionCount) }}</div></div>
                    <span class="admin-stat-icon admin-stat-rose"><i class="fas fa-receipt" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</span>
            </article>
        </div>
    </div>

    <section class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="p-3 p-lg-4 border-bottom" style="border-color:var(--cc-border)!important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Account directory</h2>
                    <p class="text-secondary small mb-0">{{ number_format($users->total()) }} matching account{{ $users->total() === 1 ? '' : 's' }}</p>
                </div>
                <span class="badge rounded-pill text-bg-success px-3 py-2"><i class="fas fa-shield-alt me-1" aria-hidden="true"></i> Admin controls</span>
            </div>

            <form action="{{ route('admin.users') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="month" value="{{ $month }}">
                <div class="col-lg-5">
                    <label for="user-search" class="form-label small text-secondary mb-1">Search accounts</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-secondary" aria-hidden="true"></i></span>
                        <input id="user-search" type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, email, or academic year">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label for="user-role" class="form-label small text-secondary mb-1">Role</label>
                    <select id="user-role" name="role" class="form-select">
                        <option value="all" @selected(request('role', 'all') === 'all')>All roles</option>
                        <option value="student" @selected(request('role') === 'student')>Students</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admins</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label for="user-status" class="form-label small text-secondary mb-1">Account status</label>
                    <select id="user-status" name="status" class="form-select">
                        <option value="all" @selected(request('status', 'all') === 'all')>All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-outline-success"><i class="fas fa-filter me-1" aria-hidden="true"></i>Apply filters</button>
                </div>
                @if(request()->hasAny(['q', 'role', 'status']))
                    <div class="col-sm-6 col-lg-1 d-grid">
                        <a href="{{ route('admin.users', ['month' => $month]) }}" class="btn btn-light">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3 ps-lg-4">Account</th>
                        <th>Role</th>
                        <th>Academic year</th>
                        <th>Monthly plan</th>
                        <th>Status</th>
                        <th class="text-end pe-3 pe-lg-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3 ps-lg-4">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="user-avatar {{ $user->isAdmin() ? 'user-avatar-admin' : '' }}" aria-hidden="true">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                                        <div class="small text-secondary text-truncate">#{{ $user->id }} · {{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $user->isAdmin() ? 'bg-warning text-dark' : 'bg-primary bg-opacity-75' }}">
                                    {{ $user->isAdmin() ? 'Administrator' : 'Student' }}
                                </span>
                            </td>
                            <td class="text-secondary">{{ $user->academic_year ?: 'Not provided' }}</td>
                            <td>
                                @php($userCurrencySymbol = $user->currency_symbol ?: $currencySymbol)
                                <span class="d-block fw-semibold">{{ $userCurrencySymbol }}{{ number_format($user->monthly_allowance_baseline, 2) }}</span>
                                <span class="small text-secondary">Allowance · {{ $userCurrencySymbol }}{{ number_format($user->monthly_savings_goal, 2) }} goal</span>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $user->is_disabled ? 'bg-danger bg-opacity-75' : 'bg-success bg-opacity-75' }}">
                                    <i class="fas {{ $user->is_disabled ? 'fa-user-slash' : 'fa-check' }} me-1" aria-hidden="true"></i>
                                    {{ $user->is_disabled ? 'Disabled' : 'Active' }}
                                </span>
                            </td>
                            <td class="text-end pe-3 pe-lg-4">
                                @if(!$user->isAdmin())
                                    <div class="d-inline-flex gap-1">
                                        <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $user->is_disabled ? 'btn-outline-success' : 'btn-outline-danger' }}" title="{{ $user->is_disabled ? 'Enable account' : 'Disable account' }}" aria-label="{{ $user->is_disabled ? 'Enable' : 'Disable' }} {{ $user->name }}">
                                                <i class="fas {{ $user->is_disabled ? 'fa-user-check' : 'fa-user-slash' }}" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-reset-url="{{ route('admin.users.reset-password', $user->id) }}" data-user-name="{{ $user->name }}" onclick="openResetModal(this)" title="Reset password" aria-label="Reset password for {{ $user->name }}">
                                            <i class="fas fa-key" aria-hidden="true"></i>
                                        </button>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" data-delete-user-form data-user-name="{{ $user->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="openDeleteUserModal(this)" title="Delete student account" aria-label="Delete student account for {{ $user->name }}">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="small text-secondary"><i class="fas fa-lock me-1" aria-hidden="true"></i>Protected</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center">
                                <span class="admin-stat-icon admin-stat-blue mx-auto mb-3"><i class="fas fa-user-slash" aria-hidden="true"></i></span>
                                <div class="fw-semibold">No accounts found</div>
                                <p class="text-secondary small mb-0">Try a different search or clear the selected filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-transparent border-0 p-3 d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </section>
</div>

<style>
    .admin-users-page .user-avatar {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border: 1px solid rgba(70, 160, 228, .24);
        border-radius: 14px;
        background: rgba(70, 160, 228, .1);
        color: #82BFEF;
        font-weight: 700;
    }
    .admin-users-page .user-avatar-admin { border-color: rgba(251, 191, 36, .24); background: rgba(251, 191, 36, .1); color: #fbbf24; }
    .admin-users-page .admin-users-table th { white-space: nowrap; font-weight: 700; }
    .admin-users-page .admin-users-table td { min-height: 66px; }
    .admin-users-page .admin-users-table tbody tr:last-child td { border-bottom: 0; }
    .admin-users-page .form-control, .admin-users-page .form-select, .admin-users-page .input-group-text { min-height: 42px; }
    .admin-users-page .delete-user-warning {
        border: 1px solid var(--theme-border);
        border-radius: .85rem;
        background: var(--theme-icon-bg);
        color: var(--theme-text);
    }
    .admin-users-page .delete-user-warning i { color: var(--theme-warning-text); }
</style>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserTitle" aria-describedby="deleteUserWarning" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header p-3 border-bottom">
                <h2 class="modal-title h5 fw-bold" id="deleteUserTitle">
                    <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete student account?
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">You are about to permanently delete <strong id="deleteUserName"></strong>.</p>
                <div class="delete-user-warning d-flex align-items-start gap-3 p-3" id="deleteUserWarning">
                    <i class="fas fa-triangle-exclamation mt-1" aria-hidden="true"></i>
                    <span>This also removes the student's transactions, budgets, categories, and other account data. This action cannot be undone.</span>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-outline-primary rounded-pill px-3" data-bs-dismiss="modal">Keep account</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="confirmDeleteUser">
                    <i class="fas fa-trash-alt me-2" aria-hidden="true"></i>Delete permanently
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <h2 class="modal-title h5 fw-bold" id="resetPasswordTitle"><i class="fas fa-key text-warning me-2" aria-hidden="true"></i>Reset user password</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Set a new password for <strong id="resetUserName"></strong>.</p>
                    <div class="mb-3">
                        <label for="reset-password" class="form-label fw-bold small text-secondary">New password</label>
                        <input id="reset-password" type="password" name="password" class="form-control" autocomplete="new-password" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label for="reset-password-confirmation" class="form-label fw-bold small text-secondary">Confirm new password</label>
                        <input id="reset-password-confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Update password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let pendingDeleteUserForm = null;
    let pendingDeleteUserButton = null;

    function openDeleteUserModal(button) {
        const form = button.closest('[data-delete-user-form]');
        if (!(form instanceof HTMLFormElement)) return;

        pendingDeleteUserForm = form;
        pendingDeleteUserButton = button;
        document.getElementById('deleteUserName').textContent = form.dataset.userName || 'this student';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteUserModal')).show();
    }

    document.getElementById('deleteUserModal').addEventListener('hidden.bs.modal', () => {
        pendingDeleteUserForm = null;
        pendingDeleteUserButton?.focus();
        pendingDeleteUserButton = null;
    });

    document.getElementById('confirmDeleteUser').addEventListener('click', () => {
        const form = pendingDeleteUserForm;
        if (!form) return;

        pendingDeleteUserForm = null;
        form.requestSubmit();
    });

    function openResetModal(button) {
        document.getElementById('resetUserName').textContent = button.dataset.userName;
        document.getElementById('resetForm').action = button.dataset.resetUrl;
        document.getElementById('resetForm').reset();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('resetPasswordModal')).show();
    }
</script>
@endsection
