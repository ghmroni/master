@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size:12px;">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Lokasi Pekerjaan</th>
                        <th>Catatan</th>
                        <th>Tanggal Keluar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/persetujuan/approval-out.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
