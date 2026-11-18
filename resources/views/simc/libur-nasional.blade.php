@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body border shadow mb-3">
        <form id="formAdd">
            @csrf
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pilih Tanggal Libur (<small class="text-primary">Bisa lebih dari 1
                            tanggal</small>)</label>
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD" name="tanggalCuti" id="tanggalCuti" />
                    <input type="hidden" class="form-control" name="idlibur" id="idlibur" readonly />
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keterangan Libur</label>
                    <input class="form-control" name="keterangan" id="keterangan"
                        placeholder="Keterangan Libur / Cuti Bersama">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btnSubmit"><i class="fas fa-save me-1"></i>Simpan</button>
                <div id="btnReset" style="display: none">
                    <button type="button" class="btn btn-outline-warning btnNew"><i
                            class="fas fa-calendar-plus me-1"></i>Tambah Baru</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-body border shadow">
        <div class="table-responsive">
            <table id="tableKalender" class="table table-hover table-bordered table-striped text-nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NO</th>
                        <th>Tahun</th>
                        <th>tanggal</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Tambah Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAdd">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Pilih Tanggal <span class="text-muted">Bisa lebih dari 1
                                        tanggal</span></label>
                                <input type="text" class="form-control" placeholder="YYYY-MM-DD" name="tanggalCuti"
                                    id="tanggalCuti" />
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan Libur</label>
                                <textarea class="form-control" name="keterangan" id="keterangan" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/simc/libur-nasional.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
