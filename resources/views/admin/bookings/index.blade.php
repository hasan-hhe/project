@extends('admin.layouts.master')
@section('title', 'الحجوزات')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'الحجوزات',
                'arr' => [
                    ['title' => 'الحجوزات', 'link' => route('admin.bookings.index')],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة الحجوزات</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search and Filter Form --}}
                        <form method="GET" action="{{ route('admin.bookings.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">البحث</label>
                                        <input type="text" 
                                               name="search" 
                                               id="search" 
                                               class="form-control" 
                                               placeholder="ابحث بالاسم أو الشقة"
                                               value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">فلترة حسب الحالة</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">جميع الحالات</option>
                                            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>قيد الانتظار</option>
                                            <option value="CONFIRMED" {{ request('status') == 'CONFIRMED' ? 'selected' : '' }}>مؤكدة</option>
                                            <option value="CANCLED" {{ request('status') == 'CANCLED' ? 'selected' : '' }}>ملغاة</option>
                                            <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>مكتملة</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="renter_id">فلترة حسب المستأجر</label>
                                        <select name="renter_id" id="renter_id" class="form-control">
                                            <option value="">جميع المستأجرين</option>
                                            @foreach($renters as $renter)
                                                <option value="{{ $renter->id }}" {{ request('renter_id') == $renter->id ? 'selected' : '' }}>
                                                    {{ $renter->first_name }} {{ $renter->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">بحث</button>
                                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">إعادة تعيين</a>
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
                                        <th>المستأجر</th>
                                        <th>الشقة</th>
                                        <th>تاريخ البداية</th>
                                        <th>تاريخ النهاية</th>
                                        <th>السعر الإجمالي</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $i => $booking)
                                        <tr>
                                            <td><a href="{{ route('admin.bookings.show', $booking->id) }}">{{ $i + 1 }}</a></td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $booking->renter_id) }}">
                                                    {{ $booking->renter->first_name }} {{ $booking->renter->last_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.apartments.show', $booking->apartment_id) }}">
                                                    {{ $booking->apartment->title }}
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
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.bookings.show', $booking->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route('admin.bookings.destroy', $booking->id),
                                                    'itemId' => $booking->id,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $bookings])
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

