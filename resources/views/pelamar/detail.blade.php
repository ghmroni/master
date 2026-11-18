@extends('template.core')

@php
    use Carbon\Carbon;
    use Firebase\JWT\JWT;

    function badgeClass($status)
    {
        return match ($status) {
            'WAITING' => 'bg-label-primary',
            'PROSES' => 'bg-label-warning',
            'COMPLETE' => 'bg-label-success',
            default => 'bg-label-secondary',
        };
    }
@endphp

@section('content')
    <div class="row g-3">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card mb-4">
                <div class="card-body shadow">
                    <div class="user-avatar-section pb-4 border-bottom">
                        <div class="d-flex align-items-center flex-column">
                            <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                src="{{ $pelamar->photo_profile != null
                                    ? '/storage/pelamar/profile/' . $pelamar->photo_profile
                                    : '/assets/img/avatars/no-image.png' }}"
                                height="100" width="100" alt="avatar" />
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ $pelamar->nama }}</h4>
                                <span class="badge {{ badgeClass($pelamar->status) }} mt-1">{{ $pelamar->status }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 small text-uppercase text-muted">Details</p>
                    <div class="info-container">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="fw-medium me-1 text-muted">Nama :</span>
                                <span class="text-uppercase">{{ $pelamar->nama }}</span>
                            </li>
                            <li class="mb-2 pt-1">
                                <span class="fw-medium me-1 text-muted">KTP :</span>
                                <span>{{ $pelamar->noktp }}</span>
                            </li>
                            <li class="mb-2 pt-1">
                                <span class="fw-medium me-1 text-muted">Email :</span>
                                <span class="text-lowercase">{{ $pelamar->email }}</span>
                            </li>
                            <li class="mb-2 pt-1">
                                <span class="fw-medium me-1 text-muted">Tempat Lahir :</span>
                                <span class="text-uppsercase">{{ $pelamar->tmptlahir }}</span>
                            </li>
                            <li class="mb-2 pt-1">
                                <span class="fw-medium me-1 text-muted">Tanggal Lahir :</span>
                                <span
                                    class="text-uppsercase">{{ Carbon::parse($pelamar->tgllahir)->translatedFormat('d F Y') }}</span>
                            </li>
                            <li class="mb-2 pt-1">
                                <span class="fw-medium me-1 text-muted">Handphone :</span>
                                <span class="text-uppsercase">+{{ $pelamar->handphone }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="d-grid w-100 mt-4">
                        <button class="btn btn-outline-dark btnBack">
                            <i class="fas fa-backward me-2"></i> Kembali
                        </button>
                    </div>
                </div>
            </div>
            <!-- /User Card -->

        </div>
        <!--/ User Sidebar -->

        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <!-- User Pills -->
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#data-diri" aria-controls="data-diri" aria-selected="true">
                            Data Diri
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnKeluarga"
                            data-token="{{ JWT::encode(['id' => $pelamar->id_pelamar], config('jwt.secret'), 'HS256') }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-keluarga"
                            aria-controls="navs-keluarga" aria-selected="false">
                            Keluarga
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnPendidikan"
                            data-token="{{ JWT::encode(['id' => $pelamar->id_pelamar], config('jwt.secret'), 'HS256') }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-pendidikan"
                            aria-controls="navs-pendidikan" aria-selected="false">
                            Pendidikan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnPaklaring"
                            data-token="{{ JWT::encode(['id' => $pelamar->id_pelamar], config('jwt.secret'), 'HS256') }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-paklaring"
                            aria-controls="navs-paklaring" aria-selected="false">
                            Pengalaman Kerja
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnSertifikat"
                            data-token="{{ JWT::encode(['id' => $pelamar->id_pelamar], config('jwt.secret'), 'HS256') }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-sertifikat"
                            aria-controls="navs-sertifikat" aria-selected="false">
                            Sertifikat
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnDokumen"
                            data-token="{{ JWT::encode(['id' => $pelamar->id_pelamar], config('jwt.secret'), 'HS256') }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-dokumen" aria-controls="navs-dokumen"
                            aria-selected="false">
                            Dokumen
                        </button>
                    </li>
                </ul>
                <div class="tab-content shadow">
                    <div class="tab-pane fade show active" id="data-diri" role="tabpanel">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nama Lengkap</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->nama ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Email</label>
                                    <input type="text" class="form-control text-lowercase"
                                        value="{{ $pelamar->email ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor KTP</label>
                                    <input type="text" class="form-control" value="{{ $pelamar->noktp ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor Handphone</label>
                                    <input type="text" class="form-control"
                                        value="{{ preg_replace('/^0/', '+62', $pelamar->handphone ?? '') }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Tempat Lahir</label>
                                    <input type="text" class="form-control" value="{{ $pelamar->tmptlahir ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Tanggal Lahir</label>
                                    <input type="text" class="form-control"
                                        value="{{ Carbon::parse($pelamar->tgllahir)->translatedFormat('d F Y') ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Jenis Kelamin</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->jenis_kelamin == 'P' ? 'PEREMPUAN' : ($pelamar->jenis_kelamin == 'L' ? 'LAKI - LAKI' : '') }}"
                                        readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Status Perkawinan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->perkawinan->nama_status ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Agama</label>
                                    <input type="text" class="form-control" value="{{ $pelamar->agama ?? '' }}"
                                        readonly>
                                </div>
                                {{-- KTP --}}

                                <div class="divider divider-primary">
                                    <div class="divider-text text-primary">ALAMAT SESUAI KTP</div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="form-label text-muted">Alamat Lengkap</label>
                                    <textarea class="form-control" readonly>{{ $pelamar->alamat_ktp ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Provinsi</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->provinsiKtp->nama_provinsi ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Kabupaten / Kota</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kabkotKtp->nama_kabkot ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Kecamatan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kecamatanKtp->nama_kecamatan ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Kelurahan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kelurahanKtp->nama_kelurahan ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Kodepos</label>
                                    <input type="text" class="form-control" value="{{ $pelamar->kodepos_ktp ?? '' }}"
                                        readonly>
                                </div>

                                {{-- DOMISILI --}}
                                <div class="divider divider-primary">
                                    <div class="divider-text text-primary">ALAMAT SESUAI DOMISILI</div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="form-label text-muted">Alamat Lengkap</label>
                                    <textarea class="form-control" readonly>{{ $pelamar->alamat_domisili ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Provinsi</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->provinsiDomisili->nama_provinsi ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Kabupaten / Kota</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kabkotDomisili->nama_kabkot ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Kecamatan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kecamatanDomisili->nama_kecamatan ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Kelurahan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kelurahanDomisili->nama_kelurahan ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Kodepos</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->kodepos_domisili ?? '' }}" readonly>
                                </div>

                                {{-- SERAGAM --}}
                                <div class="divider divider-warning">
                                    <div class="divider-text text-warning">SERAGAM</div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Berat Badan</label>
                                    <input type="text" class="form-control" value="{{ $pelamar->berat_badan ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Tinggi Badan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->tinggi_badan ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Ukuran Baju</label>
                                    <input type="text" class="form-control" value="{{ $pelamar->ukuran_baju ?? '' }}"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor Celana</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->nomor_celana ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor Sepatu</label>
                                    <input type="text" class="form-control"
                                        value="{{ $pelamar->nomor_sepatu ?? '' }}" readonly>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- ========================================================================= KELUARGA ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-keluarga" role="tabpanel">
                        <table id="tabelKeluarga" class="table table-hover table-bordered nowrap"
                            style="width: 100%;font-size:12px">
                            <thead>
                                <tr>
                                    <th>Status Keluarga</th>
                                    <th>Nama Lengkap</th>
                                    <th>Nomor KK</th>
                                    <th>Nomor KTP</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Jenis Kelamin</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                    {{-- ========================================================================= PENDIDIKAN ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-pendidikan" role="tabpanel">
                        <div class="alert alert-primary d-flex align-items-center" role="alert">
                            <span class="alert-icon text-primary me-2">
                                <i class="ti ti-crown ti-xs"></i>
                            </span>
                            Pendidikan Formal
                        </div>
                        <div class="table-responsive border-bottom pb-2">
                            <table id="tabelPendidikan" class="table table-bordered nowrap"
                                style="width:100%;font-size:12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pendidikan</th>
                                        <th>Nama Sekolah / Universitas</th>
                                        <th>Jurusan</th>
                                        <th>Tahun Lulus</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">{{ $pelamar->jenjang_pendidikan ?? '' }}</td>
                                        <td>{{ $pelamar->nama_sekolah ?? '' }}</td>
                                        <td>{{ $pelamar->jurusan_sekolah ?? '' }}</td>
                                        <td class="text-center">{{ $pelamar->tahun_lulus ?? '' }}</td>
                                        <td class="text-center">{{ $pelamar->nilai_ijazah ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info d-flex align-items-center mt-5" role="alert">
                            <span class="alert-icon text-info me-2">
                                <i class="ti ti-medal ti-xs"></i>
                            </span>
                            Pendidikan Non Formal
                        </div>
                        <table id="tabelNonformal" class="table table-hover table-bordered nowrap"
                            style="width:100%;font-size:12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Lembaga</th>
                                    <th>Program Pendidikan</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    {{-- ========================================================================= PAKLARING ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-paklaring" role="tabpanel">
                        <table id="tabelPaklaring" class="table table-hover table-bordered nowrap"
                            style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Perusahaan</th>
                                    <th>Jabatan</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Berakhir</th>
                                    <th>Lampiran</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    {{-- ========================================================================= SERTIFIKAT ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-sertifikat" role="tabpanel">
                        <table id="tabelSertifikat" class="table table-hover table-bordered nowrap"
                            style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Sertifikat</th>
                                    <th>Penyelenggara</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal Terbit</th>
                                    <th>Lampiran</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    {{-- ========================================================================= DOKUMEN ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-dokumen" role="tabpanel">
                        <div class="card-body pb-0">
                            <ul class="timeline pt-3">
                                <li class="timeline-item pb-4 timeline-item-primary border-left-dashed">
                                    <span class="timeline-indicator-advanced timeline-indicator-primary">
                                        <i class="fa-regular fa-file-pdf rounded-circle scaleX-n1-rtl"></i>
                                    </span>
                                    <div class="timeline-event">
                                        <div class="timeline-header border-bottom mb-2">
                                            <h6 class="mb-0">CURRICULUM VITAE (CV)</h6>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            @if ($pelamar->file_cv != null)
                                                <a href="javascript:void()" class="lihatLampiran"
                                                    data-lampiran="{{ JWT::encode(['lampiran' => $pelamar->file_cv], config('jwt.secret'), 'HS256') }}">
                                                    <img src="{{ asset('assets/img/banner/pdf.png') }}" alt="img"
                                                        width="40" class="me-2" />
                                                    <small class="mb-0 text-warning">klik untuk melihat</small>
                                                </a>
                                            @else
                                                <span>
                                                    <i class="fas fa-ban fa-2x text-danger me-2"></i>
                                                </span>
                                                <small class="mb-0 text-danger">Lampiran tidak ditemukan.</small>
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
                                            <h6 class="mb-0">IJAZAH TERAKHIR</h6>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            @if ($pelamar->file_ijazah != null)
                                                <a href="javascript:void()" class="lihatLampiran"
                                                    data-lampiran="{{ JWT::encode(['lampiran' => $pelamar->file_ijazah], config('jwt.secret'), 'HS256') }}">
                                                    <img src="{{ asset('assets/img/banner/pdf.png') }}" alt="img"
                                                        width="40" class="me-2" />
                                                    <small class="mb-0 text-warning">klik untuk melihat</small>
                                                </a>
                                            @else
                                                <span>
                                                    <i class="fas fa-ban fa-2x text-danger me-2"></i>
                                                </span>
                                                <small class="mb-0 text-danger">Lampiran tidak ditemukan.</small>
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
                                                <small class="mb-0 text-danger">Lampiran tidak ditemukan.</small>
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
                                                    <img src="{{ asset('assets/img/banner/pdf.png') }}" alt="img"
                                                        width="40" class="me-2" />
                                                    <small class="mb-0 text-warning">klik untuk melihat</small>
                                                </a>
                                            @else
                                                <span>
                                                    <i class="fas fa-ban fa-2x text-danger me-2"></i>
                                                </span>
                                                <small class="mb-0 text-danger">Lampiran tidak ditemukan.</small>
                                            @endif
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Invoice table -->
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/pelamar/detail-pelamar.js') }}"></script>
@endpush
