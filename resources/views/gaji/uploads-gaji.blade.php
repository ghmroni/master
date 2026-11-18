@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/monthSelect.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card border shadow rounded">
                <h5 class="card-header text-center text-uppercase">POTONGAN</h5>
                <div class="card-body">
                    <form id="formPotongan">
                        @csrf
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Periode</label>
                            <div class="input-group">
                                <button class="btn btn-dark" type="button">
                                    <i class="far fa-calendar-check"></i>
                                </button>
                                <input type="text" class="form-control" placeholder="MM YYYY" id="periodePotongan"
                                    name="periodePotongan" />
                            </div>
                        </div>
                        <div class="col-md-12 mb-0">
                            <label class="form-label">Lampiran</label>
                            <input type="file" id="file" name="file" class="dropify" accept=".xls"
                                data-error="File masih kosong" data-default-file="" />

                            {{-- <div class="modal-footer mt-3 pb-0">
                            <div class="btn-group">
                                <button type="button" class="btn btn-dark btn-sm dataSchedule"><i
                                        class="fas fa-folder-open me-1"></i>Data
                                    Schedule</button>
                                <button type="submit" class="btn btn-primary btn-sm"><i
                                        class="ti ti-cloud-up me-2"></i>Upload
                                    Schedule</button>
                            </div>
                        </div> --}}

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border shadow">
                <h5 class="card-header text-center text-uppercase">LEMBUR</h5>
                <div class="card-body">
                    <form id="formLembur">
                        @csrf
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Periode</label>
                            <div class="input-group">
                                <button class="btn btn-dark" type="button">
                                    <i class="far fa-calendar-check"></i>
                                </button>
                                <input type="text" class="form-control" placeholder="MM YYYY" id="periodeLembur"
                                    name="periodeLembur" />
                            </div>
                        </div>
                        <div class="col-md-12 mb-0">
                            <label class="form-label">Lampiran</label>
                            <input type="file" id="file" name="file" class="dropify" accept=".xls"
                                data-error="File masih kosong" data-default-file="" />

                            {{-- <div class="modal-footer mt-3 pb-0">
                            <div class="btn-group">
                                <button type="button" class="btn btn-dark btn-sm dataSchedule"><i
                                        class="fas fa-folder-open me-1"></i>Data
                                    Schedule</button>
                                <button type="submit" class="btn btn-primary btn-sm"><i
                                        class="ti ti-cloud-up me-2"></i>Upload
                                    Schedule</button>
                            </div>
                        </div> --}}

                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>

    <div id="statuBar"></div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/gaji/uploads-gaji.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>
    <script src="{{ asset('assets/customjs/monthSelect.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
