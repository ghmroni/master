@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/leaflet/leaflet.css') }}" />
@endpush

@section('content')
    <div class="modal-footer">
        <button class="btn btn-primary add-new"><i class="fas fa-map-marker-alt me-2"></i>Tambah Kontak</button>
    </div>
    <div class="row">
        <div class="col-12 col-xl-8 mb-4">
            <div class="card h-100">
                <h6 style="margin-top:15px;margin-left:22px" class="mb-0">Peta Lokasi Kantor GHM</h6>
                <h6 style="margin-top:5px;margin-left:22px" class="text-primary mb-0" id="keterangan"></h6>
                <hr>
                <div class="card-body">
                    {{-- <hr class="my-4 mx-n4" /> --}}
                    <div class="row mb-3 mt-0">
                        <div class="col-md-12">
                            <div class="leaflet-map" id="dragMap" style="height: 450px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card overflow-hidden" style="height: 612px">
                <h6 style="margin-top:15px;margin-left:22px" class="mb-0">Daftar Lokasi Kantor GHM</h6>
                <h6 style="margin-top:5px;margin-left:22px" class="text-primary mb-0" id="ket"></h6>
                <hr>
                <div class="card-body mt-0" id="vertical-scroll">
                    <div class="row mt-0">
                        <div class="col-md-12 mb-3" id="daftarLokasi">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/kontak.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/leaflet/leaflet.js') }}"></script>
@endpush
