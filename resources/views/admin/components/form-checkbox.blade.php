<div class="form-group">
    <label for="sessions">
        <h3>{{ $typeCheckboxTextArabic }}</h3>
    </label>
    <button type="button" class="btn btn-outline-primary selectAll" style="float: left;">الكل</button>
    <input type="hidden" name="isAll" id="isAll" value="">
    <button type="button" class="btn btn-outline-danger disAll mx-1" style="float: left;">إلغاء التحديد</button>
    {{-- <input id="search" class="form-control mt-3 search" type="text" placeholder="بحث"> --}}
    @include('admin.components.input', [
        'type' => 'text',
        'name' => 'search',
        'id' => 'search',
        'label' => 'ابحث',
        'value' => old('search'),
        'required' => false,
    ])
    <ul class="list-group mt-4 listSearch" id="listSearch" style="overflow-y: scroll; height:150px">
        @foreach ($items as $item)
            <li class="list-group-item">
                <div class="row">
                    <div class="col">
                        <div class="form-check">
                            <input class="checkbox" name="items[]" type="checkbox" value="{{ $item->id }}"
                                @if (App\Helpers\inArray($item->id, $arraySearch) || (isset($statusCheckboxes) && $statusCheckboxes)) checked @endif
                                id="{{ $typeCheckboxText }}-checkbox-{{ $item->id }}">
                            <label class="form-check-label" style="margin-right: 5px;font-weight: 500;"
                                for="{{ $typeCheckboxText }}-checkbox-{{ $item->id }}">
                                @if ($type == 'image')
                                    <img src="{{ asset($item->$attr) }}" class="img img-thmubnail" height="50px"
                                        width="50px">
                                @else
                                    {{ $item->$attr }}@if (isset($attr1))
                                        - {{ $item->$attr1->$attr2 }}
                                    @endif
                                @endif
                            </label>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    @error('items')
        <small class="form-text text-muted">{{ $message }}</small>
    @enderror
</div>

@push('styles')
    <style>
        .checkbox {
            appearance: none;
            width: 20px;
            margin-bottom: -6px;
            aspect-ratio: 1;
            border-radius: 8px;
            border: 2px solid black;
            position: relative;
            transition: all 0.2s ease-in-out;
        }

        .checkbox::before {
            font-family: "Quicksand", sans-serif;
            position: absolute;
            bottom: -4px;
            left: 1px;
            content: "✔";
            font-size: 25px;
            color: rgb(255, 153, 0);
            transform: scale(0);
            transition: all 0.2s ease-in-out;
        }

        .checkbox:checked::before {
            animation: zoom 0.5s ease-in-out;
            transform: scale(1);
        }

        @keyframes zoom {
            0% {
                transform: scale(0);
            }

            20% {
                transform: scale(1.5);
            }

            40% {
                transform: scale(0.5);
            }

            50% {
                transform: scale(1);
            }

            70% {
                transform: scale(1.2);
            }

            90% {
                transform: scale(0.8);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
@endpush
