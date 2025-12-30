@extends('admin.layouts.master')
@section('title', 'أصحاب البطاقات')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'أصحاب البطاقات',
                'arr' => [
                    ['title' => 'أصحاب البطاقات', 'link' => route('admin.users.index', ['type' => 'users'])],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة أصحاب البطاقات</h4>
                            @include('admin.components.buttons', [
                                'buttons' => [
                                    [
                                        'type' => 'href',
                                        'url' => route('admin.users.create', ['type' => 'user']),
                                        'icon' => 'fa-plus',
                                        'text' => 'إضافة صاحب بطاقة',
                                    ],
                                ],
                                'withDeleteChecked' => 1,
                                'withDeleteAll' => 1,
                                'urlDeleteAll' => route('admin.users.destroy-all'),
                            ])
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table display table-striped table-hover table-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الرقم</th>
                                        <th>الدور</th>
                                        <th>الشركة</th>
                                        <th>نوع البطاقة</th>
                                        <th>بيانات البطاقة</th>
                                        <th>التفعيل</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $i => $user)
                                        <tr>
                                            <td><a href="{{ route('admin.users.edit', $user->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->phone == 0 ? '---' : $user->phone }}</td>
                                            <td>
                                                @php
                                                    $roleEnum = \App\Enums\UserRole::tryFrom($user->role);
                                                @endphp

                                                @if ($roleEnum)
                                                    <span class="badge bg-{{ $roleEnum->color() }}">
                                                        {{ $roleEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">غير معروف</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a
                                                    href="{{ $user->company ? route('admin.companies.edit', $user->company->id) : '#' }}">{{ $user->company ? $user->company->name : 'لا يوجد' }}</a>
                                            </td>
                                            <td>{{ $user->card->card->type == 'prepaid' ? 'مسبق الدفع' : 'لاحق الدفع' }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $user->id) }}">
                                                    <i class="fa fa-eye fs-4"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                                    {{ $user->is_active ? 'مفعل' : 'غير مفعل' }}
                                                </span>
                                            </td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.users.edit', $user->id),
                                                            'icon' => 'fa-edit',
                                                            'text' => 'تعديل',
                                                            'class' => 'text-secondary',
                                                        ],
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.users.toggle-active', $user->id),
                                                            'icon' => 'icon-refresh',
                                                            'text' => 'تحديث',
                                                            'class' => 'text-warning',
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
                            'typeCheckboxTextArabic' => 'أصحاب البطاقات',
                            'typeCheckboxText' => 'user',
                            'route' => route('admin.users.destroy-check'),
                            'type' => 'text',
                            'attr' => 'name',
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
