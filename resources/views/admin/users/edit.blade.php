@extends('admin.layouts.master')
@section('title', 'تعديل مستخدم')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'تعديل مستخدم',
                'arr' => [
                    ['title' => 'المستخدمون', 'link' => route('admin.users.index')],
                    ['title' => 'تعديل مستخدم', 'link' => route('admin.users.edit', $user->id)],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تعديل مستخدم</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="row">
                                <div class="col-md-6">
                                    <span class="ms-4">الاسم الأول</span>
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'first_name',
                                        'id' => 'first_name',
                                        'label' => '',
                                        'value' => old('first_name', $user->first_name),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">الاسم الأخير</span>
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'last_name',
                                        'id' => 'last_name',
                                        'label' => '',
                                        'value' => old('last_name', $user->last_name),
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">رقم الهاتف</span>
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'phone_number',
                                        'id' => 'phone_number',
                                        'label' => '',
                                        'value' => old('phone_number', $user->phone_number),
                                        'required' => true,
                                        'attribute' => 'oninput=validateNumber(this)',
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">البريد الإلكتروني</span>
                                    @include('admin.components.input', [
                                        'type' => 'email',
                                        'name' => 'email',
                                        'id' => 'email',
                                        'label' => '',
                                        'value' => old('email', $user->email),
                                        'required' => false,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">تاريخ الميلاد</span>
                                    @include('admin.components.input', [
                                        'type' => 'date',
                                        'name' => 'date_of_birth',
                                        'id' => 'date_of_birth',
                                        'label' => '',
                                        'value' => old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : ''),
                                        'required' => false,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">نوع الحساب</span>
                                    @include('admin.components.select', [
                                        'selectedId' => 'account_type',
                                        'label' => '',
                                        'items' => collect($accountTypes)->map(function($label, $value) {
                                            return (object)['value' => $value, 'label' => $label];
                                        })->values(),
                                        'name' => 'label',
                                        'attr' => 'value',
                                        'valueSelected' => old('account_type', $user->account_type),
                                        'nameForm' => 'account_type',
                                        'required' => true,
                                    ])
                                </div>

                                <div class="col-md-6" id="owner_status_section" style="display: {{ $user->account_type == 'OWNER' ? 'block' : 'none' }};">
                                    <span class="ms-4">حالة صاحب الشقة</span>
                                    @include('admin.components.select', [
                                        'selectedId' => 'owner_status',
                                        'label' => '',
                                        'items' => collect($ownerStatuses)->map(function($label, $value) {
                                            return (object)['value' => $value, 'label' => $label];
                                        })->values(),
                                        'name' => 'label',
                                        'attr' => 'value',
                                        'valueSelected' => old('owner_status', $user->owner_status ?? 'PENDING'),
                                        'nameForm' => 'owner_status',
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">كلمة المرور (اتركه فارغاً إذا لم ترد تغييره)</span>
                                    @include('admin.components.input', [
                                        'type' => 'password',
                                        'name' => 'password',
                                        'id' => 'password',
                                        'label' => '',
                                        'required' => false,
                                    ])
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">الصورة الشخصية (اختياري)</span>
                                    @if($user->avatar_url)
                                        <div class="mb-2">
                                            <img src="{{ asset($user->avatar_url) }}" alt="الصورة الحالية" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" name="avatar_image" id="avatar_image" class="form-control" accept="image/*">
                                </div>

                                <div class="col-md-6">
                                    <span class="ms-4">صورة الهوية (اختياري)</span>
                                    @if($user->identity_docomunt_url)
                                        <div class="mb-2">
                                            <img src="{{ asset($user->identity_docomunt_url) }}" alt="صورة الهوية الحالية" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" name="identity_document_image" id="identity_document_image" class="form-control" accept="image/*">
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <button class="btn btn-primary" type="submit">تحديث المستخدم</button>
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">إلغاء</a>
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
<script>
    const accountTypeSelect = document.getElementById('account_type');
    const ownerStatusSection = document.getElementById('owner_status_section');

    function toggleOwnerStatus() {
        if (accountTypeSelect.value === 'OWNER') {
            ownerStatusSection.style.display = 'block';
        } else {
            ownerStatusSection.style.display = 'none';
        }
    }

    accountTypeSelect.addEventListener('change', toggleOwnerStatus);
</script>
@endpush
