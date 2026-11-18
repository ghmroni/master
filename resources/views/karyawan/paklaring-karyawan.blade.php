<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PAKLARING</title>
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            font-size: 14px;
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
            /* Jarak: Atas, Kanan, Bawah, Kiri */
            margin: 1cm 2cm 1cm 2cm;
        }
    </style>

</head>


<body>


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


    <div class="content">
        <div style="text-align:center;line-height: 1.5;margin-bottom: 10px;padding-top:10px">
            <div style="margin-bottom: 10px;">
                <strong style="text-decoration:underline; font-size:16px;">SURAT KETERANGAN KERJA</strong><br />
                <strong style="text-decoration:underline; font-size:16px;">CERTIFICATE OF EMPLOYEE</strong><br />
                <strong style="font-size:14px;">Nomor : {{ $data['nomor_surat'] }}</strong>
            </div>
        </div>

        <table border="0" width="100%" style="font-size:13px;margin-top:10px;line-height: 1.8">
            <tr>
                <td width="30%" style="vertical-align: top;">
                    <u>Dengan ini menerangkan bahwa</u>
                    <span style="display: block; line-height: 1; font-style: italic;">This is to certify that</span>
                </td>
                <td width="5%" style="text-align:center; vertical-align: top;">:</td>
                <td style="vertical-align: top;"></td>
            </tr>
            <tr>
                <td style="letter-spacing: 1px; vertical-align: top;">
                    <u>Nama</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Name</span>
                </td>
                <td style="text-align:center; vertical-align: top;">:</td>
                <td style="vertical-align: top; font-weight:bold;">{{ ucwords(strtolower($data['nama'])) }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Tempat / Tanggal Lahir</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Place / Date of Birth</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">{{ ucwords(strtolower($data['ttl'])) }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Nomor Induk Karyawan</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Badge No.</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">{{ ucwords(strtolower($data['nip'])) }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Unit Kerja</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Site Project</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">{{ ucwords(strtolower($data['nama_project'])) }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Jabatan / Golongan</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Title / Level</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">{{ ucwords(strtolower($data['jabatan'])) }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Masa Kerja</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Periode Service</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">{{ $data['periode'] }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Alasan Berhenti</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Reason for Leaving</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">{{ $alasan }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <u>Keterangan</u>
                    <span style="display: block; line-height: 1; font-style: italic;">Remarks</span>
                </td>
                <td style="text-align:center;vertical-align: top;">:</td>
                <td style="vertical-align: top;font-weight:bold;">= =</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: justify;padding-top: 25px;">
                    <u style="display: block; line-height: 1.3;">
                        Kami mengucapkan terima kasih atas usaha dan dedikasi yang telah anda berikan dan berharap
                        semoga
                        keberhasilan selalu menyertai anda di waktu mendatang.
                    </u>
                    <span style="display: block; line-height: 1; font-style: italic;padding-top: 5px;">
                        We would like to take this opportunity to thank you for your part effort and contribution and to
                        wish you every
                        success in teh future.
                    </span>
                </td>
            </tr>

        </table>

    </div>

    <table border="0" width="100%" style="margin-top:10px;">
        <tr>
            <td colspan="3" style="height:15px;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <strong>{{ ucwords(strtolower($data['lokasi_ttd'])) . ', ' . $data['tanggal'] }}</strong>
            </td>
            <td>&nbsp;</td>
            <td style="vertical-align: top;width:40%;text-align:center;">&nbsp;</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td style="height:90px;vertical-align: middle;text-align: center;">
                <img src="data:image/png;base64,{{ $barcode }}" alt="QR Code" style="width:100px; height:auto;">
            </td>
        </tr>

        <tr>
            <td style="vertical-align: top;" colspan="3">
                <strong><u>Erik Cosmas Edo.S,SH</u><br>
                    Manager HR - Legal</strong>
            </td>
        </tr>
    </table>


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

</body>

</html>
