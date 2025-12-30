@extends('admin.layouts.master')
@section('title', 'عرض شقة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'عرض شقة',
                'arr' => [
                    ['title' => 'الشقق', 'link' => route('admin.apartments.index')],
                    ['title' => 'عرض شقة', 'link' => route('admin.apartments.show', $apartment->id)],
                ],
            ])

            <div class="row">
                {{-- معلومات الشقة --}}
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">معلومات الشقة</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped text-center">
                                <tbody>
                                    <tr>
                                        <th>العنوان</th>
                                        <td>{{ $apartment->title }}</td>
                                    </tr>
                                    <tr>
                                        <th>الوصف</th>
                                        <td>{{ $apartment->description }}</td>
                                    </tr>
                                    <tr>
                                        <th>صاحب الشقة</th>
                                        <td>
                                            <a href="{{ route('admin.users.show', $apartment->owner_id) }}">
                                                {{ $apartment->owner->first_name }} {{ $apartment->owner->last_name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>السعر</th>
                                        <td>{{ number_format($apartment->price, 2) }} SYP</td>
                                    </tr>
                                    <tr>
                                        <th>عدد الغرف</th>
                                        <td>{{ $apartment->rooms_count }}</td>
                                    </tr>
                                    <tr>
                                        <th>عنوان الشارع</th>
                                        <td>{{ $apartment->address_line }}</td>
                                    </tr>
                                    <tr>
                                        <th>التقييم</th>
                                        <td>{{ $apartment->rating_avg ?? '---' }}/5</td>
                                    </tr>
                                    <tr>
                                        <th>الحالة</th>
                                        <td>
                                            <span class="badge bg-{{ $apartment->is_active ? 'success' : 'secondary' }}">
                                                {{ $apartment->is_active ? 'نشط' : 'غير نشط' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الحجوزات --}}
            @if($apartment->bookings->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">حجوزات الشقة ({{ $apartment->bookings->count() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table display table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستأجر</th>
                                    <th>تاريخ البداية</th>
                                    <th>تاريخ النهاية</th>
                                    <th>السعر الإجمالي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apartment->bookings as $index => $booking)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $booking->renter_id) }}">
                                            {{ $booking->renter->first_name }} {{ $booking->renter->last_name }}
                                        </a>
                                    </td>
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

            {{-- التقييمات --}}
            @if($apartment->reviews->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">تقييمات الشقة ({{ $apartment->reviews->count() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table display table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>التقييم</th>
                                    <th>التعليق</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apartment->reviews as $index => $review)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $review->user->first_name }} {{ $review->user->last_name }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $review->rating }}/5</span>
                                    </td>
                                    <td>{{ $review->comment ?? '---' }}</td>
                                    <td>{{ $review->created_at->format('Y-m-d') }}</td>
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
                    <a href="{{ route('admin.apartments.edit', $apartment->id) }}" class="btn btn-primary">تعديل</a>
                    <a href="{{ route('admin.apartments.index') }}" class="btn btn-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush

