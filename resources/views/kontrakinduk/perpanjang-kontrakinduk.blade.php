@extends('template.core')

@php
    use Carbon\Carbon;
@endphp
@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <style>
        form .form-label {
            color: #999;
            text-transform: uppercase;
        }

        /* 1. Berikan batas tinggi dan aktifkan scroll pada container table-responsive */
        .table-responsive {
            max-height: 450px;
            /* Batasi tinggi maksimal agar scroll vertikal aktif */
            overflow-x: auto;
            /* Aktifkan scroll horizontal */
            overflow-y: auto;
            /* Aktifkan scroll vertikal */
        }

        /* 2. Freeze Baris Header (Scroll Vertikal) */
        .table-biaya-dinamis thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            /* Menjaga header tetap di lapisan atas */
            /* background-color: #2b2c40 !important; */
            /* Sesuaikan warna dark-mode template Anda */
            /* color: #fff !important; */
        }

        /* 3. Freeze Kolom Pertama 'JENIS BIAYA' (Scroll Horizontal) */
        .table-biaya-dinamis tbody tr td:first-child,
        .table-biaya-dinamis thead tr th:first-child {
            position: sticky;
            left: 0;
            z-index: 5;
            /* background-color: #2b2c40 !important; */
            background-color: #DFDFE3 !important;
            /* Warna background cell pembeku (lebih gelap) */
            box-shadow: 5px 0 5px -3px rgba(0, 0, 0, 0.3);
            /* Memberikan efek bayangan batas potong */
            color: #44475B !important;
        }

        /* 4. Khusus untuk persimpangan Pojok Kiri Atas (Header JENIS BIAYA) */
        .table-biaya-dinamis thead tr th:first-child {
            z-index: 15;
            background-color: #DFDFE3 !important;
            color: #000 !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-12 mb-4">

        <div class="alert alert-info alert-dismissible d-flex align-items-baseline" role="alert">
            <span class="alert-icon alert-icon-lg text-info me-2">
                <i class="ti ti-bell ti-sm text-warning"></i>
            </span>
            <div class="d-flex flex-column ps-1">
                <h5 class="alert-heading mb-2">Informasi</h5>
                <p class="mb-0">
                    Proses perpanjangan kontrak akan otomatis menonaktifkan Kontrak ( Nomor : {{ $kontrak->nomor }} ) dan
                    settingan project sebelumnya.
                </p>
            </div>
        </div>

        <div id="wizard-validation" class="bs-stepper mt-2">
            <div class="bs-stepper-header bg-light">
                <div class="step" data-target="#account-details-validation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Informasi</span>
                            <span class="bs-stepper-subtitle">Detail Kontrak Induk</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="ti ti-chevron-right"></i>
                </div>
                <div class="step" data-target="#personal-info-validation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Komponen</span>
                            <span class="bs-stepper-subtitle">Nama Header dan Biaya</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="ti ti-chevron-right"></i>
                </div>
                <div class="step" data-target="#social-links-validation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Harga</span>
                            <span class="bs-stepper-subtitle">Setting Harga</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="bs-stepper-content">
                <form id="wizard-validation-form" onSubmit="return false">
                    @csrf
                    <!-- Account Details -->
                    <div id="account-details-validation" class="content">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Judul Kontrak Induk</label>
                                <input type="hidden" class="form-control" id="idinduk" name="idinduk" readonly />
                                <input type="hidden" class="form-control" id="parentId" name="parentId" readonly
                                    value="{{ $kontrak->id }}" />
                                <input type="hidden" class="form-control" id="status" name="status" value="perpanjang"
                                    readonly />
                                <input type="text" class="form-control text-uppercase" id="judul" name="judul"
                                    placeholder="Judul Kontrak Induk" autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Kontrak Induk</label>
                                <input type="text" class="form-control text-uppercase" id="nomor" name="nomor"
                                    placeholder="Nomor Kontrak Induk" autocomplete="off"
                                    oninput="this.value = this.value.replace(/\s+/g, '')" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kontrak Induk</label>
                                <div class="form-check mt-1">
                                    <label class="form-check-label form-check-success me-5">
                                        <input name="jenis" class="form-check-input" type="radio" value="SDM"
                                            id="jenis">
                                        KONTRAK SDM
                                    </label>
                                    <label class="form-check-label ms-3 me-5">
                                        <input name="jenis" class="form-check-input" type="radio" value="PEKERJAAN"
                                            id="jenis">
                                        KONTRAK PEKERJAAN
                                    </label>
                                    <label class="form-check-label form-check-info ms-3">
                                        <input name="jenis" class="form-check-input" type="radio" value="PO"
                                            id="jenis">
                                        PO
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Terbit</label>
                                <input type="text" name="tglterbit" id="tglterbit" class="form-control"
                                    placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kontrak Dari</label>
                                <input type="text" name="dari" id="dari" class="form-control"
                                    placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kontrak Sampai</label>
                                <input type="text" name="sampai" id="sampai" class="form-control"
                                    placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control text-uppercase" name="keterangan" id="keterangan" rows="3" autocomplete="off"
                                    placeholder="Keterangan"></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-1">Selanjutnya</span>
                                    <i class="ti ti-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Personal Info -->
                    <div id="personal-info-validation" class="content">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card shadow bg-transparent border border-info mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-center text-uppercase">Nama Header / Unitkerja</h5>
                                        <div class="row">
                                            <div class="col-md-12 nopadding mb-2">
                                                <div class="input-group">
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="unitkerja" name="unitkerja[]" list="list_unitkerja"
                                                        placeholder="Ketik / pilih unitkerja" autocomplete="off">
                                                    <button class="btn btn-info unitkerja_fields" type="button">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="unitkerja_fields"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card shadow bg-transparent border border-primary mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-center text-uppercase">Jenis Biaya</h5>
                                        <div class="row">
                                            <div class="col-md-12 nopadding mb-2">
                                                <div class="input-group">
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="tunjangan" name="tunjangan[]" list="list_tunjangan"
                                                        placeholder="Jenis Biaya" autocomplete="off">
                                                    <button class="btn btn-primary tunjangan_fields" type="button">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="tunjangan_fields"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary btn-prev">
                                    <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-1">Selanjutnya</span>
                                    <i class="ti ti-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Social Links -->
                    <div id="social-links-validation" class="content">

                        <div class="row g-3">
                            <div class="col-md-12 mt-2">
                                <label class="form-label">File Kontrak Induk</label>
                                <input type="file" class="form-control" id="file" name="file"
                                    accept="application/pdf" />
                            </div>
                            <div class="col-12">
                                <div id="kontainer-setting-project" class="mb-0">
                                    <div class="project-setting-block p-4 mb-4 border border-secondary rounded shadow-sm"
                                        id="block-setting-0" data-index="0">
                                        <h5>SETTING PROJECT #1</h5>
                                        <label class="form-label">Project</label>
                                        <div class="fv-row-matrix">
                                            <div class="input-group">
                                                <span class="form-control lokasipekerjaan-text"
                                                    id="lokasipekerjaan-0"></span>
                                                <div id="btnLokasi">
                                                    <button class="btn btn-dark changeLokasi-btn" type="button"
                                                        data-id="0">
                                                        📍
                                                        {{-- <i class="fas fa-street-view"></i> --}}
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" class="form-control" id="project-0"
                                                name="setting[0][project_id]" readonly>
                                            <input type="hidden" class="form-control" id="dtproject-0"
                                                name="setting[0][project_detail]" readonly>
                                        </div>

                                        <div class="table-responsive mt-3">
                                            <!-- Tabel yang akan diisi secara dinamis -->
                                            <table
                                                class="table table-sm table-bordered text-nowrap align-middle small fs-xs table-biaya-dinamis"
                                                id="table-biaya">
                                                <thead class="table-light">
                                                    <!-- Header digenerate oleh JS -->
                                                </thead>
                                                <tbody>
                                                    <!-- Body digenerate oleh JS -->
                                                </tbody>
                                            </table>
                                        </div>


                                    </div>
                                </div>
                                <div class="modal-footer mt-0">
                                    <button class="btn btn-warning btn-sm" id="btn-tambah-setting"><i
                                            class="ti ti-plus"></i>Tambah
                                        Setting</button>
                                </div>
                            </div>
                            <hr />

                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary btn-prev">
                                    <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                </button>
                                <button class="btn btn-success btn-next btn-submit"><i
                                        class="ti ti-cloud-up me-1"></i>Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <datalist id="list_unitkerja"></datalist>
    <datalist id="list_tunjangan"></datalist>

    <div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Pilih Lokasi Penempatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" id="cari" class="form-control"
                                            placeholder="Search..." aria-label="Search...">
                                    </div>
                                </div>

                                <div id="jstree-project" class="dataProject"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-warning changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/kontrakinduk/perpanjang.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
