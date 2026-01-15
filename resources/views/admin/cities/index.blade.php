@extends('admin.layouts.master')
@section('title', 'المدن')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'المدن',
                'arr' => [['title' => 'المدن', 'link' => route('admin.cities.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة المدن</h4>
                            @include('admin.components.buttons', [
                                'buttons' => [
                                    [
                                        'type' => 'href',
                                        'url' => route('admin.cities.create'),
                                        'icon' => 'fa-plus',
                                        'text' => 'إضافة مدينة',
                                    ],
                                ],
                                'withDeleteChecked' => 1,
                                'withDeleteAll' => 1,
                                'urlDeleteAll' => route('admin.cities.destroy-check'),
                            ])
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <form method="GET" action="{{ route('admin.cities.index') }}">
                                    <div class="form-group">
                                        <label>فلترة حسب المحافظة</label>
                                        <select name="governorate_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">جميع المحافظات</option>
                                            @foreach ($governorates as $governorate)
                                                <option value="{{ $governorate->id }}"
                                                    {{ request('governorate_id') == $governorate->id ? 'selected' : '' }}>
                                                    {{ $governorate->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table display table-striped table-hover table-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المدينة</th>
                                        <th>المحافظة</th>
                                        <th>عدد الشقق</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cities as $i => $city)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.cities.show', $city->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $city->name }}</td>
                                            <td>{{ $city->governorate->name ?? '---' }}</td>
                                            <td>{{ $city->apartments->count() ?? 0 }}</td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.cities.show', $city->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.cities.edit', $city->id),
                                                            'icon' => 'fa-edit',
                                                            'text' => 'تعديل',
                                                            'class' => 'text-secondary',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route('admin.cities.destroy', $city->id),
                                                    'itemId' => $city->id,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $cities])
                            </div>
                        </div>

                        @include('admin.components.delete-checkbox', [
                            'items' => $cities,
                            'typeCheckboxTextArabic' => 'المدن',
                            'typeCheckboxText' => 'city',
                            'route' => route('admin.cities.destroy-check'),
                            'type' => 'text',
                            'attr' => 'name',
                            'arraySearch' => old('items'),
                        ])
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
