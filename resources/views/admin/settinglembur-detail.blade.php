@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
@endpush

@section('content')
    <div class="card card-body">
        <form id="formLembur">
            @csrf
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lokasi Project</label>
                    <div class="input-group">
                        <span class="form-control" id="lokasipekerjaan"></span>
                        <div id="btnLokasi">
                            <button class="btn btn-dark" type="button" id="changeLokasi">
                                <i class="fas fa-street-view"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="idsetting" name="idsetting"
                        value="{{ $details->id ?? null }}" readonly>
                    <input type="hidden" class="form-control" id="project" name="project" readonly>
                    <input type="hidden" class="form-control" id="dtproject" name="dtproject" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Uang Makan Lembur</label>
                    <input type="text" id="um" name="um" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->um ?? 0 }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Uang Transport Lembur</label>
                    <input type="text" id="ut" name="ut" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->ut ?? 0 }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Uang Makan Lembur <small class="text-danger">Hari Libur</small></label>
                    <input type="text" id="uml" name="uml" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->uml ?? 0 }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="form-label">Uang Transport Lembur <small class="text-danger">Hari Libur</small></label>
                    <input type="text" id="utl" name="utl" class="form-control" placeholder="0"
                        autocomplete="off" value="{{ $details->utl ?? 0 }}">
                </div>
                <div class="col-md-2">
                    <label for="form-label">Cut Off Dari</label>
                    <select type="text" id="dari" name="dari" class="select2 form-select">
                        <option value="">-pilih-</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ $i == $cutoff[0] ? 'selected' : '' }}>{{ $i }}
                            </option>
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
                <div class="col-md-6 mb-3">
                    <label for="form-label">Hari Kerja <small class="text-muted">(Dalam 1 minggu)</small></label>
                    <input type="text" id="harikerja" name="harikerja" class="form-control" placeholder="0"
                        autocomplete="off" maxlength="1" value="{{ $details->hari_kerja ?? 0 }}">
                </div>
                <div class="divider divider-warning">
                    <div class="divider-text">Type Lembur</div>
                </div>
                <div class="col-md mb-md-0 mb-2">
                    <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content">
                            <span class="custom-option-body">
                                <i class="ti ti-star"></i>
                                <span class="custom-option-title">NORMATIVE</span>
                                <small class="text-muted">Sesuai ketentuan DISNAKER</small>
                            </span>
                            <input name="typelembur" class="form-check-input" type="radio" value="NORMATIVE"
                                id="typelembur1">
                        </label>
                    </div>
                </div>
                <div class="col-md mb-md-0 mb-2">
                    <div class="form-check custom-option custom-option-icon">
                        <label class="form-check-label custom-option-content">
                            <span class="custom-option-body">
                                <i class="ti ti-briefcase"></i>
                                <span class="custom-option-title"> FLAT / PAKET </span>
                                <small class="text-muted">Tetap setiap bulannya</small>
                            </span>
                            <input name="typelembur" class="form-check-input" type="radio" value="FLAT"
                                id="typelembur2" />
                        </label>
                    </div>
                </div>
            </div>
            <div id="flat" style="display: none">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="form-label">Nilai Lembur <small class="text-muted">Hari Biasa</small></label>
                        <input type="text" class="form-control" id="nilaibiasa" name="nilaibiasa"
                            value="{{ $details->nilai_biasa ?? null }}">
                    </div>
                    <div class="col-md-6">
                        <label for="form-label">Nilai Lembur <small class="text-danger">Hari Libur</small></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nilailibur" name="nilailibur"
                                value="{{ $details->nilai_libur ?? null }}">
                            <div class="input-group-text">
                                <label class="form-label me-2">
                                    <input class="form-check-input mt-0 me-1" type="radio" name="typenilai"
                                        value="JAM"> / Jam
                                </label>
                                <label class="form-label">
                                    <input class="form-check-input mt-0 me-1" type="radio" name="typenilai"
                                        value="HARI"> / Hari
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="normative" style="display: none">
                <div class="row">
                    <div class="alert alert-info mb-0" role="alert">PERHITUNGAN HARI KERJA
                        <span class="form-text text-primary ms-2">
                            (Input <strong>0</strong> untuk nilai selanjutnya)
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="form-label">Jam Ke 1</label>
                        <div class="input-group">
                            <input type="text" id="b1" name="b1" class="form-control" placeholder="0"
                                autocomplete="off" value="{{ floatval($details->b1 ?? 1) }}">
                            <span class="input-group-text">Jam</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="form-label">Perkalian Jam Ke 1</label>
                        <input type="text" id="nb1" name="nb1" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nb1 ?? 1.5) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="form-label">Jam Ke 2</label>
                        <div class="input-group">
                            <input type="text" id="b2" name="b2" class="form-control" placeholder="0"
                                autocomplete="off" value="{{ floatval($details->b2 ?? 0) }}">
                            <span class="input-group-text">Jam</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="form-label">Perkalian Jam Ke 2</label>
                        <input type="text" id="nb2" name="nb2" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nb2 ?? 2) }}">
                    </div>

                    {{-- Hari Libur --}}
                    <div class="alert alert-danger mb-0" role="alert">PERHITUNGAN HARI LIBUR
                        <span class="form-text text-primary ms-2">
                            (Input <strong>0</strong> untuk nilai selanjutnya)
                        </span>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="form-label">Jam Ke 2</label>
                        <div class="input-group">
                            <input type="text" id="l2" name="l2" class="form-control" placeholder="0"
                                autocomplete="off" value="{{ floatval($details->l2 ?? 7) }}">
                            <span class="input-group-text">Jam</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Perkalian Jam Ke 2</label>
                        <input type="text" id="nl2" name="nl2" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nl2 ?? 2) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Perkalian Jam Ke 2 <small class="text-danger">Libur
                                Nasional</small></label>
                        <input type="text" id="nln2" name="nln2" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nln2 ?? 2.5) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Jam Ke 3</label>
                        <div class="input-group">
                            <input type="text" id="l3" name="l3" class="form-control" placeholder="0"
                                autocomplete="off" value="{{ floatval($details->l3 ?? 1) }}">
                            <span class="input-group-text">Jam</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Perkalian Jam Ke 3</label>
                        <input type="text" id="nl3" name="nl3" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nl3 ?? 3) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Perkalian Jam Ke 3 <small class="text-danger">Libur
                                Nasional</small></label>
                        <input type="text" id="nln3" name="nln3" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nln3 ?? 3) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Jam Ke 4</label>
                        <div class="input-group">
                            <input type="text" id="l4" name="l4" class="form-control" placeholder="0"
                                autocomplete="off" value="{{ floatval($details->l4 ?? 0) }}">
                            <span class="input-group-text">Jam</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Perkalian Jam Ke 4</label>
                        <input type="text" id="nl4" name="nl4" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nl4 ?? 4) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="form-label">Perkalian Jam Ke 4 <small class="text-danger">Libur
                                Nasional</small></label>
                        <input type="text" id="nln4" name="nln4" class="form-control" placeholder="0"
                            autocomplete="off" value="{{ floatval($details->nln4 ?? 4) }}">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" type="submit"><i class="ti ti-cloud-up me-1"></i>Simpan</button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="modalLokasi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
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
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/admin/settinglembur-detail.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
