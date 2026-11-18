@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/monthSelect.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="mb-3">
        <button class="btn btn-warning btn-sm templateUpload"><i class="ti ti-palette me-1"></i>Template Upload</button>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-body border shadow rounded">
                <form id="formSchedule">
                    @csrf
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Periode Schedule</label>
                        <div class="input-group">
                            <button class="btn btn-dark" type="button">
                                <i class="far fa-calendar-check"></i>
                            </button>
                            <input type="text" class="form-control" placeholder="MM YYYY" id="periode"
                                name="periode" />
                        </div>
                    </div>
                    <div class="col-md-12 mb-0">
                        <label class="form-label">Lampiran Schedule</label>
                        <input type="file" id="file" name="file" class="dropify" accept=".xls"
                            data-error="File masih kosong" data-default-file="" />
                        @can('absensi-upload-schedule')
                            <div class="modal-footer mt-3 pb-0">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-dark btn-sm dataSchedule"><i
                                            class="fas fa-folder-open me-1"></i>Data
                                        Schedule</button>
                                    <button type="submit" class="btn btn-primary btn-sm"><i
                                            class="ti ti-cloud-up me-2"></i>Upload
                                        Schedule</button>
                                </div>
                            </div>
                        @endcan
                    </div>
                </form>
            </div>
        </div>

        {{-- <div class="col-lg-6">
            <div class="card card-body border shadow">
                <form id="formAbsensi">
                    @csrf
                    <label class="form-label">Absensi</label>
                    <input type="file" id="file" name="file" class="dropify" accept=".xls" required
                        data-error="File masih kosong" data-default-file="" />
                    @can('absensi-upload-absensi')
                        <div class="modal-footer mt-3 pb-0">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-cloud-up me-2"></i>Upload
                                Absensi</button>
                        </div>
                    @endcan

                </form>
            </div>
        </div> --}}
    </div>

    <div id="statuBar"></div>

    <div id="modalTemplate" class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Template Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTemplate">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Pilih Template</label>
                                <select name="jenis" id="jenis" class="select2 form-select" required>
                                    <option value="">-pilih-</option>
                                    <option value="schedule">SCHEDULE</option>
                                    {{-- <option value="absensi">ABSENSI</option> --}}
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Ikutkan Karyawan</label>
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

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Download Template</button>
                        </div>
                    </form>
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
                                        <input type="text" id="cari" class="form-control"
                                            placeholder="Search..." aria-label="Search...">
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
    <script src="{{ asset('assets/customjs/absensi/upload-schedule.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>
    <script src="{{ asset('assets/customjs/monthSelect.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
