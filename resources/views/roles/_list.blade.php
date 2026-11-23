<div class="row g-4">
    {{-- Tambah Roles --}}
    {{-- @hasrole('ADMIN') --}}
    @if (auth()->user()->hasRole('SUPER ADMINISTRATOR'))
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100 card-border-shadow-primary">
                <div class="row h-100">
                    <div class="col-sm-5">
                        <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-3">
                            <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}"
                                class="img-fluid mt-sm-4 mt-md-0" alt="add-new-roles" width="83" />
                        </div>

                    </div>
                    <div class="col-sm-7">
                        <div class="card-body text-sm-end text-center ps-sm-0">
                            <a href="{{ route('role.kelolaroles') }}"
                                class="btn btn-primary mb-2 text-nowrap add-new-role">
                                Tambah Level
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- @endhasrole --}}

    @foreach ($roles as $role)
        {{-- @dd($role->users) --}}
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card card-border-shadow-warning">
                <div class="card-body mb-0">
                    <div class="d-flex justify-content-between">
                        <small class="fw-normal mb-2">Total <span class="fw-bold">{{ $role->users_count }}</span>
                            Pengguna</small>
                        <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                            @foreach ($role->users as $user)
                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $user->name }}"
                                    class="avatar avatar-sm pull-up">
                                    <img class="rounded-circle"
                                        src="{{ $user->image == null ? 'assets/img/avatars/no-image.png' : '/storage/hris/profile/' . $user->image }} "
                                        alt="Avatar" />
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between align-items-end mt-1">
                        <div class="role-heading">
                            <h5 class="mb-2">{{ $role->name }}</h5>
                            @can('edit-role')
                                <a href="{{ route('role.kelolaroles', $role->token) }}" class="text-warning">
                                    <span>Edit Level</span>
                                </a>
                            @endcan
                        </div>
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
                Showing {{ $roles->firstItem() }} to {{ $roles->lastItem() }} of
                {{ $roles->total() }}
                results
            </small>
        </div>

        <div class="d-lg-inline-block">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-primary justify-content-center">
                    {{-- Previous Button --}}
                    <li class="page-item prev">
                        @if ($roles->onFirstPage())
                            <a class="page-link disabled" href="#">
                                <i class="ti ti-chevron-left ti-xs"></i>
                            </a>
                        @else
                            <a class="page-link" href="{{ $roles->previousPageUrl() }}">
                                <i class="ti ti-chevron-left ti-xs"></i>
                            </a>
                        @endif
                    </li>

                    {{-- Page Number Links --}}
                    @php
                        $start = max(1, $roles->currentPage() - 2);
                        $end = min($roles->lastPage(), $roles->currentPage() + 2);
                    @endphp

                    {{-- Page Number Links --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $roles->currentPage())
                            <li class="page-item active">
                                <a class="page-link" href="{{ $roles->url($page) }}" data-i="{{ $page }}"
                                    data-page="{{ $roles->perPage() }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $roles->url($page) }}" data-i="{{ $page }}"
                                    data-page="{{ $roles->perPage() }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endfor

                    {{-- Next Button --}}
                    <li class="page-item next">
                        @if ($roles->hasMorePages())
                            <a class="page-link pagination-next" href="{{ $roles->nextPageUrl() }}">
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
