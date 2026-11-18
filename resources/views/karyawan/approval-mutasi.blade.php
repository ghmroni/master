@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush

@section('content')
    <div class="card card-body">
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

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Mutasi Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAppMutasi">
                        @csrf
                        <input type="hidden" class="form-control" id="idmutasi" name="idmutasi" readonly>
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
                        <div class="row" id="btnProses" style="display: none">
                            <div class="d-grid gap-2 col-lg-6"></div>

                            <div class="d-grid gap-2 col-lg-6">
                                <button class="btn btn-primary rounded-pill btn-lg" type="submit">Proses</button>
                            </div>

                        </div>
                    </form>

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
    <script src="{{ asset('assets/customjs/karyawan/approval-mutasi.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
