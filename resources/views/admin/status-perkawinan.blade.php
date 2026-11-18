@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body">
        @can('tambah-statuskawin')
            <div class="modal-footer mb-3">
                <button class="btn btn-warning btn-sm Tambah"><i class="fas fa-home me-1"></i>Tambah Data</button>
            </div>
        @endcan
        <div class="table-responsive text-nowrap">
            <table id="tableKawin" class="table table-hover table-bordered border-top" style="font-size: 12px;width:100%">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Kode</th>
                        <th>Keterangan</th>
                        <th>Nilai PTKP</th>
                        <th>Kategori PTKP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formKawin">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Status</label>
                                <input type="hidden" class="form-control" id="idkawin" name="idkawin" readonly />
                                <input type="text" class="form-control text-uppercase" id="kode" name="kode"
                                    placeholder="TK, K0, K1 etc" maxlength="3" autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" class="form-control text-uppercase" id="keterangan" name="keterangan"
                                    placeholder="Keterangan" autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nilai PTKP</label>
                                <input type="text" class="form-control" id="nilai" name="nilai" placeholder="0"
                                    autocomplete="off" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori PTKP</label>
                                <input type="text" class="form-control text-uppercase" id="kategori" name="kategori"
                                    placeholder="A" maxlength="1" autocomplete="off" />
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" name="submitButton" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/admin/status-perkawinan.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
