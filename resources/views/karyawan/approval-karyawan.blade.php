@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <style>
        form .form-label {
            color: #999;
            text-transform: uppercase;
        }

        @media (max-width: 768px) {

            .table-keluarga table,
            .table-keluarga thead,
            .table-keluarga tbody,
            .table-keluarga th,
            .table-keluarga td,
            .table-keluarga tr {
                display: block;
                width: 100%;
            }

            .table-keluarga tr {
                margin-bottom: 15px;
            }

            .table-keluarga td {
                text-align: left;
                padding-left: 50%;
                position: relative;
            }

            .table-keluarga td:before {
                position: absolute;
                left: 10px;
                top: 10px;
                white-space: nowrap;
                font-weight: bold;
            }
        }
    </style>
@endpush
@php
    use Firebase\JWT\JWT;
    $program = ['JKK', 'JKM', 'JHT', 'JP'];
    $programNama = ['Jaminan Kecelakaan Kerja', 'Jaminan Kematian', 'Jaminan Hari Tua', 'Jaminan Pensiun'];
@endphp
@section('content')
    @if ($pelamar)
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper wizard-icons wizard-icons-example mt-2">
                <div class="bs-stepper-header bg-light ">
                    <div class="step" data-target="#kontrak-details-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-icon">
                                <svg viewBox="0 0 58 54">
                                    <use
                                        xlink:href="{{ asset('assets/img/svg/icons/form-wizard-personal.svg#wizardPersonal') }}">
                                    </use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label ">KONTRAK</span>
                        </button>
                    </div>
                    <div class="line">
                        <i class="ti ti-chevron-right"></i>
                    </div>
                    <div class="step" data-target="#personal-info-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-icon">
                                <svg viewBox="0 0 54 54">
                                    <use
                                        xlink:href="{{ asset('assets/img/svg/icons/form-wizard-account.svg#wizardAccount') }}">
                                    </use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label ">BIODATA</span>
                        </button>
                    </div>
                    <div class="line">
                        <i class="ti ti-chevron-right"></i>
                    </div>
                    <div class="step" data-target="#keluarga-links-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-icon">
                                <svg viewBox="0 0 58 58">
                                    <use
                                        xlink:href="{{ asset('assets/img/svg/icons/form-wizard-submit.svg#wizardSubmit') }}">
                                    </use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label">KELUARGA</span>
                        </button>
                    </div>
                    <div class="line">
                        <i class="ti ti-chevron-right"></i>
                    </div>
                    <div class="step" data-target="#pendidikan-links-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-icon">
                                <svg viewBox="0 0 54 54">
                                    <use
                                        xlink:href="{{ asset('assets/img/svg/icons/form-wizard-social-link.svg#wizardSocialLink') }}">
                                    </use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label">PENDIDIKAN</span>
                        </button>
                    </div>
                    <div class="line">
                        <i class="ti ti-chevron-right"></i>
                    </div>
                    <div class="step" data-target="#bank-links-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-icon">
                                <svg viewBox="0 0 54 54">
                                    <use
                                        xlink:href="{{ asset('assets/img/svg/icons/form-wizard-address.svg#wizardAddress') }}">
                                    </use>
                                </svg>
                            </span>
                            <span class="bs-stepper-label">BANK & PENDAPATAN</span>
                        </button>
                    </div>

                </div>

                <div class="alert alert-info align-items-center rounded-0" role="alert">
                    <span class="alert-icon me-2">
                        <i class="ti ti-user ti-xs"></i>
                    </span>
                    <span class="text-uppercase">{{ $pelamar->nama ?? '' }}</span>
                    <span class="text-warning">{{ $catatan ? ' | ' . $catatan : '' }}</span>
                </div>

                <div class="bs-stepper-content">
                    <form id="wizard-validation-form" onSubmit="return false">
                        @csrf
                        <!-- ========================================================================== KONTRAK ========================================================================= -->
                        <div id="kontrak-details-validation" class="content">
                            <div class="alert alert-danger align-items-center" role="alert" id="notifInduk"
                                style="display: none">
                                <span class="alert-icon text-danger me-2">
                                    <i class="ti ti-ban ti-xs"></i>
                                </span>
                                Oops! Kontrak induk tidak ditemukan.
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Status Kontrak</label>
                                    <select name="kontrak" id="kontrak" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                    <input type="hidden" id="idpemanggilan" name="idpemanggilan" class="form-control"
                                        value="{{ $pemanggilan }}" readonly>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Keperluan Project</label>
                                    <div class="input-group">
                                        <span class="form-control" id="lokasipekerjaan"></span>
                                        @can('approve-karyawan-in')
                                            <button class="btn btn-dark" type="button" id="changeLokasi">
                                                <i class="fas fa-street-view"></i>
                                            </button>
                                        @endcan
                                    </div>
                                    <input type="hidden" class="form-control" id="project" name="project">
                                    <input type="hidden" class="form-control" id="dtproject" name="dtproject">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tanggal Kontrak</label>
                                    <input type="text" name="tglkontrak" id="tglkontrak" class="form-control tanggal"
                                        placeholder="YYYY-MM-DD">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Jabatan</label>
                                    <select name="jabatan" id="jabatan" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Lokasi Kontrak</label>
                                    <input type="text" name="lokasikontrak" id="lokasikontrak"
                                        class="form-control text-uppercase" placeholder="Lokasi TTD" autocomplete="off">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Unitkerja</label>
                                    <select name="unitkerja" id="unitkerja" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>

                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Grade</label>
                                    <input type="text" class="form-control text-uppercase" id="grade"
                                        name="grade" autocomplete="off" maxlength="2" placeholder="A, B, etc">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Grade</label>
                                    <input type="text" class="form-control" id="tunjgrade" name="tunjgrade"
                                        autocomplete="off" placeholder="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Golongan</label>
                                    <input type="text" class="form-control text-uppercase" id="golongan"
                                        name="golongan" autocomplete="off" maxlength="1" placeholder="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Golongan</label>
                                    <input type="text" class="form-control" id="tunjgolongan" name="tunjgolongan"
                                        autocomplete="off" placeholder="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nomor PTKO</label>
                                    <select name="noptko" id="noptko" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tanggal Surat PTKO</label>
                                    <input type="text" name="tglptko" id="tglptko" class="form-control"
                                        placeholder="YYYY-MM-DD">
                                </div>
                                <div class="col-sm-12">
                                    <label class="form-label">Keterangan PTKO</label>
                                    <textarea name="ketptko" id="ketptko" class="form-control text-uppercase" placeholder="Keterangan PTKO"
                                        autocomplete="off" rows="5"></textarea>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Kontrak Dari</label>
                                    <input type="text" name="dari" id="dari" class="form-control"
                                        placeholder="YYYY-MM-DD">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Kontrak Sampai</label>
                                    <input type="text" name="sampai" id="sampai" class="form-control"
                                        placeholder="YYYY-MM-DD">
                                </div>

                                <div class="col-12 d-flex justify-content-between mt-3">
                                    <button class="btn btn-label-secondary btn-prev" disabled>
                                        <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                    </button>
                                    <button class="btn btn-primary btn-next" id="btnKontrak">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-1">Selanjutnya</span>
                                        <i class="ti ti-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- ========================================================================== BIODATA ========================================================================= -->
                        <div id="personal-info-validation" class="content">
                            <div class="divider divider-warning">
                                <div class="divider-text">
                                    <button class="btn btn-xs btn-dark lihatLampiran"
                                        data-lampiran="{{ !empty($pelamar->file_cv) ? JWT::encode(['lampiran' => $pelamar->file_cv], config('jwt.secret'), 'HS256') : '' }}">
                                        <i class="fa fa-file-pdf me-1"></i>CV
                                    </button>
                                    <button class="btn btn-xs btn-dark lihatLampiran"
                                        data-lampiran="{{ !empty($pelamar->file_kk) ? JWT::encode(['lampiran' => $pelamar->file_kk], config('jwt.secret'), 'HS256') : '' }}">
                                        <i class="fas fa-image me-1"></i>KK
                                    </button>
                                    <button class="btn btn-xs btn-dark lihatLampiran"
                                        data-lampiran="{{ !empty($pelamar->file_kk) ? JWT::encode(['lampiran' => $pelamar->file_ktp], config('jwt.secret'), 'HS256') : '' }}">
                                        <i class="fas fa-image me-1"></i>KTP
                                    </button>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Nomor KTP</label>
                                    <input type="text" id="noktp" name="noktp" class="form-control numberOnly"
                                        placeholder="16 Digits" maxlength="16" autocomplete="off"
                                        value="{{ $pelamar->noktp }}" />
                                    <input type="hidden" id="pelamarid" name="pelamarid" class="form-control"
                                        value="{{ $pelamar->id_pelamar }}" readonly>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" id="namalengkap" name="namalengkap"
                                        class="form-control text-uppercase" placeholder="Nama Lengkap" autocomplete="off"
                                        value="{{ $pelamar->nama }}" />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nomor KK</label>
                                    <input type="text" id="nokk" name="nokk" class="form-control numberOnly"
                                        placeholder="16 Digits" maxlength="16" autocomplete="off" />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="kelamin" id="kelamin" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                        <option value="L" @selected($pelamar->jenis_kelamin == 'L')>LAKI - LAKI</option>
                                        <option value="P" @selected($pelamar->jenis_kelamin == 'P')>PEREMPUAN</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" id="tmptlahir" name="tmptlahir"
                                        class="form-control text-uppercase" placeholder="Tempat Lahir" autocomplete="off"
                                        value="{{ $pelamar->tmptlahir }}" />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="text" name="tgllahir" id="tgllahir" class="form-control"
                                        placeholder="YYYY-MM-DD" value="{{ $pelamar->tgllahir }}">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Alamat Lengkap (KTP)</label>
                                    <textarea id="alamatktp" name="alamatktp" class="form-control text-uppercase"
                                        placeholder="Alamat Lengkap Sesuai KTP" rows="5">{!! $pelamar->alamat_ktp !!}</textarea>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Alamat Lengkap (DOMISILI)</label>
                                    <textarea id="alamatdomisili" name="alamatdomisili" class="form-control text-uppercase"
                                        placeholder="Alamat Lengkap Sesuai Domisili" rows="5">{{ $pelamar->alamat_domisili }}</textarea>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Provinsi (KTP)</label>
                                    <select name="provinsi" id="provinsi" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Kabupaten / Kota (KTP)</label>
                                    <select name="kabkot" id="kabkot" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Kecamatan (KTP)</label>
                                    <select name="kecamatan" id="kecamatan" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Desa / Kelurahan (KTP)</label>
                                    <select name="desa" id="desa" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Kode Pos</label>
                                    <input type="text" id="kodepos" name="kodepos" class="form-control numberOnly"
                                        placeholder="5 Digits" maxlength="5" autocomplete="off"
                                        value="{{ $pelamar->kodepos_ktp }}" />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nomor Handphone</label>
                                    <input type="text" id="hp" name="hp" class="form-control numberOnly"
                                        placeholder="08x" maxlength="11" autocomplete="off"
                                        value="{{ ltrim($pelamar->handphone, '62') }}" />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" id="email" name="email"
                                        class="form-control text-lowercase" placeholder="Alamat Email" autocomplete="off"
                                        value="{{ $pelamar->email }}" />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Agama</label>
                                    <select name="agama" id="agama" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                        <option value="ISLAM" @selected($pelamar->agama == 'ISLAM')>ISLAM</option>
                                        <option value="KRISTEN" @selected($pelamar->agama == 'KRISTEN')>KRISTEN</option>
                                        <option value="PROTESTAN" @selected($pelamar->agama == 'PROTESTAN')>PROTESTAN</option>
                                        <option value="KATOLIK" @selected($pelamar->agama == 'KATOLIK')>KATOLIK</option>
                                        <option value="HINDU" @selected($pelamar->agama == 'HINDU')>HINDU</option>
                                        <option value="BUDDHA" @selected($pelamar->agama == 'BUDDHA')>BUDDHA</option>
                                        <option value="KONGHUCU" @selected($pelamar->agama == 'KONGHUCU')>KONGHUCU</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Ukuran Baju</label>
                                    <select name="baju" id="baju" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                        <option value="S" @selected($pelamar->ukuran_baju == 'S')>S</option>
                                        <option value="M" @selected($pelamar->ukuran_baju == 'M')>M</option>
                                        <option value="L" @selected($pelamar->ukuran_baju == 'L')>L</option>
                                        <option value="XL" @selected($pelamar->ukuran_baju == 'XL')>XL</option>
                                        <option value="XXL" @selected($pelamar->ukuran_baju == 'XXL')>XXL</option>
                                        <option value="XXXL" @selected($pelamar->ukuran_baju == 'XXXL')>XXXL</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nomor Celana</label>
                                    <input type="text" name="celana" id="celana" class="form-control"
                                        maxlength="2" placeholder="2 Digits" autocomplete="off"
                                        value="{{ $pelamar->nomor_celana }}">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Tinggi Badan</label>
                                    <input type="text" name="tb" id="tb" class="form-control"
                                        maxlength="3" placeholder="Centi meter" autocomplete="off"
                                        value="{{ $pelamar->tinggi_badan }}">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Berat Badan</label>
                                    <input type="text" name="bb" id="bb" class="form-control"
                                        maxlength="3" placeholder="Kilo gram" autocomplete="off"
                                        value="{{ $pelamar->berat_badan }}">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Nomor Sepatu</label>
                                    <input type="text" name="sepatu" id="sepatu" class="form-control"
                                        maxlength="2" placeholder="2 Digits" autocomplete="off"
                                        value="{{ $pelamar->nomor_sepatu }}">
                                </div>

                                <div class="col-12 d-flex justify-content-between mt-3">
                                    <button class="btn btn-outline-secondary btn-prev">
                                        <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                    </button>
                                    <button class="btn btn-primary btn-next">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-1">Selanjutnya</span>
                                        <i class="ti ti-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- ========================================================================== KELUARGA ========================================================================= -->
                        <div id="keluarga-links-validation" class="content">
                            <div class="divider divider-warning">
                                <div class="divider-text">
                                    <button class="btn btn-xs btn-dark lihatLampiran"
                                        data-lampiran="{{ !empty($pelamar->file_kk) ? JWT::encode(['lampiran' => $pelamar->file_kk], config('jwt.secret'), 'HS256') : '' }}">
                                        <i class="fas fa-image me-1"></i>KK
                                    </button>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-12">
                                    <label class="form-label">Status Perkawinan</label>
                                    <select name="kawin" id="kawin" class="select2 form-select"
                                        data-allow-clear="true">
                                    </select>
                                </div>

                                <div class="table-responsive table-keluarga" id="tblKeluarga">
                                    <table class="table table-striped table-hover nowrap"
                                        style="width: 100%;font-size:12px">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th>Status Keluarga</th>
                                                <th>Nomor KTP</th>
                                                <th>Nama Lengkap</th>
                                                <th>Tempat Lahir</th>
                                                <th>Tanggal Lahir</th>
                                                <th>Jenis Kelamin</th>
                                            </tr>
                                        </thead>
                                        @php
                                            $pasangan =
                                                $keluarga->firstWhere('status_hubungan', 'ISTRI') ??
                                                $keluarga->firstWhere('status_hubungan', 'SUAMI');

                                            $anak = $keluarga
                                                ->where('status_hubungan', 'ANAK')
                                                ->sortBy('tgllahir_keluarga')
                                                ->values();

                                            $anak1 = $anak[0] ?? null;
                                            $anak2 = $anak[1] ?? null;
                                            $anak3 = $anak[2] ?? null;
                                        @endphp
                                        <tbody>
                                            <tr id="dtSuis">
                                                <td>
                                                    <input type="text" class="form-control" id="statussuis"
                                                        name="statussuis" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control numberOnly"
                                                        id="ktpkeluarga" name="ktpsuis" maxlength="16"
                                                        placeholder="16 Digits"
                                                        value="{{ $pasangan->ktp_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="namakeluarga" name="namasuis" placeholder="Nama Lengkap"
                                                        value="{{ $pasangan->nama_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="lahirkeluarga" name="lahirsuis" placeholder="Tempat Lahir"
                                                        value="{{ $pasangan->tmptlahir_keluarga ?? '' }}"
                                                        autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control tglkeluarga"
                                                        id="tglkeluarga" name="tglsuis" placeholder="YYYY-MM-DD"
                                                        value="{{ $pasangan->tgllahir_keluarga ?? '' }}">
                                                </td>
                                                <td>
                                                    <select name="kelaminsuis" id="kelaminsuis" class="form-select">
                                                        <option value="">-pilih-</option>
                                                        <option value="L" @selected(($pasangan->kelamin_keluarga ?? '') == 'L')>LAKI -
                                                            LAKI
                                                        </option>
                                                        <option value="P" @selected(($pasangan->kelamin_keluarga ?? '') == 'P')>PEREMPUAN
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr id="dtAnak1">
                                                <td>
                                                    <input type="text" class="form-control" id="statusanak1"
                                                        name="statusanak1" readonly value="ANAK">
                                                    <input type="hidden" id="idanak1" name="idanak1"
                                                        class="form-control" value="{{ $anak[0]->id ?? '' }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control numberOnly"
                                                        id="ktpkeluarga" name="ktpanak1" maxlength="16"
                                                        placeholder="16 Digits"
                                                        value="{{ $anak[0]->ktp_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="namakeluarga" name="namaanak1" placeholder="Nama Lengkap"
                                                        value="{{ $anak[0]->nama_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="lahirkeluarga" name="lahiranak1" placeholder="Tempat Lahir"
                                                        value="{{ $anak[0]->tmptlahir_keluarga ?? '' }}"
                                                        autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control tglkeluarga"
                                                        id="tglkeluarga" name="tglanak1" placeholder="YYYY-MM-DD"
                                                        value="{{ $anak[0]->tgllahir_keluarga ?? '' }}">
                                                </td>
                                                <td>
                                                    <select name="kelaminanak1" id="kelaminanak1" class="form-select">
                                                        <option value="">-pilih-</option>
                                                        <option value="L" @selected(($anak[0]->kelamin_keluarga ?? '') == 'L')>LAKI -
                                                            LAKI
                                                        </option>
                                                        <option value="P" @selected(($anak[0]->kelamin_keluarga ?? '') == 'P')>PEREMPUAN
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr id="dtAnak2">
                                                <td>
                                                    <input type="text" class="form-control" id="statusanak2"
                                                        name="statusanak2" readonly value="ANAK">
                                                    <input type="hidden" id="idanak2" name="idanak2"
                                                        class="form-control" value="{{ $anak[1]->id ?? '' }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control numberOnly"
                                                        id="ktpkeluarga" name="ktpanak2" maxlength="16"
                                                        placeholder="16 Digits"
                                                        value="{{ $anak[1]->ktp_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="namakeluarga" name="namaanak2" placeholder="Nama Lengkap"
                                                        value="{{ $anak[1]->nama_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="lahirkeluarga" name="lahiranak2" placeholder="Tempat Lahir"
                                                        value="{{ $anak[1]->tmptlahir_keluarga ?? '' }}"
                                                        autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control tglkeluarga"
                                                        id="tglkeluarga" name="tglanak2" placeholder="YYYY-MM-DD"
                                                        value="{{ $anak[1]->tgllahir_keluarga ?? '' }}">
                                                </td>
                                                <td>
                                                    <select name="kelaminanak2" id="kelaminanak2" class="form-select">
                                                        <option value="">-pilih-</option>
                                                        <option value="L" @selected(($anak[1]->kelamin_keluarga ?? '') == 'L')>LAKI -
                                                            LAKI
                                                        </option>
                                                        <option value="P" @selected(($anak[1]->kelamin_keluarga ?? '') == 'P')>PEREMPUAN
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr id="dtAnak3">
                                                <td>
                                                    <input type="text" class="form-control" id="statusanak3"
                                                        name="statusanak3" readonly value="ANAK">
                                                    <input type="hidden" id="idanak3" name="idanak3"
                                                        class="form-control" value="{{ $anak[2]->id ?? '' }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control numberOnly"
                                                        id="ktpkeluarga" name="ktpanak3" maxlength="16"
                                                        placeholder="16 Digits"
                                                        value="{{ $anak[2]->ktp_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="namakeluarga" name="namaanak3" placeholder="Nama Lengkap"
                                                        value="{{ $anak[2]->nama_keluarga ?? '' }}" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-uppercase"
                                                        id="lahirkeluarga" name="lahiranak3" placeholder="Tempat Lahir"
                                                        value="{{ $anak[2]->tmptlahir_keluarga ?? '' }}"
                                                        autocomplete="off">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control tglkeluarga"
                                                        id="tglkeluarga" name="tglanak3" placeholder="YYYY-MM-DD"
                                                        value="{{ $anak[2]->tgllahir_keluarga ?? '' }}">
                                                </td>
                                                <td>
                                                    <select name="kelaminanak3" id="kelaminanak3" class="form-select">
                                                        <option value="">-pilih-</option>
                                                        <option value="L" @selected(($anak[2]->kelamin_keluarga ?? '') == 'L')>LAKI -
                                                            LAKI
                                                        </option>
                                                        <option value="P" @selected(($anak[2]->kelamin_keluarga ?? '') == 'P')>PEREMPUAN
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <div class="col-12 d-flex justify-content-between mt-3">
                                    <button class="btn btn-outline-secondary btn-prev">
                                        <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                    </button>
                                    <button class="btn btn-primary btn-next">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-1">Selanjutnya</span>
                                        <i class="ti ti-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- ========================================================================== PENDIDIKAN ========================================================================= -->
                        <div id="pendidikan-links-validation" class="content">
                            <div class="divider divider-warning">
                                <div class="divider-text">
                                    <button class="btn btn-xs btn-dark lihatLampiran"
                                        data-lampiran="{{ !empty($pelamar->file_ijazah) ? JWT::encode(['lampiran' => $pelamar->file_ijazah], config('jwt.secret'), 'HS256') : '' }}">
                                        <i class="fas fa-image me-1"></i>IJAZAH
                                    </button>
                                </div>
                            </div>
                            @php
                                $dtPendidikan = ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];
                            @endphp
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Pendidikan Terakhir</label>
                                    <select name="pendidikan" id="pendidikan" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                        @foreach ($dtPendidikan as $item)
                                            <option value="{{ $item }}" @selected($item == $pelamar->jenjang_pendidikan)>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Nama Sekolah / Universitas</label>
                                    <input type="text" id="namasekolah" name="namasekolah"
                                        class="form-control text-uppercase" placeholder="Nama Sekolah"
                                        value="{{ $pelamar->nama_sekolah ?? '' }}">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Jurusan</label>
                                    <input type="text" id="jurusansekolah" name="jurusansekolah"
                                        class="form-control text-uppercase" placeholder="Jurusan Sekolah"
                                        value="{{ $pelamar->jurusan_sekolah ?? '' }}">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Tahun Lulus</label>
                                    <input type="text" id="tahunlulus" name="tahunlulus"
                                        class="form-control numberOnly" placeholder="Tahun Lulus" maxlength="4"
                                        value="{{ $pelamar->tahun_lulus ?? '' }}" autocomplete="off">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Nilai</label>
                                    <input type="text" id="nilaisekolah" name="nilaisekolah"
                                        class="form-control number" placeholder="0" maxlength="4"
                                        value="{{ $pelamar->nilai_ijazah ?? '' }}">
                                </div>

                                <div class="col-12 d-flex justify-content-between mt-3">
                                    <button class="btn btn-outline-secondary btn-prev">
                                        <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                    </button>
                                    <button class="btn btn-primary btn-next">
                                        <span class="align-middle d-sm-inline-block d-none me-sm-1">Selanjutnya</span>
                                        <i class="ti ti-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- ========================================================================== BANK ========================================================================= -->
                        <div id="bank-links-validation" class="content">
                            <div class="divider {{ $pelamar->file_bank ? 'divider-warning' : 'divider-danger' }} ">
                                <div class="divider-text">
                                    @if ($pelamar->file_bank)
                                        <button class="btn btn-xs btn-dark lihatLampiran"
                                            data-lampiran="{{ !empty($pelamar->file_bank) ? JWT::encode(['lampiran' => $pelamar->file_bank], config('jwt.secret'), 'HS256') : '' }}">
                                            <i class="fas fa-image me-1"></i>BUTAB
                                        </button>
                                    @else
                                        <span class="text-danger">Oops! Buku Tabungan belum diupload.</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <label class="form-label">Nama Bank</label>
                                    <select name="bankid" id="bankid" class="select2 form-select"
                                        data-allow-clear="true">
                                        <option value="">-pilih-</option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Nama Direkening</label>
                                    <input type="text" id="namabank" name="namabank"
                                        class="form-control text-uppercase" placeholder="Nama Direkening"
                                        autocomplete="off">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Nomor Rekening</label>
                                    <input type="text" id="norek" name="norek" class="form-control"
                                        placeholder="Nomor Rekening" autocomplete="off">
                                </div>
                                <div class="col-md mb-md-0 mb-5">
                                    <div class="form-check custom-option custom-option-basic form-check-warning">
                                        <label class="form-check-label custom-option-content">
                                            <input class="form-check-input" name="percepatan" type="checkbox"
                                                value="1" id="percepatan" />
                                            <span class="custom-option-header">
                                                <span class="h6 mb-0 text-warning text-uppercase">Kosongkan Data
                                                    BANK</span>
                                            </span>
                                            <span class="custom-option-body">
                                                <small class="option-text">
                                                    Silahkan centang <strong>
                                                        <i class="fas fa-check-square me-1 text-warning"></i></strong>
                                                    apabila belum
                                                    mendapatkan <strong>DATA BANK</strong> dari pelamar atau Project
                                                    Leader.<br>
                                                    Segala bentuk permasalahan yang ditimbulkan akibat pengosongan data bank
                                                    terutama pada saat penggajian
                                                    merupakan tanggung jawab<strong> Project Leader</strong> dan yang
                                                    melakukan approval pada saat pengaktifan karyawan.
                                                </small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <br>
                                <div class="divider divider-primary">
                                    <div class="divider-text fw-bold">PENDAPATAN</div>
                                </div>
                                <div class="alert alert-danger align-items-center" role="alert" id="notifSetting"
                                    style="display: none">
                                    <span class="alert-icon text-danger me-2">
                                        <i class="ti ti-ban ti-xs"></i>
                                    </span>
                                    Oops! Setting gaji tidak ditemukan.
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Gaji Pokok</label>
                                    <input type="text" name="gapok" id="gapok" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Jabatan</label>
                                    <input type="text" name="tunjab" id="tunjab" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Tetap</label>
                                    <input type="text" name="tunjtetap" id="tunjtetap" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Operasional</label>
                                    <input type="text" name="tunjoprs" id="tunjoprs" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Komunikasi</label>
                                    <input type="text" name="tunjkom" id="tunjkom" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Masakerja</label>
                                    <input type="text" name="tunjmasakerja" id="tunjmasakerja" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Tunjangan Keterampilan</label>
                                    <input type="text" name="tunjketerampilan" id="tunjketerampilan"
                                        class="form-control" autocomplete="off" placeholder="0" maxlength="11"
                                        value="0">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Tunjangan Transport</label>
                                    <input type="text" name="tunjtransport" id="tunjtransport" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Tunjangan Makan</label>
                                    <input type="text" name="tunjmakan" id="tunjmakan" class="form-control"
                                        autocomplete="off" placeholder="0" maxlength="11" value="0">
                                </div>
                                <div class="divider divider-primary">
                                    <div class="divider-text fw-bold text-uppercase">Tunjangan Lainnya</div>
                                </div>
                                <div class="form-repeater">
                                    <div data-repeater-list="group-tunjangan">
                                        <div data-repeater-item>
                                            <div class="row">
                                                <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                                    <label class="form-label">Nama
                                                        Tunjangan</label>
                                                    <input type="text" name="nmtunjangan" id="nmtunjangan"
                                                        class="form-control text-uppercase tunjangan-nama"
                                                        autocomplete="off" placeholder="Nama Tunjangan">
                                                </div>
                                                <div class="mb-3 col-lg-6 col-xl-4 col-12 mb-0">
                                                    <label class="form-label" for="form-repeater-1-2">Nilai
                                                        Tunjangan</label>
                                                    <input type="text" name="nilaitunjangan" id="nilaitunjangan"
                                                        class="form-control tunjangan-nilai input-rupiah"
                                                        autocomplete="off" placeholder="0" maxlength="11">
                                                </div>
                                                <div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
                                                    <button class="btn btn-label-danger mt-4" data-repeater-delete>
                                                        <i class="ti ti-x ti-xs me-1"></i>
                                                        <span class="align-middle">Hapus Tunjangan</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <button class="btn btn-sm btn-primary" data-repeater-create>
                                            <i class="ti ti-plus me-1"></i>
                                            <span class="align-middle">Tambah Tunjangan</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="divider divider-primary">
                                    <div class="divider-text fw-bold">PROGRAM BPJS</div>
                                </div>
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
                                                    id="{{ $columnName }}" name="{{ $columnName }}" checked>
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
                                            <input class="form-check-input me-2" type="checkbox" value="1"
                                                id="jkn" name="jkn" checked>
                                            Jaminan Kesehatan Nasional<span class="text-warning fw-bold ms-1">(JKN)</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-between mt-3">
                                    <button class="btn btn-outline-secondary btn-prev">
                                        <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                    </button>
                                    <button type="submit" class="btn btn-success btn-next btn-submit">
                                        <i class="ti ti-cloud-up me-1"></i> Simpan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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
                    <button type="button" class="btn btn-primary changeProject">Pilih</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/karyawan/approval-karyawan-baru.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
    <script src="{{ asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
@endpush
