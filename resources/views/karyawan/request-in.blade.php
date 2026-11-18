@extends('template.core')

@section('content')
    <div class="card card-body">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror
        <div class="table-responsive">
            <table id="tableIn" class="table table-hover table-striped table-bordered text-nowrap"
                style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr>
                        <th>Tgl Permintaan</th>
                        <th>Nama Lengkap</th>
                        <th>Keperluan Project</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/karyawan/req-in.js') }}"></script>
@endpush
