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

    <div class="row g-3">
        <div class="col-md-4 col-xl-4 mb-4">
            <div class="card overflow-hidden h-100 shadow">
                <h6 style="margin-top:15px;margin-left:22px" class="mb-0">Tanggal Pemanggilan</h6>
                <hr>
                <div class="card-body mt-0">
                    <ul class="list-unstyled mb-0">

                        <div id="resultContainer">
                            @include('pemanggilan._list', ['pemanggilans' => $pemanggilans])
                        </div>
                    </ul>
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
                                        <a class="page-link" href="{{ $pemanggilans->url($page) }}"
                                            data-i="{{ $page }}" data-page="{{ $pemanggilans->perPage() }}">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $pemanggilans->url($page) }}"
                                            data-i="{{ $page }}" data-page="{{ $pemanggilans->perPage() }}">
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
            </div>
        </div>

        <div class="col-12 col-xl-8 mb-4">
            <div class="card h-100 shadow">
                <h6 style="margin-top:15px;margin-left:22px" class="mb-0">Detail Pemanggilan</h6>
                <hr>
                <div class="card-body">
                    <div id="detail-pemanggilan">

                        <div class="bg-label-secondary p-2 rounded-2" style="height: 350px">
                            <h4>
                                <i class="ti ti-arrow-left ti-lg me-1"></i> Pilih tanggal pemanggilan
                            </h4>
                            <h6 class="text-muted ms-5 fst-italic">Detail pemanggilan ditampilkan disini</h6>
                            <div class="text-center mt-5">
                                <img src="{{ asset('assets/img/illustrations/file.png') }}" alt="" height="140">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalTanggalPanggil" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tanggal Pemanggilan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTanggal">
                        @csrf

                        <div id="dtDetail"></div>

                        <div class="row mt-3">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Tanggal Pemanggilan Baru</label>
                                <input type="text" id="tglpanggil" name="tglpanggil" class="form-control"
                                    placeholder="YYYY-MM-DD" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-warning text-black">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fullscreenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFullTitle">Form Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNilai" class="form-nilai" novalidate>
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-borderd table-hover" style="width: 100%;font-size: 12px">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>NO</th>
                                        <th>Nama Lengkap</th>
                                        <th>Hasil Test</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="dtRow"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary" form="formNilai">
                        <i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/pemanggilan/data-pemanggilan.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endpush
