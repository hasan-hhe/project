@extends('admin.layouts.master')
@section('title', 'تعديل مدينة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تعديل مدينة',
                'arr' => [
                    ['title' => 'المدن', 'link' => route('admin.cities.index')],
                    ['title' => 'تعديل مدينة', 'link' => route('admin.cities.edit', $city->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تعديل مدينة</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.cities.update', $city->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="row">
                                <div class="col-md-6">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'name',
                                        'id' => 'name',
                                        'label' => 'اسم المدينة',
                                        'value' => old('name', $city->name),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'governorate_id',
                                        'label' => 'المحافظة',
                                        'items' => collect($governorates)->map(function ($governorate) {
                                                return (object) ['value' => $governorate->id, 'label' => $governorate->name];
                                            })->values(),
                                        'name' => 'label',
                                        'attr' => 'value',
                                        'valueSelected' => old('governorate_id', $city->governorate_id),
                                        'nameForm' => 'governorate_id',
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">تحديث المدينة</button>
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

