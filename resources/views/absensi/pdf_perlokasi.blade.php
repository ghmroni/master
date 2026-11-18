<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP PERLOKASI</title>
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
            margin-top: 80px;
            margin-bottom: 60px;
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

    <div class="content" style="margin-bottom: 20px">
        <p class="line_height" style="text-align:center;">
            <strong style="font-size:14px;">REKAP KEHADIRAN PERLOKASI</strong><br />
        </p>
        @foreach ($data as $row)
            <div style="page-break-inside: avoid; margin-bottom:20px;">
                @php
                    $colors = ['green', 'orange', 'blue', 'teal', 'gray', 'black'];
                    $lokasi = array_map(
                        fn($item, $color) => "<span style='color:$color; font-weight:bold;font-size:12px'>$item</span>",
                        $row['lokasi'],
                        array_slice($colors, 0, count($row['lokasi'])),
                    );
                @endphp
                <h3 style="margin-bottom: 2px; margin-top: 0px;">{{ $row['nama'] }} ({{ $row['nip'] }})</h3>
                <p style="margin-bottom: 3px;margin-top: 0px">{!! implode(' ', $lokasi) !!}</p>

                <table border="1"
                    style="font-size:12px;width: 100%; table-layout: fixed; border-collapse: collapse; margin: auto;">
                    <thead style="background: #dfdfe3;">
                        <tr style="text-align: center;">
                            <th width="8%" style="padding: 8px">HARI</th>
                            <th width="18%">TANGGAL</th>
                            <th width="15%">ABSEN MASUK</th>
                            <th width="15%">ABSEN PULANG</th>
                            <th>MONITORING</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($row['kehadiran'] as $absen)
                            @php
                                $style = '';
                                if ($absen['hari'] === 'Minggu') {
                                    $style = 'background-color: pink;'; // merah muda
                                }
                            @endphp
                            <tr style="{{ $style }}">
                                <td>{{ $absen['hari'] }}</td>
                                <td style="text-align: center">{{ $absen['tanggal'] }}</td>
                                <td style="text-align: center">{{ $absen['masuk'] }}</td>
                                <td style="text-align: center">{{ $absen['pulang'] }}</td>
                                <td>{{ $absen['keterangan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

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
