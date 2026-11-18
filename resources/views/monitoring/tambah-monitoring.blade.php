@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body shadow">
        <form id="formMonitoring">
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
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Kegiatan</label>
                    <input class="form-control disabled" type="text" id="tanggalmonitoring" name="tanggalmonitoring"
                        placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prioritas</label>
                    <input class="form-control" type="text" id="prioritas" name="prioritas" value="NORMAL" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Uraian Kegiatan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control disabled" cols="30" rows="8"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Foto Kegiatan</label>
                    <input type="file" id="gambar" name="gambar" class="dropify" accept=".jpeg,.jpg,.png"
                        data-error="Gambar masih kosong" data-default-file="" />
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="ti ti-cloud-up me-1"></i>Simpan Monitoring</button>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/monitoring/tambah-monitoring.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
@endpush
