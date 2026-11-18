@extends('template.core')

@section('content')
    <div class="card card-body">
        <div>

            <button class="btn btn-dark btn-sm back"><i class="fas fa-angle-double-left me-1"></i>Kembali</button>
        </div>

        @php
            use Carbon\Carbon;
        @endphp
        @can('edit-recruitment')
            <div class="modal-footer mb-2" id="modalFooter"></div>
        @endcan

        <div class="alert alert-info d-flex align-items-center" role="alert">
            <span class="alert-icon text-info me-2">
                <i class="ti ti-info-circle ti-xs"></i>
            </span>
            <h5 class="mt-0 mb-0 text-center fw-bold text-uppercase">Pemanggilan Tanggal
                {{ Carbon::parse($tanggal)->translatedFormat('d F Y') }}</h5>
            <div id="keterangan"></div>
        </div>


        <div class="table-responsive">
            <table id="tabelDetail" class="table table-hover table-bordered" style="width:100%;font-size:12px">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Lengkap</th>
                        <th>Project</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalTanggalPanggil" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tanggal Pemanggilan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTanggal">
                        @csrf

                        <div id="dtDetail"></div>

                        <div class="row mt-3">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Tanggal Pemanggilan Baru</label>
                                <input type="text" id="tglpanggil" name="tglpanggil" class="form-control"
                                    placeholder="YYYY-MM-DD" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col mb-3">
                                <div class="form-check form-check-warning">
                                    <input class="form-check-input" type="checkbox" value="1" id="notify"
                                        name="notify" checked />
                                    <label class="form-check-label text-warning" for="notify"> Kirim Notifikasi Email <i
                                            class="far fa-envelope  ms-1"></i></label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-warning text-black">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fullscreenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFullTitle">Form Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNilai" class="form-nilai" novalidate>
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-borderd table-hover" style="width: 100%;font-size: 12px">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>NO</th>
                                        <th>Nama Lengkap</th>
                                        <th>Hasil Test</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="dtRow"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary" form="formNilai">
                        <i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCamera" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Foto Peserta Psikotes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <video id="camera" autoplay playsinline width="100%"></video>
                    <canvas id="canvas" style="display:none"></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" id="btnCapture" class="btn btn-primary" style="display:none;">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- <input type="file" id="cameraPhoto" accept="image/*" capture="environment" hidden> --}}
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/external/rect/detail-pemanggilan.js') }}"></script>
@endpush
