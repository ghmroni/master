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

        td.ceklisPanggil {
            cursor: pointer !important;
        }

        td.ceklisPanggil:hover {
            background-color: #E68F3C;
            /* contoh warna highlight */
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <form id="formPanggil">
                @csrf
                <div class="card">
                    <div class="card-header sticky-element bg-label-dark">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="row">
                                    <label class="col-sm-5 col-form-label" for="basic-default-name">Tanggal
                                        Pemanggilan</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="tglpanggil" id="tglpanggil" required
                                            class="form-control" placeholder="YYYY-MM-DD">
                                        <div id="errorMsg" class="invalid-feedback">
                                            Tanggal pemanggilan wajib diisi!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-md-center">
                                <span id="totalDipilih" class="badge bg-label-warning">0 dipilih</span>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="btn-group">

                                    <button type="button" class="btn btn-warning invitePelamar"><i
                                            class="fas fa-user-plus me-1"></i>Tambah Kandidat</button>
                                    <button type="submit" class="btn btn-primary"><i
                                            class="far fa-calendar-plus me-1"></i>Proses
                                        Panggil</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-md-center">
                                <div id="errorChecklist" style="display: none">
                                    <div class="alert alert-danger alert-dismissible mb-0" role="alert">
                                        Silakan pilih pelamar untuk dipanggil.

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="table-responsive">
                                <table id="tabelPemanggilan" class="table table-hover table-bordered text-nowrap"
                                    style="width: 100%;font-size: 12px">
                                    <thead class="table-light">
                                        <tr>
                                            <th>pilih
                                                {{-- <div class="form-check-primary">
                                                    <input type="checkbox" id="select-allpanggil" class="form-check-input">
                                                </div> --}}
                                            </th>
                                            <th>Status</th>
                                            <th>Nama Lowongan</th>
                                            <th>Nama Pelamar</th>
                                            <th>Pendidikan</th>
                                            <th>Nama Sekolah</th>
                                            <th>Jurusan</th>
                                            <th>Tinggi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="exLargeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="role-title mb-1">Tambah Pelamar</h3>
                        <p class="text-muted">Form tambah pelamar</p>
                    </div>
                    <form id="formInvite">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Keperluan Project / Lowongan</label>
                                <select id="lowonganid" name="lowonganid" class="form-select" required>
                                    <option value="">-pilih-</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Dipilih</label>
                                <input type="text" id="totalInvite" class="form-control" placeholder="0" readonly>
                                <div class="invalid-feedback">
                                    pelamar belum dipilih
                                </div>
                            </div>
                        </div>
                        <table id="tabelInvite" class="table table-hover table-striped" style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>Pendidikan</th>
                                    <th>Nama Sekolah</th>
                                    <th>Jurusan</th>
                                    <th>Tinggi Badan</th>
                                    <th>pilih</th>
                                </tr>
                            </thead>
                        </table>
                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-warning">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/pemanggilan/pemanggilan.js') }}"></script>
@endpush
