@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush

@php
    $program = ['JKK', 'JKM', 'JHT', 'JP'];
    $programNama = ['Jaminan Kecelakaan Kerja', 'Jaminan Kematian', 'Jaminan Hari Tua', 'Jaminan Pensiun'];
@endphp

@section('content')
    <div class="card card-body">
        <form id="formSetting">
            @csrf
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label for="form-label">Nomor Kontrak Induk</label>
                    <select type="text" id="kontrakinduk_id" name="kontrakinduk_id" class="select2 form-select">
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi Project</label>
                    <select type="text" id="settingid" name="settingid" class="select2 form-select">
                    </select>
                    <input type="hidden" class="form-control" id="idsetting" name="idsetting"
                        value="{{ $details->id ?? null }}" readonly>
                    <input type="hidden" class="form-control" id="project" name="project" readonly
                        value="{{ $details->project_id ?? null }}">
                    <input type="hidden" class="form-control" id="dtproject" name="dtproject" readonly
                        value="{{ $details->project_detail ?? null }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Gaji Pokok</label>
                    <input type="text" id="gapok" name="gapok" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->gapok ?? 0 }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Uang Makan</label>
                    <input type="text" id="makan" name="makan" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->uang_makan ?? 0 }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Uang Transport</label>
                    <input type="text" id="transport" name="transport" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->uang_transport ?? 0 }}">
                </div>
                <div class="col-md-2">
                    <label for="form-label">Cut Off Dari</label>
                    <select type="text" id="dari" name="dari" class="select2 form-select">
                        <option value="">-pilih-</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ $i == $cutoff[0] ? 'selected' : '' }}>
                                {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="form-label">Cut Off Sampai</label>
                    <select type="text" id="sampai" name="sampai" class="select2 form-select">
                        <option value="">-pilih-</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ $i == $cutoff[1] ? 'selected' : '' }}>
                                {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="form-label">Cut Off Bulan</label>
                    <select type="text" id="bulan" name="bulan" class="select2 form-select">
                        <option value="">-pilih-</option>
                        <option value="S" @selected(($cutoff[2] ?? '') == 'S')>BULAN SAMA</option>
                        <option value="B" @selected(($cutoff[2] ?? '') == 'B')>SEBELUMNYA</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="form-label">Hari Kerja <span class="text-muted">(hari)</span></label>
                    <input type="text" id="harikerja" name="harikerja" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->hari_kerja ?? 0 }}">
                </div>
                <div class="col-md-3">
                    <label for="form-label">Natura</label>
                    <input type="text" id="natura" name="natura" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->natura ?? 0 }}">
                </div>
                <div class="divider divider-warning">
                    <div class="divider-text">Potongan Kehadiran</div>
                </div>
                <div class="col-md mb-md-0 mb-2">
                    <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content">
                            <span class="custom-option-body">
                                <i class="ti ti-rocket"></i>
                                <span class="custom-option-title">STANDAR</span>
                                <small>Gaji Pokok / hari kerja</small>
                            </span>
                            <input name="typepotongan" class="form-check-input" type="radio" value="STANDAR"
                                id="typepotongan1" {{ ($details->type_pothadir ?? '') == 'STANDAR' ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>
                <div class="col-md mb-md-0 mb-2">
                    <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content">
                            <span class="custom-option-body">
                                <i class="ti ti-star"></i>
                                <span class="custom-option-title"> FLAT </span>
                                <small>Tetap setiap bulannya</small>
                            </span>
                            <input name="typepotongan" class="form-check-input" type="radio" value="FLAT"
                                id="typepotongan2" {{ ($details->type_pothadir ?? '') == 'FLAT' ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content">
                            <span class="custom-option-body">
                                <i class="ti ti-briefcase"></i>
                                <span class="custom-option-title"> CUSTOM </span>
                                <small>Perhitungan sesuai kesepakatan</small>
                            </span>
                            <input name="typepotongan" class="form-check-input" type="radio" value="CUSTOM"
                                id="typepotongan3" {{ ($details->type_pothadir ?? '') == 'CUSTOM' ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>
            </div>
            <div id="potongan" class="row mb-3">
                <div class="col-md-8">
                    <label for="form-label">Perhitungan Potongan Kehadiran</label>
                    <input type="text" id="perhitungan" name="perhitungan" class="form-control" placeholder="0">
                    <div id="petunjuk" class="form-text text-dark" style="display: none">
                        Operators : <span class="ms-3 me-3" style="letter-spacing: 5px">() + - * / </span> (kemudian tekan
                        enter
                        atau spasi)
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="form-label">Nilai Potongan</label>
                    <input type="text" id="nilaipotongan" name="nilaipotongan" class="form-control" placeholder="0"
                        readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="divider divider-info">
                        <div class="divider-text text-info">Program Tenagakerja</div>
                    </div>
                    <div class="form-check form-check-info">
                        @foreach ($program as $key => $value)
                            @php
                                $columnName = strtolower($value);
                            @endphp
                            <label class="d-flex form-check-label mt-2">
                                <input class="form-check-input me-2" type="checkbox" value="1"
                                    id="{{ $columnName }}" name="{{ $columnName }}"
                                    {{ ($details->$columnName ?? 1) == 1 ? 'checked' : '' }}>
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
                            <input class="form-check-input me-2" type="checkbox" value="1" id="jkn"
                                name="jkn" {{ ($details->jkn ?? 1) == 1 ? 'checked' : '' }}>
                            Jaminan Kesehatan Nasional<span class="text-warning fw-bold ms-1">(JKN)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" type="submit"><i class="ti ti-cloud-up me-1"></i>Simpan</button>
            </div>
        </form>
    </div>

    {{-- <div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Pilih Lokasi Penempatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" id="cari" class="form-control"
                                            placeholder="Search..." aria-label="Search...">
                                    </div>
                                </div>

                                <div id="jstree-project" class="dataProject"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-warning changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/admin/settinggaji-detail.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
