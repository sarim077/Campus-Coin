<aside class="admin-sidebar" aria-label="Admin workspace navigation">
    <div class="admin-sidebar-heading">
        <span class="admin-sidebar-icon"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
        <span>
            <strong>Admin workspace</strong>
            <small>Manage Campus Coin</small>
        </span>
    </div>
    <nav class="admin-sidebar-nav">
        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
            <i class="fas fa-chart-pie" aria-hidden="true"></i><span>{{ __('ui.nav.admin_overview') }}</span>
        </a>
        <a class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}" href="{{ route('admin.categories') }}" @if(request()->routeIs('admin.categories')) aria-current="page" @endif>
            <i class="fas fa-tags" aria-hidden="true"></i><span>{{ __('ui.nav.admin_categories') }}</span>
        </a>
        <a class="{{ request()->routeIs('admin.announcements') ? 'active' : '' }}" href="{{ route('admin.announcements') }}" @if(request()->routeIs('admin.announcements')) aria-current="page" @endif>
            <i class="fas fa-bullhorn" aria-hidden="true"></i><span>{{ __('ui.nav.admin_announcements') }}</span>
        </a>
        <a class="{{ request()->routeIs('admin.tip-templates') ? 'active' : '' }}" href="{{ route('admin.tip-templates') }}" @if(request()->routeIs('admin.tip-templates')) aria-current="page" @endif>
            <i class="fas fa-lightbulb" aria-hidden="true"></i><span>Saving-tip templates</span>
        </a>
        <a class="{{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}" @if(request()->routeIs('admin.users')) aria-current="page" @endif>
            <i class="fas fa-users" aria-hidden="true"></i><span>{{ __('ui.nav.admin_users') }}</span>
        </a>
        <a class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}" @if(request()->routeIs('admin.settings')) aria-current="page" @endif>
            <i class="fas fa-sliders-h" aria-hidden="true"></i><span>Site settings</span>
        </a>
    </nav>
    <div class="admin-sidebar-footer">
        <span class="admin-sidebar-status" aria-hidden="true"></span>
        <span>System workspace</span>
    </div>
</aside>
