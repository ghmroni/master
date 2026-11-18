<div class="row g-3">
    @foreach ($users as $user)
        <div class="col-xl-4 col-lg-6 col-md-6 mb-0">
            <div class="card card-border-shadow-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end mt-1">
                        <div class="role-heading">
                            <h5 class="mb-2">
                                <img class="rounded-circle"
                                    src="{{ $user->image == null ? 'assets/img/avatars/no-image.png' : '/storage/hris/profile/' . $user->image }} "
                                    alt="Foto" width="35px" />
                                {{ $user->name }}
                            </h5>
                            @can('edit-aksesproject')
                                <a href="{{ route('project.kelolaakses', $user->token) }}" class="text-primary mt-2"
                                    data-id="{{ $user->id }}"><span><i class="ti ti-key me-1"></i>Kelola Akses</span>
                                </a>
                            @endcan
                        </div>
                        <small class="text-light d-flex">
                            {{ $user->roles->first()?->name }}
                            <i class="ti ti-medal ti-md text-warning"></i>
                        </small>
                        {{-- <a href="javascript:void(0);" class="text-muted"><i class="ti ti-medal ti-md"></i></a> --}}
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>

<div class="container-xxl mt-5">
    <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
        <div>
            <small class="text-muted">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of
                {{ $users->total() }}
                results
            </small>
        </div>

        <div class="d-lg-inline-block">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-primary justify-content-center">
                    {{-- Previous Button --}}
                    <li class="page-item prev">
                        @if ($users->onFirstPage())
                            <a class="page-link disabled" href="#">
                                <i class="ti ti-chevron-left ti-xs"></i>
                            </a>
                        @else
                            <a class="page-link" href="{{ $users->previousPageUrl() }}">
                                <i class="ti ti-chevron-left ti-xs"></i>
                            </a>
                        @endif
                    </li>

                    {{-- Page Number Links --}}
                    @php
                        $start = max(1, $users->currentPage() - 2);
                        $end = min($users->lastPage(), $users->currentPage() + 2);
                    @endphp

                    {{-- Page Number Links --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $users->currentPage())
                            <li class="page-item active">
                                <a class="page-link" href="{{ $users->url($page) }}" data-i="{{ $page }}"
                                    data-page="{{ $users->perPage() }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url($page) }}" data-i="{{ $page }}"
                                    data-page="{{ $users->perPage() }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endfor

                    {{-- Next Button --}}
                    <li class="page-item next">
                        @if ($users->hasMorePages())
                            <a class="page-link pagination-next" href="{{ $users->nextPageUrl() }}">
                                <i class="ti ti-chevron-right ti-xs"></i>
                            </a>
                        @else
                            <a class="page-link pagination-next disabled" href="#">
                                <i class="ti ti-chevron-right ti-xs"></i>
                            </a>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
