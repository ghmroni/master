@extends('template.core')
@section('content')
    <div class="card card-body shadow">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror
        <div class="table-responsive">
            <table id="tableHasil" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal Panggil</th>
                        <th>Nama Lengkap</th>
                        <th>CFIT</th>
                        <th>MBTI</th>
                        <th>PAPI</th>
                        <th>DISC</th>
                        <th>Wawancara</th>
                        <th>Analisis</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/psikotest/hasil-psikotest.js') }}"></script>
@endpush
