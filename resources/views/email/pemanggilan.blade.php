<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Psikotest</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }

        .header {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .content {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .login-details {
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-top: 20px;
        }

        .login-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .login-details th,
        .login-details td {
            padding: 10px;
            text-align: left;
        }

        .login-details th {
            background-color: #DFDFE3;
            color: #333;
            /* background-color: #007bff;
            color: #fff; */
        }

        .login-details td {
            background-color: #fff;
        }

        .button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .logo {
            margin-bottom: 20px;
        }
    </style>
</head>
{{-- @dd($data) --}}

<body>
    <div class="container">
        <img src="{{ $message->embed(public_path('assets/img/banner/logo-nama.png')) }}" alt="Logo" class="logo"
            style="width: 300px">
        <div class="header">Halo {{ $data['nama'] }},</div>
        <div class="content">

            <p>
                Anda diundang untuk mengikuti Psikotes & Interview secara di kantor PT Grha Humanindo Manajemen
                (GHM) :<br>
                Hari/Tanggal : <strong>{{ $data['tgltest'] }} </strong><br>
                Pukul : <strong>{{ $data['jam'] }} (Hadir 30 menit sebelumnya)</strong><br>
                Alamat : Klik <a href="https://maps.app.goo.gl/dmvXbWjJkFAcNrQ19"
                    style="background-color:aqua;color: #333;padding:5px;border-radius:5px;">
                    disini</a> untuk melihat lokasi psikotes.

            </p>
            <div class="login-details">
                <table style="width: 100%" class="table">
                    <tr>
                        <th colspan="3">Persyaratan</th>
                    </tr>
                    @if ($data['project'] == 'project1')
                        <tr>
                            <td width="3%" style="text-align: center">1.</td>
                            <td>Membawa CV dan Sertifikat pendukung (Wajib ukuran kertas A4) </td>
                        </tr>
                        <tr>
                            <td width="3%" style="text-align: center">2.</td>
                            <td>Pakaian: Kemeja bebas (bukan hitam-putih), rapi, dan sopan.</td>
                        </tr>
                    @else
                        <tr>
                            <td width="3%" style="text-align: center">1.</td>
                            <td>Membawa CV, SIM A/C (Wajib ukuran kertas A4) </td>
                        </tr>
                        <tr>
                            <td width="3%" style="text-align: center">2.</td>
                            <td>Pakaian : Kemeja dan Celana Bahan (Hitam dan Putih), rapi, dan sopan.</td>
                        </tr>
                    @endif

                </table>
            </div>
            <p>Terima kasih.<br>
                Hormat kami,<br>
                Rekrutmen PT Grha Humanindo Manajemen (GHM)</p>

            <p>Silakan klik tombol di bawah ini untuk Verifikasi kedatangan.</p>
            <a href="{{ $data['url'] }}" style="color: #fff" class="button">Verifikasi Undangan</a>
        </div>
    </div>
</body>

</html>
