@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body border shadow mb-4">
        <form id="formPerorang">
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
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input class="form-control" type="text" id="dari" name="dari" placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input class="form-control" type="text" id="sampai" name="sampai" placeholder="YYYY-MM-DD"
                        disabled />
                </div>

            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search me-1"></i>Cari
                        Kehadiran</button>

                </div>
            </div>
        </form>
    </div>

    <div class="card card-body border shadow animate__animated animate__slideInUp" id="dtList" style="display: none">
        <div class="modal-footer">
            <button class="btn btn-success btn-sm btnDownload" disabled><i class="ti ti-cloud-down me-1"></i>Download
                Lampiran</button>
        </div>
        <div class="table-responsive">
            <table id="tablePerorang" class="table table-hover table-striped" style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Hari</th>
                        <th>Tanggal</th>
                        <th>Absen Masuk</th>
                        <th>Absen Pulang</th>
                        <th>Photo Absensi</th>
                        <th>Monitoring</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/absensi/perorang.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
