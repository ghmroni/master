@extends('template.core')
@section('content')
    @if ($alertMessage)
        <div class="alert alert-{{ $alertType }} alert-dismissible animate__animated animate__fadeInDown" role="alert">
            <span class="alert-icon text-{{ $alertType }} me-2">
                <i class="ti ti-bell ti-xs"></i>
            </span>
            {!! $alertMessage !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-sm-6 col-lg-6 mb-4">
            <div class="card card-border-shadow-success">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="fas fa-user ti-md"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0" id="totalAktif">0</h4>
                    </div>
                    <p class="mb-1 text-success">Karyawan Aktif</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-6 mb-4">
            <div class="card card-border-shadow-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="far fa-user ti-md"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0" id="totalKeluar">0</h4>
                    </div>
                    <p class="mb-1 text-danger">Karyawan Keluar</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dash-hr.js') }}"></script>
@endpush
