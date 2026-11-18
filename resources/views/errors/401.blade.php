@extends('template/core')

@section('content')
    <div class="card card-body text-center">
        <div class="mt-0 mb-3">
            <img src="{{ asset('') }}assets/img/illustrations/403.jpg" alt="403" width="300" class="img-fluid" />
        </div>
        <h2>
            {{-- <span class="text-danger">{{ $status }} |</span> --}}
            <span class="text-dark">{{ $exception }}</span>
        </h2>
    </div>
@endsection
