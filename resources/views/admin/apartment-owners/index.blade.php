@extends('admin.layouts.master')
@section('title', 'أصحاب الشقق')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'أصحاب الشقق',
                'arr' => [['title' => 'أصحاب الشقق', 'link' => route('admin.apartment-owners.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة أصحاب الشقق</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search and Filter Form --}}
                        <form method="GET" action="{{ route('admin.apartment-owners.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search">البحث</label>
                                        <input type="text" name="search" id="search" class="form-control"
                                            placeholder="ابحث بالاسم، الهاتف، أو البريد الإلكتروني"
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status">فلترة حسب الحالة</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">جميع الحالات</option>
                                            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>
                                                قيد الانتظار</option>
                                            <option value="APPROVED"
                                                {{ request('status') == 'APPROVED' ? 'selected' : '' }}>موافق عليه</option>
                                            <option value="REJECTED"
                                                {{ request('status') == 'REJECTED' ? 'selected' : '' }}>مرفوض</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">بحث</button>
                                            <a href="{{ route('admin.apartment-owners.index') }}"
                                                class="btn btn-secondary">إعادة تعيين</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table display table-striped table-hover table-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم الأول</th>
                                        <th>الاسم الأخير</th>
                                        <th>رقم الهاتف</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>عدد الشقق</th>
                                        <th>حالة الحساب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($owners as $i => $owner)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.apartment-owners.show', $owner->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $owner->first_name }}</td>
                                            <td>{{ $owner->last_name }}</td>
                                            <td>{{ $owner->phone_number ?? '---' }}</td>
                                            <td>{{ $owner->email ?? '---' }}</td>
                                            <td>{{ $owner->appartments_count ?? 0 }}</td>
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
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.apartment-owners.show',
                                                                $owner->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.users.edit', $owner->id),
                                                            'icon' => 'fa-edit',
                                                            'text' => 'تعديل',
                                                            'class' => 'text-secondary',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route('admin.users.destroy', $owner->id),
                                                    'itemId' => $owner->id,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $owners])
                            </div>
                        </div>
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
