@php
    $sebagai = ['AYAH KANDUNG', 'IBU KANDUNG', 'AYAH MERTUA', 'IBU MERTUA', 'LAINNYA'];
    $status = ['AKTIF', 'TIDAK DITANGGUNG'];

@endphp

<div class="col-12">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Hubungan</label>
            <select name="hubungan" id="hubungan" class="form-select" @cannot('tambah-bpjs') disabled @endcannot>
                <option value="">-pilih-</option>
                @foreach ($sebagai as $item)
                    <option value="{{ $item }}">
                        {{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nama Lengkap</label>
            <input type="hidden" id="idkaryawan" name="idkaryawan" value="{{ $karyawan->id }}" readonly>
            <input type="hidden" id="idkeluarga" name="idkeluarga" readonly>
            <input type="text" id="namalengkap" name="namalengkap" class="form-control text-uppercase"
                placeholder="Nama Lengkap" @cannot('tambah-bpjs') readonly @endcannot autocomplete="off"
                oninput="this.value = this.value.replace(/[0-9]/g, '')" maxlength="50">
        </div>
        <div class="col-md-4">
            <label class="form-label">Nomor KTP</label>
            <input type="text" id="noktp" name="noktp" class="form-control" placeholder="0"
                @cannot('tambah-bpjs') readonly @endcannot autocomplete="off" onkeydown="return event.keyCode !== 32;"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="16">
        </div>
        <div class="col-md-4">
            <label class="form-label">Nomor BPJS</label>
            <input type="text" id="nobpjskes" name="nobpjskes" class="form-control" placeholder="0"
                @cannot('tambah-bpjs') readonly @endcannot autocomplete="off" onkeydown="return event.keyCode !== 32;"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="13">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="statusbpjs" id="statusbpjs" class="form-select" @cannot('tambah-bpjs') disabled @endcannot>
                <option value="">-pilih-</option>
                @foreach ($status as $item)
                    <option value="{{ $item }}">
                        {{ $item }}</option>
                @endforeach
            </select>
        </div>

    </div>


</div>
