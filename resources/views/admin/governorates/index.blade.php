@extends('admin.layouts.master')
@section('title', 'المحافظات')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'المحافظات',
                'arr' => [['title' => 'المحافظات', 'link' => route('admin.governorates.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة المحافظات</h4>
                            @include('admin.components.buttons', [
                                'buttons' => [
                                    [
                                        'type' => 'href',
                                        'url' => route('admin.governorates.create'),
                                        'icon' => 'fa-plus',
                                        'text' => 'إضافة محافظة',
                                    ],
                                ],
                                'withDeleteChecked' => 1,
                                'withDeleteAll' => 1,
                                'urlDeleteAll' => route('admin.governorates.destroy-check'),
                            ])
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table display table-striped table-hover table-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المحافظة</th>
                                        <th>عدد المدن</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($governorates as $i => $governorate)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.governorates.show', $governorate->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $governorate->name }}</td>
                                            <td>{{ $governorate->cities_count ?? 0 }}</td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.governorates.show',
                                                                $governorate->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.governorates.edit',
                                                                $governorate->id),
                                                            'icon' => 'fa-edit',
                                                            'text' => 'تعديل',
                                                            'class' => 'text-secondary',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route(
                                                        'admin.governorates.destroy',
                                                        $governorate->id),
                                                    'itemId' => $governorate->id,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $governorates])
                            </div>
                        </div>

                        @include('admin.components.delete-checkbox', [
                            'items' => $governorates,
                            'typeCheckboxTextArabic' => 'المحافظات',
                            'typeCheckboxText' => 'governorate',
                            'route' => route('admin.governorates.destroy-check'),
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
