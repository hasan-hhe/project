@extends('admin.layouts.master')
@section('title', 'المستخدمون الذين يحتاجون الموافقة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'المستخدمون الذين يحتاجون الموافقة',
                'arr' => [
                    [
                        'title' => 'المستخدمون الذين يحتاجون الموافقة',
                        'link' => route('admin.pending-approvals.index'),
                    ],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة المستخدمين الذين يحتاجون الموافقة</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search Form --}}
                        <form method="GET" action="{{ route('admin.pending-approvals.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="search">البحث</label>
                                        <input type="text" name="search" id="search" class="form-control"
                                            placeholder="ابحث بالاسم، الهاتف، أو البريد الإلكتروني"
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">بحث</button>
                                            <a href="{{ route('admin.pending-approvals.index') }}"
                                                class="btn btn-secondary">إعادة تعيين</a>
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
                                        <th>الاسم الأول</th>
                                        <th>الاسم الأخير</th>
                                        <th>رقم الهاتف</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>تاريخ التسجيل</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $i => $user)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.pending-approvals.show', $user->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $user->first_name }}</td>
                                            <td>{{ $user->last_name }}</td>
                                            <td>{{ $user->phone_number ?? '---' }}</td>
                                            <td>{{ $user->email ?? '---' }}</td>
                                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route(
                                                                'admin.pending-approvals.show',
                                                                $user->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                    ],
                                                    'withDelete' => false,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $users])
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
