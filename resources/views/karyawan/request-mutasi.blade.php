@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush

@section('content')
    <div class="card card-body">
        <div class="modal-footer mb-3">
            <button class="btn btn-primary btn-sm Tambah"><i class="fas fa-retweet me-1"></i>Tambah Mutasi</button>
        </div>
        <div class="table-responsive">
            <table id="dataTable" class="table table-hover table-striped text-nowrap" style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Status</th>
                        <th>Nip</th>
                        <th>Nama Karyawan</th>
                        <th>Project Sebelumnya</th>
                        <th>Project Tujuan</th>
                        <th>Tgl Efektif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formMutasi">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Pilih Karyawan</label>
                                <select name="karyawanid" id="karyawanid" class="select2 form-select"></select>
                            </div>
                            <div class="col-md-12" id="boxDetail" style="display: none">
                                <div class="card shadow bg-transparent border border-warning mb-3">
                                    <div class="card-body">
                                        {{-- <h5 class="card-title text-center">Detail Karyawan</h5> --}}
                                        <div class="divider divider-secondary">
                                            <div class="divider-text text-secondary fw-bold">DETAIL KARYAWAN</div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered text-nowrap"
                                                style="width: 100;font-size:12px">
                                                <tr>
                                                    <th width="15%">NIP Karyawan
                                                        <input type="hidden" id="projectold" name="projectold">
                                                    </th>
                                                    <td id="vnip"></td>
                                                </tr>
                                                <tr>
                                                    <th>Nama Lengkap</th>
                                                    <td id="vnama"></td>
                                                </tr>
                                                <tr>
                                                    <th>Lokasi Pekerjaan</th>
                                                    <td id="vlokasi"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="divider divider-warning">
                                                <div class="divider-text text-warning fw-bold">FORM PERUBAHAN DATA</div>
                                            </div>
                                            <div class="col-md-12 mb-3 mt-0">
                                                <label class="form-label">Project Tujuan</label>
                                                <div class="input-group">
                                                    <span class="form-control" id="lokasipekerjaan"
                                                        style="cursor: pointer"></span>
                                                    @canany(['mutasi-karyawan'])
                                                        <button class="btn btn-dark" type="button" id="changeLokasi">
                                                            <i class="fas fa-street-view"></i>
                                                        </button>
                                                    @endcanany
                                                </div>
                                                <input type="hidden" class="form-control" id="project" name="project">
                                                <input type="hidden" class="form-control" id="dtproject" name="dtproject">
                                            </div>
                                            <div class="mb-2 row">
                                                <label class="col-md-3 col-form-label">Tanggal Efektif</label>
                                                <div class="col-md-9">
                                                    <input class="form-control" type="text" id="tglefektif"
                                                        name="tglefektif" placeholder="YYYY-MM-DD" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="alasan" id="alasan" rows="10" class="form-control" placeholder="Alasan mutasi, no ref, etc"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Submit</button>
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

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Mutasi Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span id="statusmutasi"></span>
                    <div class="row mb-3">
                        <div class="col-md mb-3">
                            <div class="card mb-2 border shadow">
                                <div class="card-body">
                                    <div class="card-title header-elements">
                                        <h5 class="m-0 me-2">Project Sebelumnya</h5>
                                        <div class="card-title-elements ms-auto">
                                            <span class="badge bg-primary rounded-pill"></span>
                                        </div>
                                    </div>
                                    <p class="card-text" id="projectlama"></p>
                                </div>
                            </div>
                        </div>
                        <div class="w-100"></div>
                        <div class="col-md mb-3">
                            <div class="card mb-2 border shadow">
                                <div class="card-body">
                                    <div class="card-title header-elements">
                                        <h5 class="m-0 me-2">Project Tujuan</h5>
                                        <div class="card-title-elements ms-auto">
                                            <span class="badge bg-primary rounded-pill"></span>
                                        </div>
                                    </div>
                                    <p class="card-text" id="projectbaru"></p>
                                    <p class="mb-0 text-dark" id="tglmulai"></p>
                                    <p class="mb-0 text-dark" id="keterangan"></p>
                                </div>
                            </div>
                        </div>
                        <div class="w-100"></div>
                    </div>
                    {{-- <div class="alert alert-secondary alert-dismissible d-flex align-items-baseline mb-3" role="alert">
                        <span class="alert-icon alert-icon-lg text-secondary me-2">
                            <i class="ti ti-bookmark ti-sm"></i>
                        </span>
                        <div class="d-flex flex-column ps-1">
                            <h5 class="alert-heading mb-2 text-uppercase">Project Sebelumnya</h5>
                            <p class="mb-0 text-light" id="projectlama"></p>
                        </div>
                    </div>
                    <div class="alert alert-success alert-dismissible d-flex align-items-baseline shadow" role="alert">
                        <span class="alert-icon alert-icon-lg text-success me-2">
                            <i class="ti ti-check ti-sm"></i>
                        </span>
                        <div class="d-flex flex-column ps-1">
                            <h5 class="alert-heading mb-2 text-uppercase">Project Tujuan</h5>
                            <p class="mb-3" id="projectbaru"></p>
                            <p class="mb-0 text-dark" id="tglmulai"></p>
                            <p class="mb-0 text-dark" id="keterangan"></p>
                        </div>
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/karyawan/mutasi-karyawan.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
