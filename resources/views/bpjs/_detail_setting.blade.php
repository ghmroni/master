@php
    $program = ['JKK', 'JKM', 'JHT', 'JP'];
    $programNama = ['Jaminan Kecelakaan Kerja', 'Jaminan Kematian', 'Jaminan Hari Tua', 'Jaminan Pensiun'];
@endphp
<div class="row">
    <div class="col-md-12 mb-2">
        <label class="form-label">Lokasi Penempatan</label>
        <div class="input-group">
            <span class="form-control" id="lokasipekerjaan"></span>
            <div id="btnLokasi" style="display: none">
                <button class="btn btn-dark" type="button" id="changeLokasi">
                    <i class="fas fa-street-view"></i>
                </button>
            </div>
        </div>
        <input type="hidden" class="form-control" id="idkaryawan" name="idkaryawan"
            value="{{ $karyawan->id ?? null }}">
        <input type="hidden" class="form-control" id="project" name="project"
            value="{{ $karyawan->project ?? null }}">
        <input type="hidden" class="form-control" id="dtproject" name="dtproject"
            value="{{ $karyawan->project_detail ?? null }}">
    </div>

    <div class="col-md-6 mb-0">
        <label class="form-label">Gapok Tenagakerja</label>
        <input type="text" id="upahtk" name="upahtk" class="form-control" placeholder="0"
            @cannot('setting-bpjs') readonly @endcannot autocomplete="off" maxlength="13"
            value="{{ $bpjs->gapok_bpjstk ?? 0 }}">
    </div>
    <div class="col-md-6 mb-0">
        <label class="form-label">Gapok Kesehatan</label>
        <input type="text" id="upahkes" name="upahkes" class="form-control" placeholder="0"
            @cannot('setting-bpjs') readonly @endcannot autocomplete="off" maxlength="13"
            value="{{ $bpjs->gapok_bpjskes ?? 0 }}">
    </div>

    <div class="col-md-6 mb-3">
        <div class="divider divider-info">
            <div class="divider-text text-info">Program Tenagakerja</div>
        </div>
        <div class="form-check form-check-info">
            @foreach ($program as $key => $value)
                @php
                    // Ubah JKK menjadi jkk agar sesuai dengan kolom database
                    $columnName = strtolower($value);
                @endphp
                <label class="d-flex form-check-label mt-2">
                    <input class="form-check-input me-2" type="checkbox" value="1" id="{{ $columnName }}"
                        name="{{ $columnName }}" {{ ($bpjs->$columnName ?? 1) == 1 ? 'checked' : '' }}>
                    {!! $programNama[$key] . ' <span class="text-warning fw-bold ms-1">(' . $value . ')</span>' !!}
                </label>
            @endforeach
        </div>
    </div>
    <div class="col-md-6">
        <div class="divider divider-success">
            <div class="divider-text text-success">Program Kesehatan</div>
        </div>
        <div class="form-check form-check-success">
            <label class="d-flex form-check-label mt-2">
                <input class="form-check-input me-2" type="checkbox" value="1" id="jkn" name="jkn"
                    {{ ($bpjs->jkn ?? 1) == 1 ? 'checked' : '' }}>
                Jaminan Kesehatan Nasional<span class="text-warning fw-bold ms-1">(JKN)</span>
            </label>
        </div>
    </div>
    <div class="col-md-12 mt-2 persetujuan">
        <div class="form-check custom-option custom-option-basic form-check-warning">
            <label class="form-check-label custom-option-content">
                <input class="form-check-input" name="checkAll" type="checkbox" value="1" id="checkAll">
                <span class="custom-option-header">
                    <span class="h6 mb-0 text-warning">Perbaharui Semua</span>
                </span>
                <span class="custom-option-body">
                    <small class="option-text">
                        Semua karyawan yang berada di lokasi penempatan ini akan diperbaharui semua datanya sesuai yang
                        diinput.
                    </small>
                </span>
            </label>
        </div>
    </div>
</div>
