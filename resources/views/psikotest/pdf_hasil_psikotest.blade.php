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
        <p class="line_height" style="text-align:center;">
            <strong style="font-size:14px;">HASIL PSIKOTEST</strong><br />
        </p>

        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td width="20%">Tanggal Test</td>
                <td width="2%" style="text-align:center;">:</td>
                <td><strong>{{ $data['tgltest'] }}</strong></td>
            </tr>
            <tr>
                <td>Keperluan</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $data['keperluan'] }}</strong></td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $data['nama'] }}</strong></td>
            </tr>
            <tr>
                <td>Pendidikan Terakhir</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $data['pendidikan'] }}</strong></td>
            </tr>
        </table>


        <h4 style="margin-bottom: 3px">CFIT : </h4>

        <div style="text-align: justify">
            <p>{!! $data['cfit'] !!}</p>
        </div>
        <h4 style="margin-bottom: 3px">DISC : </h4>
        <div style="text-align: justify">
            {!! $data['disc'] !!}
        </div>
        <h4 style="margin-bottom: 3px">PAPI : </h4>
        <div style="text-align: justify">
            {!! $data['papi'] !!}
        </div>
        <h4 style="margin-bottom: 3px">INTERVIEW : </h4>
        <div style="text-align: justify">
            <p>
                {!! $data['interview'] !!}
            </p>
        </div>
    </div>

    <table border="0" width="100%" style="font-size:12px;margin-top:0px">
        <tr>
            <td width="65%">&nbsp;</td>
            <td style="text-align: center">{{ $data['created'] }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td height="70px" style="vertical-align: middle;text-align: center;">
                {{-- <img src="data:image/png;base64,{{ $barcode }}" alt="QR Code" style="width:100px; height:auto;"> --}}
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style="text-align: center"><strong><u>{{ $data['petugas'] }}</u></strong></td>
        </tr>
    </table>

    {{-- ======================================================================================================================================== CFIT --}}
    @if ($cfit)
        <div style="page-break-after: always;"></div>

        <p class="line_height" style="text-align:center;">
            <strong style="font-size:14px;"> CFIT (Culture Fair Intelligence)</strong><br />
        </p>

        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td width="20%">Nama Lengkap</td>
                <td width="2%" style="text-align:center;">:</td>
                <td><strong>{{ $cfit['nama'] }}</strong></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $cfit['kelamin'] }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ Carbon::parse($cfit['tgllahir'])->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Test</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ Carbon::parse($cfit['tglpanggil'])->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td>Usia</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $cfit['usia'] }}</strong></td>
            </tr>
        </table>

        <hr style="margin-top:10px" />

        <table style="width:100%; border-collapse:collapse; margin-top:10px">
            <tr>
                @foreach ($cfit['data_jawaban'] as $sub => $jawaban)
                    <td
                        style="width:25%; text-align:center; vertical-align: top; font-weight:bold; text-transform:uppercase; color:#6c757d;">
                        Test {{ $sub }}

                        <table
                            style="width:100%; border:1px solid #000; border-collapse:collapse; font-size:12px;margin-top:15px">
                            <thead>
                                <tr>
                                    <th
                                        style="border:1px solid #000; text-align:center;background:#DFDFE3;height:20px;">
                                        NO</th>
                                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">JAWABAN
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jawaban as $item)
                                    <tr>
                                        <td style="border:0.5px solid #000; text-align:center;">
                                            {{ $item['nomor_soal'] }}
                                        </td>
                                        <td style="border:0.5px solid #000; text-align:center;">
                                            {{ $item['jawaban'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>


        <table style="width:100%;border:0.5px solid #000;border-collapse:collapse;margin-top:25px;">
            <tr>
                <th style="text-align:center;font-size:24px;width:18%;border:0.5px solid #000;" rowspan="2">IQ</th>
                <th style="text-align:center;font-size:24px;width:18%;border:0.5px solid #000;" rowspan="2">
                    {{ $cfit['skor_standar'] }}</th>
                <th style="height: 30px;text-align: left;border:0.5px solid #000;">
                    {{ $cfit['keterangan'] }}</th>
            </tr>
            <tr>
                <th style="height: 30px;text-align: left;border:0.5px solid #000;">( SKALA BINET)</th>
            </tr>
        </table>
    @endif

    {{-- ======================================================================================================================================== DISC --}}
    @if ($disc)
        @php
            $dataDisc = json_decode($disc['data_karakter'], true);
        @endphp

        <div style="page-break-after: always;"></div>

        <p class="line_height" style="text-align:center;">
            <strong style="font-size:14px;">DISC (Dominance, Influence, Steadiness, dan Compliance)</strong><br />
        </p>

        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td width="20%">Nama Lengkap</td>
                <td width="2%" style="text-align:center;">:</td>
                <td><strong>{{ $disc['nama'] }}</strong></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $disc['kelamin'] }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ Carbon::parse($disc['tgllahir'])->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Test</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ Carbon::parse($disc['tglpanggil'])->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td>Usia</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $disc['usia'] }}</strong></td>
            </tr>
        </table>

        <hr style="margin-top:10px" />

        <table style="width:100%; border-collapse:collapse;">
            {{-- <tr> --}}
            @foreach ($dataDisc as $item => $value)
                @if ($loop->index % 2 == 0)
                    <tr style="margin-top:20px;"> {{-- buka baris baru setiap 2 item --}}
                @endif
                <td style="width:50%; text-align:left; vertical-align: top; color:#6c757d;padding-top:15px;">
                    <strong><u>{{ str_replace('_', ' ', ucwords(strtolower($item))) . ' Self' }}</u></strong>
                    <table style="width:100%; border-collapse:collapse; font-size:12px;margin-top:7px;color:#000">
                        <thead>
                            <tr>
                                <th style="height:20px;text-align: left;">{{ $value['profile_title'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($value['karakter'] as $karakter)
                                <tr>
                                    <td style="text-align: left">{{ $karakter }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                @if ($loop->index % 2 == 1 || $loop->last)
                    </tr> {{-- tutup baris setelah 2 kolom atau di item terakhir --}}
                @endif
            @endforeach
            {{-- </tr> --}}
        </table>
    @endif

    {{-- ======================================================================================================================================== PAPI --}}

    @if ($papi)
        <div style="page-break-after: always;"></div>

        <p class="line_height" style="text-align:center;">
            <strong style="font-size:14px;">PAPI KOSTICK (Personality and Preference Inventory)</strong><br />
        </p>

        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td width="20%">Nama Lengkap</td>
                <td width="2%" style="text-align:center;">:</td>
                <td><strong>{{ $papi['nama'] }}</strong></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $papi['kelamin'] }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ Carbon::parse($papi['tgllahir'])->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Test</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ Carbon::parse($papi['tglpanggil'])->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td>Usia</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $papi['usia'] }}</strong></td>
            </tr>
        </table>

        <hr style="margin-top:10px" />

        @php
            $dataPapi = json_decode($papi['analisis'], true);
        @endphp

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="border:0.5px solid #000;height:18px;background:#DFDFE3;">BIDANG</th>
                    <th style="border:0.5px solid #000;background:#DFDFE3;">ASPEK</th>
                    <th style="border:0.5px solid #000;background:#DFDFE3;padding-left:3px;padding-right:3px">FAKTOR
                    </th>
                    <th style="border:0.5px solid #000;background:#DFDFE3;padding-left:3px;padding-right:3px">NILAI
                    </th>
                    <th style="border:0.5px solid #000;background:#DFDFE3;">ANALISIS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentBidang = '';
                @endphp
                @foreach ($norma as $factorCode => $items)
                    @php
                        $row = $items->first();
                    @endphp

                    <tr>
                        @if ($row->bidang !== $currentBidang)
                            @php
                                $currentBidang = $row->bidang;
                                $rowspan = $norma
                                    ->filter(function ($g) use ($currentBidang) {
                                        return $g->first()->bidang === $currentBidang;
                                    })
                                    ->count();
                            @endphp
                            <td style="border:0.5px solid #000;" rowspan="{{ $rowspan }}">
                                {{ ucwords(strtolower($row->bidang)) }}
                            </td>
                        @endif
                        <td style="border:0.5px solid #000;white-space:nowrap;">{{ ucfirst(strtolower($row->aspek)) }}
                        </td>
                        <td style="border:0.5px solid #000;text-align: center">{{ $factorCode }}</td>
                        <td style="border:0.5px solid #000;text-align: center">
                            {{ $dataPapi[$factorCode]['skor'] ?? '' }}</td>
                        <td style="border:0.5px solid #000;text-align: justify">
                            {{ $dataPapi[$factorCode]['analisis'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

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
