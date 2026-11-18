<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HASIL PSIKOTEST</title>
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
            margin-top: 75px;
            margin-bottom: 20px;
        }

        .ql-align-justify {
            text-align: justify;
        }

        p {
            text-indent: 20px;
        }

        p.line-height {
            line-height: 0.2;
        }

        tr,
        td {
            padding: 2px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
        }

        /* .header {
            width: 100%;
            padding-bottom: 10px;
        } */

        /* .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            border-top: 2px solid #000;
            padding-top: 5px;
        } */

        .clear {
            clear: both;
        }

        @page {
            margin: 15mm;
        }
    </style>

</head>

@php
    use Carbon\Carbon;
@endphp

<body>

    <header>
        <div style="float: left; width: 10%;height:55%;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/favicon/logo.png'))) }}"
                style="width:60px;height:100%;text-align:left" />
        </div>

        <div style="float: left;width: 90%;height: 6%;">
            <div
                style="width: 100%;text-align: center;margin-top:10px;font-weight: bold;font-size:24px;font-family: Arial, Helvetica, sans-serif;letter-spacing: 4.6px;">
                PT. GRHA HUMANINDO MANAJEMEN
            </div>
            <div
                style="width: 100%;margin-top:7px;text-align: center; font-weight: bold;font-size:11px;font-family: Segoe MDL2 Assets;font-style: italic;letter-spacing: 20px;margin-top:1;">
                safety - solve - secure
            </div>
        </div>

    </header>

    <div class="content" style="margin-bottom: 10px">

        {{-- ======================================================================================================================================== CFIT --}}
        @if ($data)
            <p class="line_height" style="text-align:center;">
                <strong style="font-size:14px;"> MBTI (Myers-Briggs Type Indicator)</strong><br />
            </p>

            <table border="0" width="100%" style="font-size:12px;">
                <tr>
                    <td width="20%">Nama Lengkap</td>
                    <td width="2%" style="text-align:center;">:</td>
                    <td><strong>{{ $data['nama'] }}</strong></td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td style="text-align:center;">:</td>
                    <td><strong>{{ $data['kelamin'] }}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td style="text-align:center;">:</td>
                    <td><strong>{{ Carbon::parse($data['tgllahir'])->translatedFormat('l, d F Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal Test</td>
                    <td style="text-align:center;">:</td>
                    <td><strong>{{ Carbon::parse($data['tglpanggil'])->translatedFormat('l, d F Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Usia</td>
                    <td style="text-align:center;">:</td>
                    <td><strong>{{ $data['usia'] }}</strong></td>
                </tr>
            </table>

            <hr style="margin-top:20px" />
            @php
                $persentase = json_decode($data['persentase'], true);
                $dimensi = json_decode($data['dimensi'], true);
                $analisis = json_decode($data['analisis'], true);
            @endphp

            <table border="0" width="80%"
                style="font-size:12px;border-collapse:collapse;border:1px solid #000;margin-left:60px;margin-top:10px">
                <thead>
                    <tr style="text-align: center">
                        <th style="border:0.5px solid #000;height:18px;background:#DFDFE3;">NO</th>
                        <th colspan="4" style="border:0.5px solid #000;height:18px;background:#DFDFE3;">DIMENSI
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height:15px;border:0.5px solid #000;text-align: center" width="5%">1.</td>
                        <td style="border:0.5px solid #000;text-align: right;font-weight: bold" width="32%">
                            EKSTROVERT (E)</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold" width="15%">
                            {{ $persentase['persent_e'] }}%
                        </td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold" width="15%">
                            {{ $persentase['persent_i'] }}%
                        </td>
                        <td style="border:0.5px solid #000;font-weight: bold">(I) INTROVERT</td>
                    </tr>
                    <tr>
                        <td style="border:0.5px solid #000;text-align: center">2.</td>
                        <td style="border:0.5px solid #000;text-align: right;font-weight: bold">SENSING (S)</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold">
                            {{ $persentase['persent_s'] }}%</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold">
                            {{ $persentase['persent_e'] }}%</td>
                        <td style="border:0.5px solid #000;font-weight: bold">(N) INTUITION</td>
                    </tr>
                    <tr>
                        <td style="border:0.5px solid #000;text-align: center">3.</td>
                        <td style="border:0.5px solid #000;text-align: right;font-weight: bold">THINKING (T)</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold">
                            {{ $persentase['persent_t'] }}%</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold">
                            {{ $persentase['persent_f'] }}%</td>
                        <td style="border:0.5px solid #000;font-weight: bold">(F) FEELING</td>
                    </tr>
                    <tr>
                        <td style="border:0.5px solid #000;text-align: center">4.</td>
                        <td style="border:0.5px solid #000;text-align: right;font-weight: bold">JUDGING (J)</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold">
                            {{ $persentase['persent_j'] }}%</td>
                        <td style="border:0.5px solid #000;text-align: center;font-weight: bold">
                            {{ $persentase['persent_p'] }}%</td>
                        <td style="border:0.5px solid #000;font-weight: bold">(P) PERCEIVING</td>
                    </tr>
                </tbody>
            </table>

            <table border="0" width="80%"
                style="font-size:12px;border-collapse:collapse;border:1px solid #000;margin-left:60px;margin-top:10px">
                <tr>
                    <td colspan="4">TIPE KEPRIBADIAN :</td>
                </tr>
                <tr>
                    <td width="25%" style="text-align: center;vertical-align: middle;height:50px">
                        <h1>{{ $dimensi[0] }}</h1>
                    </td>
                    <td width="25%" style="text-align: center;vertical-align: middle;height:50px">
                        <h1>{{ $dimensi[1] }}</h1>
                    </td>
                    <td width="25%" style="text-align: center;vertical-align: middle;height:50px">
                        <h1>{{ $dimensi[2] }}</h1>
                    </td>
                    <td width="25%" style="text-align: center;vertical-align: middle;height:50px">
                        <h1>{{ $dimensi[3] }}</h1>
                    </td>
                </tr>
            </table>

            <table border="0" width="100%" style="font-size:12px;border-collapse:collapse;margin-top:20px">
                <thead>
                    <tr>
                        <th width="7%" style="text-align:center">A.</th>
                        <th colspan="2" style="text-align:left">
                            DESKRIPSI KEPRIBADIAN
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="2">
                            <table width="100%"
                                style="font-size:12px;border-collapse:collapse;table-layout:fixed;margin-top:5px">
                                @foreach ($analisis['deskripsi'] as $index => $value)
                                    <tr style="border:0.5px solid #000;">
                                        <td width="5%" style="text-align:center;vertical-align: top">
                                            {{ $index + 1 }}.
                                        </td>
                                        <td style="text-align:justify">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table border="0" width="100%" style="font-size:12px;border-collapse:collapse;margin-top:20px">
                <thead>
                    <tr>
                        <th width="7%" style="text-align:center">B.</th>
                        <th colspan="2" style="text-align:left">
                            SARAN DAN PENGEMBANGAN
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="2">
                            <table width="100%"
                                style="font-size:12px;border-collapse:collapse;table-layout:fixed;margin-top:5px">
                                @foreach ($analisis['saran'] as $index => $value)
                                    <tr style="border:0.5px solid #000;">
                                        <td width="5%" style="text-align:center;vertical-align: top">
                                            {{ $index + 1 }}.
                                        </td>
                                        <td style="text-align:justify">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table border="0" width="100%" style="font-size:12px;border-collapse:collapse;margin-top:20px">
                <thead>
                    <tr>
                        <th width="7%" style="text-align:center">C.</th>
                        <th colspan="2" style="text-align:left">
                            SARAN PROFESI
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="2">
                            <table width="100%"
                                style="font-size:12px;border-collapse:collapse;table-layout:fixed;margin-top:5px">
                                <tr>
                                    <td style="text-align:justify">
                                        {{ $analisis['profesi'] }}
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <h4>Data tidak ditemukan.</h4>
        @endif
    </div>

    <footer>
        <hr style="height:2px;border:none;color:#333;background-color:#333;">
        <table width="100%"
            style="table-layout:fixed;vertical-align: bottom; font-family: serif;font-size: 7pt; color: #000000;line-height:1;">
            <tr>
                <td width="13%" style="font-weight:bold;vertical-align:top;">Kantor Pusat</td>
                <td width="1%" style="text-align:center;vertical-align:top;">:</td>
                <td style="text-align:justify; word-break:break-word; padding:2px;vertical-align:top;">
                    Komplek Perkantoran Bekasi Mas, Blok B No. 16 Jl. Jend A. Yani - Bekasi 17141 Ph. 021-8846075,
                    8896075, 88961207, 88961208, Fx.021-88957886
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td>Email : ghm-pusat@humanindo.co.id Website : www.humanindo.co.id</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Kantor Perwakilan</td>
                <td style="text-align:center;">:</td>
                <td>Denpasar - Bali, Jl. Majapahit No. 12 C Pelasa Kuta Badung - Kuta Bali, Telp. 0361 - 750224</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Kantor Perwakilan</td>
                <td style="text-align:center;">:</td>
                <td>Perum Delta Sari Indah Blok AT-21 Desa Kureksari - Waru - Sidoarjo Jawa Timur - 61257, Telp. 031
                    - 8548315</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Kantor Perwakilan</td>
                <td style="text-align:center;">:</td>
                <td>Bandara Soekarno Hatta Terminal 2D Lantai 2 Cengkareng - Tangerang - Banten, Telp. 021-5590633
                </td>
            </tr>
        </table>
    </footer>
</body>

</html>
