@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
@endpush

@section('content')
    <div class="row mb-5 ">
        {{-- TENTANG --}}
        <div class="col-md-4 col-lg-4 mb-3" id="tentang"></div>
        {{-- TIM --}}
        <div class="col-md-4 col-lg-4 mb-3" id="tim"></div>
        {{-- NILAI --}}
        <div class="col-md-4 col-lg-4 mb-3" id="nilai"></div>
    </div>

    <div class="modal fade animate__animated animate__fadeInDown" id="modalTentang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFullTitle">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTentang" class="needs-validation" novalidate>
                        @csrf
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Gambar</label>
                            <div class="col-sm-10">
                                <input type="file" id="gambar" name="gambar" class="dropify" accept=".jpeg,.jpg,.png"
                                    data-error="Gambar masih kosong" data-show-remove="false" />
                                <input type="hidden" id="gambar_lama" name="gambar_lama">
                                <div class="form-text text-primary">
                                    Ukuran gambar harus 720 x 514.
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Deskripsi Singkat</label>
                            <div class="col-sm-10">
                                <textarea id="shortdesc" name="shortdesc" class="form-control maxlength" rows="8" maxlength="500" required
                                    placeholder="Silahkan masukan deskripsi singkat disini.."
                                    style="text-align: left;background:none;font-size:1rem !important;font-weight:normal"></textarea>
                                <div class="invalid-feedback">Masukan deskripsi singkat</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Deskripsi Lengkap</label>
                            <div class="col-sm-10">
                                <textarea class="textarea_editor1 form-control" id="longdesc" name="longdesc" rows="10"
                                    placeholder="Silahkan masukan deskripsi lengkap disini.." required></textarea>
                                <div class="invalid-feedback">Masukan deskripsi lengkap</div>
                            </div>
                        </div>
                        <input type="hidden" id="title" name="title" class="form-control">
                        <input type="hidden" id="kategori" name="kategori" class="form-control">
                        <input type="hidden" id="idtentang" name="idtentang" class="form-control">

                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save me-2"></i>Simpan</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade animate__animated animate__fadeInUp" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="modalFullTitle">Modal title</h5> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-5">
                        <div class="col-md-4">
                            <img class="card-img card-img-left" src="" alt="Card image" id="cardGambar" />
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title mb-5" id="cardTitle">Card title</h5>
                                <p class="card-text" id="cardDesc"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary text-danger" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button class="btn btn-primary editTentang"><i class="far fa-edit me-1"></i>Edit</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/customjs/website/tentang-kami.js') }}"></script>
@endpush
