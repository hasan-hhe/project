@extends('admin.layouts.master')
@section('title', 'تعديل شقة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تعديل شقة',
                'arr' => [
                    ['title' => 'الشقق', 'link' => route('admin.apartments.index')],
                    ['title' => 'تعديل شقة', 'link' => route('admin.apartments.edit', $apartment->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تعديل شقة</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.apartments.update', $apartment->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="row">
                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'owner_id',
                                        'label' => 'صاحب الشقة',
                                        'items' => $owners,
                                        'name' => 'first_name',
                                        'attr' => 'id',
                                        'valueSelected' => old('owner_id', $apartment->owner_id),
                                        'nameForm' => 'owner_id',
                                        'withSearch' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'governorate_id',
                                        'label' => 'المحافظة',
                                        'items' => $governorates,
                                        'name' => 'name',
                                        'attr' => 'id',
                                        'valueSelected' => old('governorate_id', $apartment->governorate_id),
                                        'nameForm' => 'governorate_id',
                                        'withSearch' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'city_id',
                                        'label' => 'المدينة',
                                        'items' => $cities,
                                        'name' => 'name',
                                        'attr' => 'id',
                                        'valueSelected' => old('city_id', $apartment->city_id),
                                        'nameForm' => 'city_id',
                                        'withSearch' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'title',
                                        'id' => 'title',
                                        'label' => 'العنوان',
                                        'value' => old('title', $apartment->title),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-12">
                                    @include('admin.components.textarea', [
                                        'name' => 'description',
                                        'id' => 'description',
                                        'label' => 'الوصف',
                                        'value' => old('description', $apartment->description),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.input', [
                                        'type' => 'number',
                                        'name' => 'price',
                                        'id' => 'price',
                                        'label' => 'السعر (SYP)',
                                        'value' => old('price', $apartment->price),
                                        'required' => true,
                                        'attribute' => 'step=0.01 min=0',
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.input', [
                                        'type' => 'number',
                                        'name' => 'rooms_count',
                                        'id' => 'rooms_count',
                                        'label' => 'عدد الغرف',
                                        'value' => old('rooms_count', $apartment->rooms_count),
                                        'required' => true,
                                        'attribute' => 'min=1',
                                    ])
                                </div>

                                <div class="col-md-12">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'address_line',
                                        'id' => 'address_line',
                                        'label' => 'عنوان الشارع',
                                        'value' => old('address_line', $apartment->address_line),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'is_active',
                                        'label' => 'الحالة',
                                        'items' => [
                                            (object) ['id' => 1, 'name' => 'نشط'],
                                            (object) ['id' => 0, 'name' => 'غير نشط'],
                                        ],
                                        'name' => 'name',
                                        'attr' => 'id',
                                        'valueSelected' => old('is_active', $apartment->is_active),
                                        'nameForm' => 'is_active',
                                        'withSearch' => false,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.select', [
                                        'selectedId' => 'is_recommended',
                                        'label' => 'موصى بها',
                                        'items' => [
                                            (object) ['id' => 1, 'name' => 'نعم'],
                                            (object) ['id' => 0, 'name' => 'لا'],
                                        ],
                                        'name' => 'name',
                                        'attr' => 'id',
                                        'valueSelected' => old('is_recommended', $apartment->is_recommended),
                                        'nameForm' => 'is_recommended',
                                        'withSearch' => false,
                                    ])
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">تحديث الشقة</button>
                                        <a href="{{ route('admin.apartments.index') }}" class="btn btn-secondary">إلغاء</a>
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
@push('styles')
@endpush
@push('scripts')
@endpush
