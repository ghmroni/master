@extends('template.core')

@section('content')
    {{-- <div class="d-flex align-items-baseline">
        <span class="text-danger me-2"><i class="ti ti-circle-filled fs-6"></i></span>
        <div>
            <p class="mb-2 text-light">Data Bank belum lengkap.</p>
        </div>
    </div> --}}

    <div class="card card-body border">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror

        <div class="alert alert-danger" role="alert" id="dtbank" style="display: none">
            <span class="alert-icon text-danger me-2">
                <i class="ti ti-info-circle ti-xs"></i>
            </span>
            Oops! Terdapat Data BANK yang belum lengkap.
        </div>

        <div class="table-responsive">
            <table id="dataTable" class="table table-hover table-striped table-bordered text-nowrap table-sm"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th>NO</th>
                        <th>Nip</th>
                        <th>Nama Karyawan</th>
                        <th>Lokasi Pekerjaan</th>
                        <th>Jabatan</th>
                        <th>TGL Masuk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/karyawan/karyawan-aktif.js') }}"></script>
@endpush
