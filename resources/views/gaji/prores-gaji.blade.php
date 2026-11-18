@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/monthSelect.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card border shadow">
                <h5 class="card-header text-uppercase">PROSES GAJI</h5>
                <div class="card-body">
                    <form id="formGaji">
                        @csrf
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label class="form-label">Periode Penggajian</label>
                                <div class="input-group">
                                    <button class="btn btn-dark" type="button">
                                        <i class="far fa-calendar-check"></i>
                                    </button>
                                    <input type="text" class="form-control" placeholder="MM YYYY" id="periode"
                                        name="periode" value="{{ date('Y-m') }}" />
                                </div>
                            </div>
                            <div class="col-md-8 mb-2">
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
                        </div>
                        <div class="modal-footer mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-puzzle-piece me-1"></i>Proses Penggajian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card border shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableGaji" class="table table-hover table-striped text-nowrap"
                            style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Periode Penggajian</th>
                                    <th>Project</th>
                                    <th>Tanggal Proses</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

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
    <script src="{{ asset('assets/customjs/gaji/proses-gaji.js') }}"></script>
    <script src="{{ asset('assets/customjs/monthSelect.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
