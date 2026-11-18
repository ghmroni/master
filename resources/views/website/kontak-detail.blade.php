@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/leaflet/leaflet.css') }}" />
@endpush

@section('content')
    <div class="card card-body">
        <form id="formContact" class="needs-validation" novalidate>
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-12">
                    <label class="form-label">Lokasi Map</label>
                    <div class="leaflet-map" id="dragMap"></div>
                    <input type="hidden" id="latInput" name="latInput" class="form-control" value="{{ $data->latitude }}">
                    <input type="hidden" id="lngInput" name="lngInput" class="form-control"
                        value="{{ $data->longitude }}">
                    <div class="invalid-feedback" id="map-error" style="display: none;">
                        Silakan pilih lokasi di peta.
                    </div>

                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" id="kategori" class="form-select" required>
                        <option value="">-pilih-</option>
                        <option value="PUSAT" {{ old('kategori', $data->kategori ?? '') == 'PUSAT' ? 'selected' : '' }}>
                            KANTOR PUSAT</option>
                        <option value="PERWAKILAN"
                            {{ old('kategori', $data->kategori ?? '') == 'PERWAKILAN' ? 'selected' : '' }}>KANTOR
                            PERWAKILAN</option>
                    </select>
                    <input type="hidden" id="id" name="id" class="form-control" placeholder="ID"
                        value="{{ $data->id ?? '' }}" readonly>
                    <div class="invalid-feedback">silahkan pilih kategori kantor</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Kantor</label>
                    <input type="text" id="name" name="name" class="form-control" required
                        placeholder="nama kantor pusat atau perwakilan" value="{{ strtoupper($data->name) }}">
                    <div class="invalid-feedback">silahkan masukan nama kantor</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" name="email" autocomplete="off" class="form-control"
                        placeholder="Alamat email" required value="{{ strtolower($data->email) }}" />
                    <div class="invalid-feedback">silahkan masukan alamat email.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fax</label>
                    <input type="text" id="fax" name="fax" class="form-control fax" placeholder="021" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Handphone</label>
                    <input type="text" id="hp" name="hp" class="form-control hp" autocomplete="off"
                        placeholder="+62" required pattern="^\+62\s[0-9]{3}\s[0-9]{4}\s[0-9]{3,4}$">
                    <div class="invalid-feedback">silahkan masukan nomor handphone.</div>
                </div>
                {{-- @dd(substr($data->handphone, 3)) --}}
                <div class="col-md-6">
                    <label class="form-label">Telephone</label>
                    <input type="text" id="tlp" name="tlp" class="form-control phone" placeholder="021"
                        required pattern="^0[2-9][0-9]-[0-9]{4}-[0-9]{4}$" autocomplete="off">
                    <div class="invalid-feedback">silahkan masukan nomor telephone.</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control maxlength" name="alamat" id="alamat" cols="30" rows="8" required
                        maxlength="255" placeholder="alamat lengkap">{{ $data->alamat }}</textarea>
                    <div class="invalid-feedback">silahkan masukan alamat perusahaan.</div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/kontak-detail.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/leaflet/leaflet.js') }}"></script>
@endpush
