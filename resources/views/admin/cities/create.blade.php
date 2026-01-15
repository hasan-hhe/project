@extends('admin.layouts.master')
@section('title', 'إضافة مدينة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'إضافة مدينة',
                'arr' => [
                    ['title' => 'المدن', 'link' => route('admin.cities.index')],
                    ['title' => 'إضافة مدينة', 'link' => route('admin.cities.create')],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">إضافة مدينة جديدة</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.cities.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'name',
                                        'id' => 'name',
                                        'label' => 'اسم المدينة',
                                        'value' => old('name'),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'governorate_id',
                                        'label' => 'المحافظة',
                                        'items' => collect($governorates)->map(function ($governorate) {
                                                return (object) [
                                                    'value' => $governorate->id,
                                                    'label' => $governorate->name,
                                                ];
                                            })->values(),
                                        'name' => 'label',
                                        'attr' => 'value',
                                        'valueSelected' => old('governorate_id'),
                                        'nameForm' => 'governorate_id',
                                        'withSearch' => true,
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">إضافة المدينة</button>
                                        <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">إلغاء</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
