@extends('template.core')

@section('content')
    <div class="input-wrapper mb-3 input-group input-group-lg input-group-merge">
        <span class="input-group-text" id="basic-addon1"><i class="ti ti-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari lowongan, penempatan, etc..." aria-label="Search"
            aria-describedby="basic-addon1" id="searchInput" />
    </div>

    <div id="resultContainer">
        @include('lowongan._list', ['lowongans' => $lowongans])
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/lowongan/data.lowongan.js') }}"></script>
@endpush
