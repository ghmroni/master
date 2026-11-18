@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/customjs/fixedColumns.css') }}">
@endpush

@section('content')
    <div class="card card-body border shadow">

        <div class="table-responsive">
            <table id="tableDetail" class="table table-striped table-hover table-bordered" style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="text-center">NO</th>
                        <th colspan="2" class="text-center">Tanggal</th>
                        <th colspan="3" class="text-center">Aircraft</th>
                        <th colspan="4" class="text-center">Arrival</th>
                        <th colspan="4" class="text-center">Departure</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Remarks</th>
                        <th rowspan="2">Aksi</th>
                    </tr>
                    <tr class="text-center">
                        <th>Arrival</th>
                        <th>Departure</th>
                        <th>Type</th>
                        <th>Categori</th>
                        <th>Registrasi</th>
                        <th>Flight</th>
                        <th>From</th>
                        <th>STA</th>
                        <th>ATA</th>
                        <th>Flight</th>
                        <th>To</th>
                        <th>STD</th>
                        <th>ATD</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalRegistrasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Registrasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formRegistrasi">
                        @csrf
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">Nama Registrasi</label>
                                <input type="hidden" id="idreg" name="idreg" class="form-control" readonly />
                                <input type="text" id="namareg" name="namareg" class="form-control text-uppercase"
                                    placeholder="Nama Registrasi" required autocomplete="off" />
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary"><i
                                    class="ti ti-cloud-up me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalWaktu" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formWaktu">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <input type="hidden" id="idreg" name="idreg" class="form-control" readonly />
                            </div>
                            <div id="dtRow"></div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary"><i
                                    class="ti ti-cloud-up me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEdit">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tanggal Arrival</label>
                                <input type="hidden" id="idreg" name="idreg" class="form-control" readonly />
                                <input type="text" id="tglarrival" name="tglarrival" class="form-control"
                                    placeholder="YYYY-MM-DD" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tanggal Departure</label>
                                <input type="text" id="tgldeparture" name="tgldeparture" class="form-control"
                                    placeholder="YYYY-MM-DD" />
                            </div>
                            <div class="divider divider-dark mb-0 mt-0 divider-dashed">
                                <div class="divider-text text-uppercase fw-bold">Aircraft</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Type</label>
                                <input type="text" id="aircrafttype" name="aircrafttype"
                                    class="form-control text-uppercase" placeholder="Type" autocomplete="off" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Categori</label>
                                <input type="text" id="aircraftcategori" name="aircraftcategori"
                                    class="form-control text-uppercase" placeholder="Categori" autocomplete="off" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Registrasi</label>
                                <input type="text" id="aircraftregistrasi" name="aircraftregistrasi"
                                    class="form-control text-uppercase" placeholder="Registrasi" autocomplete="off" />
                            </div>
                            <div class="divider divider-dark mb-0 mt-0 divider-dashed">
                                <div class="divider-text text-uppercase fw-bold text-success">Arrival</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">Flight</label>
                                <input type="text" id="arrivalflight" name="arrivalflight"
                                    class="form-control text-uppercase" placeholder="Flight" autocomplete="off" />
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">From</label>
                                <input type="text" id="arrivalfrom" name="arrivalfrom"
                                    class="form-control text-uppercase" placeholder="From" autocomplete="off" />
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">STA</label>
                                <input type="text" id="arrivalsta" name="arrivalsta" class="form-control"
                                    placeholder="00:00" autocomplete="off" />
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">ATA</label>
                                <input type="text" id="arrivalata" name="arrivalata" class="form-control"
                                    placeholder="00:00" autocomplete="off" />
                            </div>
                            <div class="divider divider-dark mb-0 mt-0 divider-dashed">
                                <div class="divider-text text-uppercase fw-bold text-danger">Departure</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">Flight</label>
                                <input type="text" id="departureflight" name="departureflight"
                                    class="form-control text-uppercase" placeholder="Flight" autocomplete="off" />
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">To</label>
                                <input type="text" id="departureto" name="departureto"
                                    class="form-control text-uppercase" placeholder="To" autocomplete="off" />
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">STD</label>
                                <input type="text" id="departurestd" name="departurestd" class="form-control"
                                    placeholder="00:00" autocomplete="off" />
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted">ATD</label>
                                <input type="text" id="departureatd" name="departureatd" class="form-control"
                                    placeholder="00:00" autocomplete="off" />
                            </div>
                            <div class="divider divider-dark mb-0 mt-0 divider-dashed">
                                <div class="divider-text text-uppercase fw-bold text-info"><i class="ti ti-star"></i>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Status</label>
                                <input type="text" id="status" name="status" class="form-control text-uppercase"
                                    placeholder="Status" autocomplete="off" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Remarks</label>
                                <input type="text" id="remarks" name="remarks" class="form-control text-uppercase"
                                    placeholder="Remarks" autocomplete="off" />
                            </div>
                            <div id="dtLayanan" class="row g-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"><i
                                    class="ti ti-cloud-up me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailFlight" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Flight</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formDetail">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <input type="hidden" id="idreg" name="idreg" class="form-control" readonly />
                            </div>
                            <div id="dtRow"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/flight/detail-operator.js') }}"></script>
    <script src="{{ asset('assets/customjs/fixedColumns.js') }}"></script>
@endpush
