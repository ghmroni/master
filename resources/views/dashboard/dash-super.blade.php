@extends('template/core')


@section('content')
    {{-- <h5 class="py-3 mb-0 mt-0"><span class="text-muted fw-light">Pengguna Online</span></h5> --}}
    <div class="divider divider-success">
        <div class="divider-text">Pengguna Online</div>
    </div>
    <div id="dtActivity" class="row"></div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dashboard-super.js') }}"></script>
@endpush
