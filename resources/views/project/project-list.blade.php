@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <style>
        .sticky-element {
            position: sticky;
            top: 80px;
            z-index: 8;

            .window-scrolled & {
                inset-block-start: 0;
            }

            .layout-navbar-fixed & {
                inset-block-start: calc($navbar-height - .4375rem);
            }

            .layout-menu-fixed .layout-horizontal & {
                inset-block-start: calc($navbar-height * 2 - .25rem);
            }

            .layout-menu-fixed.layout-navbar-fixed .layout-horizontal & {
                inset-block-start: $navbar-height;
            }
        }

        /* Mengubah warna teks node menjadi merah */
        .node-tidak-aktif>.jstree-anchor {
            color: #ff0000 !important;
        }

        /* Mengubah warna ikon (FontAwesome / jstree icon) menjadi merah */
        .node-tidak-aktif>.jstree-anchor>.jstree-icon {
            color: #ff0000 !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header sticky-element bg-label-secondary">
                <div class="row g-2 align-items-center">
                    <div class="col-md-12 text-md-end">
                        @can('tambah-project')
                            <button class="btn btn-primary btn-sm addProject"><i class="fas fa-plus-circle me-1"></i>Tambah
                                Project</button>
                            <button class="btn btn-info btn-sm tambahSub" disabled><i class="far fa-plus-square me-1"></i>Tambah
                                Sub</button>
                        @endcan
                        @can('edit-project')
                            <button class="btn btn-warning btn-sm edit" disabled><i class="far fa-edit me-1"></i>Edit</button>
                            <button class="btn btn-danger btn-sm hapus" disabled><i class="fas fa-trash me-1"></i>Hapus</button>
                        @endcan
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" id="cari" class="form-control" placeholder="Search..."
                                aria-label="Search...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <div class="card-body">
                            <div id="jstree-project" class="dataProject"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit-->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEdit">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Perusahaan ( PT )</label>
                                <select name="ptid" id="ptid" class="form-select select2" required>
                                    {{-- <option value=""></option> --}}
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Parent</label>
                                <input type="hidden" id="id" class="form-control" name="id">
                                <input type="hidden" id="parent" class="form-control" name="parent">
                                <select name="parentid" id="parentid" class="select2 form-control" required></select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nama Sub</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Nama Sub" required autocomplete="off">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="ENABLE">Aktif</option>
                                    <option value="DISABLE">Tidak Aktif</option>
                                </select>
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

    <!-- Modal Tambah-->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambah">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3 text-center" id="previews" style="display: none">
                                <img src="" id="preview-logo" class="logo mt-0 mb-2" width="130px">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Perusahaan ( PT )</label>
                                <select name="ptid" id="ptid" class="form-select select2" required>
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nama Project</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Nama Project" required autocomplete="off">
                                <input type="hidden" id="id" class="form-control" name="id">
                                <input type="hidden" id="parent" class="form-control" name="parent">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Inisial</label>
                                <input type="text" id="inisial" name="inisial" class="form-control"
                                    placeholder="Inisial" required autocomplete="off">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="ENABLE">Aktif</option>
                                    <option value="DISABLE">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Logo Project</label>
                                <input type="file" id="logo" name="logo" class="form-control"
                                    accept="image/png, image/jpeg">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"><i
                                    class="fas fa-save me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/project/project-list.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
@endpush
