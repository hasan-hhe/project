@extends('admin.layouts.master')
@section('title', 'عرض صاحب شقة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'عرض صاحب شقة',
                'arr' => [
                    ['title' => 'أصحاب الشقق', 'link' => route('admin.apartment-owners.index')],
                    ['title' => 'عرض صاحب شقة', 'link' => route('admin.apartment-owners.show', $owner->id)],
                ],
            ])

            {{-- القسم الأول: معلومات صاحب الشقة --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">معلومات صاحب الشقة</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped text-center">
                                <tbody>
                                    <tr>
                                        <th>الاسم الأول</th>
                                        <td>{{ $owner->first_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>الاسم الأخير</th>
                                        <td>{{ $owner->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>رقم الهاتف</th>
                                        <td>{{ $owner->phone_number ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>البريد الإلكتروني</th>
                                        <td>{{ $owner->email ?? '---' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الميلاد</th>
                                        <td>{{ $owner->date_of_birth ? $owner->date_of_birth->format('Y-m-d') : '---' }}</td>
                                    </tr>
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
                                                $status = $owner->status ?? 'PENDING';
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                                {{ $statusLabels[$status] ?? $status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">تغيير حالة الحساب</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.apartment-owners.update-status', $owner->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label for="status">اختر الحالة</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="PENDING" {{ ($owner->status ?? 'PENDING') == 'PENDING' ? 'selected' : '' }}>قيد الانتظار</option>
                                        <option value="APPROVED" {{ ($owner->status ?? '') == 'APPROVED' ? 'selected' : '' }}>موافق عليه</option>
                                        <option value="REJECTED" {{ ($owner->status ?? '') == 'REJECTED' ? 'selected' : '' }}>مرفوض</option>
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">تحديث الحالة</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($owner->avatar_url)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">الصورة الشخصية</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset($owner->avatar_url) }}" alt="الصورة الشخصية" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    @endif

                    @if($owner->identity_document_url)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">صورة الهوية</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset($owner->identity_document_url) }}" alt="صورة الهوية" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- القسم الثاني: قائمة الشقق --}}
            @if($apartments->isNotEmpty())
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
                                    <td>{{ $apartment->price }} {{ config('app.currency', 'SYP') }}</td>
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
            @else
            <div class="card">
                <div class="card-body">
                    <p class="text-center text-muted">لا توجد شقق مسجلة لهذا المستخدم</p>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush

