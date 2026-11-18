@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="mb-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-dark btnBack"><i class="fas fa-backward me-1"></i>Kembali</button>
        </div>
    </div>
    <div class="card card-body border">
        <form id="formIzin">
            @csrf
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pilih Karyawan <span class="text-danger fw-bold">*</span></label>
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
                    <input type="hidden" class="form-control" id="namalengkap" name="namalengkap" readonly>
                    <input type="hidden" class="form-control" id="penempatan" name="penempatan" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Dari Tanggal <span class="text-danger fw-bold">*</span></label>
                    <input class="form-control disabled" type="text" id="dari" name="dari"
                        placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sampai Tanggal <span class="text-danger fw-bold">*</span></label>
                    <input class="form-control disabled" type="text" id="sampai" name="sampai"
                        placeholder="YYYY-MM-DD" disabled />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Hari <span class="text-danger fw-bold">*</span></label>
                    <input class="form-control disabled" type="text" id="hari" name="hari" placeholder="0"
                        maxlength="1" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keterangan <span class="text-danger fw-bold">*</span></label>
                    <textarea name="keterangan" id="keterangan" class="form-control disabled" cols="30" rows="8"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="ti ti-cloud-up me-1"></i>Proses</button>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/simc/izin/tambah-izin.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
