@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/leaflet/leaflet.css') }}" />
    <style>
        .currentLocation.active {
            border: 2px solid #685DD8;
            /* biru bootstrap */
            /* background-color: #e9f5ff; */
            background-color: #DFDFE3;
            /* biru muda */
            border-radius: 6px;
            padding: 5px;
        }

        .currentLocation.active h6 {
            color: #685DD8;
            /* warna ungu */
            font-weight: bold;
            /* opsional biar lebih menonjol */
        }

        .marker-pin {
            width: 30px;
            height: 30px;
            background: #000;
            /* warna isi */
            border: 2px solid #fff;
            /* border putih */
            border-radius: 50% 50% 50% 0;
            /* bikin bulat + ada ujung */
            transform: rotate(-45deg);
            /* bikin jadi bentuk pin */
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            /* warna icon */
        }

        .marker-pin i {
            transform: rotate(45deg);
            font-size: 16px;
        }

        .marker-pin.inactive {
            background: red;
        }

        .card-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1050;
            overflow: hidden;
            /* cegah scrollbar */
        }

        .card-fullscreen .card-body {
            height: 100%;
            padding: 0;
        }

        .card-fullscreen .leaflet-map {
            width: 100%;
            height: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="card card-action mb-4 border shadow">
        <div class="card-header">
            <div class="card-action-title"><i class="ti ti-map me-1"></i>Maps</div>
            <div class="card-action-element">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="javascript:void(0);" class="card-expand"><i
                                class="tf-icons ti ti-arrows-maximize ti-sm text-primary"></i></a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-body">
            <div class="leaflet-map" id="dragMap"></div>
        </div>
    </div>

    @can('absensi-setlokasi')
        <div class="modal-footer">
            <a class="btn btn-info btn-sm text-black" href="{{ route('absensi.tambahLokasiAbsensi') }}"><i
                    class="fas fa-map-marker-alt me-1"></i>Tambah
                Lokasi
            </a>
        </div>
    @endcan

    <div class="card card-body mb-3 border shadow rounded">
        <div class="table-responsive">
            <table id="tableMaps" class="table table-striped table-bordered table-hover"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NO</th>
                        <th>Lokasi Penempatan</th>
                        <th>Nama Mesin</th>
                        <th>Radius</th>
                        <th>Koordinat</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/absensi/lokasi-absensi.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/leaflet/leaflet.js') }}"></script>
@endpush
