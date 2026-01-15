@extends('admin.layouts.master')
@section('title', 'تعديل محافظة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تعديل محافظة',
                'arr' => [
                    ['title' => 'المحافظات', 'link' => route('admin.governorates.index')],
                    ['title' => 'تعديل محافظة', 'link' => route('admin.governorates.edit', $governorate->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تعديل محافظة</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.governorates.update', $governorate->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="row">
                                <div class="col-md-12">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'name',
                                        'id' => 'name',
                                        'label' => 'اسم المحافظة',
                                        'value' => old('name', $governorate->name),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">تحديث المحافظة</button>
                                        <a href="{{ route('admin.governorates.index') }}" class="btn btn-secondary">إلغاء</a>
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

