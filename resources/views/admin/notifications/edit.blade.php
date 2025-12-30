@extends('admin.layouts.master')
@section('title', "تعديل إشعار")
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
                    [
                        'title' => "تعديل إشعار",
                        'link' => route('admin.notifications.edit' , $notification->id),
                    ],
                ],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">تعديل إشعار</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{route('admin.notifications.update' , $notification->id)}}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group @error('title') has-error @enderror">
                                        <label for="title" class="form-label">العنوان</label>
                                        <input id="title" type="text" name="title" value="{{ $notification->title }}" class="form-control" placeholder="أدخل العنوان">
                                        @error('title')
                                            <small class="form-text text-muted">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="isActive" class="form-label">الحالة</label>
                                        <select name="isActive" class="form-select form-control-lg">
                                            <option value="1" {{ $notification->isActive == '1' ? 'selected' : null }}>مفعل</option>
                                            <option value="0" {{ $notification->isActive == '0' ? 'selected' : null }}>غير مفعل</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group @error('body') has-error @enderror">
                                        <label for="body" class="form-label">المحتوى</label>
                                        <textarea id="body" name="body" class="form-control" placeholder="أدخل المحتوى">{{ $notification->body }}</textarea>
                                        @error('body')
                                            <small class="form-text text-muted">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                @php
                                $userNotifications = App\Models\UserNotification::where('notification_id' , $notification->id)->get();
                                $ids = $userNotifications->map->user_id;
                                @endphp
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sessions">المستخدمين</label>
                                        <button type="button" class="btn btn-outline-primary selectAll" style="float: left;">الكل</button>
                                        <input type="hidden" name="isAll" id="isAll" value="">
                                        <button type="button" class="btn btn-outline-danger disAll mx-1" style="float: left;">إلغاء</button>
                                        <input id="search" class="form-control mt-3" type="text" placeholder="بحث">
                                        <ul class="list-group mt-4" id="listSearch" style="overflow-y: scroll; height:150px">
                                            @foreach($users as $user)
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <div class="col">
                                                        <div class="form-check">
                                                            <input class="form-check-input" name="users[]" type="checkbox" value="{{ $user->id }}" @if(App\Helpers\inArray($user->id, $ids)) checked @endif id="flexCheckDefault{{$user->id}}">
                                                            <label class="form-check-label" for="flexCheckDefault{{$user->id}}">
                                                                {{ $user->first_name }} {{ $user->last_name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
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

        $('.submit').click(function(){
            if(isAllSelected()) {
                $('#isAll').val('all');
            }
        });

        function isAllSelected() {
            return $('.form-check-input').length === $('.form-check-input:checked').length;
        }
    });
</script>
@endpush
