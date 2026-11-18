<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembuatan Akun HRIS</title>
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
            background-color: #007bff;
            color: #fff;
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

<body>
    <div class="container">
        {{-- <img src="https://humanindo.co.id/assets/images/logo.png" alt="Logo MyGHM" class="logo"> --}}
        <img src="{{ asset('assets/img/banner/logo-nama.png') }}" alt="Logo MyGHM" class="logo">
        <div class="header">Selamat datang {{ $data['name'] }},</div>
        <div class="content">
            {{-- <p>Kepada Yth. ADE RONI,</p> --}}
            <p>Akun HRIS Anda telah dibuat, dan Anda dapat login dengan detail berikut:</p>
            <div class="login-details">
                <table style="width: 100%" class="table">
                    <tr>
                        <th colspan="3">Detail Login</th>
                    </tr>
                    <tr>
                        <td width="25%">Nama Lengkap</td>
                        <td width="1%">:</td>
                        <td>{{ $data['name'] }}</td>
                    </tr>
                    <tr>
                        <td>Username</td>
                        <td>:</td>
                        <td>{{ $data['email'] }}</td>
                    </tr>
                    <tr>
                        <td>Password</td>
                        <td>:</td>
                        <td>{{ $data['password'] }}</td>
                    </tr>
                    <tr>
                        <td>Level</td>
                        <td>:</td>
                        <td>{{ $data['level'] }}</td>
                    </tr>
                </table>
            </div>
            <p>Silakan klik tombol di bawah ini untuk login ke akun Anda :</p>
            <a href="{{ $data['url'] }}" style="color: #fff" class="button">Login</a>
        </div>
    </div>
</body>

</html>
