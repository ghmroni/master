@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        <form id="formTim">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Gambar Depan</label>
                    <input type="file" class="dropify" accept=".jpeg,.jpg,.png" data-error="Gambar masih kosong"
                        data-show-remove="false" id="gambar" name="gambar">
                    <input type="hidden" id="gambar_lama" name="gambar_lama">
                    <input type="hidden" id="title" name="title" class="form-control">
                    <input type="hidden" id="kategori" name="kategori" class="form-control">
                    <input type="hidden" id="idtim" name="idtim" class="form-control">
                    <div class="form-text text-primary">
                        Ukuran gambar harus 720 x 514.
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea class="form-control maxlength" name="shortdesc" id="shortdesc" rows="8" required maxlength="500"
                        placeholder="Deskripsi Tim GHM"></textarea>
                </div>

            </div>
            <hr class="my-4 mx-n4" />
            <div class="modal-footer">
                <span class="btn btn-info btn-sm tambahUser"><i class="fas fa-user-plus me-1"></i>Tambah Tim</span>
            </div>
            <table id="dataTable" class="table table-hover table-bordered table-responsive"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Urutan</th>
                        <th>Nama Lengkap</th>
                        <th>Jabatan</th>
                        <th>Quote</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
            <hr class="my-4 mx-n4" />
            <div class="modal-footer mt-5">
                <button type="submit" class="btn btn-primary me-2 btn-simpan"><i
                        class="fas fa-save me-1"></i>Simpan</button>
            </div>

        </form>
    </div>

    <div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasTop" aria-labelledby="offcanvasTopLabel"
        style="height: 500px;">
        <div class="offcanvas-header">
            <h5 id="offcanvasTopLabel" class="offcanvas-title"></h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mt-0">
            <form id="formTambah">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Photo</label>
                        <input type="file" class="form-control" accept=".jpeg,.jpg,.png" id="photo" name="photo">
                        <input type="hidden" class="form-control" id="idsementara" name="idsementara">
                        <input type="hidden" class="form-control" id="photo_lama" name="photo_lama">
                        <div class="form-text text-primary">
                            Ukuran photo harus 360 x 360 pixel.
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Urut</label>
                        <input type="text" class="form-control number" id="nourut" name="nourut" required
                            placeholder="0" maxlength="2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" required
                            placeholder="Nama Lengkap">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="jabatan" name="jabatan" required
                            placeholder="Jabatan">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Quote / Kutipan</label>
                        <textarea class="form-control maxlength" name="quote" id="quote" rows="8" required maxlength="300"
                            placeholder="Quote"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info me-2"><i class="fas fa-user-plus me-1"></i>Tambah</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/customjs/website/tim-kami.js') }}"></script>
@endpush
