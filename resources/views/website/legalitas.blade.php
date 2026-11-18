@extends('template.core')

@section('content')
    <div class="card card-body">
        <div class="modal-footer">
            <span class="btn btn-primary tambah"><i class="fas fa-file-medical me-1"></i>Tambah Legalitas</span>
        </div>
        <table id="dataTable" class="table table-hover table-bordered table-responsive" style="width: 100%;font-size: 12px">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Dokumen Legalitas</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Legalitas</h3>
                        <p class="text-muted modal-title"></p>
                    </div>
                    <form id="formLegal">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Nama Dokumen Legalitas</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                    placeholder="Nama Dokumen Legalitas" maxlength="150">
                                <input type="hidden" id="kategori" name="kategori" value="LEGALITAS">
                                <input type="hidden" id="idlegal" name="idlegal">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="shortdesc" id="shortdesc" cols="30" rows="10" class="form-control maxlength" required
                                    maxlength="255"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submti" class="btn btn-primary btn-simpan">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/website/legalitas.js') }}"></script>
@endpush
