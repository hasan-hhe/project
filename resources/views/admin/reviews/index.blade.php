@extends('admin.layouts.master')
@section('title', 'التقييمات')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'التقييمات',
                'arr' => [['title' => 'التقييمات', 'link' => route('admin.reviews.index')]],
            ])
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">قائمة التقييمات</h4>
                            @include('admin.components.buttons', [
                                'buttons' => [],
                                'withDeleteChecked' => 1,
                                'withDeleteAll' => 1,
                                'urlDeleteAll' => route('admin.reviews.destroy-check'),
                            ])
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Search and Filter Form --}}
                        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        @include('admin.components.input', [
                                            'type' => 'text',
                                            'name' => 'search',
                                            'id' => 'search',
                                            'label' => 'البحث',
                                            'value' => request('search'),
                                        ])
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="apartment_id">الشقة</label>
                                        <select name="apartment_id" id="apartment_id" class="form-control">
                                            <option value="">جميع الشقق</option>
                                            @foreach($apartments as $apartment)
                                                <option value="{{ $apartment->id }}" {{ request('apartment_id') == $apartment->id ? 'selected' : '' }}>
                                                    {{ $apartment->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="user_id">المستخدم</label>
                                        <select name="user_id" id="user_id" class="form-control">
                                            <option value="">جميع المستخدمين</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        @include('admin.components.select', [
                                            'selectedId' => 'rating',
                                            'nameForm' => 'rating',
                                            'label' => 'التقييم',
                                            'items' => [
                                                (object) ['id' => '', 'name' => 'جميع التقييمات'],
                                                (object) ['id' => '5', 'name' => '5 نجوم'],
                                                (object) ['id' => '4', 'name' => '4 نجوم'],
                                                (object) ['id' => '3', 'name' => '3 نجوم'],
                                                (object) ['id' => '2', 'name' => '2 نجوم'],
                                                (object) ['id' => '1', 'name' => '1 نجمة'],
                                            ],
                                            'valueSelected' => request('rating'),
                                            'name' => 'name',
                                            'attr' => 'id',
                                        ])
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">بحث</button>
                                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">إعادة
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
                                        <th>المستخدم</th>
                                        <th>الشقة</th>
                                        <th>التقييم</th>
                                        <th>التعليق</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reviews as $i => $review)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.reviews.show', $review->id) }}">{{ $i + 1 }}</a>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $review->user_id) }}">
                                                    {{ $review->user->first_name }} {{ $review->user->last_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.apartments.show', $review->apartment_id) }}">
                                                    {{ $review->apartment->title }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $review->rating }}/5</span>
                                            </td>
                                            <td>{{ Str::limit($review->comment ?? '---', 50) }}</td>
                                            <td>{{ $review->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @include('admin.components.procedures', [
                                                    'buttons' => [
                                                        [
                                                            'type' => 'href',
                                                            'url' => route('admin.reviews.show', $review->id),
                                                            'icon' => 'fa-eye',
                                                            'text' => 'عرض',
                                                            'class' => 'text-info',
                                                        ],
                                                    ],
                                                    'withDelete' => true,
                                                    'urlDelete' => route('admin.reviews.destroy', $review->id),
                                                    'itemId' => $review->id,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                @include('admin.components.pagination', ['paginations' => $reviews])
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

