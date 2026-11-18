<table>
    <thead>
        <tr>
            <th rowspan="2"
                style="background: yellow;vertical-align: center;text-align:center;font-weight: bold;border:1px solid black">
                NIP</th>
            <th rowspan="2"
                style="background: yellow;vertical-align: center;text-align:center;font-weight: bold;border:1px solid black">
                NAMA LENGKAP</th>
            <th colspan="31"
                style="background: yellow;vertical-align: center;text-align:center;font-weight: bold;border:1px solid black">
                TANGGAL SCHEDULE</th>
        </tr>
        <tr>
            @for ($i = 1; $i <= 31; $i++)
                <th style="background: yellow;text-align:center;font-weight: bold;border:1px solid black">
                    {{ $i }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @if ($data)
            @foreach ($data as $item)
                <tr>
                    <td style="text-align: center" data-type="s">{{ $item['nip'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
