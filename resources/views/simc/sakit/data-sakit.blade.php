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
    @can('simc-approval-sakit')
        <div class="text-md-end mb-3">
            <div class="btn-group">
                <button class="btn btn-sm btn-warning btnTambah"><i class="fas fa-syringe me-1"></i>Tambah Sakit</button>
                @if ($pending > 0)
                    <button class="btn btn-sm btn-primary btnApproval"><i class="fas fa-cart-plus me-1"></i>Approval</button>
                @endif
                @if ($deleted > 0)
                    <button class="btn btn-sm btn-info btnRecycle"><i class="fas fa-recycle me-1"></i>Recycle</button>
                @endif
            </div>
        </div>
    @endcan
    <div class="card card-body border shadow">
        <div class="table-responsive">
            <table id="tableSakit" class="table table-hover table-bordered table-striped table-sm text-nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nip</th>
                        <th>Nama Lengkap</th>
                        <th>Penempatan</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/simc/sakit/data-sakit.js') }}"></script>
@endpush
