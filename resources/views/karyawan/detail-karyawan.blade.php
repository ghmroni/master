@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/pages/page-profile.css" />
    <link rel="stylesheet"
        href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <style>
        form .form-label {
            color: #999;
            text-transform: uppercase;
        }
    </style>
@endpush

@php
    use Carbon\Carbon;
    use Firebase\JWT\JWT;
@endphp
@section('content')
    <!-- Header -->
    <form id="formKaryawan">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card  border mb-4">
                    <div class="user-profile-header-banner">
                        <img src="{{ asset('/assets/img/banner/profile-banner.png') }}" alt="Banner image"
                            class="rounded-top" />
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <img src="{{ $karyawan->photo == null ? '/assets/img/avatars/no-image.png' : '/storage/hris/karyawan/photo/' . $karyawan->photo }}"
                                alt="Photo" class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                        </div>
                        <div class="flex-grow-1 mt-3 mt-sm-5">
                            <div
                                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4>{{ $karyawan->nama }}</h4>
                                    <ul
                                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                        <li class="list-inline-item d-flex gap-1 text-warning">
                                            <i class="ti ti-calendar"></i> Joined
                                            {{ Carbon::parse($karyawan->tanggal_masuk)->translatedFormat('d F Y') }}
                                            @if (empty($karyawan->deleted_at))
                                            <span class="badge rounded-pill bg-label-success">Aktif</span>
                                        @else
                                            <span class="badge rounded-pill bg-label-danger">Tidak Aktif</span>
                                        @endif
                                        </li>
                                    </ul>
                                </div>
                                @if (empty($karyawan->deleted_at))
                                    @can('edit-karyawan')
                                        <button type="submit" class="btn btn-primary mt-3"> <i class="ti ti-cloud-up me-2"></i>Simpan</button>
                                    @endcan
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header -->
        <div class="row">
            <!-- ====================================================================================================================== TAB HEADER -->
            <div class="col-md-12">
                <ul class="nav nav-pills flex-column flex-sm-row mb-4">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#dtkaryawan" aria-controls="dtkaryawan" aria-selected="true">
                            <i class="tf-icons ti ti-user me-1"></i> Data Karyawan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#dtkeluarga" aria-controls="dtkeluarga" aria-selected="false">
                            <i class="tf-icons ti ti-heart me-1"></i> Data Keluarga
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#dtpendidikan" aria-controls="dtpendidikan" aria-selected="false">
                            <i class="tf-icons ti ti-medal me-1"></i> Data Pendidikan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#dtbank"
                            aria-controls="dtbank" aria-selected="false">
                            <i class="tf-icons ti ti-wallet me-1"></i> Data Bank
                        </button>
                    </li>
                    @can('edit-karyawan')
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#dtkontrak" aria-controls="dtkontrak" aria-selected="false">
                            <i class="tf-icons ti ti-receipt me-1"></i> Data Kontrak
                        </button>
                    </li>
                    @endcan
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#dtlampiran" aria-controls="dtlampiran" aria-selected="false">
                            <i class="tf-icons ti ti-cloud me-1"></i> Lampiran
                        </button>
                    </li>
                </ul>
                <!-- ====================================================================================================================== END TAB HEADER -->

                <!-- CONTENT -->
                <div class="tab-content border card" id="card-block">
                    <!-- ====================================================================================================================== TAB KARYAWAN -->
                    <div class="tab-pane fade show active" id="dtkaryawan" role="tabpanel">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input type="hidden" class="form-control" id="id" name="id" readonly
                                    value="{{ request('token') }}">
                                <input type="text" class="form-control" id="nip"
                                    placeholder="Nomor Induk Pegawai" disabled value="{{ $karyawan->nip }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi Pekerjaan</label>
                                <div class="input-group">
                                    <span class="form-control" id="lokasipekerjaan"></span>

                                </div>
                                <input type="hidden" class="form-control" id="project" name="project"
                                    value="{{ $karyawan->project }}">
                                <input type="hidden" class="form-control" id="dtproject" name="dtproject"
                                    value="{{ $karyawan->project_detail }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Kartu Keluarga</label>
                                <input type="text" class="form-control numberOnly" id="kk" name="kk"
                                    placeholder="Nomor KK" value="{{ $karyawan->kk }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    placeholder="Nama Lengkap" value="{{ $karyawan->nama }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor KTP</label>
                                <input type="text" class="form-control numberOnly" id="ktp" name="ktp"
                                    placeholder="Nomor KTP" value="{{ $karyawan->ktp }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempatlahir" name="tempat_lahir"
                                    placeholder="Tempat Lahir" value="{{ $karyawan->tempat_lahir }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <select name="jabatan" id="jabatan" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="text" class="form-control" id="tanggallahir" name="tanggal_lahir"
                                    placeholder="Tanggal Lahir" value="{{ $karyawan->tanggal_lahir }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unitkerja</label>
                                <select name="unitkerja" id="unitkerja" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                </select>

                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="kelamin" id="kelamin" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                    <option value="L" @selected($karyawan->kelamin == 'L')>LAKI - LAKI</option>
                                    <option value="P" @selected($karyawan->kelamin == 'P')>PEREMPUAN</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Grade</label>
                                <input type="text" id="grade" name="grade" class="form-control"
                                    placeholder="A,B" maxlength="2" value="{{ $karyawan->gradeGolongan->grade ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Golongan</label>
                                <input type="text" id="golongan" name="golongan" class="form-control"
                                    placeholder="0,1" maxlength="1" value="{{ $karyawan->gradeGolongan->golongan ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Agama</label>
                                <select name="agama" id="agama" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                    <option value="ISLAM" @selected($karyawan->agama == 'ISLAM')>ISLAM</option>
                                    <option value="KRISTEN" @selected($karyawan->agama == 'KRISTEN')>KRISTEN</option>
                                    <option value="PROTESTAN" @selected($karyawan->agama == 'PROTESTAN')>PROTESTAN</option>
                                    <option value="KATOLIK" @selected($karyawan->agama == 'KATOLIK')>KATOLIK</option>
                                    <option value="HINDU" @selected($karyawan->agama == 'HINDU')>HINDU</option>
                                    <option value="BUDDHA" @selected($karyawan->agama == 'BUDDHA')>BUDDHA</option>
                                    <option value="KONGHUCU" @selected($karyawan->agama == 'KONGHUCU')>KONGHUCU</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Handphone</label>
                                <input type="text" id="hp" name="hp" class="form-control numberOnly"
                                    placeholder="08x" maxlength="11" autocomplete="off"
                                    value="{{ ltrim($karyawan->handphone, '62') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Alamat Email" value="{{ $karyawan->email }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat KTP</label>
                                <textarea class="form-control" name="alamat_ktp" id="alamatktp" rows="5" placeholder="Alamat Sesuai KTP">{{ $karyawan->alamat_ktp }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Domisili</label>
                                <textarea class="form-control" name="alamat_domisili" id="alamatdomisili" rows="5"
                                    placeholder="Alamat Domisili">{{ $karyawan->alamat_domisili }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select id="provinsi" name="provinsi_id" class="select2 form-select"
                                    data-allow-clear="true">
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kabupaten / Kota</label>
                                <select id="kabkot" name="kabkot_id" class="select2 form-select"
                                    data-allow-clear="true">
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kecamatan</label>
                                <select id="kecamatan" name="kecamatan_id" class="select2 form-select"
                                    data-allow-clear="true">
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Desa / Kelurahan</label>
                                <select id="desa" name="desa_id" class="select2 form-select"
                                    data-allow-clear="true">
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" class="form-control numberOnly" id="kodepos" name="kodepos"
                                    placeholder="Kode Pos" value="{{ $karyawan->kodepos }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Ukuran Baju</label>
                                <select name="baju" id="baju" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value="">-pilih-</option>
                                    <option value="S" @selected($karyawan->ukuranbaju == 'S')>S</option>
                                    <option value="M" @selected($karyawan->ukuranbaju == 'M')>M</option>
                                    <option value="L" @selected($karyawan->ukuranbaju == 'L')>L</option>
                                    <option value="XL" @selected($karyawan->ukuranbaju == 'XL')>XL</option>
                                    <option value="XXL" @selected($karyawan->ukuranbaju == 'XXL')>XXL</option>
                                    <option value="XXXL" @selected($karyawan->ukuranbaju == 'XXXL')>XXXL</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Nomor Celana</label>
                                <input type="text" name="celana" id="celana" class="form-control" maxlength="3"
                                    placeholder="2 Digits" autocomplete="off" value="{{ $karyawan->ukurancelana }}">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Tinggi Badan</label>
                                <input type="text" name="tb" id="tb" class="form-control" maxlength="3"
                                    placeholder="Centi meter" autocomplete="off" value="{{ $karyawan->tinggibadan }}">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Berat Badan</label>
                                <input type="text" name="bb" id="bb" class="form-control" maxlength="3"
                                    placeholder="Kilo gram" autocomplete="off" value="{{ $karyawan->beratbadan }}">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Nomor Sepatu</label>
                                <input type="text" name="sepatu" id="sepatu" class="form-control" maxlength="2"
                                    placeholder="2 Digits" autocomplete="off" value="{{ $karyawan->nomorsepatu }}">
                            </div>
                        </div>
                    </div>

                    <!-- ====================================================================================================================== KELUARGA -->
                    <div class="tab-pane fade" id="dtkeluarga" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status Perkawinan</label>
                                <select name="kawin" id="kawin" class="select2 form-select"
                                    data-allow-clear="true">
                                </select>
                            </div>

                            <div class="table-responsive table-keluarga" id="tblKeluarga">
                                <table class="table table-striped table-hover nowrap" style="width: 100%;font-size:12px">
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
                                                <input type="text" class="form-control numberOnly" id="ktpkeluarga"
                                                    name="ktpsuis" maxlength="16" placeholder="16 Digits"
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
                                                    value="{{ $pasangan->tmptlahir_keluarga ?? '' }}" autocomplete="off">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control tglkeluarga" id="tglkeluarga"
                                                    name="tglsuis" placeholder="YYYY-MM-DD"
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
                                                <input type="hidden" id="idanak1" name="idanak1" class="form-control"
                                                    value="{{ $anak[0]->id ?? '' }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control numberOnly" id="ktpkeluarga"
                                                    name="ktpanak1" maxlength="16" placeholder="16 Digits"
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
                                                    value="{{ $anak[0]->tmptlahir_keluarga ?? '' }}" autocomplete="off">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control tglkeluarga" id="tglkeluarga"
                                                    name="tglanak1" placeholder="YYYY-MM-DD"
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
                                                <input type="hidden" id="idanak2" name="idanak2" class="form-control"
                                                    value="{{ $anak[1]->id ?? '' }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control numberOnly" id="ktpkeluarga"
                                                    name="ktpanak2" maxlength="16" placeholder="16 Digits"
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
                                                    value="{{ $anak[1]->tmptlahir_keluarga ?? '' }}" autocomplete="off">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control tglkeluarga" id="tglkeluarga"
                                                    name="tglanak2" placeholder="YYYY-MM-DD"
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
                                                <input type="hidden" id="idanak3" name="idanak3" class="form-control"
                                                    value="{{ $anak[2]->id ?? '' }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control numberOnly" id="ktpkeluarga"
                                                    name="ktpanak3" maxlength="16" placeholder="16 Digits"
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
                                                    value="{{ $anak[2]->tmptlahir_keluarga ?? '' }}" autocomplete="off">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control tglkeluarga" id="tglkeluarga"
                                                    name="tglanak3" placeholder="YYYY-MM-DD"
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
                        </div>
                    </div>
                    <!-- ====================================================================================================================== PENDIDIKAN -->
                    <div class="tab-pane fade" id="dtpendidikan" role="tabpanel">
                        <div class="row">

                            <div class="table-responsive table-keluarga">
                                <table class="table table-striped table-hover text-nowrap"
                                    style="width: 100%;font-size:12px">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>Pendidikan Terakhir</th>
                                            <th>Nama Sekolah</th>
                                            <th>Jurusan</th>
                                            <th>Tahun Lulus</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $valPendidikan = ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];
                                    @endphp
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="pendidikan" id="pendidikan" class="form-select">
                                                    <option value="">-pilih-</option>
                                                    @foreach ($valPendidikan as $item)
                                                    <option value="{{ $item }}" {{ $karyawan->pendidikan == $item ? 'selected' : '' }} >{{ $item }}</option> @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
        class="form-control text-uppercase" id="namasekolah" name="namasekolah" placeholder="Sekolah / Universitas"
        value="{{ $karyawan->namasekolah ?? '' }}" autocomplete="off">
    </td>
    <td>
        <input type="text" class="form-control text-uppercase" id="jurusan" name="jurusan" placeholder="jurusan"
            value="{{ $karyawan->jurusan ?? '' }}" autocomplete="off">
    </td>
    <td>
        <input type="text" class="form-control text-uppercase" id="tahunlulus" name="tahunlulus" placeholder="Tahun"
            value="{{ $karyawan->tahunlulus ?? '' }}" autocomplete="off">
    </td>
    <td>
        <input type="text" class="form-control text-uppercase" id="nilai" name="nilai" placeholder="IPK"
            value="{{ $karyawan->nilai ?? '' }}" autocomplete="off">
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    </div>
    </div>
    <!-- ====================================================================================================================== BANK -->
    <div class="tab-pane fade" id="dtbank" role="tabpanel">
        <div class="row">

            <div class="col-md-4">
                <label class="form-label">Nama Bank</label>
                <input type="hidden" class="form-control" name="bank_id" value="{{ $karyawan->bank_id ?? '' }}">
                <select name="bankid" id="bankid" class="select2 form-select" data-allow-clear="true"
                    @cannot('edit-bank') disabled @endcannot>
                    <option value="">-pilih-</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nama Direkening</label>
                <input type="text" id="namabank" name="namabank" class="form-control text-uppercase"
                    placeholder="Nama Direkening" autocomplete="off" value="{{ $karyawan->namabank ?? '' }}"
                    @cannot('edit-bank') readonly @endcannot>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nomor Rekening</label>
                <input type="text" id="norek" name="norek" class="form-control" placeholder="Nomor Rekening"
                    autocomplete="off" value="{{ $karyawan->norek ?? '' }}" @cannot('edit-bank') readonly @endcannot>
            </div>
        </div>
    </div>
    <!-- ====================================================================================================================== KONTRAK -->
    <div class="tab-pane fade" id="dtkontrak" role="tabpanel">
        <div class="row">
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-nowrap" style="width: 100%;font-size:12px">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Tanggal Kontrak</th>
                            <th>Nomor Kontrak</th>
                            <th>Jenis Kontrak</th>
                            <th>Kontrak Ke</th>
                            <th>Periode Kontrak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kontrak as $item)
                            <tr class="text-center">
                                <td>{{ Carbon::parse($item->tanggal_kontrak)->translatedFormat('d F Y') }}</td>
                                <td>{{ $item->nomor_kontrak }}</td>
                                <td>{{ $item->statuskontrak->name }}</td>
                                <td>{{ $item->kontrak_ke }}</td>
                                <td>
                                    <span class="text-success">
                                        {{ Carbon::parse($item->kontrak_dari)->translatedFormat('d F Y') }}
                                    </span>
                                    <br>sampai<br>
                                    <span class="text-danger">
                                        {{ Carbon::parse($item->kontrak_sampai)->translatedFormat('d F Y') }}
                                    </span>
                                </td>
                                <td>
                                    {!! $item->status_kontrak == 'AKTIF'
                                        ? '<span class="badge bg-label-success">Aktif</span>'
                                        : '<span class="badge bg-label-danger">Tidak Aktif</span>' !!}
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- ====================================================================================================================== LAMPIRAN -->
    <div class="tab-pane
        fade" id="dtlampiran" role="tabpanel">
        <div class="row">
            <div class="card-body pb-0">
                <ul class="timeline pt-3">

                    <li class="timeline-item pb-4 timeline-item-primary border-left-dashed">
                        <span class="timeline-indicator-advanced timeline-indicator-primary">
                            <i class="fa-regular fa-image rounded-circle scaleX-n1-rtl"></i>
                        </span>
                        <div class="timeline-event">
                            <div class="timeline-header border-bottom mb-2">
                                <h6 class="mb-0">PHOTO</h6>
                            </div>
                            <div class="d-flex align-items-center">
                                @if ($karyawan->photo != null)
                                    <a href="javascript:void()" class="lihatLampiran"
                                        data-lampiran="{{ JWT::encode(['lampiran' => $karyawan->photo], config('jwt.secret'), 'HS256') }}">
                                        <img src="{{ asset('assets/img/banner/gambar.png') }}" alt="img"
                                            width="30" class="me-2" />
                                        <small class="mb-0 text-warning">klik untuk melihat</small>
                                    </a>
                                @else
                                    <span>
                                        <i class="fas fa-ban fa-2x text-danger me-2"></i>
                                    </span>
                                    <small class="mb-0 text-danger">Photo tidak ditemukan.</small>
                                @endif
                            </div>
                        </div>
                    </li>

                    <li class="timeline-item pb-4 timeline-item-info border-left-dashed">
                        <span class="timeline-indicator-advanced timeline-indicator-info">
                            <i class="fa-regular fa-image rounded-circle scaleX-n1-rtl"></i>
                        </span>
                        <div class="timeline-event">
                            <div class="timeline-header border-bottom mb-2">
                                <h6 class="mb-0">KARTU KELUARGA (KK)</h6>
                            </div>
                            <div class="d-flex align-items-center">
                                @if ($pelamar->file_kk != null)
                                    <a href="javascript:void()" class="lihatLampiran"
                                        data-lampiran="{{ JWT::encode(['lampiran' => $pelamar->file_kk], config('jwt.secret'), 'HS256') }}">
                                        <img src="{{ asset('assets/img/banner/gambar.png') }}" alt="img"
                                            width="30" class="me-2" />
                                        <small class="mb-0 text-warning">klik untuk melihat</small>
                                    </a>
                                @else
                                    <span>
                                        <i class="fas fa-ban fa-2x text-danger me-2"></i>
                                    </span>
                                    <small class="mb-0 text-danger">KK tidak ditemukan.</small>
                                @endif
                            </div>
                        </div>
                    </li>

                    <li class="timeline-item pb-4 timeline-item-success border-left-dashed">
                        <span class="timeline-indicator-advanced timeline-indicator-success">
                            <i class="fa-regular fa-image rounded-circle scaleX-n1-rtl"></i>
                        </span>
                        <div class="timeline-event">
                            <div class="timeline-header border-bottom mb-2">
                                <h6 class="mb-0">KARTU TANDA PENDUDUK (KTP)</h6>
                            </div>
                            <div class="d-flex align-items-center">
                                @if ($pelamar->file_ktp != null)
                                    <a href="javascript:void()" class="lihatLampiran"
                                        data-lampiran="{{ JWT::encode(['lampiran' => $pelamar->file_ktp], config('jwt.secret'), 'HS256') }}">
                                        <img src="{{ asset('assets/img/banner/gambar.png') }}" alt="img"
                                            width="40" class="me-2" />
                                        <small class="mb-0 text-warning">klik untuk melihat</small>
                                    </a>
                                @else
                                    <span>
                                        <i class="fas fa-ban fa-2x text-danger me-2"></i>
                                    </span>
                                    <small class="mb-0 text-danger">KTP tidak ditemukan.</small>
                                @endif
                            </div>
                        </div>
                    </li>

                </ul>
            </div>

        </div>
    </div>
    </div>
    </div>
    </div>
    </form>
@endsection

@push('extra_script')
    <script>
        window.userCanEditBank = @json(auth()->user()->can('edit-bank'));
    </script>
    <script src="{{ asset('assets/customjs/karyawan/detail-karyawan.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
