@php
    $status = [
        'AKTIF',
        'PBI (APBD)',
        'PBI (APBN)',
        'AKTIF DI BU LAIN',
        'MANDIRI AKTIF',
        'MANDIRI TUNGGAKAN',
        'TIDAK DITANGGUNG',
    ];
    $kodeStatus = $details->statuskawin->kode_status ?? 'TK0';
    // Ambil angka maksimal anak dari kode status (misal K2 -> 2)
    $jumlahAnakMaks = (int) filter_var($kodeStatus, FILTER_SANITIZE_NUMBER_INT);
    $counterAnak = 0;
@endphp

@if ($details->deleted_at)
    <div class="alert alert-danger">
        <i class="ti ti-info-circle me-1"></i>
        Karyawan ini sudah tidak aktif, data BPJS tidak dapat diubah.
    </div>
@endif
<div class="row">
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped text-nowrap align-top mb-4"
            style="width: 100%;font-size: 12px">
            <thead class="table-light">
                <tr class="text-center">
                    <th>Sebagai</th>
                    <th>Nama Lengkap</th>
                    <th>Nomor KTP</th>
                    <th>BPJS TK</th>
                    <th>BPJS KES</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                {{-- KARYAWAN --}}
                <tr class="table-primary">
                    <th>Karyawan</th>
                    <td>
                        <input type="hidden" class="form-control" name="idkaryawan" id="idkaryawan"
                            value="{{ $details->id }}" readonly>
                        {{ $details->nama }}
                    </td>
                    <td>{{ $details->ktp }}</td>
                    <td class="text-center">
                        <input type="text" id="nobpjstk" name="nobpjstk"
                            value="{{ $details->bpjs->nobpjs_tk ?? null }}"
                            class="form-control text-uppercase d-inline-block w-auto"
                            @cannot('edit-bpjs') readonly @endcannot autocomplete="off"
                            onkeydown="return event.keyCode !== 32;"
                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')" maxlength="13"
                            placeholder="NO KPJ">

                        @can('tambah-bpjs')
                            <input type="text"
                                class="form-control mt-2 {{ $details->bpjs && $details->bpjs->nobpjs_tk ? 'd-none' : '' }}"
                                id="upahtk" name="upahtk" autocomplete="off" placeholder="UPAH BPJSTK"
                                value="{{ ($details->bpjs->gapok_bpjstk ?? 0) == 0 ? '' : $details->bpjs->gapok_bpjstk }}">
                        @endcan


                    </td>
                    <td class="text-center">
                        <input type="text" id="nobpjskes" name="nobpjskes"
                            value="{{ $details->bpjs->nobpjs_kes ?? null }}" class="form-control d-inline-block w-auto"
                            @cannot('edit-bpjs') readonly @endcannot autocomplete="off"
                            onkeydown="return event.keyCode !== 32;"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="13"
                            placeholder="NO JKN">

                        @can('tambah-bpjs')
                            <input type="text" class="form-control mt-2" id="upahkes" name="upahkes" autocomplete="off"
                                placeholder="UPAH JKN"
                                value="{{ ($details->bpjs->gapok_bpjskes ?? 0) == 0 ? '' : $details->bpjs->gapok_bpjskes }}">
                        @endcan

                    </td>
                    <td class="text-center">
                        <select name="status_bpjskar" id="status_bpjskar" class="form-select d-inline-block w-auto"
                            @cannot('edit-bpjs') disabled @endcannot>
                            <option value="">-pilih-</option>
                            @foreach ($status as $item)
                                <option value="{{ $item }}"
                                    {{ ($details->bpjs->status_kepesertaan ?? null) == $item ? 'selected' : '' }}>
                                    {{ $item }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>

                {{-- KELUARGA --}}
                @if ($kodeStatus !== 'TK0' && $details->keluarga->isNotEmpty())
                    @foreach ($details->keluarga as $anggota)
                        @php
                            $hubungan = strtolower($anggota->status_hubungan ?? '-');
                            $noBpjs = $anggota->no_bpjskes;
                            $isAllowed = false;
                            // LOGIKA PENENTU: Apakah baris ini boleh tampil?
                            if (str_contains($hubungan, 'istri') || str_contains($hubungan, 'suami')) {
                                $isAllowed = true;
                            } elseif (str_contains($hubungan, 'anak') && $counterAnak < $jumlahAnakMaks) {
                                $counterAnak++;
                                $isAllowed = true;
                            }
                        @endphp

                        {{-- 1. Tampilkan Pasangan --}}
                        @if ($isAllowed)
                            <tr>
                                <th>{{ strtoupper($hubungan) }}</th>
                                <td>{{ $anggota->nama_keluarga }}</td>
                                <td>{{ $anggota->ktp_keluarga ?? '-' }}</td>
                                <td>&nbsp;</td>

                                <td class="text-center">
                                    {{-- Penamaan array menggunakan ID anggota keluarga --}}
                                    <input type="text" name="keluarga[{{ $anggota->id }}][no_bpjskes]"
                                        value="{{ $anggota->no_bpjskes ?? null }}"
                                        class="form-control d-inline-block w-auto"
                                        @cannot('edit-bpjs') readonly @endcannot autocomplete="off"
                                        onkeydown="return event.keyCode !== 32;"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="13"
                                        placeholder="NO JKN">
                                </td>

                                <td class="text-center">
                                    {{-- Penamaan array untuk select --}}
                                    <select name="keluarga[{{ $anggota->id }}][status_bpjskes]"
                                        class="form-select d-inline-block w-auto"
                                        @cannot('edit-bpjs') disabled @endcannot>
                                        <option value="">-pilih-</option>
                                        @foreach ($status as $item)
                                            <option value="{{ $item }}"
                                                {{ ($anggota->status_bpjskes ?? null) == $item ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                @if ($tambahan->isNotEmpty())
                    @foreach ($tambahan as $item)
                        <tr class="table-info">
                            <th>TAMBAHAN</th>
                            <td>{{ $item->nama_keluarga }}</td>
                            <td>{{ $item->ktp_keluarga }}</td>
                            <th>{{ $item->status_hubungan }}</th>
                            <td class="text-center">{{ $item->no_bpjskes }}</td>
                            <td class="text-center">{{ $item->status_bpjskes }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
