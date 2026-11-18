@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/css/ribbon-page.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror
        @can('tambah-user')
            <div class="modal-footer mb-3">
                <button class="btn btn-warning btn-sm Tambah"><i class="fas fa-user-plus me-1"></i>Tambah Pengguna</button>
            </div>
        @endcan
        <div class="table-responsive text-nowrap">
            <table id="dataTable" class="table table-hover table-bordered border-top" style="font-size: 12px;width:100%">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-3 modal-title">Tambah Pengguna</h3>
                        <p class="text-muted">Pastikan data diisi dengan lengkap dan benar.</p>
                    </div>
                    <form id="formTambah">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-user"></i></span>
                                <input type="hidden" class="form-control" id="id" name="id">
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Nama Pengguna" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Alamat Email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level</label>
                            <select class="select2 form-select" id="roles" name="roles" data-allow-clear="true"
                                required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="ENABLE">AKTIF</option>
                                <option value="DISABLE">TIDAK AKTIF</option>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Simpan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="role-title mb-2 modal-title">Hak Akses Tambahan</h3>
                        <p class="text-muted">Special Permissions</p>
                    </div>
                    <form id="formRoles">
                        @csrf
                        <div class="col-12 mb-4">
                            <label class="form-label">Nama Pengguna</label>
                            <input type="hidden" id="id" name="id" class="form-control">
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Masukan nama level" tabindex="-1" readonly>
                        </div>
                        <div class="col-12">
                            <h5>Role Permissions</h5>
                            <!-- Permission table -->
                            <div class="row mt-0">
                                <div id="dtPermissionGroups" class="row"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success me-sm-3 me-1"><i
                                    class="fas fa-save me-1"></i>Simpan</button>
                            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                                aria-label="Close">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/users/user-list.js') }}"></script>
@endpush
