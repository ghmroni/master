@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="card card-body">
        <form id="formSlider" class="needs-validation" novalidate>
            @csrf
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Gambar Slider</label>
                <div class="col-sm-10">
                    <input type="file" id="gambar" name="gambar" class="dropify" accept=".jpeg,.jpg,.png" required
                        data-error="Gambar masih kosong" data-default-file="" />
                    <div id="defaultFormControlHelp" class="form-text text-primary">
                        Ukuran gambar minimal 1024 x 600.
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Judul Slider</label>
                <div class="col-sm-10">
                    <input type="text" id="judul" name="judul" class="form-control" placeholder="Judul Slider"
                        required maxlength="100">
                    <input type="hidden" id="idslider" name="idslider" class="form-control">
                    <div class="invalid-feedback">Masukan judul slider</div>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Nomor Urut</label>
                <div class="col-sm-10">
                    <select name="nourut" id="nourut" class="form-control" required>
                        @for ($i = 0; $i <= 6; $i++)
                            <option value="{{ $i }}"> {{ $i }}</option>
                        @endfor
                    </select>
                    <div class="form-text text-danger">
                        Nilai 0 (NOL) sama dengan Disable atau Tidak Aktif.
                    </div>
                    <div class="invalid-feedback">Pilih nomor urut slider</div>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Deskripsi Singkat</label>
                <div class="col-sm-10">
                    {{-- <textarea class="textarea_editor form-control" id="shortdesc" name="shortdesc" rows="9"
                        placeholder="Silahkan masukan deskripsi singkat disini.." required></textarea> --}}
                    <textarea id="shortdesc" name="shortdesc" class="form-control maxlength" rows="8" maxlength="100" required
                        placeholder="Silahkan masukan deskripsi singkat disini.."
                        style="text-align: left;background:none;font-size:1rem !important;font-weight:normal"></textarea>
                    <div class="invalid-feedback">Masukan deskripsi singkat</div>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Deskripsi Lengkap</label>
                <div class="col-sm-10">
                    <textarea class="textarea_editor1 form-control" id="longdesc" name="longdesc" rows="9"
                        placeholder="Silahkan masukan deskripsi lengkap disini.." required></textarea>
                    <div class="invalid-feedback">Masukan deskripsi lengkap</div>
                </div>
            </div>


            <div class="modal-footer mt-3">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save me-2"></i>Simpan</button>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/customjs/website/slider-tambah.js') }}"></script>
@endpush
