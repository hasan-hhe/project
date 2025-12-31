<div class="form-button-action">
    @foreach($buttons as $button)
    @if($button['type'] == 'href')
    <a href="{{ $button['url'] }}" class="btn btn-link {{$button['class']}}"
    type="button">
        <i class="fa {{$button['icon']}} fs-4"></i>
    </a>
    @else
    <a type="button" data-bs-toggle="modal" data-bs-target="#{{$button['url']}}"
        class="btn btn-link {{$button['class']}}">
        <i class="fa {{$button['icon']}} fs-4"></i>
    </a>
    @endif
    @endforeach

    @if($withDelete)
    <a type="button" onclick="destroy('{{ $itemId }}')"
        class="btn btn-link btn-danger">
        <i class="fa fa-trash-alt fs-4"></i>
    </a>
    <form action="{{ $urlDelete }}" method="POST" id="delete-form-{{ $itemId }}">
        @csrf
        @method('DELETE')
    </form>
    @endif
</div>