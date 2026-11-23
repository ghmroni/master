@extends('template/core')

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
    <div class="divider divider-success">
        <div class="divider-text">Pengguna Online</div>
    </div>
    <div id="dtActivity" class="row"></div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dashboard-admin.js') }}"></script>
@endpush
