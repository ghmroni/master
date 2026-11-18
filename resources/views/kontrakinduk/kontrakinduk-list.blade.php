@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror
        @can('tambah-kontrakinduk')
            <div class="modal-footer mb-3">
                <a href="{{ route('ki.tambah') }}" class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>Kontrak
                    Induk</a>
            </div>
        @endcan

        <div class="table-responsive">
            <table id="tableInduk" class="table table-hover table-bordered text-nowrap" style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Nomor</th>
                        <th>Judul</th>
                        <th>Periode</th>
                        <th>Setting Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalNonaktif" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNonaktif" class="needs-validation" novalidate>
                        @csrf
                        <div class="text-center mb-4">
                            <h3 class="role-title mb-2">Konfirmasi</h3>
                            <p class="text-muted">Ajukan alasan penonaktifan kontrak induk</p>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">Nomor Kontrak Induk</label>
                                <input type="text" id="nomor" class="form-control" placeholder="Nomor" readonly />
                                <input type="hidden" id="idkontrak" name="idkontrak" class="form-control" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">Alasan Penonaktifan</label>
                                <textarea name="alasan" class="form-control text-uppercase" id="alasan" name="alasan" rows="5" required></textarea>
                                <div class="invalid-feedback">Please enter your name.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">Lampiran</label>
                                <input type="file" id="file" name="file" class="dropify" accept="application/pdf"
                                    data-default-file="" />
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
    <script src="{{ asset('assets/customjs/kontrakinduk/kontrakinduk-list.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
@endpush
