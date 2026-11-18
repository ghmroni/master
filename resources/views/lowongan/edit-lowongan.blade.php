@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/quill/typography.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/quill/katex.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/quill/editor.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/highlight/highlight.css" />
@endpush

@section('content')
    <div class="card card-body">
        <form id="formLowongan" class="needs-validation" novalidate>
            @csrf
            <div class="row">

                <div class="col-md-12 mb-3">
                    <label class="form-label">Judul Lowongan</label>
                    <input type="hidden" name="id" value="{{ $detail->id }}" readonly>
                    <input type="text" class="form-control text-uppercase" id="judul" name="judul"
                        placeholder="Judul Lowongan" required autocomplete="off">
                    <div class="invalid-feedback">Judul Lowongan masih kosong.</div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keperluan Project</label>
                    <select name="project_id" id="projectid" class="select2 form-select" data-allow-clear="true" required>
                        <option value="">-pilih-</option>
                    </select>

                    <div class="invalid-feedback"> Keperluan Project masih kosong</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Lokasi Penempatan</label>
                    <input type="text" class="form-control text-uppercase" id="penempatan" name="penempatan"
                        placeholder="Lokasi Penempatan" required autocomplete="off" />
                    <div class="invalid-feedback">Lokasi Penempatan masih kosong</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jabatan</label>
                    <input type="text" class="form-control text-uppercase" id="pekerjaandilamar" name="pekerjaan_dilamar"
                        placeholder="Lowongan Jabatan" required autocomplete="off" />
                    <div class="invalid-feedback">Lowongan Jabatan masih kosong</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pengalaman Kerja</label>
                    <input type="text" class="form-control text-uppercase" id="pengalaman" name="pengalaman"
                        placeholder="Fresh / Pengalaman" required autocomplete="off" />
                    <div class="invalid-feedback">Pengalaman Kerja masih kosong</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Mulai Lowongan</label>
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD" id="dari" name="start"
                        required />
                    <div class="invalid-feedback">Tanggal Mulai Lowongan masih kosong</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Berakhir Lowongan</label>
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD" id="sampai" name="end"
                        required>
                    <div class="invalid-feedback">Tanggal Berakhir Lowongan masih kosong</div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Kualifikasi Umum</label>
                    <input id="kualifikasi" class="form-control text-uppercase" name="kualifikasi"
                        placeholder="Kualifikasi Umum" required>
                    <small class="text-primary">ketik dan enter untuk menambahkan</small>
                    <div class="invalid-feedback">Kualifikasi Umum masih kosong</div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi Pekerjaan</label>
                    <div id="deskripsiQuill"></div>
                    <input type="hidden" class="form-control" name="deskripsi" id="deskripsi" required
                        value="{{ $detail->deskripsi }}">
                    <div class="invalid-feedback deskripsi">Deskripsi Pekerjaan masih kosong</div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Persyaratan</label>
                    <div id="persyaratanQuill"></div>
                    <input type="hidden" class="form-control" name="persyaratan" id="persyaratan" required
                        value="{{ $detail->persyaratan }}">
                    <div class="invalid-feedback persyaratan">Persyaratan masih kosong</div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/lowongan/edit-lowongan.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>

    <script src="{{ asset('') }}assets/vendor/libs/quill/katex.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/quill/quill.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/highlight/highlight.js"></script>
@endpush
