@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
@endpush

@section('content')
    <div class="card card-body">
        <div class="table-responsive mb-4">
            <table id="dataTable" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Lokasi Penempatan</th>
                        <th>Status</th>
                        <th>BPJS Tenagakerja</th>
                        <th>BPJS Kesehatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="modalFullTitle">Modal title</h5> --}}
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="text-center mb-3">
                    <h3 class="modal-title mb-2">Detail</h3>
                    <p class="text-muted">Detail BPJS Karyawan, Keluarga dan Tambahan 1%</p>
                </div>

                <form id="formBpjs">
                    @csrf
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer mb-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        @can('edit-bpjs')
                            <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal
        fade" id="modalTambahan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="modalFullTitle">Modal title</h5> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="text-center mb-0">
                    <h3 class="modal-title mb-1">Tambahan</h3>
                    <p class="text-muted">Tambahan potongan 1% dari upah yang dilaporkan</p>
                </div>
                <div class="modal-body">

                    <div class="modal-footer">
                        <button class="btn btn-info me-1" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                            <i class="fas fa-plus-circle me-1"></i> Tambah Baru
                        </button>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-primary d-flex align-items-center" role="alert">
                            <span class="alert-icon text-primary me-2">
                                <i class="ti ti-user ti-xs"></i>
                            </span>
                            <label id="dtKar"></label>
                        </div>
                    </div>
                    <div class="collapse" id="collapseExample">
                        <div class="card bg-transparent border border-primary">
                            <div class="card-body">
                                <form id="formTambahan">
                                    @csrf
                                    <div id="input-tambahan"></div>
                                    @can('tambah-bpjs')
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary mb-0"> <i
                                                    class="ti ti-cloud-up me-1"></i>Simpan</button>
                                        </div>
                                    @endcan
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableTambah" class="table table-hover table-bordered table-striped text-nowrap"
                            style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>Sebagai</th>
                                    <th>Nama Lengkap</th>
                                    <th>Nomor KTP</th>
                                    <th>BPJS KES</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
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
    <script src="{{ asset('assets/customjs/bpjs/data-bpjs.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endpush
