@extends('template.core')

@section('content')
    <div class="row">
        <div class="col-12">
            <form id="formApproval">
                @csrf
                <div class="card">
                    <div
                        class="card-header sticky-element bg-label-success d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                        <div class="col-md-6 text-center">
                            <span class="alert-icon text-warning me-2">
                                <i class="ti ti-bell ti-sm"></i>
                            </span>
                            <label class="text-dark">Silahkan pilih pelamar yang akan diproses menjadi karyawan.</label>
                            <label class="text-danger">Pelamar yang tidak di pilih / diceklis akan dikembalikan lagi ke data
                                pelamar</label>
                        </div>
                        <div class="col-md-3 text-center">
                            <h1 id="totalDipilih" class="text-success">0</h1>
                        </div>

                        <div class="action-btns">
                            @can('approve-banksdm')
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>
                                    Proses (semua)</button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body mt-2">
                        <div class="row">
                            <table id="tabelApproval" class="table table-hover table-striped table-hover nowrap"
                                style="width: 100%;font-size:12px">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal Permintaan</th>
                                        <th>Nama Pelamar</th>
                                        <th>Usia</th>
                                        <th>Pendidikan</th>
                                        <th>Keperluan Project</th>
                                        <th>Pilih</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/pemanggilan/persetujuan-user.js') }}"></script>
@endpush
