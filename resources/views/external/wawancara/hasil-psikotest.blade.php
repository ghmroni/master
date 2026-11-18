@extends('template.core')

@section('content')
    <div class="card card-body">
        <div class="table-responsive">
            <table id="tableHasil" class="table table-hover table-bordered text-nowrap" style="width:100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Tgl Pemanggilan</th>
                        <th>Nama Lengkap</th>
                        <th>CFIT</th>
                        <th>Mbti</th>
                        <th>Papi</th>
                        <th>Disc</th>
                        <th>Wawancara</th>
                        <th>Analisis</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/external/wawancara/hasil-psikotest.js') }}"></script>
@endpush
