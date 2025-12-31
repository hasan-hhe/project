@extends('admin.layouts.master')
@section('title', 'إضافة إشعار')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'الإشعارات',
                'arr' => [
                    [
                        'title' => 'الإشعارات',
                        'link' => route('admin.notifications.index'),
                    ],
                    [
                        'title' => 'إضافة إشعار',
                        'link' => route('admin.notifications.create'),
                    ],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">إضافة إشعار</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.notifications.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'title',
                                        'id' => 'title',
                                        'label' => 'العنوان',
                                        'value' => old('title'),
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
                                        'valueSelected' => old('is_active'),
                                        'nameForm' => 'is_active',
                                        'withSearch' => false,
                                    ])
                                </div>

                                <div class="col-12">
                                    @include('admin.components.textarea', [
                                        'name' => 'body',
                                        'id' => 'body',
                                        'label' => 'المحتوى',
                                        'value' => old('body'),
                                    ])
                                </div>

                                <div class="col-md-12">
                                    {{-- <div class="form-group">
                                        <label for="sessions">المستخدمين</label>
                                        <button type="button" class="btn btn-outline-primary selectAll"
                                            style="float: left;">الكل</button>
                                        <input type="hidden" name="isAll" id="isAll" value="">
                                        <button type="button" class="btn btn-outline-danger disAll mx-1"
                                            style="float: left;">إلغاء</button>
                                        <input id="search" class="form-control mt-3" type="text" placeholder="بحث">
                                        <ul class="list-group mt-4" id="listSearch"
                                            style="overflow-y: scroll; height:150px">
                                            @foreach ($users as $user)
                                                <li class="list-group-item">
                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="form-check">
                                                                <input class="form-check-input" name="users[]"
                                                                    type="checkbox" value="{{ $user->id }}"
                                                                    @if (App\Helpers\inArray($user->id, old('users'))) checked @endif
                                                                    id="flexCheckDefault{{ $user->id }}">
                                                                <label class="form-check-label"
                                                                    for="flexCheckDefault{{ $user->id }}">
                                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div> --}}

                                    @include('admin.components.form-checkbox', [
                                        'items' => $users,
                                        'typeCheckboxTextArabic' => 'المستخدمين',
                                        'typeCheckboxText' => 'user',
                                        'type' => 'user',
                                        'attr' => 'first_name',
                                        'attr1' => 'last_name',
                                        'name' => 'users[]',
                                        'arraySearch' => old('users'),
                                    ])
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <button type="reset" class="btn btn-warning">إعادة</button>
                                        <button class="btn btn-success submit" type="submit">إرسال</button>
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
    <script>
        let isAll = '';
        $(document).ready(function() {
            $('#isAll').val('');

            $('.submit').click(function() {
                if (isAllSelected()) {
                    $('#isAll').val('all');
                }
            });

            function isAllSelected() {
                return $('.form-check-input').length === $('.form-check-input:checked').length;
            }
        });
    </script>
@endpush
