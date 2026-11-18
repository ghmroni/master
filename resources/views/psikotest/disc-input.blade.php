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

        .input-disc[readonly]:focus {
            outline: none !important;
            border: 2px solid #ff9800 !important;
            /* Warna border oranye/kuning yang sangat kontras */
            /* background-color: #fffde7 !important; */
            /* Warna background kuning soft agar eye-catching */
            box-shadow: 0 0 8px rgba(255, 152, 0, 0.6) !important;
        }

        .flot-chart {
            display: block;
            height: 400px;
        }

        .flot-chart-content {
            width: 100%;
            height: 100%;
        }

        html body .jqstooltip,
        html body .flotTip {
            width: auto !important;
            height: auto !important;
            background: #adb5bd;
            color: #000;
            padding: 5px 10px;
        }

        body .jqstooltip {
            border-color: transparent;
            border-radius: 60px;
        }
    </style>
@endpush
@php
    use Carbon\Carbon;
    $soalPerKolom = 8;
    $totalKolom = 3;
@endphp
@section('content')
    <form id="discForm">
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
                                class="fa fa-long-arrow-up"></i>)
                            <strong>Arah Panah Bawah</strong> (<i class="fa fa-long-arrow-down"></i>)
                            <strong>Arah Panah Kanan</strong> (<i class="fa fa-long-arrow-right"></i>)
                            <strong>Arah Panah Kiri</strong> (<i class="fa fa-long-arrow-left"></i>)
                        </small>
                        <center>
                            <small class="fw-bold text-primary">Gunakan SPASI untuk input jawaban (X)</small>
                        </center>
                    </center>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    @for ($kolom = 0; $kolom < $totalKolom; $kolom++)
                        <div class="col-md-4">
                            @php
                                $startSoal = $kolom * $soalPerKolom + 1;
                                $endSoal = $startSoal + $soalPerKolom - 1;
                            @endphp

                            @for ($nomorSoal = $startSoal; $nomorSoal <= $endSoal; $nomorSoal++)
                                @if (!isset($soals[$nomorSoal]))
                                    @break
                                @endif

                                @php
                                    $pilihanSifat = $soals[$nomorSoal];
                                    $isFirst = true;
                                @endphp

                                <!-- Hapus table-layout: fixed, biarkan auto agar kolom teks mengambil ruang sisa secara alami -->
                                <table class="table table-xs table-bordered table-disc mb-3"
                                    style="width:100%; font-size:12px;">
                                    <thead>
                                        <tr class="fw-bold bg-light">
                                            <th class="text-center"
                                                style="width: 30px; max-width: 30px; padding: 6px 2px;font-size: 12px">NO
                                            </th>
                                            <th class="text-center"
                                                style="width: 30px; max-width: 30px; padding: 6px 2px;font-size: 12px">P
                                            </th>
                                            <th class="text-center"
                                                style="width: 30px; max-width: 30px; padding: 6px 2px;font-size: 12px">K
                                            </th>
                                            <th class="ps-2" style="padding: 6px 2px;font-size: 12px">GAMBARAN DIRI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pilihanSifat as $rows)
                                            <tr>
                                                {{-- Nomor soal dengan rowspan --}}
                                                @if ($isFirst)
                                                    <td rowspan="4" class="text-center fw-bold"
                                                        style="vertical-align: middle; width: 30px; max-width: 30px; padding: 0;">
                                                        {{ $nomorSoal }}
                                                    </td>
                                                    @php $isFirst = false; @endphp
                                                @endif

                                                <!-- Kolom P -->
                                                <td style="text-align: center; width: 30px; max-width: 30px; padding: 2px;">
                                                    <input type="text"
                                                        class="form-control form-control-sm text-center input-disc chk-most fw-bold text-dark"
                                                        data-nomor="{{ $nomorSoal }}"
                                                        data-baris="{{ $rows->nomor_baris }}" data-tipe="most"
                                                        style="width: 24px; height: 24px; margin: 0 auto; padding: 0; text-transform: uppercase;  font-size: 11px;"
                                                        readonly inputmode="none">
                                                </td>

                                                <!-- Kolom K -->
                                                <td style="text-align: center; width: 30px; max-width: 30px; padding: 2px;">
                                                    <input type="text"
                                                        class="form-control form-control-sm text-center input-disc chk-least fw-bold text-dark"
                                                        data-nomor="{{ $nomorSoal }}"
                                                        data-baris="{{ $rows->nomor_baris }}" data-tipe="least"
                                                        style="width: 24px; height: 24px; margin: 0 auto; padding: 0; text-transform: uppercase; font-size: 11px;"
                                                        readonly inputmode="none">
                                                </td>

                                                <!-- Kolom Pernyataan (Akan menghabiskan seluruh sisa lebar layar secara otomatis) -->
                                                <td class="ps-2" style="vertical-align: middle; padding: 4px 0 4px 8px;">
                                                    {{ $rows->pernyataan }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div id="hidden-answers-container"></div>
        <input type="hidden" name="hasil_line" id="hasil_line" class="form-control" readonly>
        <input type="hidden" name="hasil_grafik" id="hasil_grafik" class="form-control" readonly>
        <input type="hidden" name="hasil_tabel" id="hasil_tabel" class="form-control" readonly>
        <input type="hidden" name="hasil_definisi" id="hasil_definisi" class="form-control" readonly>
        <input type="hidden" name="hasil_kepribadian" id="hasil_kepribadian" class="form-control" readonly>
        <input type="hidden" name="hasil_job" id="hasil_job" class="form-control" readonly>

        <div class="accordion mt-3" id="accordionExample">
            <div class="card accordion-item border shadow rounded">
                <h2 class="accordion-header" id="headingOne">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                        TABEL HASIL
                    </button>
                </h2>

                <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row">
                            @php
                                $totalPerKolom = 8;
                            @endphp
                            @for ($kolom = 0; $kolom < 3; $kolom++)
                                <div class="col-md-4">
                                    <table class="table table-xs table-bordered nowrap table-striped"
                                        style="width: 100%;font-size: 12px">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th
                                                    style="width: 25px; max-width: 25px; padding: 2px;font-size: 12px;vertical-align: middle">
                                                    NO
                                                </th>
                                                <th
                                                    style="width: 35px; max-width: 35px; padding: 2px;font-size: 12px;vertical-align: middle">
                                                    P</th>
                                                <th
                                                    style="width: 35px; max-width: 35px; padding: 2px;font-size: 12px;vertical-align: middle">
                                                    K</th>
                                                <th
                                                    style="width: 100px; max-width: 100px; padding: 5px;font-size: 12px;vertical-align: middle">
                                                    Faktor (P)</th>
                                                <th
                                                    style="width: 100px; max-width: 100px; padding: 5px;font-size: 12px;vertical-align: middle">
                                                    Faktor (K)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $start = $kolom * $totalPerKolom + 1;
                                                $end = $start + $totalPerKolom - 1;
                                            @endphp
                                            @for ($i = $start; $i <= $end; $i++)
                                                <tr>
                                                    <td class="fw-bold">{{ $i }}</td>
                                                    <td class="text-center" id="m-baris-{{ $i }}">-</td>
                                                    <td class="text-center" id="k-baris-{{ $i }}">-</td>
                                                    <td class="text-center fw-bold" id="m-faktor-{{ $i }}">*
                                                    </td>
                                                    <td class="text-center fw-bold" id="k-faktor-{{ $i }}">*
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div class="card accordion-item border shadow rounded">
                <h2 class="accordion-header" id="headingTwo">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">
                        GAMBARAN KARAKTER
                    </button>
                </h2>
                <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="font-weight-bold text-primary"><u>Mask Public Self</u></h6>
                                <div id="maskProfile" class="font-weight-bold"></div>
                                <div id="maskText"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="font-weight-bold text-primary"><u>Core Private Self</u></h6>
                                <div id="coreProfile" class="font-weight-bold"></div>
                                <div id="coreText"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="font-weight-bold text-primary"><u>Mirror Received Self</u></h6>
                                <div id="mirrorProfile" class="font-weight-bold"></div>
                                <div id="mirrorText"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card accordion-item border shadow rounded">
                <h2 class="accordion-header" id="headingKepribadian">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionKepribadian" aria-expanded="false"
                        aria-controls="accordionKepribadian">
                        KEPRIBADIAN
                    </button>
                </h2>
                <div id="accordionKepribadian" class="accordion-collapse collapse" aria-labelledby="headingKepribadian"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card shadow-none bg-transparent border border-secondary mb-3">
                                    <div class="card-body" id="kepribadianText">
                                        Some quick example text to build on the card title and make up
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card accordion-item border shadow rounded">
                <h2 class="accordion-header" id="headingJob">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionJob" aria-expanded="false" aria-controls="accordionJob">
                        JOB MATCH
                    </button>
                </h2>
                <div id="accordionJob" class="accordion-collapse collapse" aria-labelledby="headingJob"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card shadow-none bg-transparent border border-secondary mb-3">
                                    <div class="card-body" id="jobText">
                                        Some quick example text to build on the card title and make up
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card accordion-item border shadow rounded active">
                <h2 class="accordion-header" id="headingThree">
                    <button type="button" class="accordion-button" data-bs-toggle="collapse"
                        data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">
                        GRAFIK
                    </button>
                </h2>
                <div id="accordionThree" class="accordion-collapse collapse show" aria-labelledby="headingThree"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <table id="tableDisc" class="table table-bordered table-striped"
                                    style="width: 100%;font-size: 12px">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>Line</th>
                                            <th>D</th>
                                            <th>I</th>
                                            <th>S</th>
                                            <th>C</th>
                                            <th>*</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7">Data tidak ditemukan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-7">
                                <div class="row mb-3">
                                    <div class="col-md-4 text-center mb-3">
                                        <h6 style="font-weight: bold; margin-bottom: 2px;">GRAPH 1 MOST</h6>
                                        <p class="text-muted" style="font-size: 12px; margin-bottom: 15px;">Mask Public
                                            Self
                                        </p>
                                        <div class="border shadow rounded"
                                            style="position: relative; height: 400px; padding: 10px;">
                                            <div class="flot-chart">
                                                <div class="flot-chart-content" id="mask-line-chart"
                                                    style="height:400px;width: 100%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h6 style="font-weight: bold; margin-bottom: 2px;">GRAPH 2 LEAST</h6>
                                        <p class="text-muted" style="font-size: 12px; margin-bottom: 15px;">Core Private
                                            Self
                                        </p>
                                        <div class="border shadow rounded"
                                            style="position: relative; height: 400px; padding: 10px;">
                                            <div class="flot-chart">
                                                <div class="flot-chart-content" id="core-line-chart"
                                                    style="height:400px;width: 100%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h6 style="font-weight: bold; margin-bottom: 2px;">GRAPH 3 CHANGE</h6>
                                        <p class="text-muted" style="font-size: 12px; margin-bottom: 15px;">Mirror
                                            Perceived
                                            Self
                                        </p>
                                        <div class="border shadow rounded"
                                            style="position: relative; height: 400px; padding: 10px;">
                                            <div class="flot-chart">
                                                <div class="flot-chart-content" id="mirror-line-chart"
                                                    style="height:400px;width: 100%;"></div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
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
    <script src="{{ asset('assets/customjs/psikotest/disc-input.js') }}"></script>
    <script src="{{ asset('') }}assets/flot/jquery.flot.js"></script>
    <script src="{{ asset('') }}assets/flot/jquery.flot.pie.js"></script>
    <script src="{{ asset('') }}assets/flot/jquery.flot.time.js"></script>
    <script src="{{ asset('') }}assets/flot/jquery.flot.stack.js"></script>
    <script src="{{ asset('') }}assets/flot/jquery.flot.crosshair.js"></script>
    <script src="{{ asset('') }}assets/flot/excanvas.js"></script>
    <script src="{{ asset('') }}assets/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
@endpush
