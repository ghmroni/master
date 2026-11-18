@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
    {{-- <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" /> --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/ui-carousel.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
@endpush

@section('content')
    <div class="card card-body mb-3" id="daftarKlien" style="display: none">
        <div class="col-12 mb-4">
            <h6 class="text-muted">Daftar Klien</h6>
            <div id="swiper-multiple-slides" class="swiper">
                <div class="swiper-wrapper" id="dtKlien">
                    <!-- Slide dinamis akan dimasukkan di sini -->
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <h5 class="card-header">Ukuran gambar harus 400 x 300 pixel</h5>
        <div class="card-body">
            <form class="dropzone needsclick" id="dropzone-multi">
                @csrf
                <div class="dz-message needsclick">
                    Drop files here or click to upload
                    <span class="note needsclick">Selected files are
                        <span class="fw-medium">not</span> actually uploaded.</span>
                </div>
            </form>
            <button class="btn btn-primary mt-3 float-end" id="form-upload">Submit</button>
        </div>
    </div>
@endsection

@push('extra_script')
    {{-- <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script> --}}
    <script src="{{ asset('assets/vendor/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ asset('assets/customjs/website/klien.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
@endpush
