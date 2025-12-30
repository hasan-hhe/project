@extends('admin.layouts.master')
@section('title', 'الإعدادات')
@section('main-content')

    <div class="container">
        <div class="page-inner">
            @include('admin.components.page-header', [
                'title' => 'الإعدادات',
                'arr' => [['title' => 'الإعدادات', 'link' => route('admin.settings.index')]],
            ])

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">الإعدادات العامة</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.update', $settings) }}" method="POST">
                            @csrf @method('PATCH')

                            <div class="row">
                                <div class="col-12">
                                    @include('admin.components.input', [
                                        'type' => 'text',
                                        'name' => 'version',
                                        'id' => 'version',
                                        'label' => 'النسخة',
                                        'value' => old('version' , $settings->version),
                                        'required' => true,
                                        'attribute' => 'oninput=validateNumber(this)',
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.textarea', [
                                        'name' => 'content_privacy_policy',
                                        'id' => 'privacy_policy',
                                        'label' => 'سياسة الخصوصية',
                                        'value' => old('content_privacy_policy', $settings->content_privacy_policy ?? ''),
                                        'required' => true,
                                        'attribute' => 'rows=5',
                                    ])
                                </div>

                                <div class="col-md-6">
                                    @include('admin.components.textarea', [
                                        'name' => 'content_terms_conditions',
                                        'id' => 'terms_conditions',
                                        'label' => 'الشروط والأحكام',
                                        'value' => old('content_terms_conditions', $settings->content_terms_conditions ?? ''),
                                        'required' => true,
                                        'attribute' => 'rows=5',
                                    ])
                                </div>

                                <div class="col-12">
                                    @include('admin.components.textarea', [
                                        'name' => 'link_update',
                                        'id' => 'links',
                                        'label' => 'روابط التحميل (ضع , بين كل حالة)',
                                        'value' => old('links', implode(',', $settings->link_update) ?? ''),
                                        'required' => true,
                                        'attribute' => 'rows=5',
                                    ])
                                </div>

                                <button class="btn btn-primary mt-3" type="submit">حفظ التغييرات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
