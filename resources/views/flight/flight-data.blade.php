@extends('template.core')

@section('content')
    <div class="card card-body border shadow">
        @error('token_expired')
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <span class="alert-icon text-danger me-2">
                    <i class="ti ti-ban ti-xs"></i>
                </span>
                {{ $message }}
            </div>
        @enderror
        <div class="table-responsive">
            <table id="tableFlight" class="table table-striped table-hover table-bordered text-nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>NO</th>
                        <th>Periode</th>
                        <th>Project (Cabang)</th>
                        <th>Operator</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalLayanan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Kelola Header</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div id="addLayanan"></div>
                </div>
                <div class="modal-body mt-0"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPetugas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel4">Petugas Terkait</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formPetugas">
                        @csrf
                        <table class="table table-bordered" style="width: 100%;font-size: 12px">
                            <tr class="text-center">
                                <th width="30%">
                                    <input type="hidden" id="tokenid" name="tokenid" class="form-control" readonly>
                                    <input type="hidden" id="lampiranid" name="lampiranid" class="form-control" readonly>
                                    <input type="hidden" name="generate" id="generate" class="form-control" readonly>
                                    <input type="text" id="ghm" name="ghm"
                                        class="form-control text-center text-uppercase" value="PT GRHA HUMANINDO MANAJEMEN"
                                        required>
                                </th>
                                <th width="38%">&nbsp;</th>
                                <th width="30%"><input type="text" id="gapura" name="gapura"
                                        class="form-control text-center text-uppercase" value="PT GAPURA ANGKASA" required>
                                </th>
                            </tr>
                            <tr class="text-center">
                                <td>Dibuat Oleh,</td>
                                <td>Mengetahui,</td>
                                <td>Menyetujui,</td>
                            </tr>
                            <tr class="text-center" style="height: 50px;">
                                <td colspan="3">&nbsp;</td>
                            </tr>
                            <tr>
                                <th><input type="text" id="dibuat" name="dibuat" placeholder="Nama Lengkap"
                                        class="form-control text-center text-uppercase" required autocomplete="off"></th>
                                <th><input type="text" id="mengetahui" name="mengetahui" placeholder="Nama Lengkap"
                                        class="form-control text-center text-uppercase" required autocomplete="off"></th>
                                <th><input type="text" id="menyetujui" name="menyetujui" placeholder="Nama Lengkap"
                                        class="form-control text-center text-uppercase" required autocomplete="off"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="jab1" name="jab1" placeholder="Jabatan"
                                        class="form-control text-center" autocomplete="off"></th>
                                <th><input type="text" id="jab2" name="jab2" placeholder="Jabatan"
                                        class="form-control text-center" required autocomplete="off"></th>
                                <th><input type="text" id="jab3" name="jab3" placeholder="Jabatan"
                                        class="form-control text-center" required autocomplete="off"></th>
                            </tr>
                        </table>
                        <div id="modalFlightBody" class="mt-3">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/flight/flight-data.js') }}"></script>
@endpush
