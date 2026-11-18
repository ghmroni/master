@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/monthSelect.css') }}">
@endpush

@section('content')
    <div class="col-md-4 mb-3">
        <label class="form-label">Periode Penggajian</label>
        <div class="input-group">
            <button class="btn btn-dark" type="button">
                <i class="far fa-calendar-check"></i>
            </button>
            <input type="text" class="form-control" placeholder="MM YYYY" id="periode" name="periode"
                value="{{ date('Y-m') }}" />
        </div>
    </div>

    <div class="card card-body">
        <div class="table-responsive">
            <table id="tableSlip" class="table table-hover table-striped text-nowrap" style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>PERIODE</th>
                        <th>NIP</th>
                        <th>NAMA LENGKAP</th>
                        <th>LOKASI PENEMPATAN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/gaji/slip-gaji.js') }}"></script>
    <script src="{{ asset('assets/customjs/monthSelect.js') }}"></script>
@endpush
