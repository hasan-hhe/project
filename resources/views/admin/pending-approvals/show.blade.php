@extends('admin.layouts.master')
@section('title', 'تفاصيل المستخدم')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تفاصيل المستخدم',
                'arr' => [
                    ['title' => 'المستخدمون الذين يحتاجون الموافقة', 'link' => route('admin.pending-approvals.index')],
                    ['title' => 'تفاصيل المستخدم', 'link' => route('admin.pending-approvals.show', $user->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">معلومات المستخدم</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الاسم الأول:</label>
                                    <p class="form-control-static">{{ $user->first_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الاسم الأخير:</label>
                                    <p class="form-control-static">{{ $user->last_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم الهاتف:</label>
                                    <p class="form-control-static">{{ $user->phone_number ?? '---' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>البريد الإلكتروني:</label>
                                    <p class="form-control-static">{{ $user->email ?? '---' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تاريخ الميلاد:</label>
                                    <p class="form-control-static">{{ $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '---' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع الحساب:</label>
                                    <p class="form-control-static">
                                        @if ($user->account_type == 'OWNER')
                                            صاحب شقة
                                        @elseif ($user->account_type == 'RENTER')
                                            مستأجر
                                        @else
                                            مدير
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>حالة الحساب:</label>
                                    <p class="form-control-static">
                                        <span class="badge bg-warning">قيد الانتظار</span>
                                    </p>
                                </div>
                            </div>
                            @if ($user->avatar_url)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الصورة الشخصية:</label>
                                        <div>
                                            <img src="{{ asset($user->avatar_url) }}" alt="Avatar" class="img-thumbnail"
                                                style="max-width: 200px;">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($user->identity_document_url)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>صورة الهوية:</label>
                                        <div>
                                            <img src="{{ asset($user->identity_document_url) }}" alt="Identity"
                                                class="img-thumbnail" style="max-width: 200px;">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>الإجراءات</h5>
                                <form action="{{ route('admin.pending-approvals.approve', $user->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">رفض حساب المستخدم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.pending-approvals.reject', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">سبب الرفض (اختياري):</label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"
                                placeholder="أدخل سبب الرفض..."></textarea>
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
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush

