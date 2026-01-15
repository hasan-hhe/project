@extends('admin.layouts.master')
@section('title', 'تفاصيل المحافظة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تفاصيل المحافظة',
                'arr' => [
                    ['title' => 'المحافظات', 'link' => route('admin.governorates.index')],
                    ['title' => 'تفاصيل المحافظة', 'link' => route('admin.governorates.show', $governorate->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">تفاصيل المحافظة</h4>
                            <div class="ms-auto">
                                <a href="{{ route('admin.governorates.edit', $governorate->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <a href="{{ route('admin.governorates.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> رجوع
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>معلومات المحافظة</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>اسم المحافظة</th>
                                        <td>{{ $governorate->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>عدد المدن</th>
                                        <td>{{ $governorate->cities->count() }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>المدن التابعة</h5>
                                @if ($governorate->cities->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>اسم المدينة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($governorate->cities as $index => $city)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $city->name }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.cities.show', $city->id) }}"
                                                                class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">لا توجد مدن تابعة لهذه المحافظة</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
