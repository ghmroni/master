<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PKWT</title>
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
        }

        p.line-height {
            line-height: 0.2;
        }

        tr,
        td {
            padding: 2px;
        }

        .header {
            width: 100%;
            /* border-bottom: 2px solid #000; */
            padding-bottom: 10px;
            /* margin-bottom: 20px; */
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            /* font-size: 10px; */
            font-family: Arial, Helvetica, sans-serif;
            border-top: 2px solid #000;
            padding-top: 5px;
        }

        .clear {
            clear: both;
        }

        @page {
            margin-top: {{ $margin['top'] }};
            margin-right: {{ $margin['right'] }};
            margin-bottom: {{ $margin['bottom'] }};
            margin-left: {{ $margin['left'] }};
        }
    </style>

</head>


<body>

    @if ($kopsurat == 'kop')
        <div class="header">
            <div style="float: left; width: 10%;height:6%;">
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
            <div class="clear"></div>
        </div>
    @endif

    <div class="content">
        <p class="line_height" style="text-align:center;">
            <strong style="text-decoration:underline;font-size:14px;">PERJANJIAN KERJA WAKTU TERTENTU</strong><br />
            <strong style="font-size:12px;">Nomor : {{ $data->nomor }}</strong>
        </p>

        <table border="0" width="100%" style="font-size:12px;">
            <tr>
                <td colspan="3">Yang bertanda tangan di bawah ini :</td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td width="30%" style="padding-left:30px;">Nama</td>
                <td width="2%" style="text-align:center;">:</td>
                <td width="68%"><strong></strong></td>
            </tr>
            <tr>
                <td style="padding-left:30px;">Jabatan</td>
                <td style="text-align:center;">:</td>
                <td><strong></strong></td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: justify;padding-left:30px;">
                    Dalam hal ini bertindak untuk dan atas nama PT. Grha Humanindo Manajemen, berkedudukan di Komplek
                    Perkantoran Bekasi
                    Mas Blok B 16, Jl. Jend. A Yani, Bekasi 17141, yang selanjutnya disebut <strong><i>Pihak
                            Kesatu</i>.</strong>
                </td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td style="padding-left:30px;">Nama</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ ucwords(strtolower($data->nama)) }}</strong></td>
            </tr>
            <tr>
                <td style="padding-left:30px;">Nomor Pegawai</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ ucwords(strtolower($data->nip)) }}</strong></td>
            </tr>
            <tr>
                <td style="padding-left:30px;">Tempat / Tanggal Lahir</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ ucwords(strtolower($data->ttl)) }}</strong></td>
            </tr>
            <tr>
                <td style="padding-left:30px;vertical-align: top;">Alamat</td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="text-align: justify;"><strong>{{ ucwords(strtolower($data->alamat)) }}</strong></td>
            </tr>
            <tr>
                <td style="padding-left:30px;">Hp / Telepon</td>
                <td style="text-align:center;">:</td>
                <td><strong>{{ $data->hp }}</strong></td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: justify;">
                    Dalam hal ini bertindak untuk dan atas nama diri sendiri dan selanjutnya disebut <strong><i>Pihak
                            Kedua.</i></strong>
                </td>
            </tr>
        </table>

    </div>

    <table border="0" width="100%" style="margin-top:10px;">
        <tr>
            <td colspan="3" style="height:15px;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><strong>PIHAK KESATU</strong></td>
            <td></td>
            <td style="vertical-align: top;width:40%;text-align:center;"><strong>PIHAK KEDUA</strong></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td style="height:90px;vertical-align: middle;text-align: center;">

            </td>
        </tr>
        <tr>
            <td>
                <strong><u></u></strong><br>
                <strong></strong>
            </td>
            <td></td>
            <td style="text-align: center;"><strong><u><?= ucwords(strtolower($data->nama)) ?></u></strong></td>
        </tr>
        <tr>
            <td style="vertical-align: top;" colspan="2">
                <small style="font-size:8px;color:darkgray;"><i>*) {{ $cc }} </i></small>
            </td>
            <td style="font-weight:bold;text-align: center;">
                <img src="data:image/png;base64,{{ $barcode }}" alt="QR Code" style="width:100px; height:auto;">
            </td>
        </tr>
    </table>


    @if ($kopsurat == 'kop')
        <div class="footer">
            <table width="100%" style="vertical-align: bottom; font-family: serif;font-size: 7pt; color: #000000;">
                <tr>
                    <td width="17%" style="font-weight:bold;vertical-align: top;">Kantor Pusat</td>
                    <td width="1%" style="text-align:center;vertical-align: top;">:</td>
                    <td>Komplek Perkantoran Bekasi Mas, Blok B No. 16 Jl. Jend A. Yani - Bekasi 17141 Ph. 021-8846075,
                        8896075, 88961207
                        Fx.021-88957886 Email : ghm-pusat@humanindo.co.id Website : www.humanindo.co.id</td>
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
        </div>
    @endif

</body>

</html>
