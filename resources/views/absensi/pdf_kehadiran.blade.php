<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP KEHADIRAN</title>
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
            <strong style="font-size: 14px;">REKAP KEHADIRAN
                {{ $useSchedule ? '- BERDASARKAN SCHEDULE' : '' }}
            </strong><br>
            @if (!empty($data) && count($data) > 0)
                <strong style="font-size: 12px;">{{ strtoupper($data[0]['dari']) }} s/d
                    {{ strtoupper($data[0]['sampai']) }}</strong>
            @endif
        </p>
    </div>
    <div class="content" style="margin-bottom: 20px">
        @forelse ($data as $row)

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
                            <th colspan="{{ count($row['data_kehadiran']) }}"
                                style="padding: 8px; background-color: #dfdfe3;">
                                TANGGAL
                            </th>
                        </tr>
                        <tr style="text-align: center;font-size:9px">
                            @foreach ($row['data_tanggal'] as $item)
                                @php
                                    // Ambil nama hari yang bersesuaian menggunakan index loop saat ini
                                    $namaHari = $row['hari'][$loop->index];

                                    $style = '';
                                    if ($namaHari === 'Minggu') {
                                        $style = 'background-color: #E5C7C9; color: #721C24; font-weight: bold;'; // Merah muda lembut
                                    } elseif ($namaHari === 'Sabtu') {
                                        $style = 'background-color: #FFF3CD; color: #856404; font-weight: bold;'; // Kuning lembut
                                    }
                                @endphp
                                <th style="{{ $style }}">{{ date('j', strtotime($item)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align: center;font-size:9px;font-weight: bold">
                            @foreach ($row['data_kehadiran'] as $item)
                                @php
                                    // Ambil nama hari yang bersesuaian menggunakan index loop saat ini
                                    $namaHari = $row['hari'][$loop->index];

                                    $style = '';
                                    if ($namaHari === 'Minggu') {
                                        $style = 'background-color: #E5C7C9; color: #721C24;'; // Merah muda lembut
                                    } elseif ($namaHari === 'Sabtu') {
                                        $style = 'background-color: #FFF3CD; color: #856404;'; // Kuning lembut
                                    }

                                    // Tambahan: jika valuenya "L" → merah
                                    if ($item === 'L') {
                                        $style = 'background-color: #F5C6CB; color: #721C24;'; // Merah lembut
                                    }
                                @endphp
                                <td style="font-weight: bold;{{ $style }}">{{ $item != null ? $item : '-' }}</td>
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
