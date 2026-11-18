@extends('template.core')
@push('extra_style')
    <style>
        .sticky-element {
            position: sticky;
            top: 79px;
            z-index: 8;

            .window-scrolled & {
                inset-block-start: 0;
            }

            .layout-navbar-fixed & {
                inset-block-start: calc($navbar-height - .4375rem);
            }

            .layout-menu-fixed .layout-horizontal & {
                inset-block-start: calc($navbar-height * 2 - .25rem);
            }

            .layout-menu-fixed.layout-navbar-fixed .layout-horizontal & {
                inset-block-start: $navbar-height;
            }
        }
    </style>
@endpush
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    $grouped = $master->groupBy('sub_test');
@endphp
@section('content')
    <form id="cfitForm">
        @csrf
        <div class="card border shadow rounded">
            <div class="card-header bg-label-dark mb-3">
                <div class="col-md-12">
                    <div class="row align-items-center">
                        <div class="col-md-2 d-flex justify-content-center align-items-center">
                            <img src="{{ asset('assets/img/favicon/test.gif') }}" alt="Card image"
                                style="height:150px; width:auto;" />
                        </div>

                        <div class="col-md-10">
                            <div class="card-body">
                                <h5 class="card-title mb-2">{{ $peserta->nama }}</h5>
                                <span>
                                    {{ $peserta->jenis_kelamin == 1 ? 'LAKI - LAKI' : 'PEREMPUAN' }}</span><br>
                                <label>Tanggal Lahir :
                                    {{ Carbon::parse($peserta->tgllahir)->translatedFormat('d F Y') }}</label><br>
                                <label>Tanggal Test :
                                    {{ Carbon::parse($pemanggilan->tgl_panggil)->translatedFormat('d F Y') }}</label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" name="idpel" value="{{ $peserta->id_pelamar }}" readonly>
                    <input type="hidden" class="form-control" name="nomor"
                        value="{{ Str::replace(['INVITE-', 'RESEND-'], '', $pemanggilan->no_registrasi) }}" readonly>
                    <input type="hidden" class="form-control" name="nama" value="{{ $peserta->nama }}" readonly>
                    <input type="hidden" class="form-control" name="kelamin"
                        value="{{ $peserta->jenis_kelamin == 1 ? 'LAKI - LAKI' : 'PEREMPUAN' }}" readonly>
                    <input type="hidden" class="form-control" name="tgllahir" value="{{ $peserta->tgllahir }}" readonly>
                    <input type="hidden" class="form-control" name="tgltest" value="{{ $pemanggilan->tgl_panggil }}"
                        readonly>
                </div>
                <div class="col-md-12 mb-0">
                    <center>
                        <small class="text-danger">Silahkan menggunakan <strong>Arah Panah Atas</strong> (<i
                                class="fa fa-long-arrow-up"></i>) <strong>Arah Panah Bawah</strong> (<i
                                class="fa fa-long-arrow-down"></i>) atau <strong>Enter</strong> (&#8617) untuk melakukan
                            perpindahan</small>
                    </center>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($grouped as $subtest => $soalList)
                        <div class="col-md-6 col-lg-3">
                            <h6 class="mt-1 mb-0 text-muted text-uppercase fw-bold text-center">Test {{ $subtest }}
                            </h6>

                            <div class="card-body">
                                <table class="table table-hover table-sm table-bordered"
                                    style="width: 100%;font-size: 12px">
                                    <thead class="table-light">
                                        <tr class="text-center fw-bold">
                                            <th width="30%">NO</th>
                                            <th>Jawaban</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($soalList as $soal)
                                            <tr class="text-center fw-bold">
                                                <td style="background: #DFDFE3">{{ $soal->nomor_soal }}</td>
                                                <td>
                                                    <input type="text" name="jawaban[{{ $soal->id }}]"
                                                        class="form-control text-center text-uppercase jawaban-cfit"
                                                        data-soal-id="{{ $soal->id }}" maxlength="2"
                                                        autocomplete="off">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="accordion mt-3 border shadow rounded" id="accordionExample">
            <div class="card accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                        Lihat Hasil
                    </button>
                </h2>

                <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="table color-table table-primary table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-uppercase" style="text-align:center;vertical-align: middle;"
                                        rowspan="2" width="8%">
                                        <h2 style="font-weight:bold">IQ</h2>
                                    </th>
                                    <th class="text-uppercase" style="text-align:center;vertical-align: middle;"
                                        rowspan="2" width="15%">
                                        <h2 id="hasil-nilai" style="font-weight:bold">Skor</h2>
                                    </th>
                                    <th id="hasil-kategori">Mentally Retardation</th>
                                </tr>
                                <tr>
                                    <th>(Skala Binet)</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer mt-3">
            <button type="submit" class="btn btn-success"><i class="ti ti-cloud-up me-1"></i>Simpan Hasil</button>
        </div>

    </form>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/psikotest/cfit-input.js') }}"></script>
@endpush
