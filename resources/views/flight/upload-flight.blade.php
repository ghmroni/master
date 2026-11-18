@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="row">
        @can('flight-upload')
            <div class="col-md-6">
                <div class="card card-body border shadow mb-0">
                    <h5 class="card-title">Flight</h5>
                    <form id="formFlight">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <select name="project" id="project" class="select2 form-select mb-3" data-allow-clear="true"
                                    required>
                                    <option value="">-pilih project-</option>
                                    @foreach ($projects as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <input type="file" id="file" name="file" class="dropify" accept=".xls" required
                                    data-error="File masih kosong" data-default-file="" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-cloud-up me-2"></i>Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
        @can('flighthaji-upload')
            <div class="col-md-6">
                <div class="card card-body border shadow mb-0">
                    <h5 class="card-title">Flight Haji</h5>
                    <form id="formFlightHaji">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <select name="projectid" id="projectid" class="select2 form-select mb-3" data-allow-clear="true"
                                    required>
                                    <option value="">-pilih project-</option>
                                    @foreach ($projects as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <input type="file" id="file" name="file" class="dropify" accept=".xls" required
                                    data-error="File masih kosong" data-default-file="" />

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary"><i
                                        class="ti ti-cloud-up me-2"></i>Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/customjs/flight/upload-flight.js') }}"></script>
@endpush
