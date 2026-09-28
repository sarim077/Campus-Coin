@extends('layouts.app')

@section('title', 'Saving-tip templates - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin Portal</a></li>
<li class="breadcrumb-item active">Saving-tip templates</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">System saving-tip templates</h1>
        <p class="text-secondary mb-0">Active templates are added to each student's saving tips when they recalculate tips.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold">Create a template</h2>
        <form action="{{ route('admin.tip-templates.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control" maxlength="255" required></div>
            <div class="col-md-3"><label class="form-label">Expense category</label><select name="category_id" class="form-select"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Estimated monthly savings</label><input type="number" min="0" step="0.01" name="potential_savings" class="form-control" value="0" required></div>
            <div class="col-12"><label class="form-label">Tip message</label><textarea name="body" class="form-control" rows="3" maxlength="5000" required></textarea></div>
            <div class="col-12"><button class="btn btn-success">Create template</button></div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($templates as $template)
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.tip-templates.update', $template) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-5"><label class="form-label">Title</label><input name="title" class="form-control" maxlength="255" value="{{ $template->title }}" required></div>
                        <div class="col-md-3"><label class="form-label">Expense category</label><select name="category_id" class="form-select"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($template->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                        <div class="col-md-2"><label class="form-label">Monthly savings</label><input type="number" min="0" step="0.01" name="potential_savings" class="form-control" value="{{ $template->potential_savings }}" required></div>
                        <div class="col-md-2"><label class="form-label">Status</label><select name="is_active" class="form-select"><option value="1" @selected($template->is_active)>Active</option><option value="0" @selected(!$template->is_active)>Paused</option></select></div>
                        <div class="col-12"><label class="form-label">Tip message</label><textarea name="body" class="form-control" rows="3" maxlength="5000" required>{{ $template->body }}</textarea></div>
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-outline-success">Save changes</button>
                        </div>
                    </form>
                    <form action="{{ route('admin.tip-templates.destroy', $template) }}" method="POST" class="mt-2" onsubmit="return confirm('Remove this template? Existing student copies will remain.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Remove template</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-secondary">No saving-tip templates have been created.</div></div>
    @endforelse
</div>
@endsection
