@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/css/ribbon-page.css') }}">
@endpush

@section('content')
    <div class="input-wrapper mb-3 input-group input-group-lg input-group-merge">
        <span class="input-group-text" id="basic-addon1"><i class="ti ti-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari nama pengguna..." aria-label="Search"
            aria-describedby="basic-addon1" id="searchInput" />
    </div>

    <div id="resultContainer">
        @include('project._list-user', ['users' => $users])
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/project/akses-project.js') }}"></script>
@endpush
