@extends('admin.layouts.master')
@section('title', 'أصحاب الشقق')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'أصحاب الشقق',
                'arr' => [['title' => 'أصحاب الشقق', 'link' => route('admin.users.index', ['type' => 'owner'])]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة أصحاب الشقق</h4>
                            @include('admin.components.buttons', [
                                'buttons' => [
                                    [
                                        'type' => 'href',
                                        'url' => route('admin.users.create'),
                                        'icon' => 'fa-plus',
                                        'text' => 'إضافة صاحب شقة',
                                    ],
                                ],
                                'withDeleteChecked' => 1,
                                'withDeleteAll' => 1,
                                'urlDeleteAll' => route('admin.users.destroy-check'),
                            ])
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table display table-striped table-hover table-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم الأول</th>
                                        <th>الاسم الأخير</th>
                                        <th>رقم الهاتف</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>عدد الشقق</th>
                                        <th>حالة الحساب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $i => $user)
                                        <tr>
                                            <td><a href="{{ route('admin.users.show', $user->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $user->first_name }}</td>
                                            <td>{{ $user->last_name }}</td>
                                            <td>{{ $user->phone_number ?? '---' }}</td>
                                            <td>{{ $user->email ?? '---' }}</td>
                                            <td>{{ $user->appartments->count() ?? 0 }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'PENDING' => 'warning',
                                                        'APPROVED' => 'success',
                                                        'REJECTED' => 'danger',
                                                    ];
                                                    $statusLabels = [
                                                        'PENDING' => 'قيد الانتظار',
                                                        'APPROVED' => 'موافق عليه',
                                                        'REJECTED' => 'مرفوض',
                                                    ];
                                                    $status = $user->status ?? 'PENDING';
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                                    {{ $statusLabels[$status] ?? $status }}
                                                </span>
                                            </td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.users.show', $user->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.users.edit', $user->id),
                                                            'icon' => 'fa-edit',
                                                            'text' => 'تعديل',
                                                            'class' => 'text-secondary',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route('admin.users.destroy', $user->id),
                                                    'itemId' => $user->id,
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

                        @include('admin.components.delete-checkbox', [
                            'items' => $users,
                            'typeCheckboxTextArabic' => 'أصحاب الشقق',
                            'typeCheckboxText' => 'owner',
                            'route' => route('admin.users.destroy-check'),
                            'type' => 'text',
                            'attr' => 'first_name',
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
