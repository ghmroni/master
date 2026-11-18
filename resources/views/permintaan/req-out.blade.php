@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body">
        <div class="modal-footer">
            <button class="btn btn-instagram btn-sm Tambah"><i class="fa fa-plus me-1"></i>Tambah</button>
        </div>
        <div class="table-responsive">
            <table id="dataTable" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size:12px;">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Lokasi Pekerjaan</th>
                        <th>Catatan</th>
                        <th>Tanggal Keluar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @php
        $alasan = ['MENGUNDURKAN DIRI', 'HABIS KONTRAK', 'MENINGGAL DUNIA'];
    @endphp
    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formOut">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Pilih Karyawan</label>
                                <select name="karyawanid" id="karyawanid" class="select2 form-select"></select>
                            </div>
                            <div class="col-md-12" id="boxDetail" style="display: none">
                                <div class="card shadow-none bg-transparent border border-primary mb-3">
                                    <div class="card-body">
                                        {{-- <h5 class="card-title text-center">Detail Karyawan</h5> --}}
                                        <div class="divider divider-secondary">
                                            <div class="divider-text text-secondary fw-bold">DETAIL KARYAWAN</div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered text-nowrap"
                                                style="width: 100;font-size:12px">
                                                <tr>
                                                    <th width="15%">NIP Karyawan
                                                        <input type="hidden" id="idreq" name="idreq"
                                                            class="form-control" readonly>
                                                    </th>
                                                    <td id="vnip"></td>
                                                </tr>
                                                <tr>
                                                    <th>Nama Lengkap</th>
                                                    <td id="vnama"></td>
                                                </tr>
                                                <tr>
                                                    <th>Lokasi Pekerjaan</th>
                                                    <td id="vlokasi"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="divider divider-danger">
                                                <div class="divider-text text-danger fw-bold">FORM REQUEST OUT KARYAWAN
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Alasan Keluar</label>
                                                <select name="statusout" id="statusout" class="form-select">
                                                    <option value="">-pilih-</option>
                                                    @foreach ($alasan as $item)
                                                        <option value="{{ $item }}">{{ $item }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal Keluar</label>
                                                <input class="form-control tanggal" type="text" id="tglkeluar"
                                                    name="tglkeluar" placeholder="YYYY-MM-DD" />
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Catatan</label>
                                                <textarea name="catatan" id="catatan" rows="5" class="form-control" placeholder="Alasan keluar, noref, etc"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/permintaan/req-out.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
