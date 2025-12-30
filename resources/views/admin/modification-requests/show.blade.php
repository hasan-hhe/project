@extends('admin.layouts.master')
@section('title', 'تفاصيل طلب التعديل')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تفاصيل طلب التعديل',
                'arr' => [
                    ['title' => 'طلبات التعديل', 'link' => route('admin.modification-requests.index')],
                    ['title' => 'تفاصيل الطلب', 'link' => route('admin.modification-requests.show', $modificationRequest->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">معلومات الطلب</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المستخدم:</label>
                                    <p class="form-control-static">
                                        {{ $modificationRequest->requestedBy->first_name }} {{ $modificationRequest->requestedBy->last_name }}
                                        <br>
                                        <small class="text-muted">{{ $modificationRequest->requestedBy->phone_number }}</small>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحجز:</label>
                                    <p class="form-control-static">
                                        @if ($modificationRequest->booking)
                                            <a href="{{ route('admin.bookings.show', $modificationRequest->booking_id) }}">
                                                حجز #{{ $modificationRequest->booking_id }}
                                            </a>
                                        @else
                                            ---
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة:</label>
                                    <p class="form-control-static">
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
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$modificationRequest->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$modificationRequest->status] ?? $modificationRequest->status }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تاريخ الطلب:</label>
                                    <p class="form-control-static">{{ $modificationRequest->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                            @if ($modificationRequest->reason || $modificationRequest->comment)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>السبب/التعليق:</label>
                                        <p class="form-control-static">{{ $modificationRequest->reason ?? $modificationRequest->comment }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($modificationRequest->admin_comment)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>تعليق المدير:</label>
                                        <p class="form-control-static">{{ $modificationRequest->admin_comment }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($modificationRequest->reviewed_by)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>تمت المراجعة من قبل:</label>
                                        <p class="form-control-static">
                                            {{ $modificationRequest->reviewer->first_name ?? '---' }}
                                            {{ $modificationRequest->reviewer->last_name ?? '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>تاريخ المراجعة:</label>
                                        <p class="form-control-static">
                                            {{ $modificationRequest->reviewed_at ? $modificationRequest->reviewed_at->format('Y-m-d H:i') : '---' }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        <h5>التعديلات المطلوبة</h5>
                        <div class="row">
                            @if ($modificationRequest->request_type == 'BOOKING' && $modificationRequest->booking)
                                <div class="col-md-6">
                                    <h6>التواريخ الحالية:</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <p><strong>تاريخ البداية:</strong> {{ $modificationRequest->booking->start_date->format('Y-m-d') }}</p>
                                            <p><strong>تاريخ النهاية:</strong> {{ $modificationRequest->booking->end_date->format('Y-m-d') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>التواريخ الجديدة:</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <p><strong>تاريخ البداية:</strong> {{ $modificationRequest->new_start_date ? $modificationRequest->new_start_date->format('Y-m-d') : '---' }}</p>
                                            <p><strong>تاريخ النهاية:</strong> {{ $modificationRequest->new_end_date ? $modificationRequest->new_end_date->format('Y-m-d') : '---' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if ($modificationRequest->old_data)
                                    <div class="col-md-6">
                                        <h6>البيانات القديمة:</h6>
                                        <div class="card">
                                            <div class="card-body">
                                                <pre class="mb-0">{{ json_encode($modificationRequest->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($modificationRequest->new_data)
                                    <div class="col-md-6">
                                        <h6>البيانات الجديدة:</h6>
                                        <div class="card">
                                            <div class="card-body">
                                                <pre class="mb-0">{{ json_encode($modificationRequest->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        @if ($modificationRequest->status == 'PENDING')
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>الإجراءات</h5>
                                    <form action="{{ route('admin.modification-requests.approve', $modificationRequest->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label for="admin_comment">تعليق (اختياري):</label>
                                            <textarea name="admin_comment" id="admin_comment" class="form-control" rows="3"
                                                placeholder="أدخل تعليق..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-check"></i> الموافقة
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        <i class="fa fa-times"></i> رفض
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($modificationRequest->status == 'PENDING')
        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">رفض طلب التعديل</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.modification-requests.reject', $modificationRequest->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="admin_comment">سبب الرفض:</label>
                                <textarea name="admin_comment" id="admin_comment" class="form-control" rows="3"
                                    placeholder="أدخل سبب الرفض..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger">رفض</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush

