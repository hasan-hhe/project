@extends('admin.layouts.master')
@section('title', 'شحن محفظة المستأجر')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'شحن محفظة المستأجر',
                'arr' => [
                    ['title' => 'شحن محفظة المستأجرين', 'link' => route('admin.wallet.index')],
                    ['title' => 'شحن محفظة المستأجر', 'link' => route('admin.wallet.show', $user->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">معلومات المستأجر</h4>
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
                                    <label>الرصيد الحالي:</label>
                                    <p class="form-control-static">
                                        <span class="badge bg-{{ ($user->wallet_balance ?? 0) > 0 ? 'success' : 'secondary' }} fs-4">
                                            {{ number_format($user->wallet_balance ?? 0, 2) }} SYP
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>شحن المحفظة</h5>
                        <form action="{{ route('admin.wallet.recharge', $user->id) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">المبلغ (SYP) <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" id="amount" class="form-control"
                                            step="0.01" min="0.01" required placeholder="أدخل المبلغ">
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="note">ملاحظة (اختياري)</label>
                                        <textarea name="note" id="note" class="form-control" rows="3"
                                            placeholder="أدخل ملاحظة..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-wallet"></i> شحن المحفظة
                                    </button>
                                    <a href="{{ route('admin.wallet.index') }}" class="btn btn-secondary">إلغاء</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush

