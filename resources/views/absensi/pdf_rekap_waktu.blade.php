<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP WAKTU</title>
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
            /* marin-bottom: 60px; */
        }

        p.line-height {
            line-height: 0.2;
        }

        tr,
        td {
            padding: 2px;
        }

        /* header {
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
        } */

        .clear {
            clear: both;
        }

        @page {
            size: A4 landscape;
            margin-top: 12mm;
            /* Ruang kosong di atas khusus tempat judul */
            margin-bottom: 2mm;
            margin-left: 2mm;
            margin-right: 2mm;
        }

        .header-pasti {
            position: fixed;
            top: -9mm;
            /* Didorong ke atas masuk area margin 12mm */
            left: 0px;
            right: 0px;
            height: 6mm;
            text-align: center;
        }
    </style>

</head>


<body>
    <div class="header-pasti">
        <p style="margin: 0; text-align: center;">
            <strong style="font-size: 14px;">REKAP WAKTU </strong><br>
            @if (!empty($data['data']) && count($data['data']) > 0)
                <strong style="font-size: 12px;">{{ strtoupper($data['periode']) }}</strong>
            @endif
        </p>
    </div>
    <div class="content" style="margin-bottom: 20px">
        @forelse ($data['data'] as $row)
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
                    style="width: 100%; table-layout: fixed; border-collapse: collapse; margin: auto;">
                    <thead>
                        <tr style="text-align: center;font-size:12px;">
                            @foreach ($row['kehadiran'] as $item)
                                @php
                                    // Ambil nama hari yang bersesuaian menggunakan index loop saat ini
                                    $namaHari = $item['hari'][$loop->index];

                                    $style = '';
                                    if ($namaHari === 'Minggu') {
                                        $style = 'background-color: #E5C7C9; color: #721C24; font-weight: bold;'; // Merah muda lembut
                                    } elseif ($namaHari === 'Sabtu') {
                                        $style = 'background-color: #FFF3CD; color: #856404; font-weight: bold;'; // Kuning lembut
                                    }
                                @endphp
                                <th colspan="2" style="{{ $style }}">TGL {{ $item['tanggal'] }}</th>
                            @endforeach
                        </tr>
                        <tr style="text-align: center;font-size:9px">
                            @foreach ($row['kehadiran'] as $item)
                                <th>IN</th>
                                <th>OUT</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align: center;font-size:9px;font-weight: bold">
                            @foreach ($row['kehadiran'] as $item)
                                @php
                                    $styleMasuk = '';
                                    $stylePulang = '';
                                    if ($item['hari'] === 'Minggu') {
                                        $styleMasuk = 'background-color:#E5C7C9; color:#721C24;';
                                        $stylePulang = 'background-color:#E5C7C9; color:#721C24;';
                                    } elseif ($item['hari'] === 'Sabtu') {
                                        $styleMasuk = 'background-color:#FFF3CD; color:#856404;';
                                        $stylePulang = 'background-color:#FFF3CD; color:#856404;';
                                    }
                                @endphp
                                <td style="{{ $styleMasuk }}">
                                    {{ $item['masuk'] ?? '-' }}
                                </td>
                                <td style="{{ $stylePulang }}">
                                    {{ $item['pulang'] ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @empty
            <p>Oops! Rekap kehadiran tidak ditemukan.</p>
        @endforelse
    </div>


</body>

</html>
