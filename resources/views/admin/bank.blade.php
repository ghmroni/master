@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/customjs/dropify/css/dropify.min.css') }}">
    <style>
        table.dataTable td img {
            display: block;
            margin: 0 auto;
        }
    </style>
@endpush
@section('content')
    <div class="card card-body">
        @can('tambah-bank')
            <div class="modal-footer mb-3">
                <button class="btn btn-warning btn-sm Tambah"><i class="fas fa-university me-1"></i>Tambah BANK</button>
            </div>
        @endcan
        <div class="table-responsive text-nowrap">
            <table id="tableBank" class="table table-hover table-bordered border-top" style="font-size: 12px;width:100%">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Logo</th>
                        <th>Nama Bank</th>
                        <th>Kode Bank</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formBank">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Logo Bank</label>
                                <input type="file" id="logobank" name="logobank" class="dropify"
                                    data-show-remove="false" accept=".jpeg,.jpg,.png" />
                                <input type="hidden" id="logoid" name="logoid" readonly class="form-control">
                                <input type="hidden" id="bankid" name="bankid" readonly class="form-control">
                            </div>
                            <div class="col-md-12 text-center" id="gambarlogo" style="display: none">
                                <img src="" id="gambar" width="80px">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Nama Bank</label>
                                <input type="text" id="nama" name="nama" class="form-control text-uppercase"
                                    placeholder="Nama Bank" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Kode Bank</label>
                                <input type="text" id="kode" name="kode" class="form-control text-uppercase"
                                    placeholder="Kode Bank" />
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/admin/bank.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/customjs/dropify/js/dropify.min.js') }}"></script>
@endpush
