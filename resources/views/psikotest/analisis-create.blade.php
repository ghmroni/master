@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/quill/typography.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/quill/katex.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/quill/editor.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/highlight/highlight.css" />
@endpush

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <form id="formResume" class="needs-validation" novalidate>
        @csrf
        <h4><span class="text-muted fw-light">Data Pelamar</h4>
        <div class="card card-body border shadow">
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal Test</label>
                <div class="col-sm-10">
                    <input type="hidden" class="form-control" id="idpanggil" name="idpanggil" readonly
                        value="{{ $token }}">
                    <input type="text" class="form-control" id="tgltest" name="tgltest" readonly
                        value="{{ Carbon::parse($pemanggilan->tgl_panggil)->translatedFormat('l, d F Y') ?? '' }}">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Keperluan</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="keperluan" name="keperluan" readonly
                        value="Seleksi Kandidat {{ ucwords(strtolower($lowongan->pekerjaan_dilamar)) ?? '' }} {{ ucwords(strtolower($lowongan->Project->name)) ?? '' }}{{ ' - ' . ucwords(strtolower($lowongan->penempatan)) ?? '' }}">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Lengkap</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="nama" name="nama" readonly
                        value="{{ $pelamar->nama ?? '' }}">
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Pendidikan Terakhir</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="pendidikan" name="pendidikan" readonly
                        value="{{ $pelamar->jenjang_pendidikan ?? '' }}">
                </div>
            </div>
        </div>

        <h4 class="mt-3"><span class="text-muted fw-light">Hasil Psikotest</h4>
        <div class="card card-body border shadow">
            <div class="mb-4">
                <label class="form-label" for="basic-default-fullname">CFIT</label>
                <textarea name="cfit" id="cfit" class="form-control">{{ 'Memiliki Hasil IQ dengan skor ' . ($pemanggilan->cfit->skor_standar ?? '0') . ' dengan kualifikasi ' . ($pemanggilan->cfit ? ucwords(strtolower($pemanggilan->cfit->keterangan)) : '') . '.' }}</textarea>
            </div>
            <div class="mb-4">
                <label class="form-label" for="basic-default-fullname">DISC</label>
                <div id="discQuill">
                </div>
                <input type="hidden" class="form-control" name="disc" id="disc" required>
                <div class="invalid-feedback disc">Inputan masih kosong</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="basic-default-fullname">PAPI</label>
                <div id="papiQuill"></div>
                <input type="hidden" class="form-control" name="papi" id="papi" required>
                <div class="invalid-feedback papi">Inputan masih kosong</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="basic-default-fullname">Interview</label>
                <input type="text" class="form-control" id="interview" name="interview"
                    value="{{ $pemanggilan->wawancara ? ucwords($pemanggilan->wawancara->hasil) : '' }}" autocomplete="off"
                    required>
                <div class="invalid-feedback">Hasil Interview masih kosong.</div>
            </div>
            <div class="mb-4">
                <label class="form-label">Petugas Pemeriksa</label>
                <input type="text" class="form-control" id="petugas" name="petugas" required autocomplete="off"
                    value="{{ auth()->user()->name }}">
                <input type="hidden" class="form-control" id="created" name="created" readonly autocomplete="off"
                    value="{{ Carbon::parse(date('Y-m-d H:i:s'))->translatedFormat('l, d F Y') }}">
                <div class="invalid-feedback">Nama Petugas masih kosong.</div>
            </div>
        </div>

        <div class="modal-footer mt-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
        </div>
    </form>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/psikotest/analisis-create.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/quill/katex.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/quill/quill.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/highlight/highlight.js"></script>
@endpush
