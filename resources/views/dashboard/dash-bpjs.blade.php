@extends('template.core')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" id="btnIn" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-karyawan-masuk" aria-controls="navs-karyawan-masuk" aria-selected="true">
                            Karyawan Masuk
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" id="btnOut" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-karyawan-out" aria-controls="navs-karyawan-out" aria-selected="false">
                            Karyawan Keluar
                        </button>
                    </li>
                </ul>
                <div class="tab-content border">
                    <div class="tab-pane fade show active" id="navs-karyawan-masuk" role="tabpanel">
                        <div class="alert alert-success" role="alert">
                            Data Karyawan yang masuk dari tanggal
                            <strong>{{ Carbon::now()->subMonths(2)->format('d F Y') }}</strong>
                            sampai dengan
                            <strong>{{ Carbon::now()->format('d F Y') }}</strong>
                        </div>
                        <div class="table-responsive">
                            <table id="tableMasuk" class="table table-hover table-striped table-bordered text-nowrap"
                                style="width: 100%;font-size:12px">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>NO</th>
                                        <th>NIP</th>
                                        <th>Nomor KTP</th>
                                        <th>Nama Lengkap</th>
                                        <th>Lokasi Penempatan</th>
                                        <th>Tanggal Masuk</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-karyawan-out" role="tabpanel">
                        <div class="alert alert-danger" role="alert">
                            Data Karyawan yang Keluar dari tanggal
                            <strong>{{ Carbon::now()->subMonths(2)->format('d F Y') }}</strong>
                            sampai dengan
                            <strong>{{ Carbon::now()->format('d F Y') }}</strong>
                        </div>
                        <div class="table-responsive">
                            <table id="tableKeluar" class="table table-hover table-striped table-bordered text-nowrap"
                                style="width: 100%;font-size:12px">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>NO</th>
                                        <th>NIP</th>
                                        <th>Nama Lengkap</th>
                                        <th>Lokasi Penempatan</th>
                                        <th>Tanggal Keluar</th>
                                        <th>Alasan Keluar</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/dashboard/dashboard-bpjs.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush
