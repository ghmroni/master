<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP MONITORING</title>
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
            size: A4 landscape;
            margin: 12mm;
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
            <strong style="font-size:14px;">REKAP MONITORING</strong><br />
            <strong style="font-size:12px;">{{ $periode }}</strong><br />
        </p>
    </div>

    <table border="1"
        style="font-size:12px;width: 100%; table-layout: fixed; border-collapse: collapse; margin: auto;">
        <thead style="background: #dfdfe3;">
            <tr style="text-align: center;">
                <th width="10%" style="padding: 10px">TANGGAL</th>
                <th width="10%">NIP</th>
                <th>NAMA LENGKAP</th>
                <th>PENEMPATAN</th>
                <th>KETERANGAN</th>
                <th width="8%">PHOTO</th>
            </tr>
        </thead>
        <tbody>

            @forelse ($data as $row)
                @php
                    $colors = ['green', 'orange', 'blue', 'teal', 'gray', 'black'];
                    // ambil array penempatan dari row
                    $penempatan = $row['penempatan'] ?? [];
                    // mapping warna ke setiap lokasi
                    $lokasi = array_map(
                        fn($lok, $color) => "<span style='color:$color; font-weight:bold;font-size:10px'>$lok</span>",
                        $penempatan,
                        array_slice($colors, 0, count($penempatan)),
                    );
                @endphp

                <tr>
                    <td style="padding:4px;text-align: center;vertical-align: top">{{ $row['tanggal'] }}</td>
                    <td style="padding:4px;text-align: center;vertical-align: top">{{ $row['nip'] }}</td>
                    <td style="padding:4px;vertical-align: top">{{ $row['nama'] }}</td>
                    <td
                        style="padding:4px;vertical-align: top;white-space: nowrap;width:150px; overflow:hidden; text-overflow:ellipsis;">
                        {!! implode('<br>', $lokasi) !!}</td>
                    <td style="vertical-align: top;">
                        {!! $row['prioritas']
                            ? '<small style="color:' .
                                ($row['prioritas'] == 'NORMAL' ? 'green' : 'red') .
                                '; font-weight:bold;font-size:10px;">' .
                                $row['prioritas'] .
                                '</small>'
                            : '-' !!}
                        <br>
                        {{ $row['keterangan'] ?? '' }}
                    </td>
                    <td style="text-align: center;">
                        {!! $row['photo'] != null
                            ? '<img src="data:image/png;base64,' .
                                base64_encode(file_get_contents(public_path($row['photo']))) .
                                '" class="img-thumbnail" alt="image" width="70">'
                            : '-' !!}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:5px" class="text-mutted">Oops! Data tidak ditemukan</td>
                </tr>
            @endforelse


        </tbody>
    </table>


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
