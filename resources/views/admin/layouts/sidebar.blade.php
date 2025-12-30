<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="" class="logo d-none d-lg-flex">
                <img src="{{ asset('assets/img/logo.png') }}" alt="navbar brand" class="navbar-brand" height="180px"
                    style="margin-right: -35px !important;margin-top: 50px !important;" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner mt-5">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard.index') }}">
                        <i class="fas fa-chart-line"></i>
                        <p>لوحة التحكم</p>
                    </a>
                </li>

                <li class="nav-item
                    {{ Route::is('admin.users.index') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#usersCollapse">
                        <i class="fas fa-user-friends"></i>
                        <p>المستخدمون</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="usersCollapse">
                        <ul class="nav nav-collapse">
                            <li class="{{ request('type') == 'renter' ? 'active' : '' }}">
                                <a href="{{ route('admin.users.index', ['type' => 'renter']) }}">
                                    <span class="sub-item">المستأجرون</span>
                                </a>
                            </li>
                            <li class="{{ request('type') == 'owner' ? 'active' : '' }}">
                                <a href="{{ route('admin.users.index', ['type' => 'owner']) }}">
                                    <span class="sub-item">أصحاب الشقق</span>
                                </a>
                            </li>
                            <li class="{{ request('type') == 'admin' ? 'active' : '' }}">
                                <a href="{{ route('admin.users.index', ['type' => 'admin']) }}">
                                    <span class="sub-item">المدراء</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item {{ Request::is('admin/apartments*') ? 'active' : '' }}">
                    <a href="{{ route('admin.apartments.index') }}">
                        <i class="fas fa-building"></i>
                        <p>الشقق</p>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/bookings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.bookings.index') }}">
                        <i class="fas fa-calendar-check"></i>
                        <p>الحجوزات</p>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/apartment-owners*') ? 'active' : '' }}">
                    <a href="{{ route('admin.apartment-owners.index') }}">
                        <i class="fas fa-user-tie"></i>
                        <p>إدارة أصحاب الشقق</p>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/notifications*') ? 'active' : '' }}">
                    <a href="{{ route('admin.notifications.index') }}">
                        <i class="fas fa-bell"></i>
                        <p>الإشعارات</p>
                    </a>
                </li>
            </ul>

        </div>
    </div>
</div>
