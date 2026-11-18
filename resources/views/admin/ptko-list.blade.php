@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush

@section('content')
    <div class="card card-body">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror
        @can('tambah-ptko')
            <div class="modal-footer mb-3">
                <button class="btn btn-primary btn-sm Tambah"><i class="fas fa-plus-circle me-1"></i>PTKO</button>
            </div>
        @endcan

        <div class="table-responsive">
            <table id="tablePtko" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Nomor PTKO</th>
                        <th>Tanggal PTKO</th>
                        <th>Keperluan Project</th>
                        <th>Status</th>
                        <th>Posisi</th>
                        <th>Keterangan</th>
                        <th>HRAPPS</th>
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
                    <form id="formPtko">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Keperluan Project</label>
                                <div class="input-group">
                                    <span class="form-control" id="keperluanproject"></span>
                                    @canany(['tambah-ptko', 'edit-ptko'])
                                        <button class="btn btn-dark" type="button" id="changeLokasi">
                                            <i class="fas fa-street-view"></i>
                                        </button>
                                    @endcanany
                                </div>
                                <input type="hidden" class="form-control" id="project" name="project" readonly>
                                <input type="hidden" class="form-control" id="idptko" name="idptko" readonly />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal PTKO</label>
                                <input type="text" class="form-control tanggal" id="tanggal" name="tanggal"
                                    placeholder="YYYY-MM-DD" autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor PTKO</label>
                                <input type="text" class="form-control text-uppercase" id="nomor" name="nomor"
                                    placeholder="Nomor PTKO" autocomplete="off" onkeydown="return event.keyCode !== 32;" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="select2 form-select" data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                    <option value="PENGGANTI">PENGGANTI</option>
                                    <option value="BARU">BARU</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Posisi</label>
                                <select name="posisi" id="posisi" class="select2 form-select" data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                    <option value="COURIER">COURIER</option>
                                    <option value="FMGA">FMGA</option>
                                    <option value="NON SKILL">NON SKILL</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
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
                    <h5 class="modal-title" id="exampleModalLabel3">Pilih Lokasi</h5>
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

    <!-- Modal -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Nomor</label>
                        <div class="col-sm-10">
                            <span id="vnomor" class="form-control">Nomor Ptko</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Nip</label>
                        <div class="col-sm-10">
                            <span id="vnip" class="form-control">Nip Karyawan</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                        <div class="col-sm-10">
                            <span id="vnama" class="form-control">Nama Karyawan</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Lokasi</label>
                        <div class="col-sm-10">
                            <span id="vlokasi" class="form-control">Lokasi Karyawan</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Status</label>
                        <div class="col-sm-10">
                            <span id="vstatus" class="form-control">Status Karyawan</span>
                        </div>
                    </div>
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
    <script src="{{ asset('assets/customjs/admin/ptko-list.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
