@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body">
        <form id="formAbsensi">
            @csrf
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pilih Karyawan</label>
                    <select name="karyawanid" id="karyawanid" class="select2 form-select">
                        <option value="">-pilih-</option>
                        @foreach ($karyawans as $item)
                            <option value="{{ $item->token_id }}">{{ $item->nip }} | {{ $item->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lokasi Penempatan</label>
                    <span class="form-control" id="lokasipekerjaan">-</span>
                    <input type="hidden" class="form-control" id="penempatan" name="penempatan" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Absensi</label>
                    <input class="form-control disabled" type="text" id="tanggalabsensi" name="tanggalabsensi"
                        placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Waktu Absensi</label>
                    <input class="form-control disabled" type="text" id="waktuabsensi" name="waktuabsensi"
                        placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipe Absensi</label>
                    <select name="typeabsensi" id="typeabsensi" class="select2 form-control disabled">
                        <option value="">-pilih-</option>
                        <option value="I">Masuk</option>
                        <option value="O">Pulang</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="ti ti-cloud-up me-1"></i>Simpan Absensi</button>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/absensi/tambah-absensi.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
