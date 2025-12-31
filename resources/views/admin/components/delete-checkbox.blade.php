<div class="modal fade" id="deleteChecked" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">حذف {{ $typeCheckboxTextArabic }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ $route }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('admin.components.form-checkbox', [
                        'items' => $items,
                        'typeCheckboxTextArabic' => $typeCheckboxTextArabic,
                        'typeCheckboxText' => $typeCheckboxText,
                        'type' => $type,
                        'attr' => $attr,
                        'statusCheckboxes' => isset($statusCheckboxes) ? $statusCheckboxes : false,
                        'arraySearch' => $arraySearch,
                    ])
                </div>
                <div class="modal-footer" dir="ltr">
                    <button type="submit" class="btn btn-primary">حذف</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>
