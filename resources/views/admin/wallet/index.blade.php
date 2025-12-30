@extends('admin.layouts.master')
@section('title', 'شحن محفظة المستأجرين')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'شحن محفظة المستأجرين',
                'arr' => [['title' => 'شحن محفظة المستأجرين', 'link' => route('admin.wallet.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة المستأجرين</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search Form --}}
                        <form method="GET" action="{{ route('admin.wallet.index') }}" class="mb-4">
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
                                            <a href="{{ route('admin.wallet.index') }}" class="btn btn-secondary">إعادة
                                                تعيين</a>
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
                                        <th>رصيد المحفظة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $i => $user)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.wallet.show', $user->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $user->first_name }}</td>
                                            <td>{{ $user->last_name }}</td>
                                            <td>{{ $user->phone_number ?? '---' }}</td>
                                            <td>{{ $user->email ?? '---' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ ($user->wallet_balance ?? 0) > 0 ? 'success' : 'secondary' }}">
                                                    {{ number_format($user->wallet_balance ?? 0, 2) }} SYP
                                                </span>
                                            </td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.wallet.show', $user->id),
                                                            'icon' => 'fa-wallet',
                                                            'text' => 'شحن المحفظة',
                                                            'class' => 'text-success',
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
