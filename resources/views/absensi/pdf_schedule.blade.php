<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <title>DATA SCHEDULE</title> --}}
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
            <strong style="font-size: 14px;">DATA SCHEDULE</strong><br>
            <strong style="font-size: 12px;">PERIODE BULAN {{ strtoupper($periode) }} </strong>
        </p>
    </div>
    <div class="content" style="margin-bottom: 20px">
        @foreach ($data as $row)
            <div style="page-break-inside: avoid; margin-bottom:20px;">
                <h3 style="margin-bottom: 2px; margin-top: 0px;">{{ $row['nama'] }} ({{ $row['nip'] }})</h3>
                @foreach ($row['lokasi'] as $i => $lok)
                    @php $color = ['green','orange','blue','teal','gray','black'][$i]; @endphp
                    <span
                        style="color:{{ $color }}; font-weight:bold; font-size:12px;margin-right:2px">{{ $lok }}</span>
                @endforeach


                <table border="1"
                    style="width: 100%; table-layout: fixed; border-collapse: collapse; margin: auto;">
                    <thead>
                        <tr>
                            @foreach ($header as $item)
                                <th style="text-align: center;padding:3px">{{ $item }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach ($header as $item)
                                @php
                                    $style =
                                        $row[$item] == 'L'
                                            ? 'background-color: #E5C7C9; color: #721C24; font-weight: bold;'
                                            : '';
                                @endphp
                                <td style="text-align: center;{{ $style }}">{{ $row[$item] }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>


</body>

</html>
