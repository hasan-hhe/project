<div class="form-group">
    <div class="custom-select-wrapper {{ isset($class) ? $class : '' }}" id="wrapper-{{ $selectedId }}">
        <label class="custom-label" id="label-{{ $selectedId }}">{{ $label }}</label>

        <div class="custom-select" id="trigger-{{ $selectedId }}"
            @if (isset($style)) style="{{ $style }}" @endif>
            @if (getItem($items, $valueSelected, $attr) == null)
                @if (
                    (isset($value_all) && $value_all == '' && $valueSelected == null) ||
                        (isset($value_all) && $valueSelected == $value_all))
                    الكل
                @elseif ((isset($withNull) && $withNull) || count($items) || $valueSelected == '')
                    لا يوجد
                @else
                    {{ $items[0]->$attr }}
                @endif
            @else
                @if (isset($withFunName))
                    {{ getItem($items, $valueSelected, $attr)->$name() }}
                @else
                    {{ getItem($items, $valueSelected, $attr)->$name }}
                @endif
            @endif
        </div>
        <div class="select-options" id="option-{{ $selectedId }}">

            <span class="search-box">
                <input type="text" class="form-control @if (!isset($withSearch) || !$withSearch) d-none @endif"
                    placeholder="ابحث..." id="search-{{ $selectedId }}">
            </span>

            @if (isset($value_all))
                <div data-value="{{ $value_all }}">الكل</div>
            @endif
            @if (isset($withNull) && $withNull)
                <div data-value="null">لا يوجد</div>
            @endif
            @foreach ($items as $item)
                <div data-value="{{ $item->$attr }}">{{ isset($withFunName) ? $item->$name() : $item->$name }}</div>
            @endforeach
        </div>
        <input type="hidden" name="{{ $nameForm }}" @if (isset($attribute)) {{ $attribute }} @endif
            id="{{ $selectedId }}"
            value="{{ getItem($items, $valueSelected, $attr) == null ? ((isset($withNull) && $withNull) || count($items) == 0 || $valueSelected == '' ? 'null' : $items[0]->$attr) : getItem($items, $valueSelected, $attr)->$attr }}">
    </div>
</div>

@push('scripts')
    <script>
        let wrapper{{ $selectedId }} = document.getElementById('wrapper-{{ $selectedId }}');
        let trigger{{ $selectedId }} = document.getElementById('trigger-{{ $selectedId }}');
        let options{{ $selectedId }} = document.getElementById('option-{{ $selectedId }}');
        let hiddenInput{{ $selectedId }} = document.getElementById('{{ $selectedId }}');

        let searchInput{{ $selectedId }} = document.getElementById('search-{{ $selectedId }}');



        trigger{{ $selectedId }}.addEventListener('click', () => {
            wrapper{{ $selectedId }}.classList.toggle('open');
        });

        options{{ $selectedId }}.querySelectorAll('div').forEach(option => {
            option.addEventListener('click', () => {
                const value = option.dataset.value;
                const text = option.textContent;

                hiddenInput{{ $selectedId }}.value = value;
                @if (isset($jsFunctions))
                    {{ $jsFunctions }}
                @endif
                trigger{{ $selectedId }}.textContent = text;
                wrapper{{ $selectedId }}.classList.remove('open');
                wrapper{{ $selectedId }}.classList.add('active');
            });
        });

        document.addEventListener('click', (e) => {
            if (!wrapper{{ $selectedId }}.contains(e.target)) {
                wrapper{{ $selectedId }}.classList.remove('open');
            }
        });

        // document.addEventListener('DOMContentLoaded', () => {
        //     if (hiddenInput{{ $selectedId }}.value) {
        //         wrapper{{ $selectedId }}.classList.add('active');
        //         trigger{{ $selectedId }}.textContent = options{{ $selectedId }}.querySelector(
        //                 `[data-value="${hiddenInput{{ $selectedId }}.value}"]`)?.textContent ||
        //             '';
        //     }
        // });

        // البحث داخل الخيارات
        searchInput{{ $selectedId }}.addEventListener('input', () => {
            const filter = searchInput{{ $selectedId }}.value.toLowerCase();
            options{{ $selectedId }}.querySelectorAll('div[data-value]').forEach(option => {
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endpush
