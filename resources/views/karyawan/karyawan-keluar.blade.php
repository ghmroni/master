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
            <table id="dataTable" class="table table-hover table-striped table-bordered nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th>NO</th>
                        <th>Nip</th>
                        <th>Nama Karyawan</th>
                        <th>Lokasi Pekerjaan</th>
                        <th>Jabatan</th>
                        <th>Tgl Keluar</th>
                        <th>Alasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/karyawan/karyawan-keluar.js') }}"></script>
@endpush
