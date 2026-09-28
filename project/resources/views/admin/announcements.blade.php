@extends('layouts.app')

@section('title', 'System Announcements - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin Portal</a></li>
<li class="breadcrumb-item active">Announcements</li>
@endsection

@section('content')
@php
    $activeAnnouncements = $announcements->where('is_active', true)->count();
    $pausedAnnouncements = $announcements->count() - $activeAnnouncements;
@endphp
<div class="admin-announcements-page">
    <section class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="admin-stat-icon admin-stat-green" style="width:48px;height:48px;font-size:1.15rem;">
                <i class="fas fa-bullhorn" aria-hidden="true"></i>
            </span>
            <div>
                <p class="text-success small fw-bold text-uppercase mb-1" style="letter-spacing:.11em;">Campus Coin administration</p>
                <h1 class="h3 fw-bold mb-1">System announcements</h1>
                <p class="text-secondary small mb-0">Publish and manage messages displayed on student dashboards.</p>
            </div>
        </div>
        <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#announcementModal">
            <i class="fas fa-plus me-2" aria-hidden="true"></i>New announcement
        </button>
    </section>

    @if($errors->any())
        <div class="alert alert-danger rounded-4" role="alert">
            <div class="fw-semibold mb-1">The announcement could not be published.</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">All announcements</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($announcements->count()) }}</div></div>
                    <span class="admin-stat-icon admin-stat-blue"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Messages created for student dashboards</span>
            </article>
        </div>
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Active broadcasts</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($activeAnnouncements) }}</div></div>
                    <span class="admin-stat-icon admin-stat-green"><i class="fas fa-broadcast-tower" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Currently visible to students</span>
            </article>
        </div>
        <div class="col-sm-6 col-xl-4">
            <article class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div><span class="small text-secondary">Paused messages</span><div class="h3 fw-bold mb-0 mt-1">{{ number_format($pausedAnnouncements) }}</div></div>
                    <span class="admin-stat-icon admin-stat-rose"><i class="fas fa-pause" aria-hidden="true"></i></span>
                </div>
                <span class="small text-secondary mt-2">Saved and ready to enable again</span>
            </article>
        </div>
    </div>

    <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Announcement library</h2>
            <p class="text-secondary small mb-0">Enable or pause a message at any time. Paused messages stay saved here.</p>
        </div>
        @if($announcements->isNotEmpty())
            <span class="small text-secondary">{{ $announcements->count() }} message{{ $announcements->count() === 1 ? '' : 's' }}</span>
        @endif
    </div>

    <div class="row g-3 g-xl-4">
        @forelse($announcements as $announcement)
            <div class="col-md-6 col-xxl-4">
                <article class="card announcement-card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <span class="announcement-icon {{ $announcement->is_active ? 'announcement-icon-active' : 'announcement-icon-paused' }}" aria-hidden="true">
                                <i class="fas {{ $announcement->is_active ? 'fa-bullhorn' : 'fa-bell-slash' }}"></i>
                            </span>
                            <span class="badge rounded-pill {{ $announcement->is_active ? 'bg-success bg-opacity-75' : 'bg-secondary bg-opacity-75' }}">
                                <span class="announcement-status-dot" aria-hidden="true"></span>
                                {{ $announcement->is_active ? 'Live to students' : 'Paused' }}
                            </span>
                        </div>
                        <h3 class="h5 fw-bold mb-2">{{ $announcement->title }}</h3>
                        <p class="announcement-content text-secondary mb-0">{{ $announcement->content }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                        <div class="announcement-meta d-flex align-items-center justify-content-between gap-3 pt-3">
                            <div class="small text-secondary">
                                <span class="d-block fw-semibold">{{ $announcement->creator?->name ?? 'Campus Coin admin' }}</span>
                                <time datetime="{{ $announcement->created_at->toIso8601String() }}">Posted {{ $announcement->created_at->diffForHumans() }}</time>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <form action="{{ route('admin.announcements.toggle', $announcement->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $announcement->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" aria-label="{{ $announcement->is_active ? 'Pause' : 'Publish' }} {{ $announcement->title }}">
                                        <i class="fas {{ $announcement->is_active ? 'fa-pause' : 'fa-play' }} me-1" aria-hidden="true"></i>{{ $announcement->is_active ? 'Pause' : 'Publish' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Delete this announcement permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $announcement->title }}" title="Delete announcement">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 text-center p-5">
                    <span class="announcement-empty-icon mx-auto mb-3"><i class="fas fa-bullhorn" aria-hidden="true"></i></span>
                    <h3 class="h5 fw-bold">No announcements yet</h3>
                    <p class="text-secondary mb-3">Create your first message to share important updates with students.</p>
                    <button type="button" class="btn btn-success rounded-pill px-4 mx-auto" data-bs-toggle="modal" data-bs-target="#announcementModal">
                        <i class="fas fa-plus me-2" aria-hidden="true"></i>Create announcement
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .admin-announcements-page .announcement-card { overflow: hidden; transition: transform .18s ease, box-shadow .18s ease; }
    .admin-announcements-page .announcement-card:hover { transform: translateY(-3px); box-shadow: 0 1rem 2rem rgba(0,0,0,.2) !important; }
    .admin-announcements-page .announcement-icon,
    .admin-announcements-page .announcement-empty-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 14px; }
    .admin-announcements-page .announcement-icon-active { color: #82BFEF; background: rgba(70, 160, 228,.11); }
    .admin-announcements-page .announcement-icon-paused { color: #AAB1B7; background: rgba(148,163,184,.12); }
    .admin-announcements-page .announcement-empty-icon { width: 54px; height: 54px; color: #82BFEF; background: rgba(70, 160, 228,.1); font-size: 1.25rem; }
    .admin-announcements-page .announcement-status-dot { display: inline-block; width: 7px; height: 7px; margin-right: 5px; border-radius: 50%; background: currentColor; vertical-align: 1px; }
    .admin-announcements-page .announcement-content { min-height: 4.5rem; line-height: 1.65; white-space: pre-line; overflow-wrap: anywhere; }
    .admin-announcements-page .announcement-meta { border-top: 1px solid var(--cc-border); }
    @media (prefers-reduced-motion: reduce) { .admin-announcements-page .announcement-card { transition: none; } .admin-announcements-page .announcement-card:hover { transform: none; } }
</style>

<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <h2 class="modal-title h5 fw-bold" id="announcementModalTitle"><i class="fas fa-bullhorn text-success me-2" aria-hidden="true"></i>New announcement</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-secondary small">This message will appear on student dashboards as soon as you publish it.</p>
                    <div class="mb-3">
                        <label for="announcement-title" class="form-label fw-semibold">Title</label>
                        <input id="announcement-title" type="text" name="title" maxlength="255" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g., Midterm budget challenge" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label for="announcement-content" class="form-label fw-semibold">Message</label>
                        <textarea id="announcement-content" name="content" class="form-control @error('content') is-invalid @enderror" rows="5" maxlength="5000" placeholder="Write a clear, helpful message for students..." required>{{ old('content') }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Keep it concise and useful. Up to 5,000 characters.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-paper-plane me-2" aria-hidden="true"></i>Publish announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('announcementModal')).show();
    });
</script>
@endif
@endsection
