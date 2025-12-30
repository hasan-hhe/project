@extends('admin.layouts.master')
@section('title', 'عرض مستخدم')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'عرض مستخدم',
                'arr' => [
                    ['title' => 'المستخدمون', 'link' => route('admin.users.index')],
                    ['title' => 'عرض مستخدم', 'link' => route('admin.users.show', $user->id)],
                ],
            ])

            <div class="row">
                {{-- معلومات المستخدم --}}
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">معلومات المستخدم</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped text-center">
                                <tbody>
                                    <tr>
                                        <th>الاسم الأول</th>
                                        <td>{{ $user->first_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>الاسم الأخير</th>
                                        <td>{{ $user->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>رقم الهاتف</th>
                                        <td>{{ $user->phone_number ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>البريد الإلكتروني</th>
                                        <td>{{ $user->email ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الميلاد</th>
                                        <td>{{ $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>نوع الحساب</th>
                                        <td>
                                            @php
                                                $accountTypeLabels = [
                                                    'RENTER' => 'مستأجر',
                                                    'OWNER' => 'صاحب شقة',
                                                    'ADMIN' => 'مدير',
                                                ];
                                            @endphp
                                            <span class="badge bg-primary">
                                                {{ $accountTypeLabels[$user->account_type] ?? $user->account_type }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($user->account_type == 'OWNER')
                                    <tr>
                                        <th>حالة الحساب</th>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'PENDING' => 'warning',
                                                    'APPROVED' => 'success',
                                                    'REJECTED' => 'danger',
                                                ];
                                                $statusLabels = [
                                                    'PENDING' => 'قيد الانتظار',
                                                    'APPROVED' => 'موافق عليه',
                                                    'REJECTED' => 'مرفوض',
                                                ];
                                                $status = $user->status ?? 'PENDING';
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                                {{ $statusLabels[$status] ?? $status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- الصور --}}
                <div class="col-md-6">
                    @if($user->avatar_url)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">الصورة الشخصية</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset($user->avatar_url) }}" alt="الصورة الشخصية" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    @endif

                    @if($user->identity_docomunt_url)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">صورة الهوية</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset($user->identity_docomunt_url) }}" alt="صورة الهوية" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- الشقق (لأصحاب الشقق) --}}
            @if($user->account_type == 'OWNER' && isset($apartments) && $apartments->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">شقق صاحب الشقة ({{ $apartments->count() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table display table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>العنوان</th>
                                    <th>السعر</th>
                                    <th>عدد الغرف</th>
                                    <th>التقييم</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apartments as $index => $apartment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $apartment->title }}</td>
                                    <td>{{ number_format($apartment->price, 2) }} SYP</td>
                                    <td>{{ $apartment->rooms_count }}</td>
                                    <td>{{ $apartment->rating_avg ?? '---' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $apartment->is_active ? 'success' : 'secondary' }}">
                                            {{ $apartment->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- الحجوزات (للمستأجرين) --}}
            @if($user->account_type == 'RENTER' && isset($bookings) && $bookings->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">حجوزات المستأجر ({{ $bookings->count() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table display table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الشقة</th>
                                    <th>تاريخ البداية</th>
                                    <th>تاريخ النهاية</th>
                                    <th>السعر الإجمالي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $index => $booking)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $booking->apartment->title }}</td>
                                    <td>{{ $booking->start_date->format('Y-m-d') }}</td>
                                    <td>{{ $booking->end_date->format('Y-m-d') }}</td>
                                    <td>{{ number_format($booking->total_price, 2) }} SYP</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'PENDING' => 'warning',
                                                'CONFIRMED' => 'success',
                                                'CANCLED' => 'danger',
                                                'COMPLETED' => 'info',
                                            ];
                                            $statusLabels = [
                                                'PENDING' => 'قيد الانتظار',
                                                'CONFIRMED' => 'مؤكدة',
                                                'CANCLED' => 'ملغاة',
                                                'COMPLETED' => 'مكتملة',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$booking->status] ?? $booking->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="row mt-3">
                <div class="col-12">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">تعديل</a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush
