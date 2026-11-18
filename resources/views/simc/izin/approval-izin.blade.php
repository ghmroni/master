@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <style>
        table.dataTable td.wrap-text {
            white-space: normal !important;
            /* override nowrap */
            word-wrap: break-word;
        }
    </style>
@endpush

@section('content')
    <div class="mb-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-dark btnBack"><i class="fas fa-backward me-1"></i>Kembali</button>
        </div>
    </div>
    @error('token_expired')
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <span class="alert-icon text-danger me-2">
                <i class="ti ti-ban ti-xs"></i>
            </span>
            {{ $message }}
        </div>
    @enderror
    <div class="card card-body border shadow">
        <div class="table-responsive">
            <table id="tableApproval" class="table table-hover table-bordered table-striped table-sm text-nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nip</th>
                        <th>Nama Lengkap</th>
                        <th>Penempatan</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Keterangan</th>
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
                    <form id="formApproval">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIP</label>
                                <input type="hidden" id="idizin" name="idizin" class="form-control" readonly />
                                <input type="hidden" id="idkar" name="idkar" class="form-control" readonly />
                                <input type="text" id="nip" name="nip" class="form-control" placeholder="NIP"
                                    readonly />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" class="form-control"
                                    placeholder="Nama Lengkap" readonly />
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Lokasi Penempatan</label>
                                <span class="form-control" id="lokasipekerjaan">-</span>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Dari <span class="text-danger fw-bold">*</span></label>
                                <input type="text" id="dari" name="dari" class="form-control"
                                    placeholder="YYYY-MM-DD" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Sampai <span class="text-danger fw-bold">*</span></label>
                                <input type="text" id="sampai" name="sampai" class="form-control"
                                    placeholder="YYYY-MM-DD" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah Hari <span class="text-danger fw-bold">*</span></label>
                                <input type="text" id="hari" name="hari" class="form-control" placeholder="0"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="1" />
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="keterangan" rows="5" readonly></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-primary">Proses Approval</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/simc/izin/approval-izin.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
