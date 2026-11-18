<!doctype html>

<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="horizontal-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ $peserta->nama ?? null }}</title>

    <meta name="description" content="HRIS | Formulir Wawancara" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/interview.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/rtl/core.css"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/rtl/theme-default.css"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/animate-css/animate.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/sweetalert2/sweetalert2.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/spinkit/spinkit.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('') }}assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('') }}assets/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('') }}assets/js/config.js"></script>
    <style>
        td.clickable {
            cursor: pointer;
        }
    </style>
</head>

<body>
    @php
        use Carbon\Carbon;
    @endphp

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
        <div class="layout-container">
            <!-- Navbar -->

            <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme">
                <div class="container-xxl">
                    <div class="navbar-brand app-brand demo d-flex py-0 mx-auto">
                        <a href="javascript:void(0)" class="app-brand-link gap-2 d-flex align-items-center">
                            <img src="{{ asset('assets/img/favicon/interview.png') }}" alt="" width="32"
                                height="22">
                            <span class="app-brand-text demo menu-text fw-bold text-center text-wrap">
                                FORMULIR WAWANCARA
                            </span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- / Navbar -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper">


                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <!-- Basic example section -->
                            <div class="col-12 mb-4">
                                <div class="card border shadow">
                                    <div class="card-body">
                                        <form id="formWawancara" action="{{ route('test.storeWawancara') }}"
                                            method="POST">
                                            @csrf
                                            <div class="table-responsive">
                                                <table class="table table-sm" style="width: 100%;font-size:12px">
                                                    <tr>
                                                        <th width="12%">Nama Lengkap</th>
                                                        <th width="1%" class="text-center">:</th>
                                                        <td width="30%" class="text-muted">{{ $peserta->nama }}</td>
                                                        <th width="15%">Pendidikan</th>
                                                        <th width="1%" class="text-center">:</th>
                                                        <td class="text-muted">
                                                            {{ $peserta->jenjang_pendidikan ?? null }}
                                                            ({{ $peserta->jurusan_sekolah ?? null }})</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tanggal Lahir</th>
                                                        <th class="text-center">:</th>
                                                        <td class="text-muted">
                                                            {{ strtoupper(Carbon::parse($peserta->tgllahir)->translatedFormat('d F Y')) }}
                                                        </td>
                                                        <th>Kategori Pelamar</th>
                                                        <th class="text-center">:</th>
                                                        <td class="text-muted">{{ $peserta->referensi }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Berat Badan</th>
                                                        <th class="text-center">:</th>
                                                        <td class="text-muted">
                                                            {{ $peserta->berat_badan ?? 0 }} cm
                                                        </td>
                                                        <th>Tinggi Badan</th>
                                                        <th class="text-center">:</th>
                                                        <td class="text-muted">{{ $peserta->tinggi_badan ?? 0 }} cm
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6">&nbsp;</td>
                                                        <input type="hidden" class="form-control" name="pemanggilanid"
                                                            id="pemanggilanid" value="{{ $pemanggilan->id }}" readonly>
                                                        <input type="hidden" class="form-control" name="pelamarid"
                                                            id="pelamarid" value="{{ $peserta->id_pelamar }}" readonly>
                                                        <input type="hidden" class="form-control" name="nama"
                                                            id="nama" value="{{ $peserta->nama }}" readonly>
                                                        <input type="hidden" class="form-control" name="kategori"
                                                            id="kategori" value="{{ $peserta->referensi }}"
                                                            readonly>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div class="table-responsive mb-3">
                                                <table class="table table-striped table-bordered table-hover"
                                                    style="width: 100%;font-size: 12px">
                                                    <thead class="table-light" style="vertical-align: middle">
                                                        <tr class="text-center">
                                                            <th rowspan="2">NO</th>
                                                            <th rowspan="2">DIMENSI</th>
                                                            <th rowspan="2" width="57%">URAIAN</th>
                                                            <th colspan="4">WAWANCARA</th>
                                                        </tr>
                                                        <tr class="text-center">
                                                            <th width="3%">1</th>
                                                            <th width="3%">2</th>
                                                            <th width="3%">3</th>
                                                            <th width="3%">4</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center" width="2%">1</td>
                                                            <td>Pendidikan Formal</td>
                                                            <td>
                                                                Tingkat pendidikan (formal) yang dapat menunjang posisi
                                                                yang dilamar
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="pendidikan" class="form-check-input"
                                                                        type="radio" value="1" required />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="pendidikan" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="pendidikan" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="pendidikan" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">2</td>
                                                            <td>Pengalaman Kerja</td>
                                                            <td>Kesesuaian antara berbagai hal yang pernah ditangani
                                                                dalam pekerjaan sebelumnya dengan pekerjaan yang dilamar
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="pengalaman" class="form-check-input"
                                                                        type="radio" value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="pengalaman" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="pengalaman" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="pengalaman" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">3</td>
                                                            <td>Pengetahuan Teknis</td>
                                                            <td>Pengetahuan praktis atau teoritis yang dikuasai
                                                                berkenaan dengan posisi yang dilamar</td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="pengetahuan" class="form-check-input"
                                                                        type="radio" value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="pengetahuan" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="pengetahuan" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="pengetahuan" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">4</td>
                                                            <td>Keterampilan Teknis</td>
                                                            <td>Kemampuan mengatasi permasalahan yang mungkin terjadi
                                                                pada posisi yang dilamar</td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="keterampilan"
                                                                        class="form-check-input" type="radio"
                                                                        value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="keterampilan"
                                                                        class="form-check-input" type="radio"
                                                                        value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="keterampilan"
                                                                        class="form-check-input" type="radio"
                                                                        value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="keterampilan"
                                                                        class="form-check-input" type="radio"
                                                                        value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">5</td>
                                                            <td>Motivasi</td>
                                                            <td>Menunjukan besarnya semangat serta minat terhadap
                                                                kondisi pekerjaan yang dilamar</td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="motivasi" class="form-check-input"
                                                                        type="radio" value="1" required />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="motivasi" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="motivasi" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="motivasi" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">6</td>
                                                            <td>Kerjasama (Team Work)</td>
                                                            <td>Kemampuan beradaptasi dan bekerjasama dengan rekan kerja
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="kerjasama" class="form-check-input"
                                                                        type="radio" value="1" required />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="kerjasama" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="kerjasama" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="kerjasama" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">7</td>
                                                            <td>Etika (Sopan Santun)</td>
                                                            <td>Menunjukan sikap sopan santun dalam bertutur kata maupun
                                                                bertingkah laku
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="etika" class="form-check-input"
                                                                        type="radio" value="1" required />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="etika" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="etika" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="etika" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">8</td>
                                                            <td>Kemampuan Komunikasi</td>
                                                            <td>Kemampuan mengungkapkan ide secara sistematis dan jelas
                                                                sehingga dapat dipahami dengan benar oleh orang lain
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="komunikasi" class="form-check-input"
                                                                        type="radio" value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="komunikasi" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="komunikasi" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="komunikasi" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">9</td>
                                                            <td>Kemampuan Bahasa Asing <span
                                                                    class="text-warning">*</span><br>
                                                                <input type="text" name="bahasa_asing"
                                                                    class="form-control text-uppercase"
                                                                    style="border:1px solid #3B5998;"
                                                                    placeholder="Bahasa" autocomplete="off">
                                                            </td>
                                                            <td style="vertical-align: top">Kemampuan dalam berbahasa
                                                                asing yang dapat dipahami
                                                                dengan benar oleh orang lain</td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="bahasa" class="form-check-input"
                                                                        type="radio" value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="bahasa" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="bahasa" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="bahasa" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">10</td>
                                                            <td>Penampilan Diri</td>
                                                            <td>Cara berpakaian, kerapihan, serta keadaan fisik secara
                                                                umum, ada tidaknya cacat fisik</td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="penampilan" class="form-check-input"
                                                                        type="radio" value="1" required />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="penampilan" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="penampilan" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="penampilan" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">11</td>
                                                            <td>Kemampuan Memimpin <span class="text-warning">*
                                                                    *</span>
                                                            </td>
                                                            <td>Kemampuan untuk mengorganisif orang lain dan bawahan
                                                                yang berada di bawah pengawasannya</td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="memimpin" class="form-check-input"
                                                                        type="radio" value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="memimpin" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="memimpin" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="memimpin" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">12</td>
                                                            <td>Pemeriksaan Referensi <span class="text-warning">* *
                                                                    *</span></td>
                                                            <td>Riwayat pekerjaan kandidiat di perusahaan sebelumnya
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Kurang"
                                                                data-bs-custom-class="tooltip-danger">
                                                                <div class="form-check form-check-danger">
                                                                    <input name="referensi" class="form-check-input"
                                                                        type="radio" value="1" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Cukup"
                                                                data-bs-custom-class="tooltip-warning">
                                                                <div class="form-check form-check-warning">
                                                                    <input name="referensi" class="form-check-input"
                                                                        type="radio" value="2" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Baik"
                                                                data-bs-custom-class="tooltip-primary">
                                                                <div class="form-check form-check-primary">
                                                                    <input name="referensi" class="form-check-input"
                                                                        type="radio" value="3" />
                                                                </div>
                                                            </td>
                                                            <td class="text-center clickable" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Sangat Baik"
                                                                data-bs-custom-class="tooltip-success">
                                                                <div class="form-check form-check-success">
                                                                    <input name="referensi" class="form-check-input"
                                                                        type="radio" value="4" />
                                                                </div>
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="row">

                                                <div class="col-md-5">
                                                    <small class="pb-1 mb-4 text-muted">Keterangan Nilai</small>
                                                    <table class="table table-borderless"
                                                        style="width: 100%;font-size: 12px">
                                                        <tr>
                                                            <td width="25%"><i
                                                                    class="fas fa-dot-circle me-2 text-danger"></i>Kurang
                                                            </td>
                                                            <td><i class="fas fa-dot-circle me-2 text-primary"></i>Baik
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><i
                                                                    class="fas fa-dot-circle me-2 text-warning"></i>Cukup
                                                            </td>
                                                            <td><i
                                                                    class="fas fa-dot-circle me-2 text-success"></i>Sangat
                                                                Baik
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <div class="col-md-6">
                                                    <small class="pb-1 mb-4 text-muted">Petunjuk Penilaian : (Isi
                                                        dengan Menggunakan tanda <i
                                                            class="fas fa-dot-circle"></i>)</small>
                                                    <table class="table table-borderless table-sm"
                                                        style="width: 100%;font-size: 12px">
                                                        <tr>
                                                            <td class="text-muted text-center" width="1%">1.</td>
                                                            <td>(<span class="text-warning ms-1 me-1">*</span>)
                                                                Lingkari yang sesuai / sebutkan yang lainnya</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted text-center">2.</td>
                                                            <td>(<span class="text-warning ms-1 me-1">* *</span>) Hanya
                                                                untuk pangkat Supervisor atau setara ke atas</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted text-center">3.</td>
                                                            <td>(<span class="text-warning ms-1 me-1">* * *</span>)
                                                                Hanya untuk pangkat Manager atau setara ke atas</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="divider text-start divider-primary mb-0 mt-0">
                                                <div class="divider-text">Kesimpulan dan Saran</div>
                                            </div>
                                            <div class="card-body d-flex justify-content-center">
                                                <div class="col-md-9">
                                                    <div class="row text-center">
                                                        <div class="col-md-4">
                                                            <div
                                                                class="form-check form-check-success form-check-inline mt-0">
                                                                <input class="form-check-input" type="radio"
                                                                    name="hasil" id="disarankan" value="disarankan"
                                                                    required>
                                                                <label class="form-check-label" for="disarankan">Dapat
                                                                    Disarankan</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check form-check-inline mt-0">
                                                                <input class="form-check-input" type="radio"
                                                                    name="hasil" id="dipertimbangkan"
                                                                    value="dipertimbangkan">
                                                                <label class="form-check-label"
                                                                    for="dipertimbangkan">Dipertimbangkan</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div
                                                                class="form-check form-check-warning form-check-inline mt-0">
                                                                <input class="form-check-input" type="radio"
                                                                    name="hasil" id="tidakDisarankan"
                                                                    value="tidak disarankan">
                                                                <label class="form-check-label"
                                                                    for="tidakDisarankan">Tidak Disarankan</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <textarea name="catatan" id="catatan" class="form-control" rows="8" placeholder="saran / komentar"></textarea>
                                            </div>
                                            <div class="modal-footer mt-3">
                                                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan
                                                    Hasil</button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--/ Content -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!--/ Content wrapper -->
            </div>

            <!--/ Layout container -->
        </div>
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>

    <!--/ Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="{{ asset('') }}assets/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('') }}assets/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/i18n/i18n.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('') }}assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('') }}assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('') }}assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="{{ asset('') }}assets/js/extended-ui-sweetalert2.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/block-ui/block-ui.js"></script>

    <script>
        $(document).ready(function() {
            $(document).on('click', 'td.text-center', function() {
                var radio = $(this).find('input[type=radio]');
                if (radio.length) {
                    radio.prop('checked', true).trigger('change');
                }
            });

            document.getElementById('formWawancara').addEventListener('submit', function(e) {
                e.preventDefault();

                var form = this;
                var formData = new FormData(form);
                var peserta = document.getElementById('nama').value;

                Swal.fire({
                    title: "Konfirmasi!",
                    html: "Simpan Hasil Wawancara <strong>" + peserta + "</strong>",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Simpan",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function(result) {
                    if (result.value) {
                        $.blockUI({
                            message: '<div class="d-flex justify-content-center"><p class="mb-0">Please wait...</p> <div class="sk-wave m-0"><div class="sk-rect sk-wave-rect"></div><div class="sk-rect sk-wave-rect"></div><div class="sk-rect sk-wave-rect"></div><div class="sk-rect sk-wave-rect"></div><div class="sk-rect sk-wave-rect"></div></div></div>',
                            css: {
                                backgroundColor: 'transparent',
                                color: '#fff',
                                border: '0'
                            },
                            overlayCSS: {
                                opacity: 0.5
                            }
                        });

                        // proses simpan via fetch
                        fetch(form.action, {
                                method: form.method,
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    // kalau status bukan 2xx, lempar error dengan isi response
                                    return response.json().then(errData => {
                                        throw new Error(errData.message ||
                                            "Terjadi kesalahan");
                                    });
                                }
                                return response.json()
                            })
                            .then(data => {
                                $.unblockUI(); // tutup loading

                                if (data.success) {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Hasil Interview Berhasil Disimpan.",
                                        icon: "success",
                                        showConfirmButton: false,
                                        timer: 1200,
                                        allowOutsideClick: false,
                                        showClass: {
                                            popup: "animate__animated animate__rubberBand",
                                        },
                                        hideClass: {
                                            popup: "animate__animated animate__bounceOut",
                                        },
                                        customClass: {
                                            confirmButton: "",
                                        },
                                        buttonsStyling: false,
                                        willOpen: function() {
                                            Swal.showLoading();
                                        },
                                    }).then(function(result) {
                                        window.close();
                                        location.href = '/data-pemanggilan';
                                    });

                                } else {
                                    Swal.fire({
                                        title: "Gagal",
                                        text: err,
                                        icon: "error",
                                        showClass: {
                                            popup: "animate__animated animate__shakeX",
                                        },
                                        hideClass: {
                                            popup: "animate__animated animate__bounceOut",
                                        },
                                        customClass: {
                                            confirmButton: "btn btn-primary waves-effect waves-light",
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            })
                            .catch(err => {
                                $.unblockUI();
                                Swal.fire({
                                    title: "Error",
                                    text: err,
                                    icon: "error",
                                    showClass: {
                                        popup: "animate__animated animate__shakeX",
                                    },
                                    hideClass: {
                                        popup: "animate__animated animate__bounceOut",
                                    },
                                    customClass: {
                                        confirmButton: "btn btn-primary waves-effect waves-light",
                                    },
                                    buttonsStyling: false,
                                });
                            });
                    }
                });

            });


        })
    </script>
</body>

</html>
