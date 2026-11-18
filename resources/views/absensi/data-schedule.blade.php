@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/monthSelect.css') }}">
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

        .clickable {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="col-12">
        @can('absensi-upload-schedule')
            <div class="modal-footer">
                <button class="btn btn-sm btn-info uploadSchedule"><i class="ti ti-alarm me-1"></i>Upload Schedule</button>
            </div>
        @endcan
        <div class="card">
            <div class="card-header sticky-element bg-label-dark mb-2 pb-0">
                <form id="formShcedule">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Periode Schedule</label>
                            <div class="input-group">
                                <button class="btn btn-dark" type="button">
                                    <i class="far fa-calendar-check"></i>
                                </button>
                                <input type="text" class="form-control" placeholder="MM YYYY" id="periode"
                                    name="periode" value="{{ date('Y-m') }}" />
                            </div>
                        </div>
                        <div class="col-md-8 mb-2">
                            <label class="form-label">Lokasi Penempatan</label>
                            <div class="input-group">
                                <button class="btn btn-dark" type="button" id="changeLokasi">
                                    <i class="fas fa-street-view"></i>
                                </button>
                                <span class="form-control" id="lokasipekerjaan" style="cursor: pointer"></span>
                            </div>
                            <input type="hidden" class="form-control" id="projectid" name="projectid">
                            <input type="hidden" class="form-control" id="dtproject" name="dtproject">
                        </div>
                    </div>
                    <div class="modal-footer mb-0">
                        <button type="submit" class="btn btn-primary" id="btnDownload" disabled>
                            <i class="ti ti-cloud-down me-1"></i>Download
                            Schedule</button>
                    </div>
                </form>
            </div>

            <div class="card card-body animate__animated animate__slideInUp">
                <div class="table-responsive">
                    <table id="tableSchedule" class="table table-hover table-striped text-nowrap"
                        style="width: 100%;font-size: 12px">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Tanggal Schedule</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                                        <input type="text" id="cari" class="form-control" placeholder="Search..."
                                            aria-label="Search...">
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
                    <button type="button" class="btn btn-primary changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Schedule dipilih</h3>
                        <p class="text-muted p-title">
                            You also need to select a method by which the proxy authenticates to the directory serve.
                        </p>
                    </div>
                    <div class="row" id="choise">

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================================================================================== MODAL PERGANTIAN --}}
    <div class="modal fade" id="modalPergantian" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Detail Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formPergantian">
                        @csrf
                        <table class="table table-bordered" style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th rowspan="2" style="vertical-align: middle">Nama Karyawan</th>
                                    <th colspan="2">Schedule</th>
                                </tr>
                                <tr class="text-center">
                                    <th>Lama</th>
                                    <th>Baru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fv-row">
                                            <span id="namaKaryawan"></span>
                                            <input type="hidden" id="fromId" name="fromId" class="form-control">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fv-row">
                                            <input type="text" id="fromScLama" name="fromScLama"
                                                class="form-control text-center" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fv-row">
                                            <input type="text" id="fromScBaru" name="fromScBaru"
                                                class="form-control text-center" readonly>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="50%">
                                        <div class="fv-row">
                                            <select name="karyawan" id="karyawan" class="select2 form-select"></select>
                                            <input type="hidden" id="toId" name="toId" class="form-control"
                                                readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fv-row">
                                            <input type="text" id="toScBaru" name="toScBaru"
                                                class="form-control text-center" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fv-row">
                                            <input type="text" id="toScLama" name="toScLama"
                                                class="form-control text-center" readonly>
                                            <input type="hidden" id="namaKolom" name="namaKolom"
                                                class="form-control text-center" readonly>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"><i
                                    class="ti ti-cloud-up me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================================================================================== MODAL PERUBAHAN --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEdit">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label text-muted">Lokasi Penempatan</label>
                                <span id="lokasi" class="form-control" placeholder="Lokasi Penempatan"></span>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label text-muted">NIP</label>
                                <input type="text" id="nip" class="form-control" placeholder="Nip" readonly>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label text-muted">Nama Lengkap</label>
                                <input type="text" id="nama" class="form-control" placeholder="Nama Lengkap"
                                    readonly>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label text-muted">Schedule Sebelumnya</label>
                                <input type="text" id="oldSchedule" class="form-control" placeholder="Schedule"
                                    readonly>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Schedule Baru</label>
                                <input type="text" id="newSchedule" name="newSchedule"
                                    class="form-control text-uppercase" placeholder="New Schedule" required
                                    maxlength="6" autocomplete="off">
                                <input type="hidden" id="ids" name="ids" class="form-control"
                                    placeholder="ids" readonly>
                                <input type="hidden" id="kolom" name="kolom" class="form-control"
                                    placeholder="kolom" readonly>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"><i
                                    class="ti ti-cloud-up me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/monthSelect.js') }}"></script>
    <script src="{{ asset('assets/customjs/absensi/data-schedule.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
