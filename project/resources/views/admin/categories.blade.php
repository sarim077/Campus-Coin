@extends('layouts.app')

@section('title', 'System Default Categories - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin Portal</a></li>
<li class="breadcrumb-item active">Default Categories</li>
@endsection

@section('content')
<div class="admin-categories-page">
    <section class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="admin-stat-icon admin-stat-green" style="width:48px;height:48px;font-size:1.15rem;">
                <i class="fas fa-tags" aria-hidden="true"></i>
            </span>
            <div>
                <p class="text-success small fw-bold text-uppercase mb-1" style="letter-spacing:.11em;">Campus Coin administration</p>
                <h1 class="h3 fw-bold mb-1">System default categories</h1>
                <p class="text-secondary small mb-0">Manage shared categories available to every student account.</p>
            </div>
        </div>
        <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="openCategoryModal()">
            <i class="fas fa-plus me-2" aria-hidden="true"></i>Add category
        </button>
    </section>

    @if($errors->any())
        <div class="alert alert-danger rounded-4" role="alert">
            <div class="fw-semibold mb-1">The category could not be saved.</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">All system categories</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($incomeCategories->count() + $expenseCategories->count()) }}</div></div>
                    <span class="admin-stat-icon admin-stat-blue"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Shared with student accounts</span>
            </article>
        </div>
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Income categories</span><div class="h3 fw-bold text-success mb-0 mt-1">{{ number_format($incomeCategories->count()) }}</div></div>
                    <span class="admin-stat-icon admin-stat-green"><i class="fas fa-arrow-down" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Money students receive</span>
            </article>
        </div>
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Expense categories</span><div class="h3 fw-bold text-danger mb-0 mt-1">{{ number_format($expenseCategories->count()) }}</div></div>
                    <span class="admin-stat-icon admin-stat-rose"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Money students spend</span>
            </article>
        </div>
    </div>

    <div class="row g-3 g-xl-4">
        <div class="col-lg-6">
            @include('admin.partials.category-list', [
                'categories' => $incomeCategories,
                'type' => 'income',
                'title' => 'Income',
                'description' => 'Sources of student funds',
                'icon' => 'fa-arrow-down',
                'tone' => 'green',
            ])
        </div>
        <div class="col-lg-6">
            @include('admin.partials.category-list', [
                'categories' => $expenseCategories,
                'type' => 'expense',
                'title' => 'Expenses',
                'description' => 'Everyday spending categories',
                'icon' => 'fa-arrow-up',
                'tone' => 'rose',
            ])
        </div>
    </div>
</div>

<style>
    .admin-categories-page .category-list { overflow: hidden; }
    .admin-categories-page .category-list-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 20px; border-bottom: 1px solid var(--cc-border); }
    .admin-categories-page .category-list-heading { display: flex; align-items: center; gap: 12px; }
    .admin-categories-page .category-list-icon { display: grid; width: 40px; height: 40px; flex: 0 0 40px; place-items: center; border-radius: 13px; }
    .admin-categories-page .category-list-icon-green { color: #82BFEF; background: rgba(70, 160, 228,.1); }
    .admin-categories-page .category-list-icon-rose { color: #fda4af; background: rgba(251,113,133,.1); }
    .admin-categories-page .category-row { min-height: 74px; padding: 14px 18px; border-color: var(--cc-border); background: transparent; }
    .admin-categories-page .category-row:last-child { border-bottom: 0; }
    .admin-categories-page .category-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border: 1px solid rgba(70, 160, 228,.14); border-radius: 13px; background: rgba(70, 160, 228,.08); color: #82BFEF; }
    .admin-categories-page .category-actions { opacity: .78; transition: opacity .15s ease; }
    .admin-categories-page .category-row:hover .category-actions,
    .admin-categories-page .category-actions:focus-within { opacity: 1; }
    .admin-categories-page .category-empty { padding: 32px 20px; color: var(--cc-muted); text-align: center; }
    .admin-categories-page .category-empty i { display: block; margin-bottom: 10px; color: #82BFEF; font-size: 1.25rem; }
    @media (max-width: 575.98px) {
        .admin-categories-page .category-row { padding-inline: 13px; }
        .admin-categories-page .category-actions { opacity: 1; }
    }
</style>

<div class="modal fade category-modal" id="adminCategoryModal" tabindex="-1" aria-labelledby="adminCategoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="adminCategoryForm" action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <input id="categoryMethod" type="hidden" name="_method" value="">
                <div class="modal-header category-modal-header p-4 border-0">
                    <span class="category-modal-mark"><i class="fas fa-tags" aria-hidden="true"></i></span>
                    <div class="flex-grow-1">
                        <h2 class="modal-title h5 fw-bold mb-1" id="adminCategoryModalTitle"><span id="categoryModalHeading">Add a category</span></h2>
                        <p class="small mb-0">Create a clear, reusable category for student transactions.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <fieldset class="mb-4">
                                <legend class="form-label fw-semibold mb-2">Category type</legend>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="category-type-option" for="category-type-expense">
                                            <input id="category-type-expense" type="radio" name="type" value="expense" @checked(old('type', 'expense') === 'expense') required>
                                            <span class="category-type-icon category-type-icon-expense"><i class="fas fa-arrow-up" aria-hidden="true"></i></span>
                                            <span><strong>Expense</strong><small>Money going out</small></span>
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <label class="category-type-option" for="category-type-income">
                                            <input id="category-type-income" type="radio" name="type" value="income" @checked(old('type') === 'income') required>
                                            <span class="category-type-icon category-type-icon-income"><i class="fas fa-arrow-down" aria-hidden="true"></i></span>
                                            <span><strong>Income</strong><small>Money coming in</small></span>
                                        </label>
                                    </div>
                                </div>
                                @error('type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </fieldset>

                            <div class="mb-3">
                                <label for="category-name" class="form-label fw-semibold">Category name</label>
                                <input id="category-name" type="text" name="name" maxlength="255" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Books or Campus job" autocomplete="off" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Keep it short so it is easy to recognize in a transaction list.</div>
                            </div>

                            <div>
                                <label for="category-icon" class="form-label fw-semibold">Icon class <span class="text-secondary fw-normal">(optional)</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text category-icon-preview" aria-hidden="true"><i id="categoryIconPreview" class="fas fa-tag"></i></span>
                                    <input id="category-icon" type="text" name="icon" maxlength="100" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', 'fas fa-tag') }}" placeholder="fas fa-tag" autocomplete="off">
                                </div>
                                @error('icon')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">Use a Font Awesome class, such as <code>fas fa-book</code>.</div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <aside class="category-live-preview h-100" aria-live="polite">
                                <span class="category-preview-eyebrow"><i class="fas fa-eye me-1" aria-hidden="true"></i> Student view</span>
                                <h3 class="h6 fw-bold mt-3 mb-1">Preview</h3>
                                <p class="small text-secondary mb-3">This is how the category appears in the app.</p>
                                <div id="categoryPreviewCard" class="category-preview-card category-preview-expense">
                                    <span class="category-preview-icon"><i id="categoryPreviewCardIcon" class="fas fa-tag" aria-hidden="true"></i></span>
                                    <span class="min-w-0"><strong id="categoryPreviewName" class="d-block text-truncate">New category</strong><small id="categoryPreviewType">Expense</small></span>
                                    <i class="fas fa-chevron-right ms-auto small text-secondary" aria-hidden="true"></i>
                                </div>
                                <div class="category-preview-note mt-3"><i class="fas fa-info-circle me-2" aria-hidden="true"></i>Shared with all students after saving.</div>
                            </aside>
                        </div>
                    </div>
                </div>
                <div class="modal-footer category-modal-footer p-3 px-4 border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-check me-2" aria-hidden="true"></i><span id="categorySubmitLabel">Save category</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .category-modal .modal-dialog { max-width: 720px; }
    .category-modal .modal-content { overflow: hidden; background: #15181A; color: #E8EDF0; }
    .category-modal-header { display: flex; align-items: center; gap: 14px; color: #F2F7FA; background: radial-gradient(circle at 92% 10%, rgba(70, 160, 228,.18), transparent 42%), linear-gradient(135deg,#182025,#15181A); }
    .category-modal-header p { color: #AAB1B7; }
    .category-modal-mark { display: grid; width: 44px; height: 44px; flex: 0 0 44px; place-items: center; border: 1px solid rgba(70, 160, 228,.24); border-radius: 14px; background: rgba(70, 160, 228,.1); color: #82BFEF; }
    .category-modal .form-label, .category-modal legend { color: #DCE2E7; font-size: .88rem; }
    .category-modal .form-control, .category-modal .input-group-text { min-height: 46px; border-color: rgba(235, 241, 245,.14); background: #101315; color: #EDF1F5; }
    .category-modal .form-control:focus { border-color: rgba(70, 160, 228,.6); box-shadow: 0 0 0 .2rem rgba(70, 160, 228,.12); }
    .category-modal .form-control::placeholder { color: #787F84; }
    .category-modal .form-text { color: #899096; }
    .category-modal .category-type-option { display: flex; min-height: 78px; align-items: center; gap: 10px; padding: 11px; border: 1px solid rgba(235, 241, 245,.13); border-radius: 14px; background: #111517; cursor: pointer; transition: border-color .16s ease, background .16s ease; }
    .category-modal .category-type-option:has(input:checked) { border-color: rgba(70, 160, 228,.65); background: rgba(70, 160, 228,.08); box-shadow: inset 0 0 0 1px rgba(70, 160, 228,.12); }
    .category-modal .category-type-option input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .category-modal .category-type-option:focus-within { outline: 2px solid rgba(70, 160, 228,.55); outline-offset: 2px; }
    .category-modal .category-type-icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border-radius: 11px; }
    .category-modal .category-type-icon-expense { color: #fda4af; background: rgba(251,113,133,.12); }
    .category-modal .category-type-icon-income { color: #82BFEF; background: rgba(70, 160, 228,.12); }
    .category-modal .category-type-option strong, .category-modal .category-type-option small { display: block; }
    .category-modal .category-type-option strong { color: #E8EDF0; font-size: .82rem; }
    .category-modal .category-type-option small { margin-top: 2px; color: #899096; font-size: .68rem; }
    .category-modal .category-live-preview { padding: 18px; border: 1px solid rgba(235, 241, 245,.1); border-radius: 16px; background: linear-gradient(160deg,#111517,#101213); }
    .category-modal .category-preview-eyebrow { color: #82BFEF; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .category-modal .category-preview-card { display: flex; align-items: center; gap: 11px; padding: 12px; border: 1px solid rgba(70, 160, 228,.18); border-radius: 13px; background: rgba(70, 160, 228,.06); }
    .category-modal .category-preview-card.category-preview-income { border-color: rgba(70, 160, 228,.2); background: rgba(70, 160, 228,.07); }
    .category-modal .category-preview-card.category-preview-expense { border-color: rgba(251,113,133,.2); background: rgba(251,113,133,.06); }
    .category-modal .category-preview-icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border-radius: 11px; background: rgba(70, 160, 228,.12); color: #82BFEF; }
    .category-modal .category-preview-expense .category-preview-icon { background: rgba(251,113,133,.12); color: #fda4af; }
    .category-modal .category-preview-card strong { color: #E8EDF0; font-size: .82rem; }
    .category-modal .category-preview-card small { color: #9AA1A7; font-size: .69rem; }
    .category-modal .category-preview-note { color: #899096; font-size: .72rem; line-height: 1.5; }
    .category-modal-footer { background: #111517; }
    @media (max-width: 575.98px) {
        .category-modal .modal-dialog { margin: .65rem; }
        .category-modal .modal-header, .category-modal .modal-body { padding: 1.2rem !important; }
    }
</style>
@endsection

@section('scripts')
<script>
    function openCategoryModal(button = null) {
        const form = document.getElementById('adminCategoryForm');
        const editing = button !== null;
        form.action = editing ? button.dataset.updateUrl : @json(route('admin.categories.store'));
        document.getElementById('categoryMethod').value = editing ? 'PUT' : '';
        document.getElementById('categoryModalHeading').textContent = editing ? 'Edit category' : 'Add a category';
        document.getElementById('categorySubmitLabel').textContent = editing ? 'Save changes' : 'Save category';
        document.getElementById('category-name').value = editing ? button.dataset.name : '';
        document.getElementById('category-icon').value = editing ? button.dataset.icon : 'fas fa-tag';
        const selectedType = editing ? button.dataset.type : 'expense';
        document.querySelector(`input[name="type"][value="${selectedType}"]`).checked = true;
        updateCategoryPreview();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('adminCategoryModal')).show();
    }

    function updateCategoryPreview() {
        const selectedType = document.querySelector('input[name="type"]:checked').value;
        const iconClass = document.getElementById('category-icon').value.trim() || 'fas fa-tag';
        const categoryName = document.getElementById('category-name').value.trim() || 'New category';
        const previewCard = document.getElementById('categoryPreviewCard');

        document.getElementById('categoryIconPreview').className = iconClass;
        document.getElementById('categoryPreviewCardIcon').className = `${iconClass} category-preview-live-icon`;
        document.getElementById('categoryPreviewName').textContent = categoryName;
        document.getElementById('categoryPreviewType').textContent = selectedType === 'income' ? 'Income' : 'Expense';
        previewCard.classList.toggle('category-preview-income', selectedType === 'income');
        previewCard.classList.toggle('category-preview-expense', selectedType === 'expense');
    }

    document.querySelectorAll('#category-name, #category-icon, input[name="type"]').forEach((input) => {
        input.addEventListener('input', updateCategoryPreview);
        input.addEventListener('change', updateCategoryPreview);
    });
    updateCategoryPreview();

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('adminCategoryModal')).show();
        });
    @endif
</script>
@endsection
