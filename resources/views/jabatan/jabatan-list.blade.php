@extends('template.core')

@section('content')
    <div class="card card-body">
        @can('tambah-jabatan')
            <div class="modal-footer mb-3">
                <button class="btn btn-primary btn-sm Tambah"><i class="fas fa-user-plus me-1"></i>Tambah Jabatan</button>
            </div>
        @endcan

        <div class="table-responsive">
            <table id="tableJabatan" class="table table-hover table-bordered text-nowrap" style="width: 100%;font-size:12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NO</th>
                        <th>Nama Jabatan</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasJabatan" aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title">Enable backdrop</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="formJabatan">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Jabatan</label>
                    <input type="hidden" class="form-control" id="id" name="id">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Nama Jabatan"
                        required autocomplete="off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/jabatan/jabatan-list.js') }}"></script>
@endpush
