@extends('admin.layouts.master')
@section('title', 'لوحة التحكم')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'لوحة التحكم',
                'arr' => [],
            ])

            {{-- Cards الإحصائيات الرئيسية --}}
            <div class="row">
                {{-- المستخدمون --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>إجمالي المستخدمين</h5>
                            <h3>{{ $totalUsers }}</h3>
                            <small>
                                مستأجرون: {{ $rentersCount }} |
                                أصحاب شقق: {{ $ownersCount }} |
                                مدراء: {{ $adminsCount }}
                            </small>
                            <br>
                            <small>جديد (شهر): {{ $newUsersLastMonth }} | جديد (أسبوع): {{ $newUsersLastWeek }}</small>
                        </div>
                    </div>
                </div>

                {{-- الشقق --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>إجمالي الشقق</h5>
                            <h3>{{ $totalApartments }}</h3>
                            <small>
                                نشط: {{ $activeApartments }} |
                                غير نشط: {{ $inactiveApartments }}
                            </small>
                            <br>
                            <small>جديد (شهر): {{ $newApartmentsLastMonth }} | جديد (أسبوع):
                                {{ $newApartmentsLastWeek }}</small>
                        </div>
                    </div>
                </div>

                {{-- الحجوزات --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>إجمالي الحجوزات</h5>
                            <h3>{{ $totalBookings }}</h3>
                            <small>
                                قيد الانتظار: {{ $pendingBookings }} |
                                مؤكدة: {{ $confirmedBookings }} |
                                مكتملة: {{ $completedBookings }}
                            </small>
                            <br>
                            <small>جديد (شهر): {{ $newBookingsLastMonth }} | جديد (أسبوع):
                                {{ $newBookingsLastWeek }}</small>
                        </div>
                    </div>
                </div>

                {{-- الإيرادات --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>إجمالي الإيرادات</h5>
                            <h3>{{ number_format($totalRevenue, 2) }} SYP</h3>
                            <small>
                                آخر شهر: {{ number_format($revenueLastMonth, 2) }} SYP
                            </small>
                            <br>
                            <small>آخر أسبوع: {{ number_format($revenueLastWeek, 2) }} SYP</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cards إضافية --}}
            <div class="row mt-3">
                {{-- التقييمات --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5>التقييمات</h5>
                            <h3>{{ $totalReviews }}</h3>
                            <small>متوسط التقييم: {{ number_format($averageRating, 1) }}/5</small>
                            <br>
                            <small>جديد (شهر): {{ $newReviewsLastMonth }}</small>
                        </div>
                    </div>
                </div>

                {{-- أصحاب الشقق --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5>أصحاب الشقق</h5>
                            <h3>{{ $pendingOwners + $approvedOwners + $rejectedOwners }}</h3>
                            <small>
                                قيد الانتظار: {{ $pendingOwners }} |
                                موافق: {{ $approvedOwners }} |
                                مرفوض: {{ $rejectedOwners }}
                            </small>
                        </div>
                    </div>
                </div>

                {{-- الحجوزات الملغاة --}}
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h5>الحجوزات الملغاة</h5>
                            <h3>{{ $cancelledBookings }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الرسوم البيانية --}}
            <div class="row mt-4">
                {{-- رسم بياني أسبوعي --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">الحجوزات والإيرادات الأسبوعية</h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- رسم بياني شهري --}}
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">الإحصائيات الشهرية (آخر 12 شهر)</h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- أفضل الشقق --}}
            @if ($topRatedApartments->isNotEmpty())
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">أفضل الشقق تقييماً</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>العنوان</th>
                                                <th>التقييم</th>
                                                <th>السعر</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topRatedApartments as $index => $apartment)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $apartment->title }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-success">{{ number_format($apartment->rating_avg, 1) }}/5</span>
                                                    </td>
                                                    <td>{{ number_format($apartment->price, 2) }} SYP</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- الحجوزات القادمة --}}
            @if ($upcomingBookings->isNotEmpty())
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">الحجوزات القادمة (الأسبوع القادم)</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>المستأجر</th>
                                                <th>الشقة</th>
                                                <th>تاريخ البداية</th>
                                                <th>تاريخ النهاية</th>
                                                <th>السعر الإجمالي</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($upcomingBookings as $index => $booking)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $booking->renter->first_name }}
                                                        {{ $booking->renter->last_name }}</td>
                                                    <td>{{ $booking->apartment->title }}</td>
                                                    <td>{{ $booking->start_date->format('Y-m-d') }}</td>
                                                    <td>{{ $booking->end_date->format('Y-m-d') }}</td>
                                                    <td>{{ number_format($booking->total_price, 2) }} SYP</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
    <script>
        // الانتظار حتى يتم تحميل Chart.js
        function initCharts() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                setTimeout(initCharts, 100);
                return;
            }

            // الرسم البياني الأسبوعي
            const weeklyChartEl = document.getElementById('weeklyChart');
            if (weeklyChartEl) {
                const weeklyLabels = @json($chartLabels ?? []);
                const weeklyBookingsData = @json($chartBookingsData ?? []);
                const weeklyRevenueData = @json($chartRevenueData ?? []);

                // التحقق من وجود البيانات
                if (weeklyLabels && Array.isArray(weeklyLabels) && weeklyLabels.length > 0 &&
                    weeklyBookingsData && Array.isArray(weeklyBookingsData) && weeklyBookingsData.length > 0 &&
                    weeklyRevenueData && Array.isArray(weeklyRevenueData) && weeklyRevenueData.length > 0) {
                    try {
                        const weeklyCtx = weeklyChartEl.getContext('2d');
                        if (!weeklyCtx) {
                            console.error('Could not get 2d context for weekly chart');
                            return;
                        }

                        // التأكد من أن البيانات لها نفس الطول
                        const minLength = Math.min(weeklyLabels.length, weeklyBookingsData.length, weeklyRevenueData
                            .length);
                        const finalLabels = weeklyLabels.slice(0, minLength);
                        const finalBookingsData = weeklyBookingsData.slice(0, minLength);
                        const finalRevenueData = weeklyRevenueData.slice(0, minLength);

                        const weeklyChart = new Chart(weeklyCtx, {
                            type: 'line',
                            data: {
                                labels: finalLabels,
                                datasets: [{
                                    label: 'عدد الحجوزات',
                                    data: finalBookingsData,
                                    borderColor: '#007bff',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                    fill: true,
                                    tension: 0.3
                                }, {
                                    label: 'الإيرادات (SYP)',
                                    data: finalRevenueData,
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    fill: true,
                                    tension: 0.3,
                                    yAxisID: 'y1'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        enabled: true,
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        position: 'left',
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        beginAtZero: true,
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                    }
                                },
                                onHover: function(event, elements) {
                                    if (event && event.native && event.native.target) {
                                        event.native.target.style.cursor = elements && elements.length > 0 ?
                                            'pointer' : 'default';
                                    }
                                }
                            }
                        });

                        // حفظ المرجع للرسم البياني
                        window.weeklyChart = weeklyChart;
                    } catch (error) {
                        console.error('Error creating weekly chart:', error);
                    }
                } else {
                    console.warn('Weekly chart data is missing or invalid', {
                        labels: weeklyLabels,
                        bookings: weeklyBookingsData,
                        revenue: weeklyRevenueData
                    });
                }
            }

            // الرسم البياني الشهري
            const monthlyChartEl = document.getElementById('monthlyChart');
            if (monthlyChartEl) {
                const monthsLabels = @json($monthsLabels ?? []);
                const usersMonthlyData = @json($usersMonthlyData ?? []);
                const apartmentsMonthlyData = @json($apartmentsMonthlyData ?? []);
                const bookingsMonthlyData = @json($bookingsMonthlyData ?? []);
                const revenueMonthlyData = @json($revenueMonthlyData ?? []);

                // التحقق من وجود البيانات
                if (monthsLabels && Array.isArray(monthsLabels) && monthsLabels.length > 0 &&
                    usersMonthlyData && Array.isArray(usersMonthlyData) &&
                    apartmentsMonthlyData && Array.isArray(apartmentsMonthlyData) &&
                    bookingsMonthlyData && Array.isArray(bookingsMonthlyData) &&
                    revenueMonthlyData && Array.isArray(revenueMonthlyData)) {
                    try {
                        const monthlyCtx = monthlyChartEl.getContext('2d');
                        if (!monthlyCtx) {
                            console.error('Could not get 2d context for monthly chart');
                            return;
                        }

                        // التأكد من أن البيانات لها نفس الطول
                        const minLength = Math.min(
                            monthsLabels.length,
                            usersMonthlyData.length,
                            apartmentsMonthlyData.length,
                            bookingsMonthlyData.length,
                            revenueMonthlyData.length
                        );
                        const finalMonthsLabels = monthsLabels.slice(0, minLength);
                        const finalUsersData = usersMonthlyData.slice(0, minLength);
                        const finalApartmentsData = apartmentsMonthlyData.slice(0, minLength);
                        const finalBookingsData = bookingsMonthlyData.slice(0, minLength);
                        const finalRevenueData = revenueMonthlyData.slice(0, minLength);

                        const monthlyChart = new Chart(monthlyCtx, {
                            type: 'line',
                            data: {
                                labels: finalMonthsLabels,
                                datasets: [{
                                    label: 'المستخدمون الجدد',
                                    borderColor: '#6861ce',
                                    pointBorderColor: '#FFF',
                                    pointBackgroundColor: '#443f8b',
                                    pointBorderWidth: 2,
                                    pointHoverRadius: 4,
                                    pointHoverBorderWidth: 1,
                                    pointRadius: 4,
                                    backgroundColor: 'transparent',
                                    fill: true,
                                    borderWidth: 2,
                                    data: finalUsersData,
                                }, {
                                    label: 'الشقق الجديدة',
                                    borderColor: '#48abf7',
                                    pointBorderColor: '#FFF',
                                    pointBackgroundColor: '#2d6d9e',
                                    pointBorderWidth: 2,
                                    pointHoverRadius: 4,
                                    pointHoverBorderWidth: 1,
                                    pointRadius: 4,
                                    backgroundColor: 'transparent',
                                    fill: true,
                                    borderWidth: 2,
                                    data: finalApartmentsData,
                                }, {
                                    label: 'الحجوزات الجديدة',
                                    borderColor: '#31ce36',
                                    pointBorderColor: '#FFF',
                                    pointBackgroundColor: '#258228',
                                    pointBorderWidth: 2,
                                    pointHoverRadius: 4,
                                    pointHoverBorderWidth: 1,
                                    pointRadius: 4,
                                    backgroundColor: 'transparent',
                                    fill: true,
                                    borderWidth: 2,
                                    data: finalBookingsData,
                                }, {
                                    label: 'الإيرادات (SYP)',
                                    borderColor: '#ffad46',
                                    pointBorderColor: '#FFF',
                                    pointBackgroundColor: '#b88546',
                                    pointBorderWidth: 2,
                                    pointHoverRadius: 4,
                                    pointHoverBorderWidth: 1,
                                    pointRadius: 4,
                                    backgroundColor: 'transparent',
                                    fill: true,
                                    borderWidth: 2,
                                    data: finalRevenueData,
                                    yAxisID: 'y1'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    intersect: false,
                                    mode: 'index',
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        enabled: true,
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        position: 'left',
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        beginAtZero: true,
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                    }
                                },
                                layout: {
                                    padding: {
                                        left: 15,
                                        right: 15,
                                        top: 15,
                                        bottom: 15
                                    },
                                },
                                onHover: function(event, elements) {
                                    if (event && event.native && event.native.target) {
                                        event.native.target.style.cursor = elements && elements.length > 0 ?
                                            'pointer' : 'default';
                                    }
                                }
                            }
                        });

                        // حفظ المرجع للرسم البياني
                        window.monthlyChart = monthlyChart;
                    } catch (error) {
                        console.error('Error creating monthly chart:', error);
                    }
                } else {
                    console.warn('Monthly chart data is missing or invalid', {
                        labels: monthsLabels,
                        users: usersMonthlyData,
                        apartments: apartmentsMonthlyData,
                        bookings: bookingsMonthlyData,
                        revenue: revenueMonthlyData
                    });
                }
            }
        }

        // الانتظار حتى يتم تحميل الصفحة و Chart.js
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // الانتظار قليلاً للتأكد من تحميل Chart.js
                setTimeout(initCharts, 100);
            });
        } else {
            // الصفحة محملة بالفعل
            setTimeout(initCharts, 100);
        }
    </script>
@endpush
