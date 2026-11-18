@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/monthSelect.css') }}">
@endpush

@section('content')
    <div class="card card-body border shadow mb-4">
        <form id="formRekap">
            @csrf
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lokasi Penempatan</label>
                    <div class="input-group">
                        <button class="btn btn-dark" type="button" id="changeLokasi">
                            <i class="fas fa-street-view"></i>
                        </button>
                        <span class="form-control" id="lokasipekerjaan" style="cursor: pointer"></span>
                    </div>
                    <input type="hidden" class="form-control" id="projectid" name="projectid">
                    <input type="hidden" class="form-control" id="dtproject" name="dtproject">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dari Bulan</label>
                    <input class="form-control" type="text" id="dari" name="dari" placeholder="MM YYYY" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sampai Bulan</label>
                    <input class="form-control" type="text" id="sampai" name="sampai" placeholder="MM YYYY" />
                </div>

            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-cloud-down me-1"></i>Download
                        Lampiran</button>

                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Pilih Lokasi Penempatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" id="cari" class="form-control" placeholder="Search..."
                                            aria-label="Search...">
                                    </div>
                                </div>

                                <div id="jstree-project" class="dataProject"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/monthSelect.js') }}"></script>
    <script src="{{ asset('assets/customjs/simc/cuti/summary-cuti.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
