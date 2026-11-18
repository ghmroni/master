@extends('template.core')


@section('content')
    <div class="row">
        <div class="col-xl-3 mb-4 col-md-4 col-6">
            <div class="card card-border-shadow-warning">
                <div class="card-header pb-0 text-center">
                    <h6 class="card-title mb-0 text-uppercase badge text-dark">Cetak PKWT</h6>
                </div>
                <div class="card-body">
                    {{-- <div id="expensesChart"></div> --}}
                    <div id="chartRound"></div>
                    <div class="mt-md-2 text-center mt-lg-3 mt-3">
                        <small class="text-muted mt-3">Progress cetak pkwt</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dash-legal.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush
