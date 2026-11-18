@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card card-body">
        @can('tambah-unitkerja')
            <div class="modal-footer mb-3">
                <button class="btn btn-primary btn-sm Tambah"><i class="ti ti-plus me-1"></i>Tambah Unitkerja</button>
            </div>
        @endcan

        <div class="table-responsive">
            <table id="tableUnitkerja" class="table table-hover table-bordered text-nowrap" style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NO</th>
                        <th>Nama Unitkerja</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formUnitkerja" class="needs-validation" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nama Unitkerja</label>
                                <input type="text" id="nama" name="nama" class="form-control text-uppercase"
                                    placeholder="Nama Unitkerja" required autocomplete="off">
                                <input type="hidden" id="idunit" name="idunit" class="form-control" readonly>
                                <div class="invalid-feedback">Nama unitkerja masih kosong.</div>
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
    <script src="{{ asset('assets/customjs/jabatan/unitkerja-list.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
