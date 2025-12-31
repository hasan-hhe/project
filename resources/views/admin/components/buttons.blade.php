@if ($withDeleteChecked)
    <a type="button" data-bs-toggle="modal" data-bs-target="#deleteChecked" class="btn btn-danger btn-round ms-auto"
        style="margin-left:10px;">
        <i class="fa fa-trash-alt"></i> حذف بالتحديد
    </a>
@endif
@if ($withDeleteAll)
    <a type="button" href="#" onclick="destroy('all')" data-toggle="tooltip" data-original-title="Delete"
        class="btn btn-danger btn-round" style="margin-left:10px;">
        <i class="fa fa-trash-alt"></i> حذف الكل
    </a>
    <form action="{{ $urlDeleteAll }}" method="POST" id="delete-form-all">
        @csrf
        {{-- @method('DELETE') --}}
    </form>
@endif

@foreach ($buttons as $button)
    @if ($button['type'] === 'href')
        <a href="{{ $button['url'] }}" class="btn btn-primary btn-round" style="margin-left:10px;">
            <i class="fa {{ $button['icon'] }}"></i> {{ $button['text'] }}
        </a>
    @else
        <a type="button" data-bs-toggle="modal" data-bs-target="#{{ $button['url'] }}"
            class="btn btn-primary btn-round" style="margin-left:10px;">
            <i class="fa {{ $button['icon'] }}"></i> {{ $button['text'] }}
        </a>
    @endif
@endforeach
