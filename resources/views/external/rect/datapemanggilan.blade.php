@extends('template.core')

@push('extra_style')
    <style>
        .sticky-element {
            position: sticky;
            top: 79px;
            z-index: 8;

            .window-scrolled & {
                inset-block-start: 0;
            }

            .layout-navbar-fixed & {
                inset-block-start: calc($navbar-height - .4375rem);
            }

            .layout-menu-fixed .layout-horizontal & {
                inset-block-start: calc($navbar-height * 2 - .25rem);
            }

            .layout-menu-fixed.layout-navbar-fixed .layout-horizontal & {
                inset-block-start: $navbar-height;
            }
        }
    </style>
@endpush

@section('content')
    @error('token_expired')
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <span class="alert-icon text-danger me-2">
                <i class="ti ti-ban ti-xs"></i>
            </span>
            {{ $message }}
        </div>
    @enderror
    <div class="input-wrapper mb-3 input-group input-group-lg input-group-merge">
        <span class="input-group-text" id="basic-addon1"><i class="ti ti-search"></i></span>
        <input type="text" class="form-control" placeholder="Tanggal, pelamar, pengguna..." aria-label="Search"
            aria-describedby="basic-addon1" id="searchInput" />
    </div>

    {{-- <div class="row"> --}}
    <div id="resultContainer" class="row mt-3">
        @include('external.rect._list', ['pemanggilans' => $pemanggilans])
    </div>

    <div class="align-items-center">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-round pagination-warning justify-content-center">

                <li class="page-item prev">
                    @if ($pemanggilans->onFirstPage())
                        <a class="page-link disabled" href="#">
                            <i class="ti ti-chevron-left ti-xs"></i>
                        </a>
                    @else
                        <a class="page-link" href="{{ $pemanggilans->previousPageUrl() }}">
                            <i class="ti ti-chevron-left ti-xs"></i>
                        </a>
                    @endif
                </li>
                {{-- Page Number Links --}}
                @php
                    $start = max(1, $pemanggilans->currentPage() - 2);
                    $end = min($pemanggilans->lastPage(), $pemanggilans->currentPage() + 2);
                @endphp

                {{-- Page Number Links --}}
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $pemanggilans->currentPage())
                        <li class="page-item active">
                            <a class="page-link" href="{{ $pemanggilans->url($page) }}" data-i="{{ $page }}"
                                data-page="{{ $pemanggilans->perPage() }}">
                                {{ $page }}
                            </a>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $pemanggilans->url($page) }}" data-i="{{ $page }}"
                                data-page="{{ $pemanggilans->perPage() }}">
                                {{ $page }}
                            </a>
                        </li>
                    @endif
                @endfor

                <li class="page-item next">
                    @if ($pemanggilans->hasMorePages())
                        <a class="page-link pagination-next" href="{{ $pemanggilans->nextPageUrl() }}">
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


@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/external/rect/datapemanggilan.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endpush
