@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')

    <div class="row">
        @can('import-karyawan')
            <div class="col-md-6">
                <div class="card card-body border shadow mb-0">
                    <h5 class="card-title">Migrasi Karyawan</h5>
                    <form id="formKaryawan">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Perusahaan ( PT )</label>
                                <select name="pt" id="pt" class="select2 form-select mb-3" data-allow-clear="true"
                                    required>
                                    <option value="">-pilih perusahaan-</option>
                                    @foreach ($companies as $item)
                                        <option value="{{ $item->token_id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keperluan Project</label>
                                <select name="project" id="project" class="select2 form-select mb-3" data-allow-clear="true"
                                    required>
                                    <option value="">-pilih project-</option>

                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">File</label>
                                <input type="file" id="file" name="file" class="dropify" accept=".xls" required
                                    data-error="File masih kosong" data-default-file="" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning" id="btnTemplate">
                                    <i class="ti ti-palette me-1"></i>Dowload Template</button>
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
    <script src="{{ asset('assets/customjs/import/karyawan-import.js') }}"></script>
@endpush
