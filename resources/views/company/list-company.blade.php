@extends('template.core')

@section('content')
    <div class="card card-body">
        @can('tambah-company')
            <div class="modal-footer mb-3">
                <button class="btn btn-primary btn-sm Tambah"><i class="fas fa-university me-1"></i>Tambah Perusahaan</button>
            </div>
        @endcan

        <div class="table-responsive">
            <table id="tableCompany" class="table table-hover table-bordered text-nowrap" style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NO</th>
                        <th>Logo</th>
                        <th>Nama Perusahaan</th>
                        <th>Kode</th>
                        <th>Inisial</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCompany">
                        <div class="text-center mb-4" id="previews" style="display: none">
                            <img id="preview-logo" src="" alt="Logo" style="max-width: 100px; display: none;">
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Nama Perusahaan</label>
                                <input type="text" id="name" name="name" class="form-control text-uppercase"
                                    placeholder="Nama Perusahaan" required autocomplete="off" />
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Inisial</label>
                                <input type="text" id="inisial" name="inisial" class="form-control text-uppercase"
                                    placeholder="Inisial" autocomplete="off" required maxlength="3" />
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Kode</label>
                                <input type="hidden" id="idcompany" name="idcompany" class="form-control"
                                    autocomplete="off" readonly />
                                <input type="text" id="code" name="code" class="form-control"
                                    placeholder="Nama Perusahaan" autocomplete="off" readonly />
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Logo Perusahaan</label>
                                <input type="file" id="logo" name="logo" class="form-control" accept="image/*" />
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
    <script src="{{ asset('assets/customjs/company/list-company.js') }}"></script>
@endpush
