<div class="main-header">
    <div class="main-header-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('admin.dashboard.index') }}" class="logo">
                <img src="{{ asset('assets/img/logo.png') }}" alt="navbar brand" class="navbar-brand" height="40px" />
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
    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
        <div class="container-fluid">
            <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="submit" class="btn btn-search pe-1">
                            <i class="fa fa-search search-icon"></i>
                        </button>
                    </div>
                    <input type="text" placeholder="ابحث ..." class="form-control" />
                </div>
            </nav>

            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                        aria-expanded="false" aria-haspopup="true">
                        <i class="fa fa-search"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-search animated fadeIn">
                        <form class="navbar-left navbar-form nav-search">
                            <div class="input-group">
                                <input type="text" placeholder="ابحث ..." class="form-control" />
                            </div>
                        </form>
                    </ul>
                </li>

                <!-- Notifications -->
                <li class="nav-item topbar-icon dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell"></i>
                        <span class="notification">{{ count($notificationsNav) }}</span>
                    </a>
                    <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                        <li>
                            <div class="dropdown-title d-flex justify-content-between align-items-center">
                                الإشعارات <br>
                                <a href="{{ route('admin.notifications.mark-all') }}" class="small">رؤية الكل</a>
                            </div>
                        </li>
                        <li>
                            <div class="notif-scroll scrollbar-outer">
                                <div class="notif-center" id="notifications">
                                    @foreach ($notificationsNav as $notification)
                                        <a href="{{ route('admin.notifications.edit', $notification->id) }}">
                                            <div class="notif-icon notif-primary">
                                                <i class="fa fa-user" style="padding: 15px;"></i>
                                            </div>
                                            <div class="notif-content">
                                                <span class="block">{{ $notification->title }}</span>
                                                <span class="block">{{ $notification->body }}</span>
                                                <span
                                                    class="time">{{ $notification->created_at->format('h:i:s') }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="see-all" href="{{ route('admin.notifications.index') }}">رؤية كل الإشعارات<i
                                    class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- Profile -->
                <li class="nav-item topbar-user dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                        aria-expanded="false">
                        <div class="avatar-sm">
                            <img src="{{ asset('assets/img/default.png') }}" alt="..."
                                class="avatar-img rounded-circle" />
                        </div>
                        <span class="profile-username">
                            <span class="op-7">مرحبا,</span>
                            <span class="fw-bold">{{ auth()->user()->first_name }}
                                {{ auth()->user()->last_name }}</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <div class="dropdown-user-scroll scrollbar-outer">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        <img src="{{ asset('assets/img/default.png') }}" alt="image profile"
                                            class="avatar-img rounded" />
                                    </div>
                                    <div class="u-text">
                                        <h4>{{ auth()->user()->name }}</h4>
                                        <p class="text-muted">{{ auth()->user()->phone }}</p>
                                        <a href="{{ route('admin.users.edit', auth()->id()) }}"
                                            class="btn btn-xs btn-secondary btn-sm">رؤية الملف الشخصي</a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('admin.users.edit', auth()->id()) }}"> <i
                                        class="fa fa-user-shield me-1"></i>ملفي الشخصي</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('admin.auth.logout') }}"><i
                                        class="fa fa-sign-out-alt"></i> تسجيل الخروج</a>
                            </li>
                        </div>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
</div>
