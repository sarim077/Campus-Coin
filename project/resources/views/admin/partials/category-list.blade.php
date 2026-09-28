<section class="card category-list border-0 shadow-sm rounded-4 h-100">
    <header class="category-list-header">
        <div class="category-list-heading">
            <span class="category-list-icon category-list-icon-{{ $tone }}"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
            <div>
                <h2 class="h5 fw-bold mb-1">{{ $title }} categories</h2>
                <p class="text-secondary small mb-0">{{ $description }}</p>
            </div>
        </div>
        <span class="badge rounded-pill text-bg-success">{{ $categories->count() }}</span>
    </header>
    <ul class="list-group list-group-flush">
        @forelse($categories as $category)
            <li class="list-group-item category-row d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="category-icon" aria-hidden="true"><i class="{{ $category->icon ?: 'fas fa-tag' }}"></i></span>
                    <span class="fw-semibold text-truncate">{{ $category->name }}</span>
                </div>
                <div class="category-actions d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-success rounded-circle" data-update-url="{{ route('admin.categories.update', $category->id) }}" data-name="{{ $category->name }}" data-icon="{{ $category->icon ?: 'fas fa-tag' }}" data-type="{{ $type }}" onclick="openCategoryModal(this)" title="Edit {{ $category->name }}" aria-label="Edit {{ $category->name }}">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                    </button>
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Remove this shared category? Existing transactions will remain, but will no longer be linked to it.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Remove {{ $category->name }}" aria-label="Remove {{ $category->name }}">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </li>
        @empty
            <li class="list-group-item category-empty">
                <i class="fas fa-tags" aria-hidden="true"></i>
                <span class="d-block fw-semibold text-body">No {{ strtolower($title) }} categories yet</span>
                <span class="small">Add one to make it available to students.</span>
            </li>
        @endforelse
    </ul>
</section>
