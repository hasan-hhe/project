@extends('admin.layouts.master')
@section('title', 'عرض حجز')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'عرض حجز',
                'arr' => [
                    ['title' => 'الحجوزات', 'link' => route('admin.bookings.index')],
                    ['title' => 'عرض حجز', 'link' => route('admin.bookings.show', $booking->id)],
                ],
            ])

            <div class="row">
                {{-- معلومات الحجز --}}
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">معلومات الحجز</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped text-center">
                                <tbody>
                                    <tr>
                                        <th>المستأجر</th>
                                        <td>
                                            <a href="{{ route('admin.users.show', $booking->renter_id) }}">
                                                {{ $booking->renter->first_name }} {{ $booking->renter->last_name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>الشقة</th>
                                        <td>
                                            <a href="{{ route('admin.apartments.show', $booking->apartment_id) }}">
                                                {{ $booking->apartment->title }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>صاحب الشقة</th>
                                        <td>
                                            <a href="{{ route('admin.users.show', $booking->apartment->owner_id) }}">
                                                {{ $booking->apartment->owner->first_name }} {{ $booking->apartment->owner->last_name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ البداية</th>
                                        <td>{{ $booking->start_date->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ النهاية</th>
                                        <td>{{ $booking->end_date->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <th>السعر الإجمالي</th>
                                        <td>{{ number_format($booking->total_price, 2) }} SYP</td>
                                    </tr>
                                    <tr>
                                        <th>الحالة</th>
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
                                    @if($booking->status == 'CANCLED' && $booking->cancel_reason)
                                    <tr>
                                        <th>سبب الإلغاء</th>
                                        <td>{{ $booking->cancel_reason }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>تاريخ الإنشاء</th>
                                        <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- تغيير الحالة --}}
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">تغيير حالة الحجز</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label for="status">اختر الحالة</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="PENDING" {{ $booking->status == 'PENDING' ? 'selected' : '' }}>قيد الانتظار</option>
                                        <option value="CONFIRMED" {{ $booking->status == 'CONFIRMED' ? 'selected' : '' }}>مؤكدة</option>
                                        <option value="CANCLED" {{ $booking->status == 'CANCLED' ? 'selected' : '' }}>ملغاة</option>
                                        <option value="COMPLETED" {{ $booking->status == 'COMPLETED' ? 'selected' : '' }}>مكتملة</option>
                                    </select>
                                </div>
                                <div class="form-group mt-3" id="cancel_reason_section" style="display: none;">
                                    <label for="cancel_reason">سبب الإلغاء</label>
                                    <textarea name="cancel_reason" id="cancel_reason" class="form-control" rows="3">{{ old('cancel_reason', $booking->cancel_reason) }}</textarea>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">تحديث الحالة</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التقييمات --}}
            @if($booking->reviews->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تقييمات الحجز ({{ $booking->reviews->count() }})</h4>
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
                                @foreach($booking->reviews as $index => $review)
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
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
    const statusSelect = document.getElementById('status');
    const cancelReasonSection = document.getElementById('cancel_reason_section');

    function toggleCancelReason() {
        if (statusSelect.value === 'CANCLED') {
            cancelReasonSection.style.display = 'block';
        } else {
            cancelReasonSection.style.display = 'none';
        }
    }

    statusSelect.addEventListener('change', toggleCancelReason);
    toggleCancelReason(); // تشغيل عند تحميل الصفحة
</script>
@endpush

