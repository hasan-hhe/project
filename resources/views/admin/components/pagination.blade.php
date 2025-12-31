<div class="row mt-5">
    <div class="col-md-12 justify-content-center d-flex">
        <nav aria-label="Page navigation example" class="mb-5">
            <ul class="pagination justify-content-center">
              <li class="page-item @if($paginations->currentPage() == 1) disabled @endif">
                <a class="page-link" href="{{ $paginations->previousPageUrl() }}">السابق</a>
              </li>
              @if ($paginations->currentPage() != 1)
                <li class="page-item"><a class="page-link" href="{{ $paginations->previousPageUrl() }}">{{ $paginations->currentPage() - 1 }}</a></li>
              @endif
              <li class="page-item active" aria-current="page">
                <span class="page-link">{{ $paginations->currentPage() }}</span>
              </li>
              @if ($paginations->currentPage() != $paginations->lastPage())
                <li class="page-item"><a class="page-link" href="{{ $paginations->nextPageUrl() }}">{{ $paginations->currentPage() + 1 }}</a></li>
              @endif
              <li class="page-item @if($paginations->currentPage() == $paginations->lastPage()) disabled @endif">
                <a class="page-link" href="{{ $paginations->nextPageUrl() }}">التالي</a>
              </li>
              
            </ul>
          </nav>
    </div>
</div>
