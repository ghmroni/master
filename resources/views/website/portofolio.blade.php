@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        <div class="modal-footer">
            <span class="btn btn-primary tambah"><i class="far fa-image me-1"></i>Tambah Portofolio</span>
        </div>
        <table id="dataTable" class="table table-hover table-bordered table-responsive" style="width: 100%;font-size: 12px">
            <thead class="table-light">
                <tr>
                    <th>Photo</th>
                    <th>Title</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">PORTOFOLIO</h3>
                    </div>
                    <form id="formPortofolio">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Gambar</label>
                                <input type="file" id="gambar" name="gambar" class="dropify" accept=".jpeg,.jpg,.png"
                                    data-error="Gambar masih kosong" data-default-file="" data-show-remove="false" />
                                <input type="hidden" id="gambarlama" name="gambarlama">
                                <input type="hidden" id="kategori" name="kategori" value="PORTOFOLIO">
                                <input type="hidden" id="idport" name="idport">
                                <div id="defaultFormControlHelp" class="form-text text-primary">
                                    Ukuran gambar 528 x 808.
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Judul</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                    placeholder="Title Portofolio" maxlength="150">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="shortdesc" id="shortdesc" rows="6" class="form-control maxlength" required maxlength="255"></textarea>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submti" class="btn btn-primary btn-simpan">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/portofolio.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
@endpush
