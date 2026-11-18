@extends('template/core')


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Recruitment</h5>

            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dashboard-admin.js') }}"></script>
@endpush
