{{-- <div class="form-group ms-auto">
    <div class="input-group">
        <div class="input-group-append">
            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                الخيارات
            </button>
            <div class="dropdown-menu">
                @foreach ($buttons as $button)
                    @if ($button['type'] === 'href')
                        <a href="{{ $button['url'] }}" class="dropdown-item" type="button">
                            <i class="fa {{ $button['icon'] }} me-1"></i>
                            {{ $button['text'] }}
                        </a>
                    @else
                        <a type="button" data-bs-toggle="modal" data-bs-target="#{{ $button['url'] }}"
                            class="dropdown-item">
                            <i class="fa {{ $button['icon'] }} me-1"></i> {{ $button['text'] }}
                        </a>
                    @endif
                @endforeach

                @if ($withDeleteChecked)
                    <a type="button" data-bs-toggle="modal" data-bs-target="#deleteChecked" class="dropdown-item">
                        <i class="fa fa-trash-alt"></i> حذف بالتحديد
                    </a>
                @endif
                @if ($withDeleteAll)
                    <a type="button" href="#" onclick="destroy('all')" data-toggle="tooltip"
                        data-original-title="Delete" class="dropdown-item">
                        <i class="fa fa-trash-alt"></i> حذف الكل
                    </a>
                    <form action="{{ $urlDeleteAll }}" method="POST" id="delete-form-all">
                        @csrf
                        
                    </form>
                @endif
            </div>
        </div>
    </div>
</div> --}}

@if ($withDeleteChecked)
    <a type="button" data-bs-toggle="modal" data-bs-target="#deleteChecked" class="btn btn-danger btn-round ms-auto" style="margin-left:10px;">
        <i class="fa fa-trash-alt"></i> حذف بالتحديد
    </a>
@endif
@if ($withDeleteAll)
    <a type="button" href="#" onclick="destroy('all')" data-toggle="tooltip"
        data-original-title="Delete" class="btn btn-danger btn-round" style="margin-left:10px;">
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
        <a type="button" data-bs-toggle="modal" data-bs-target="#{{ $button['url'] }}" class="btn btn-primary btn-round" style="margin-left:10px;">
            <i class="fa {{ $button['icon'] }}"></i> {{ $button['text'] }}
        </a>
    @endif
@endforeach

