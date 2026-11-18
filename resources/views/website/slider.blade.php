@extends('template.core')

@section('content')
    <div class="card card-body">
        @can('edit-website')
            <div class="modal-footer mb-3">
                <button class="btn btn-primary addSlider"><i class="fas fa-plus-circle me-1"></i>Tambah Slider</button>
            </div>
        @endcan

        <table id="dataTable" class="table table-hover table-bordered nowrap table-responsive"
            style="width: 100%;font-size: 12px">
            <thead class="table-light">
                <tr>
                    <th>Gambar</th>
                    <th>Title</th>
                    <th>Deskripsi Singkat</th>
                    <th>Urutan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/slider.js') }}"></script>
@endpush
