<!DOCTYPE html>
<html>

<head>
    <title>Absensi Pelamar</title>
    <style>
        /* body {
            font-family: sans-serif;
        } */

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px
        }


        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        tbody tr {
            height: 36px;
        }
    </style>
</head>

<body>
    <h3 style="text-align: center">Absensi Pelamar</h3>
    <p style="font-size: 12px">Hari / Tanggal : {{ $tanggal }}</p>

    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NAMA LENGKAP</th>
                <th>TANGGAL KONFIRMASI</th>
            </tr>
        </thead>
        <tbody>
            @if (count($datas) > 0)
                @foreach ($datas as $i => $pelamar)
                    <tr>
                        <td style="text-align:center;width:10%">{{ $i + 1 }}</td>
                        <td>{{ $pelamar->lowonganApply->pelamar->nama }}</td>
                        <td style="width: 23%;vertical-align: middle;height:30px;text-align:center">
                            <small style="font-size: 9px;color:gray;">
                                {{ \Carbon\Carbon::parse($pelamar->create_hadir)->translatedFormat('l, d F Y H:i') }}
                            </small>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" style="color: gray">Oops! Pelamar tidak ditemukan</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
