@extends('template.core')

@push('extra_style')
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
            <table id="tableRecycle" class="table table-hover table-bordered table-striped table-sm text-nowrap"
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
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/simc/sakit/recycle-sakit.js') }}"></script>
@endpush
