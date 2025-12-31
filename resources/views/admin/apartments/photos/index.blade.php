@extends('admin.layouts.master')
@section('title', 'صور الشقة')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'صور الشقة',
                'arr' => [
                    ['title' => 'الشقق', 'link' => route('admin.apartments.index')],
                    ['title' => $apartment->title, 'link' => route('admin.apartments.show', $apartment->id)],
                    ['title' => 'صور الشقة', 'link' => route('admin.apartments.photos.index', $apartment->id)],
                ],
            ])

            <div class="row">
                <div class="col-md-12">
                    {{-- معلومات الشقة --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">معلومات الشقة</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>العنوان:</strong> {{ $apartment->title }}</p>
                                    <p><strong>السعر:</strong> {{ number_format($apartment->price, 2) }} SYP</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>عدد الغرف:</strong> {{ $apartment->rooms_count }}</p>
                                    <p><strong>الحالة:</strong>
                                        <span class="badge bg-{{ $apartment->is_active ? 'success' : 'secondary' }}">
                                            {{ $apartment->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- إضافة صور جديدة --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">إضافة صور جديدة</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.apartments.photos.store', $apartment->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="photos">اختر الصور</label>
                                    <input type="file" name="photos[]" id="photos" class="form-control" multiple
                                        accept="image/*" required>
                                    <small class="form-text text-muted">يمكنك اختيار عدة صور في نفس الوقت. الحد الأقصى لحجم
                                        الصورة: 5MB</small>
                                </div>
                                <div class="form-group mt-3">
                                    <label>اختر صورة الغلاف (اختياري)</label>
                                    <div id="cover-selection" class="mt-2">
                                        <small class="text-muted">سيتم عرض الصور المختارة هنا بعد الاختيار</small>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> رفع الصور
                                    </button>
                                    <a href="{{ route('admin.apartments.show', $apartment->id) }}"
                                        class="btn btn-secondary">رجوع</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- قائمة الصور --}}
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">صور الشقة ({{ $photos->count() }})</h4>
                        </div>
                        <div class="card-body">
                            @if ($photos->count() > 0)
                                <div class="row">
                                    @foreach ($photos as $photo)
                                        <div class="col-md-3 mb-4">
                                            <div class="card h-100">
                                                <div class="position-relative">
                                                    <img src="{{ asset($photo->url) }}" class="card-img-top"
                                                        alt="صورة الشقة" style="height: 200px; object-fit: cover;">
                                                    @if ($photo->is_cover)
                                                        <span
                                                            class="badge bg-success position-absolute top-0 start-0 m-2">صورة
                                                            الغلاف</span>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            الترتيب: {{ $photo->sort_order }}
                                                        </small>
                                                    </p>
                                                    <div class="btn-group w-100" role="group">
                                                        @if (!$photo->is_cover)
                                                            <form
                                                                action="{{ route('admin.apartments.photos.set-cover', [$apartment->id, $photo->id]) }}"
                                                                method="POST" class="d-inline mx-1">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                    title="تعيين كصورة غلاف">
                                                                    <i class="fas fa-star"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <a type="button" onclick="destroy('{{ $photo->id }}')"
                                                            class="btn btn-link btn-danger">
                                                            <i class="fa fa-trash-alt fs-4"></i>
                                                        </a>
                                                        <form
                                                            action="{{ route('admin.apartments.photos.destroy', [$apartment->id, $photo->id]) }}"
                                                            method="POST" id="delete-form-{{ $photo->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info text-center">
                                    <p>لا توجد صور لهذه الشقة بعد. استخدم النموذج أعلاه لإضافة صور.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <style>
        .card-img-top {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .card-img-top:hover {
            transform: scale(1.05);
        }
    </style>
@endpush
@push('scripts')
    <script>
        document.getElementById('photos').addEventListener('change', function(e) {
            const files = e.target.files;
            const coverSelection = document.getElementById('cover-selection');

            if (files.length > 0) {
                let html = '<div class="row">';
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        html += `
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_cover" id="cover_${i}" value="new_${i}">
                                <label class="form-check-label" for="cover_${i}">
                                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                    <small class="d-block mt-1">صورة ${i + 1}</small>
                                </label>
                            </div>
                        </div>
                    `;

                        if (i === files.length - 1) {
                            html += '</div>';
                            coverSelection.innerHTML = html;
                        }
                    };

                    reader.readAsDataURL(file);
                }
            } else {
                coverSelection.innerHTML =
                    '<small class="text-muted">سيتم عرض الصور المختارة هنا بعد الاختيار</small>';
            }
        });
    </script>
@endpush
