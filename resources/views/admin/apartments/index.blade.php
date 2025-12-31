@extends('admin.layouts.master')
@section('title', 'الشقق')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'الشقق',
                'arr' => [['title' => 'الشقق', 'link' => route('admin.apartments.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة الشقق</h4>
                            @include('admin.components.buttons', [
                                'buttons' => [
                                    [
                                        'type' => 'href',
                                        'url' => route('admin.apartments.create'),
                                        'icon' => 'fa-plus',
                                        'text' => 'إضافة شقة',
                                    ],
                                ],
                                'withDeleteChecked' => 1,
                                'withDeleteAll' => 1,
                                'urlDeleteAll' => route('admin.apartments.destroy-check'),
                            ])
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search and Filter Form --}}
                        <form method="GET" action="{{ route('admin.apartments.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        @include('admin.components.input', [
                                            'type' => 'text',
                                            'name' => 'search',
                                            'id' => 'search',
                                            'label' => 'البحث',
                                            'value' => request('search'),
                                        ])
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        @include('admin.components.select', [
                                            'selectedId' => 'status',
                                            'nameForm' => 'status',
                                            'label' => 'الحالة',
                                            'items' => [
                                                (object) ['id' => 'active', 'name' => 'نشط'],
                                                (object) ['id' => 'inactive', 'name' => 'غير نشط'],
                                                (object) ['id' => '', 'name' => 'جميع الحالات'],
                                            ],
                                            'valueSelected' => request('status'),
                                            'name' => 'name',
                                            'attr' => 'id',
                                        ])
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        @include('admin.components.select', [
                                            'selectedId' => 'owner_id',
                                            'nameForm' => 'owner_id',
                                            'label' => 'صاحب الشقة',
                                            'items' => $owners,
                                            'name' => 'first_name',
                                            'attr' => 'id',
                                            'valueSelected' => request('owner_id'),
                                        ])
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">بحث</button>
                                            <a href="{{ route('admin.apartments.index') }}" class="btn btn-secondary">إعادة
                                                تعيين</a>
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
                                        <th>العنوان</th>
                                        <th>صاحب الشقة</th>
                                        <th>السعر</th>
                                        <th>عدد الغرف</th>
                                        <th>التقييم</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($apartments as $i => $apartment)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.apartments.show', $apartment->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $apartment->title }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $apartment->owner_id) }}">
                                                    {{ $apartment->owner->first_name }} {{ $apartment->owner->last_name }}
                                                </a>
                                            </td>
                                            <td>{{ number_format($apartment->price, 2) }} SYP</td>
                                            <td>{{ $apartment->rooms_count }}</td>
                                            <td>{{ $apartment->rating_avg ?? '---' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $apartment->is_active ? 'success' : 'secondary' }}">
                                                    {{ $apartment->is_active ? 'نشط' : 'غير نشط' }}
                                                </span>
                                            </td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.apartments.show',
                                                                $apartment->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.apartments.photos.index',
                                                                $apartment->id),
                                                            'icon' => 'fa-images',
                                                            'text' => 'الصور',
                                                            'class' => 'text-primary',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.apartments.edit',
                                                                $apartment->id),
                                                            'icon' => 'fa-edit',
                                                            'text' => 'تعديل',
                                                            'class' => 'text-secondary',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.apartments.toggle-active',
                                                                $apartment->id),
                                                            'icon' => 'icon-refresh',
                                                            'text' => 'تحديث',
                                                            'class' => 'text-warning',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route(
                                                        'admin.apartments.destroy',
                                                        $apartment->id),
                                                    'itemId' => $apartment->id,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $apartments])
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
