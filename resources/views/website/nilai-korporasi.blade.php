@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        <form id="formNilai">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Gambar Depan</label>
                    <input type="file" class="dropify" accept=".jpeg,.jpg,.png" data-error="Gambar masih kosong"
                        data-show-remove="false" id="gambar" name="gambar">
                    <input type="hidden" id="gambar_lama" name="gambar_lama">
                    <input type="hidden" id="title" name="title" class="form-control">
                    <input type="hidden" id="kategori" name="kategori" class="form-control">
                    <input type="hidden" id="idnilai" name="idnilai" class="form-control">
                    <div class="form-text text-primary">
                        Ukuran gambar harus 720 x 514.
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea class="form-control maxlength" name="shortdesc" id="shortdesc" rows="8" required maxlength="500"
                        placeholder="Deskripsi Nilai Perusahaan"></textarea>
                </div>

            </div>
            <hr class="my-4 mx-n4" />
            <div class="modal-footer">
                <span class="btn btn-info btn-sm addNilai"><i class="fas fa-plus-square me-1"></i>Tambah Nilai</span>
            </div>
            <table id="dataTable" class="table table-hover table-bordered table-responsive"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th>Logo</th>
                        <th>Urutan</th>
                        <th>Title</th>
                        <th>Keterangan / Deskripsi</th>
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

    {{-- Modal --}}
    <div class="modal fade" id="modalNilai" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambah">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Icon / Logo</label>
                                <select class="select2-icons form-select" required id="logo" name="logo">
                                    <option value="">-pilih-</option>
                                    <option value="fa-university" data-icon="fas fa-university">Universitas</option>
                                    <option value="fa-building" data-icon="fa fa-building">Gedung</option>
                                    <option value="fa-fax" data-icon="fas fa-fax">Fax</option>
                                    <option value="fa-briefcase" data-icon="fas fa-briefcase">Tas</option>
                                    <option value="fa-gavel" data-icon="fas fa-gavel">Palu</option>
                                    <option value="fa-archive" data-icon="fas fa-archive">Arsip</option>
                                    <option value="fa-folder-open" data-icon="fas fa-folder-open">Folder</option>
                                    <option value="fa-inbox" data-icon="fas fa-inbox">Drive</option>
                                    <option value="fa-laptop" data-icon="fas fa-laptop">Laptop</option>
                                    <option value="fa-map" data-icon="fas fas fa-map">Map</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Urut</label>
                                <input type="text" class="form-control number" id="nourut" name="nourut" required
                                    maxlength="2" placeholder="0">
                                <input type="hidden" id="idtampung" name="idtampung">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" id="nama" name="nama" required
                                    placeholder="Title Nilai Perusahaan">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control maxlength" id="quote" name="quote" required maxlength="200"
                                    placeholder="Keterangan Nilai" rows="6"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-info">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/customjs/website/nilai-korporasi.js') }}"></script>
@endpush
