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
@endphp
@section('content')
    <form id="mbtiForm">
        @csrf
        <div class="card border shadow rounded">
            <div class="card-header sticky-element bg-label-dark mb-3">
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
                                    {{ Carbon::parse($pemanggilan->tgl_pemanggilan)->translatedFormat('d F Y') }}</label>
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
                <table class="table table-hover table-sm table-striped" style="width: 100%;font-size: 12px">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="3%">NO</th>
                            <th>Pernyataan</th>
                            <th class="text-center" width="25%">Jawaban</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($soals as $soal)
                            <tr>
                                <td class="text-center fw-bold" style="vertical-align: top;">{{ $soal->nomor_soal }}</td>
                                <td style="text-align: justify">
                                    {{ $soal->soal }}
                                    <label class="d-flex mt-1">
                                        <span style="font-weight: bold;padding-right: 13px">a</span> {{ $soal->pilihan_a }}
                                    </label>
                                    <label class="d-flex mt-1">
                                        <span style="font-weight: bold;padding-right: 13px">b</span> {{ $soal->pilihan_b }}
                                    </label>
                                </td>
                                <td style="vertical-align:middle;">
                                    <input type="text" name="jawaban[{{ $soal->id }}]"
                                        class="form-control text-center text-uppercase jawaban-mbti fw-bold"
                                        data-soal-id="{{ $soal->id }}"
                                        oninput="this.value = this.value.replace(/[^AaBb]/g,'');" maxlength="1"
                                        autocomplete="off" style="border: 1px solid #aaa;">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" name="hasil_analisis" id="hasil_analisis" class="form-control" readonly>
        <input type="hidden" name="hasil_dimensi" id="hasil_dimensi" class="form-control" readonly>
        <input type="hidden" name="hasil_persentase" id="hasil_persentase" class="form-control" readonly>

        <div class="accordion mt-3" id="accordionExample">
            <div class="card accordion-item border shadow">
                <h2 class="accordion-header" id="headingOne">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                        TIPE KEPRIBADIAN
                    </button>
                </h2>

                <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="card-body d-flex justify-content-center mb-0">
                            <div class="col-md-8">
                                <table class="table table-sm table-bordered m-b-0 table-striped" width="100%">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th class="fw-bold">NO</th>
                                            <th colspan="4" class="fw-bold">DIMENSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center" width="5%">1.</td>
                                            <td class="text-right fw-bold" width="32%">EKSTROVERT (E)</td>
                                            <td class="text-center fw-bold" width="15%" id="persent_e">0%</td>
                                            <td class="text-center fw-bold" width="15%" id="persent_i">0%</td>
                                            <td class="fw-bold">(I) INTROVERT</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">2.</td>
                                            <td class="text-right fw-bold">SENSING (S)</td>
                                            <td class="text-center fw-bold" id="persent_s">0%</td>
                                            <td class="text-center fw-bold" id="persent_n">0%</td>
                                            <td class="fw-bold">(N) INTUITION</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">3.</td>
                                            <td class="text-right fw-bold">THINKING (T)</td>
                                            <td class="text-center fw-bold" id="persent_t">0%</td>
                                            <td class="text-center fw-bold" id="persent_f">0%</td>
                                            <td class="fw-bold">(F) FEELING</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">4.</td>
                                            <td class="text-right fw-bold">JUDGING (J)</td>
                                            <td class="text-center fw-bold" id="persent_j">0%</td>
                                            <td class="text-center fw-bold" id="persent_p">0%</td>
                                            <td class="fw-bold">(P) PERCEIVING</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-body d-flex justify-content-center mt-0">
                            <div class="col-md-6">
                                <ul class="nav nav-tabs widget-nav-tabs pb-3 gap-4 mx-1 d-flex flex-nowrap"
                                    role="tablist">
                                    <li class="nav-item">
                                        <a href="javascript:void(0);"
                                            class="nav-link btn active d-flex flex-column align-items-center justify-content-center"
                                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id"
                                            aria-controls="navs-orders-id" aria-selected="true">
                                            <h1 class="text-uppercase fw-bold text-primary tab-widget-title mb-0 mt-0"
                                                id="dimensi_1">I</h1>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="javascript:void(0);"
                                            class="nav-link btn active d-flex flex-column align-items-center justify-content-center"
                                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id"
                                            aria-controls="navs-orders-id" aria-selected="true">
                                            <h1 class="text-uppercase fw-bold text-primary tab-widget-title mb-0 mt-0"
                                                id="dimensi_2">N</h1>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="javascript:void(0);"
                                            class="nav-link btn active d-flex flex-column align-items-center justify-content-center"
                                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id"
                                            aria-controls="navs-orders-id" aria-selected="true">
                                            <h1 class="text-uppercase fw-bold text-primary tab-widget-title mb-0 mt-0"
                                                id="dimensi_3">F</h1>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="javascript:void(0);"
                                            class="nav-link btn active d-flex flex-column align-items-center justify-content-center"
                                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-orders-id"
                                            aria-controls="navs-orders-id" aria-selected="true">
                                            <h1 class="text-uppercase fw-bold text-primary tab-widget-title mb-0 mt-0"
                                                id="dimensi_4">P</h1>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card accordion-item border shadow">
                <h2 class="accordion-header" id="headingTwo">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">
                        ANALISIS
                    </button>
                </h2>
                <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="table table-sm table-borderless" style="width:100%;font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="2">
                                        <h6 class="font-weight-bold">A. DESKRIPSI KEPRIBADIAN</h6>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="rowDeskripsi">
                            </tbody>
                        </table>
                        <table class="table table-sm table-borderless" style="width:100%;font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="2">
                                        <h6 class="font-weight-bold">B. SARAN DAN PENGEMBANGAN</h6>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="rowSaran">
                            </tbody>
                        </table>
                        <table class="table table-sm table-borderless" style="width:100%;font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <h6 class="font-weight-bold">C. SARAN PROFESI</h6>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="rowProfesi"></td>
                                </tr>
                            </tbody>
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
    <script src="{{ asset('assets/customjs/psikotest/mbti-input.js') }}"></script>
@endpush
