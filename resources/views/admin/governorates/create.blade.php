@extends('admin.layouts.master')
@section('title', 'إضافة محافظة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'إضافة محافظة',
                'arr' => [
                    ['title' => 'المحافظات', 'link' => route('admin.governorates.index')],
                    ['title' => 'إضافة محافظة', 'link' => route('admin.governorates.create')],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">إضافة محافظة جديدة</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.governorates.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'name',
                                        'id' => 'name',
                                        'label' => 'اسم المحافظة',
                                        'value' => old('name'),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">إضافة المحافظة</button>
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

