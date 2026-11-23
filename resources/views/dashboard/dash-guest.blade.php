@extends('template/core')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Halaman Awal Guest</h5>
                <div class="card-body">
                    @foreach (auth()->user()->unreadNotifications as $notification)
                        {{-- @dd($notification->data[0]['url']) --}}
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dashboard-guest.js') }}"></script>
@endpush
