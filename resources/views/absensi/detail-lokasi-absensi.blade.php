@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/leaflet/leaflet.css') }}" />
    <style>
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
            /* balikin icon biar tegak */
            font-size: 16px;
        }

        .marker-pin.inactive {
            background: red;
        }
    </style>
@endpush

@php
    if ($data) {
        $latLng = explode(',', $data->tag_location);
        $dtProject = explode(',', $data->penempatan);
        $last = end($dtProject);
    }
@endphp

@section('content')
    <form id="formLokasi">
        @csrf

        <div class="accordion mb-3 border shadow rounded" id="accordionWithIcon">
            <div class="card accordion-item active">
                <h2 class="accordion-header d-flex align-items-center">
                    <button type="button" class="accordion-button" data-bs-toggle="collapse"
                        data-bs-target="#accordionMaps" aria-expanded="true">
                        <i class="ti ti-map ti-xs me-2"></i>
                        Maps
                    </button>
                </h2>

                <div id="accordionMaps" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <div class="col-md-12">
                            <div class="leaflet-map" id="dragMap" style="height: 350px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-body border shadow animate__animated animate__slideInUp" id="dtMaps">
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label class="form-label">URL</label>
                    <div class="input-group">
                        <input type="url" name="url" id="url" placeholder="https://" class="form-control"
                            autocomplete="off" value="{{ $data->url_map ?? null }}">
                        <input type="hidden" class="form-control" id="idlokasi" name="idlokasi" readonly
                            value="{{ $data->id ?? null }}">
                        <button class="btn btn-info" type="button" id="generateUrl">
                            <i class="fas fa-map-marker-alt me-1"></i> Ambil Coordinat
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude</label>
                    <input class="form-control" type="text" id="lat" name="lat" placeholder="Latitude"
                        value="{{ $latLng[0] ?? null }}" autocomplete="off" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude</label>
                    <input class="form-control" type="text" id="lng" name="lng" placeholder="Longitude"
                        value="{{ $latLng[1] ?? null }}" autocomplete="off" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Koordinat</label>
                    <input class="form-control disabled text-uppercase" type="text" id="nama" name="nama"
                        placeholder="Nama Titik Absensi" autocomplete="off" value="{{ $data->nama_koordinat ?? null }}" />
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Radius (meter)</label>
                    <input class="form-control disabled" type="text" id="radius" name="radius" placeholder="0"
                        maxlength="3" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                        value="{{ $data->radius ?? null }}" />
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="A" @selected(($data->status ?? '') == 'A')>AKTIF</option>
                        <option value="T" @selected(($data->status ?? '') == 'T')>TIDAK AKTIF</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lokasi Penempatan</label>
                    <div class="input-group">
                        <button class="btn btn-dark" type="button" id="changeLokasi">
                            <i class="fas fa-street-view"></i>
                        </button>
                        <span class="form-control" id="lokasipekerjaan" style="cursor: pointer">
                            {!! implode(' ', $lokasi) !!}
                        </span>
                    </div>
                    <input type="hidden" class="form-control" id="projectid" name="projectid"
                        value="{{ $last ?? null }}">
                    <input type="hidden" class="form-control" id="dtproject" name="dtproject"
                        value="{{ $data->penempatan ?? null }}">
                </div>
            </div>
            @can('absensi-setlokasi')
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" id="btnPerlokasi">
                            <i class="ti ti-cloud-down me-1"></i> Simpan Lokasi Absensi
                        </button>
                    </div>
                </div>
            @endcan
        </div>
    </form>

    <div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Pilih Lokasi Penempatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" id="cari" class="form-control"
                                            placeholder="Search..." aria-label="Search...">
                                    </div>
                                </div>

                                <div id="jstree-project" class="dataProject"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/absensi/detail-lokasi-absensi.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/leaflet/leaflet.js') }}"></script>
@endpush
