@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="mb-3">
        <div class="btn-group">
            <button class="btn btn-sm btn-dark btnBack"><i class="fas fa-backward me-1"></i>Kembali</button>
        </div>
    </div>
    <div class="card card-body border">
        <form id="formCuti">
            @csrf
            <div class="row mb-3">
                <div class="col-md-12 mb-2">
                    <label class="form-label">Pilih Karyawan</label>
                    <select name="karyawanid" id="karyawanid" class="select2 form-select">
                        <option value="">-pilih-</option>
                        @foreach ($karyawans as $item)
                            <option value="{{ $item->token_id }}">{{ $item->nip }} | {{ $item->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-2">
                    <label class="form-label">Lokasi Penempatan</label>
                    <span class="form-control" id="lokasipekerjaan">-</span>
                    <input type="hidden" class="form-control" id="namalengkap" name="namalengkap" readonly>
                    <input type="hidden" class="form-control" id="penempatan" name="penempatan" readonly>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" id="kategori" class="form-select select2">
                        <option value="TAHUNAN">TAHUNAN</option>
                        <option value="GRAVIDA">GRAVIDA</option>
                        <option value="ISTIMEWA">ISTIMEWA</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Jumlah Hari</label>
                    <div class="input-group">
                        <input class="form-control disabled" type="text" id="hari" name="hari" placeholder="0"
                            maxlength="1" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
                        <span class="input-group-text">Sisa Cuti :
                            <span class="fw-bold ms-1" id="saldoCutiContainer">0</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input class="form-control disabled" type="text" id="dari" name="dari"
                        placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input class="form-control disabled" type="text" id="sampai" name="sampai"
                        placeholder="YYYY-MM-DD" disabled />
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tanggal Masuk</label>
                    <input class="form-control disabled" type="text" id="tglmasuk" name="tglmasuk"
                        placeholder="YYYY-MM-DD" disabled />
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control disabled" cols="30" rows="8"></textarea>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Surat Cuti</label>
                    <input type="file" id="gambar" name="gambar" class="dropify" accept=".jpeg,.jpg,.png"
                        data-error="Gambar masih kosong" data-default-file="" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="ti ti-cloud-up me-1"></i>Proses</button>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/simc/cuti/tambah-cuti.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
@endpush
