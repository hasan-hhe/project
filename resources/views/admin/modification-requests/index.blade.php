@extends('admin.layouts.master')
@section('title', 'طلبات التعديل')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'طلبات التعديل',
                'arr' => [['title' => 'طلبات التعديل', 'link' => route('admin.modification-requests.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة طلبات التعديل</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search and Filter Form --}}
                        <form method="GET" action="{{ route('admin.modification-requests.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">البحث</label>
                                        <input type="text" name="search" id="search" class="form-control"
                                            placeholder="ابحث بالاسم أو رقم الهاتف" value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">بحث</button>
                                            <a href="{{ route('admin.modification-requests.index') }}"
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
                                        <th>المستخدم</th>
                                        <th>نوع الطلب</th>
                                        <th>السبب</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الطلب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $i => $request)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.modification-requests.show', $request->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>
                                                {{ $request->requestedBy->first_name }}
                                                {{ $request->requestedBy->last_name }}
                                                <br>
                                                <small class="text-muted">{{ $request->requestedBy->phone_number }}</small>
                                            </td>
                                            <td>
                                                @if ($request->booking)
                                                    <a href="{{ route('admin.bookings.show', $request->booking_id) }}">
                                                        حجز #{{ $request->booking_id }}
                                                    </a>
                                                @else
                                                    ---
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($request->reason ?? $request->comment, 50) ?? '---' }}</td>
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
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                                    {{ $statusLabels[$request->status] ?? $request->status }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.modification-requests.show',
                                                                $request->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                    ],
                                                    'withDelete' => false,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $requests])
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
