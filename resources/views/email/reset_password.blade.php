<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: auto;
        }

        .logo {
            margin-bottom: 20px;
        }

        .button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }

        .footer img {
            width: 50px;
            margin: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- <img src="https://humanindo.co.id/assets/images/logo.png" alt="Logo MyGHM" class="logo"> --}}
        <img src="{{ asset('assets/img/banner/logo-nama.png') }}" alt="Logo MyGHM" class="logo">
        <p>Hai {{ $data['nama'] }},</p>
        <p>Kami menerima permintaan untuk mereset kata sandi akun HRIS Anda. Klik tombol di bawah ini untuk
            melanjutkan proses reset kata sandi.</p>
        <a href="{{ $data['url'] }}" class="button" style="color: #fff">Reset Password</a>
        <p>Link Reset Password akan berakhir dalam <strong>30</strong> menit.</p>
        <div class="footer">
            <p>Jika ada pertanyaan lebih lanjut, Anda dapat menghubungi Team
                <strong>IT</strong> PT Grha Humanindo Manajemen.
            </p>
        </div>
    </div>
</body>

</html>
