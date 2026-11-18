@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        <div class="modal-footer">
            <span class="btn btn-primary tambah"><i class="fas fa-briefcase me-1"></i>Tambah Layanan</span>
        </div>
        <table id="dataTable" class="table table-hover table-bordered table-responsive" style="width: 100%;font-size: 12px">
            <thead class="table-light">
                <tr>
                    <th>Photo</th>
                    <th>Nama Layanan</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade animate__animated animate__fadeInUp" id="myModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                {{-- <div class="modal-header">
                    <h5 class="modal-title" id="modalFullTitle">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div> --}}
                <div class="modal-body">
                    <form id="formLayanan" class="needs-validation" novalidate>
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Nama Layanan</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                    placeholder="Nama layanan" maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gambar Layanan</label>
                                <input type="file" id="gambar" name="gambar" class="dropify" accept=".jpeg,.jpg,.png"
                                    data-error="Gambar masih kosong" data-default-file="" data-show-remove="false" />
                                <input type="hidden" id="gambarlama" name="gambarlama">
                                <input type="hidden" id="kategori" name="kategori" value="LAYANAN">
                                <input type="hidden" id="idlayanan" name="idlayanan">
                                <div id="defaultFormControlHelp" class="form-text text-primary">
                                    Ukuran gambar 800 x 500.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan Layanan</label>
                                <textarea name="shortdesc" id="shortdesc" rows="8" class="form-control maxlength" required maxlength="100"
                                    placeholder="Keterangan layanan"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi Layanan</label>
                                <textarea class="textarea_editor form-control" id="longdesc" name="longdesc" rows="10"
                                    placeholder="Silahkan masukan deskripsi lengkap disini.." required></textarea>
                                <div class="invalid-feedback">Masukan deskripsi lengkap</div>
                            </div>

                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary tombol-submit">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/layanan.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
@endpush
