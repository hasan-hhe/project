@php
    $textAlign = 'left';
    $textAlignOp = 'right';
@endphp

@php
    function getItem($items, $valueSelected, $attr)
    {
        foreach ($items as $item) {
            if ($item->$attr == $valueSelected) {
                return $item;
            }
        }
        return null;
    }
@endphp

<div class="page-header">
    <h3 class="fw-bold mb-3">{{ $title }}</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('admin.dashboard.index') }}">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-{{ $textAlign }}"></i>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.dashboard.index') }}">لوحة التحكم</a>
        </li>

        @foreach ($arr as $ar)
            <li class="separator">
                <i class="icon-arrow-{{ $textAlign }}"></i>
            </li>
            <li class="nav-item">
                <a href="{{ $ar['link'] }}">{{ $ar['title'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
