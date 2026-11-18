<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP FLIGHT</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
            /* default */
        }

        @page landscapePage {
            margin: 10mm;
            size: A4 landscape;
        }

        .landscape {
            page: landscapePage;
        }

        body {
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
            margin-top: 75px;
            margin-bottom: 70px;
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
            /* height: 50px; */
            height: 55px;
        }

        .clear {
            clear: both;
        }
    </style>

</head>

</head>

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

    @php
        use Carbon\Carbon;
    @endphp

    @foreach ($datas as $key => $sheetData)
        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td colspan="3">REKAPITULASI PRODUKSI FLIGHT - <strong>{{ $sheetData['sheet'] }}</strong></td>
            </tr>
            <tr>
                <td width="20%">CABANG</td>
                <td width="2%" style="text-align:center;">:</td>
                <td><strong>{{ $sheetData['records'][$key]['cabang'] }}</strong></td>
            </tr>
            <tr>
                <td>OPERATOR</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $sheetData['records'][$key]['operator'] }}</strong></td>
            </tr>
            <tr>
                <td>AREA</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $sheetData['records'][$key]['area'] }}</strong></td>
            </tr>
            <tr>
                <td>PERIODE</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $datas[0]['periode'] }}</strong>
                </td>
            </tr>
        </table>
        <hr>
        <table style="width:100%; border-collapse:collapse; margin-top:10px">
            <thead class="table-light">
                <tr>
                    <th rowspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">NO</th>
                    <th colspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">TANGGAL</th>
                    <th colspan="3" style="border:1px solid #000; text-align:center;background:#DFDFE3;">AIRCRAFT
                    </th>
                    <th colspan="4" style="border:1px solid #000; text-align:center;background:#DFDFE3;">ARRIVAL</th>
                    <th colspan="4" style="border:1px solid #000; text-align:center;background:#DFDFE3;">DEPARTURE
                    </th>
                    <th rowspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">STATUS</th>
                    <th rowspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">REMARKS</th>
                </tr>
                <tr class="text-center">
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Arrival</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Departure</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Type</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Categori</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Registrasi</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Flight</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">From</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">STA</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">ATA</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">Flight</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">To</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">STD</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">ATD</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($sheetData['records'] as $item)
                    <tr>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">{{ $no++ }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ Carbon::parse($item['tgl_arrival'])->format('d-m-Y') }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ Carbon::parse($item['tgl_departure'])->format('d-m-Y') }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['aircraft_type'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['aircraft_categori'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['aircraft_registrasi'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['arrival_flight'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['arrival_from'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ Carbon::parse($item['arrival_sta'])->format('H:i') }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ !empty($item['arrival_ata']) && $item['arrival_ata'] !== '00:00:00' ? Carbon::parse($item['arrival_ata'])->format('H:i') : '' }}
                        </td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['departure_flight'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ $item['departure_to'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ Carbon::parse($item['departure_std'])->format('H:i') }}</td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">
                            {{ !empty($item['departure_atd']) && $item['departure_atd'] !== '00:00:00' ? Carbon::parse($item['departure_atd'])->format('H:i') : '' }}
                        </td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">{{ $item['status'] }}
                        </td>
                        <td style="border:0.5px solid #000; text-align:center;font-size:11px;">{{ $item['remarks'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="page-break-after: always;"></div>
    @endforeach

    {{-- REKAP FLIGHT --}}
    <div class="landscape">
        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td colspan="3"><strong>REKAPITULASI FLIGHT PRODUKSI PT GRHA HUMANINDO MANAJEMEN</strong></td>
            </tr>
            <tr>
                <td width="25%">CABANG</td>
                <td width="2%" style="text-align:center;">:</td>
                <td><strong>{{ $datas[0]['cabang'] }}</strong></td>
            </tr>
            <tr>
                <td>PAKET PEKERJAAN</td>
                <td style="text-align:center;">:</td>
                <td><strong>PAKET PEKERJAAN GH</strong></td>
            </tr>
            <tr>
                <td>PERIODE</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $datas[0]['periode'] }}</strong>
                </td>
            </tr>
            <tr>
                <td>AREA</td>
                <td style="text-align:center;">:</td>
                <td><strong>PT GAPURA ANGKASA</strong></td>
            </tr>
        </table>
        <hr>
        <table style="width:100%; border-collapse:collapse; margin-top:10px;white-space: nowrap !important;">
            <thead>
                <tr>
                    <th rowspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">NO
                    </th>
                    <th rowspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">OPERATOR /
                        FLIGHT</th>
                    <th colspan="31" style="border:1px solid #000; text-align:center;background:#DFDFE3;">TANGGAL</th>
                    <th rowspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">
                        TOTAL</th>
                </tr>
                <tr class="text-center">
                    @for ($i = 1; $i <= 31; $i++)
                        <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">{{ $i }}
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($datas as $sheet)
                    <tr>
                        <td style="border:0.5px solid #000; text-align:center;">{{ $no++ }}</td>
                        <td style="border:0.5px solid #000; text-align:left;">{{ $sheet['sheet'] }}</td>
                        @foreach ($sheet['rekap'] as $hari => $val)
                            <td style="border:0.5px solid #000; text-align:center;">{{ $val }}</td>
                        @endforeach
                        <td style="border:0.5px solid #000; text-align:center;"><strong>{{ $sheet['total'] }}</strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">TOTAL</th>
                    @foreach ($grandRekap as $day => $totalPerDay)
                        <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">
                            {{ $totalPerDay }}</th>
                    @endforeach
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">{{ $grandTotal }}</th>
                </tr>
            </tfoot>
        </table>

        {{-- REKAP TAGIHAN --}}
        <table style="width:50%; border-collapse:collapse; margin-top:18px;white-space: nowrap !important;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;width:10%">NO</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">TYPE AC</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;width:20%">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($datas as $item)
                    <tr>
                        <td style="border:0.5px solid #000; text-align:center;">{{ $no++ }}</td>
                        <td style="border:0.5px solid #000; text-align:left;">{{ $item['sheet'] }}</td>
                        <td style="border:0.5px solid #000; text-align:center;"><strong>{{ $item['total'] }}</strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">TOTAL
                        FLIGHT</th>
                    <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">{{ $grandTotal }}</th>
                </tr>
            </tfoot>
        </table>


        {{-- REKAP TAGIHAN PER LAYANAN --}}
        @foreach ($dataTitles as $title => $rows)
            <table style="width:50%; border-collapse:collapse; margin-top:18px;white-space: nowrap !important;">
                <thead>
                    <tr>
                        <th colspan="3" style="text-align: left"><strong>REKAP TAGIHAN
                                {{ $title }}</strong></th>
                    </tr>
                    <tr>
                        <th style="border:1px solid #000; text-align:center;background:#DFDFE3;width:10%">NO</th>
                        <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">TYPE AC</th>
                        <th style="border:1px solid #000; text-align:center;background:#DFDFE3;width:20%">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($rows['namaSheet'] as $sheetName => $count)
                        <tr>
                            <td style="border:0.5px solid #000; text-align:center;">{{ $no++ }}</td>
                            <td style="border:0.5px solid #000; text-align:left;">{{ $sheetName }}</td>
                            <td style="border:0.5px solid #000; text-align:center;">
                                <strong>{{ $count }}</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" style="border:1px solid #000; text-align:center;background:#DFDFE3;">TOTAL
                            {{ $title }}</th>
                        <th style="border:1px solid #000; text-align:center;background:#DFDFE3;">
                            {{ $rows['grandTotalTitle'] }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        @endforeach

        <table style="width:100%; border-collapse:collapse; margin-top:30px;white-space: nowrap !important;">
            <tr>
                <td colspan="3" style="text-align: left">
                    {{ ucfirst(strtolower($datas[0]['cabang'])) . ', ' . Carbon::parse(now())->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center;width:34%"><strong>{{ strtoupper($dataPetugas['ghm']) }}</strong></td>
                <td style="text-align: center;width:32%">&nbsp;</td>
                <td style="text-align: center;"><strong>{{ strtoupper($dataPetugas['gapura']) }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: center">Dibuat Oleh,</td>
                <td style="text-align: center">Mengetahui,</td>
                <td style="text-align: center">Menyetujui,</td>
            </tr>
            <tr>
                <td style="height:80px">&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: center;text-decoration: underline">
                    <strong>{{ strtoupper($dataPetugas['dibuat']) }}</strong>
                </td>
                <td style="text-align: center;text-decoration: underline">
                    <strong>{{ strtoupper($dataPetugas['mengetahui']) }}</strong>
                </td>
                <td style="text-align: center;text-decoration: underline">
                    <strong>{{ strtoupper($dataPetugas['menyetujui']) }}</strong>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">{{ $dataPetugas['jab1'] }}</td>
                <td style="text-align: center;">{{ $dataPetugas['jab2'] }}</td>
                <td style="text-align: center;">{{ $dataPetugas['jab3'] }}</td>
            </tr>
        </table>
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
