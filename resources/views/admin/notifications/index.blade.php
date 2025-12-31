@extends('admin.layouts.master')
@section('title', "الإشعارات")
@section('main-content')

    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => "الإشعارات",
                'arr' => [
                    [
                        'title' => "الإشعارات",
                        'link' => route('admin.notifications.index'),
                    ],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة الإشعارات</h4>
                            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-round ms-auto" type="button">
                                <i class="fa fa-plus"></i>
                                إضافة إشعار
                            </a>
                            <button data-bs-toggle="modal" data-bs-target="#deleteChecked"
                                class="btn btn-danger btn-round mx-2">
                                <i class="fa fa-trash-alt"></i> حذف بالتحديد
                            </button>
                            <a href="#"
                                onclick="destroy('all')"
                                data-toggle="tooltip" data-original-title="Delete"
                                class="btn btn-danger btn-round mx-2">
                                <i class="fa fa-trash-alt"></i> حذف الكل
                            </a>
                            <form action="{{ route('admin.notifications.destroy-all') }}"
                                method="POST" id="delete-form-all">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table display table-striped table-hover table-datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>العنوان</th>
                                        <th>المحتوى</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notifications as $i => $notification)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.notifications.edit', $notification->id) }}">
                                                    {{$i + 1}}
                                                </a>
                                            </td>
                                            <td>{{ $notification->title }}</td>
                                            <td style="max-width: 40px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap">
                                                {{ $notification->body }}
                                            </td>
                                            <td>{{ $notification->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $notification->is_active == 1 ? 'success' : 'secondary' }}">
                                                    {{ $notification->is_active == 1 ? 'مفعل' : 'غير مفعل' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="form-button-action">
                                                    <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-link btn-secondary" data-bs-toggle="tooltip" title="تعديل">
                                                        <i class="fa fa-edit fs-4"></i>
                                                    </a>
                                                    <a href="{{ route('admin.notifications.toggle-active', $notification->id) }}" class="btn btn-link btn-warning" title="تغيير الحالة">
                                                        <i class="fa icon-refresh fs-4"></i>
                                                    </a>
                                                    <a href="#" onclick="destroy('{{ $notification->id }}')" class="btn btn-link btn-danger" title="حذف">
                                                        <i class="fa fa-trash-alt fs-4"></i>
                                                    </a>
                                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" id="delete-form-{{ $notification->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="deleteChecked" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">حذف الإشعارات</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.notifications.destroy-check') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="sessions">الإشعارات</label>
                                    <button type="button" class="btn btn-outline-primary selectAll" style="float: left;">الكل</button>
                                    <input type="hidden" name="isAll" id="isAll" value="">
                                    <button type="button" class="btn btn-outline-danger disAll mx-1" style="float: left;">إلغاء</button>
                                    <input id="search" class="form-control mt-3" type="text" placeholder="بحث">
                                    <ul class="list-group mt-4" id="listSearch" style="overflow-y: scroll; height:150px">
                                        @foreach($notifications as $notification)
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input" name="notifications[]" type="checkbox" value="{{ $notification->id }}" @if(App\Helpers\inArray($notification->id, old('products'))) checked @endif id="flexCheckDefault{{$notification->id}}">
                                                        <label class="form-check-label" for="flexCheckDefault{{$notification->id}}">
                                                            {{ $notification->title }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">إضافة</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
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
@endpush
