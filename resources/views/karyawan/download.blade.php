@extends('template.core')

@push('extra_style')
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/ribbon-page.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <style>
        .sticky-element {
            position: sticky;
            top: 80px;
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
    <div class="col-12">
        <div class="card">

            <div class="card-header sticky-element bg-label-primary">
                <div class="row g-2 align-items-center">
                    <!-- Row 1 -->
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-sm-4 col-form-label" for="basic-default-name">Stataus Karyawan</label>
                            <div class="col-sm-8">
                                <select class="form-select" id="status">
                                    <option value="A">AKTIF</option>
                                    <option value="T">TIDAK AKTIF</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        @can('download-karyawan')
                            <button class="btn btn-primary btnDownload" disabled>
                                <i class="ti ti-cloud-down me-1"></i>Download Karyawan
                            </button>
                        @endcan
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" id="cari" class="form-control" placeholder="Search..."
                                aria-label="Search...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bordered shadow">
                <div id="jstree-project" class="dataProject mt-3"></div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/karyawan/download.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
