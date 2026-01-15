@extends('admin.layouts.master')
@section('title', 'تفاصيل المدينة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تفاصيل المدينة',
                'arr' => [
                    ['title' => 'المدن', 'link' => route('admin.cities.index')],
                    ['title' => 'تفاصيل المدينة', 'link' => route('admin.cities.show', $city->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">تفاصيل المدينة</h4>
                            <div class="ms-auto">
                                <a href="{{ route('admin.cities.edit', $city->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <a href="{{ route('admin.cities.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> رجوع
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>معلومات المدينة</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>اسم المدينة</th>
                                        <td>{{ $city->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>المحافظة</th>
                                        <td>
                                            <a href="{{ route('admin.governorates.show', $city->governorate->id) }}">
                                                {{ $city->governorate->name ?? '---' }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>عدد الشقق</th>
                                        <td>{{ $city->apartments->count() ?? 0 }}</td>
                                    </tr>
                                </table>
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
