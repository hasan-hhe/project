@extends('admin.layouts.master')
@section('title', 'عرض تقييم')
@section('main-content')
    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'عرض تقييم',
                'arr' => [
                    ['title' => 'التقييمات', 'link' => route('admin.reviews.index')],
                    ['title' => 'عرض تقييم', 'link' => route('admin.reviews.show', $review->id)],
                ],
            ])

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">معلومات التقييم</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped text-center">
                                <tbody>
                                    <tr>
                                        <th>المستخدم</th>
                                        <td>
                                            <a href="{{ route('admin.users.show', $review->user_id) }}">
                                                {{ $review->user->first_name }} {{ $review->user->last_name }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>الشقة</th>
                                        <td>
                                            <a href="{{ route('admin.apartments.show', $review->apartment_id) }}">
                                                {{ $review->apartment->title }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>التقييم</th>
                                        <td>
                                            <span class="badge bg-success" style="font-size: 16px;">
                                                {{ $review->rating }}/5
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>التعليق</th>
                                        <td>{{ $review->comment ?? 'لا يوجد تعليق' }}</td>
                                    </tr>
                                    @if ($review->booking)
                                        <tr>
                                            <th>الحجز المرتبط</th>
                                            <td>
                                                <a href="{{ route('admin.bookings.show', $review->booking_id) }}">
                                                    حجز #{{ $review->booking_id }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>تاريخ الإنشاء</th>
                                        <td>{{ $review->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>آخر تحديث</th>
                                        <td>{{ $review->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush
