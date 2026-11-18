@php
    $maxLokasi = 1;
    foreach ($data as $row) {
        if (isset($row['lokasi_penempatan']) && is_array($row['lokasi_penempatan'])) {
            $currentCount = count($row['lokasi_penempatan']);
            if ($currentCount > $maxLokasi) {
                $maxLokasi = $currentCount;
            }
        }
    }
@endphp
<table>
    <thead>
        <tr>
            <th style="text-align:center;font-weight: bold;border:1px solid black">NO</th>
            <th style="text-align:center;font-weight: bold;border:1px solid black">NIP</th>
            <th style="text-align:center;font-weight: bold;border:1px solid black">NAMA LENGKAP</th>
            <th style="text-align:center;font-weight: bold;border:1px solid black" colspan="{{ $maxLokasi }}">
                LOKASI PEKERJAAN
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 0;
        @endphp
        @foreach ($data as $row)
            @php
                $no++;
                $lokasi = $row['lokasi_penempatan'];
                $countLokasiUser = count($lokasi);
            @endphp
            <tr>
                <td style="text-align:center;border:1px solid black">{{ $no }}</td>
                <td style="text-align:center;border:1px solid black;" data-type="s">{{ $row['nip'] }}</td>
                <td style="border:1px solid black">{{ $row['nama'] }}</td>
                @foreach ($lokasi as $val)
                    <td style="border:1px solid black; vertical-align: middle;">
                        {{ $val }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
