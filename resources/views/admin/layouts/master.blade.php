<!DOCTYPE html>
<html lang="ar" style="overflow-x: hidden;">

@include('admin.layouts.head')

<body dir="rtl">

    <!-- Page Wrapper -->
    <div class="wrapper">

        <!-- Sidebar -->
        @include('admin.layouts.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div class="main-panel">

            <!-- Topbar -->
            @include('admin.layouts.header')
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            @yield('main-content')
            <!-- /.container-fluid -->

            <!-- End of Main Content -->
            @include('admin.layouts.footer')
        </div>

    </div>

    <!-- صفحة التحميل -->
    <div id="loader">

    </div>

    <div id="loader-new" class="loader-new">
        <div class="inner one"></div>
        <div class="inner two"></div>
        <div class="inner three"></div>
    </div>



    @include('admin.layouts.scripts')
</body>

</html>
