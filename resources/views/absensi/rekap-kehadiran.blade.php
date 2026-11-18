@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush

@section('content')
    <div class="card card-body border shadow mb-4">
        <form id="formKehadiran">
            @csrf
            <div class="row mb-2">
                <div class="col-md-12 mb-2">
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
                <div class="col-md-6 mb-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input class="form-control" type="text" id="dari" name="dari" placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input class="form-control" type="text" id="sampai" name="sampai" placeholder="YYYY-MM-DD"
                        disabled />
                </div>

                <div class="col-md mb-md-0 mb-2">
                    <div class="form-check custom-option custom-option-basic">
                        <label class="form-check-label custom-option-content" for="schedule">
                            <input class="form-check-input" type="checkbox" value="1" id="schedule" name="schedule" />
                            <span class="custom-option-header">
                                <span class="h6 mb-0">Berdasarkan Schedule</span>
                            </span>
                            <span class="custom-option-body">
                                <small class="option-text text-muted">Bisa disesuaikan apabila project menggunakan upload
                                    schedule</small>
                            </span>
                        </label>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="submit" name="action" value="view" class="btn btn-warning"><i
                            class="ti ti-eye me-1"></i>Lihat Data</button>
                    <button type="submit" name="action" value="download" class="btn btn-primary"><i
                            class="ti ti-cloud-down me-1"></i>Download
                        Rekap Kehadiran</button>

                </div>
            </div>
        </form>
    </div>

    <div class="card card-body border shadow animate__animated animate__slideInUp" id="dtViews" style="display: none">
        <div class="table-responsive">
            <table id="tableRekap" class="table table-hover table-striped text-nowrap" style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Tanggal Rekap</th>
                    </tr>
                </thead>
            </table>
        </div>
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
    <script src="{{ asset('assets/customjs/absensi/rekap-kehadiran.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
