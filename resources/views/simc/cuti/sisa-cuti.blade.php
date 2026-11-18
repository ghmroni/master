@extends('template.core')

@section('content')
    <div class="card card-body border">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror

        <div class="table-responsive">
            <table id="dataTable" class="table table-hover table-striped table-bordered text-nowrap table-sm"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th>NO</th>
                        <th>Nip</th>
                        <th>Nama Karyawan</th>
                        <th>Lokasi Pekerjaan</th>
                        <th>Tanggal Masuk</th>
                        <th>Sisa Cuti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md mb-4 mb-md-2">
                            <small class="text-light fw-medium">Data Cuti Per Tahun</small>
                            <div id="dataRecord">
                            </div>
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
    <script src="{{ asset('assets/customjs/simc/cuti/sisa-cuti.js') }}"></script>
@endpush
