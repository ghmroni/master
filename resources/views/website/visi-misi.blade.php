@extends('template.core')

@section('content')
    <div class="row g-3" id="viewVisi">
        <div class="col-12 col-xl-6 mb-3">
            <div class="card h-100 shadow border border-warning" style="min-height: 450px">
                <div class="card-body">
                    <h5 class="card-title">Fokus Kami</h5>
                    <h6 class="card-subtitle text-muted">Berkualitas, Terpercaya & Berpengalaman.</h6>
                    <hr class="my-4 mx-n4 mb-2" />
                    <div id="vFokus"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6 mb-3">
            <div class="card h-500 mb-3 shadow border border-secondary rounded-3">
                <div class="card-body">
                    <h5 class="card-title">Visi</h5>
                    <hr class="my-4 mx-n4 mb-2" />
                    <div id="vVisi"></div>

                </div>
            </div>

            <div class="card h-500 shadow border border-secondary rounded-3">
                <div class="card-body">
                    <h5 class="card-title">Misi</h5>
                    <hr class="my-4 mx-n4 mb-2" />
                    <div id="vMisi"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-12 mb-4">
            <div class="modal-footer shadow border border-secondary rounded-3 p-2">
                <button class="btn btn-primary btnVisi"><i class="fa fa-spin fa-spinner"></i></button>
            </div>
        </div>
    </div>

    <div id="formView" style="display: none">
        <form id="formVisi" class="needs-validation" novalidate>
            <div class="row">
                @csrf
                <div class="col-12 col-xl-5 mb-3">
                    <div class="card h-100 shadow border border-warning" style="min-height: 450px">
                        <div class="card-body">
                            <h5 class="card-title">Fokus Kami</h5>
                            <h6 class="card-subtitle text-muted">Berkualitas, Terpercaya & Berpengalaman.</h6>
                            <hr class="my-4 mx-n4 mb-2" />

                            <textarea name="shortdesc" id="shortdesc" cols="30" rows="22" required class="form-control textarea_editor2"
                                placeholder="Focus GHM"></textarea>
                            <div class="invalid-feedback">Inputan masih kosong.</div>
                            <input type="hidden" name="kategori" id="kategori" value="VISIMISI" placeholder="kategori"
                                readonly>
                            <input type="hidden" name="title" id="title" value="VISI MISI" placeholder="title"
                                readonly>
                            <input type="hidden" name="gambar_layanan" id="gambar_layanan" value="gambar_layanan"
                                placeholder="gambar_layanan" readonly>
                            <input type="hidden" name="idvisi" id="idvisi" readonly>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-7 mb-3">
                    <div class="card h-500 mb-3 shadow border border-secondary rounded-3">
                        <div class="card-body">
                            <h5 class="card-title">Visi</h5>
                            <hr class="my-4 mx-n4 mb-2" />

                            <textarea name="visi" id="visi" cols="30" rows="10" class="form-control textarea_editor" required
                                placeholder="Visi"></textarea>
                            <div class="invalid-feedback">Visi perusahaan masih kosong.</div>
                        </div>
                    </div>

                    <div class="card h-500 shadow border border-secondary rounded-3">
                        <div class="card-body">
                            <h5 class="card-title">Misi</h5>
                            <hr class="my-4 mx-n4 mb-2" />

                            <textarea name="misi" id="misi" cols="30" rows="10" class="form-control textarea_editor1" required
                                placeholder="Misi"></textarea>
                            <div class="invalid-feedback">Misi perusahaan masih kosong.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-12 mb-4">
                    <div class="modal-footer shadow border border-secondary rounded-3 p-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/visi-misi.js') }}"></script>
@endpush
