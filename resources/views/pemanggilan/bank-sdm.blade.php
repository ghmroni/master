@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <form id="formUser">
                @csrf
                <div class="card">
                    <div
                        class="card-header sticky-element bg-label-dark d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                        <div class="col-md-3">
                            <label class="text-dark">Calon Kandidat</label>
                        </div>
                        <div class="col-md-5">
                            <div id="errorChecklist" class="alert alert-danger text-center" role="alert"
                                style="display: none">

                                <i class="fas fa-user-plus me-1"></i> Silakan pilih pelamar untuk dikirim.
                            </div>
                        </div>
                        <div class="col-md-1">
                            <span id="totalDipilih" class="badge bg-label-info text-dark">0 dipilih</span>
                        </div>

                        <div class="action-btns">
                            @can('banksdm')
                                <button type="button" class="btn btn-primary resendKandidat"><i
                                        class="fas fa-user-plus me-1"></i>Resend</button>
                                <button type="submit" class="btn btn-info"><i class="fas fa-paper-plane me-1"></i>
                                    Proses Kirim</button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 mt-3 mb-4">
                                <label class="form-label">Lokasi Penempatan</label>
                                <div class="input-group">
                                    @can('banksdm')
                                        <span class="form-control" id="keperluan" style="cursor: pointer"></span>
                                        <button class="btn btn-dark" type="button" id="changeLokasi">
                                            <i class="fas fa-street-view"></i>
                                        </button>
                                    @endcan
                                </div>
                                <div id="errorMsg" class="invalid-feedback">
                                    Lokasi Penempatan wajib diisi.
                                </div>
                                <input type="hidden" class="form-control" id="keperluanid" name="keperluanid">
                            </div>

                            <table id="pelamarUser" class="table table-hover table-striped table-hover"
                                style="width: 100%;font-size:12px">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Tanggal Panggil</th>
                                        <th>Project</th>
                                        <th>Nama Pelamar</th>
                                        <th>Pendidikan</th>
                                        <th>Jurusan</th>
                                        <th>Usia</th>
                                        <th>
                                            <div class="form-check-info">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="kirimUlang" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="role-title mb-1">Kirim Ulang</h3>
                        <p class="text-muted">Kirim ulang kandidat yang sudah di test sampai user.</p>
                    </div>
                    <form id="formResend">
                        <div class="table-responsive">
                            <table id="tabelResend" class="table table-hover table-striped nowrap"
                                style="width: 100%;font-size: 12px">
                                <thead class="table-light">
                                    <tr>
                                        <th>pilih</th>
                                        <th>Alasan / Catatan</th>
                                        <th>Nama Lengkap</th>
                                        <th>Lokasi Penempatan</th>
                                        <th>Riwayat</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary me-1"><i class="fas fa-paper-plane me-1"></i>
                                Kirim Ulang</button>
                        </div>
                    </form>
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
                    <button type="button" class="btn btn-primary changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/pemanggilan/bank-sdm.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
