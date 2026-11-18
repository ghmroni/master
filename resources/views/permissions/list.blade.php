@extends('template.core')

@section('content')
    <div class="card card-body">
        @can('tambah-permission')
            <div class="modal-footer mb-3">
                <button class="btn btn-primary btn-sm Tambah"><i class="fas fa-user-plus me-1"></i>Tambah Hak Akses</button>
            </div>
        @endcan

        <div class="table-responsive text-nowrap">
            <table id="dataTable" class="table table-hover table-bordered border-top" style="font-size: 13px;width:100%">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Hak Akses</th>
                        <th>Grup Modul</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

            </table>
        </div>
    </div>

    <div class="modal fade" id="modalPermissions" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="text-center mb-4">
                        <h3 class="role-title mb-2 modal-title">Add New Role</h3>
                        <p class="text-muted">Set permissions atau hak akses</p>
                    </div>

                    <form id="formPermission">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nama Permission</label>
                                <input type="hidden" id="id" name="id" class="form-control">
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Nama Hak Akses" required>
                                <input type="hidden" id="oldname" name="oldname" class="form-control"
                                    placeholder="Nama Hak Akses Lama">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Grup Module</label>
                                <input type="text" id="module" name="module" class="form-control typeahead"
                                    placeholder="Nama Module" required autocomplete="off">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/permissions/list-permissions.js') }}"></script>
@endpush
