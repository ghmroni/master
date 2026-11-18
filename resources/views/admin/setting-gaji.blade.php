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

        @can('setting-gaji')
            <div class="modal-footer mb-3">
                <a href="{{ route('admin.tambahSettinggaji') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i>Setting Gaji
                </a>
            </div>
        @endcan
        <div class="table-responsive">
            <table id="dataTable" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Project</th>
                        <th>Cut Off Tanggal</th>
                        <th>Gaji Pokok</th>
                        <th>Hari Kerja</th>
                        <th>Type Potongan</th>
                        <th>Nilai Potongan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/admin/setting-gaji.js') }}"></script>
@endpush
