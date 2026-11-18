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
            <table id="tableApprove" class="table table-hover table-bordered text-nowrap" style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Keterangan</th>
                        <th>Nomor</th>
                        <th>Peminta</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/kontrakinduk/kontrakinduk-approval.js') }}"></script>
@endpush
