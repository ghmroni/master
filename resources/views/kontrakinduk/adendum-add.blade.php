@extends('template.core')

@php
    use Carbon\Carbon;
@endphp

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/jstree/jstree.css" />
    <style>
        .row .form-label {
            color: #999;
            text-transform: uppercase;
        }

        .table-responsive {
            max-height: 450px;
            overflow-x: auto;
            overflow-y: auto;
        }

        .table-biaya-dinamis thead th {
            position: sticky;
            top: 0;
            z-index: 10;

        }

        /* 3. Freeze Kolom Pertama 'JENIS BIAYA' (Scroll Horizontal) */
        .table-biaya-dinamis tbody tr td:first-child,
        .table-biaya-dinamis thead tr th:first-child {
            position: sticky;
            left: 0;
            z-index: 5;
            /* background-color: #2b2c40 !important; */
            background-color: #DFDFE3 !important;
            /* Warna background cell pembeku (lebih gelap) */
            box-shadow: 5px 0 5px -3px rgba(0, 0, 0, 0.3);
            /* Memberikan efek bayangan batas potong */
            color: #44475B !important;
        }

        /* 4. Khusus untuk persimpangan Pojok Kiri Atas (Header JENIS BIAYA) */
        .table-biaya-dinamis thead tr th:first-child {
            z-index: 15;
            /* Harus paling tinggi agar tidak tertimpa header lain */
            /* background-color: #2b2c40 !important;color: #fff !important; */
            background-color: #DFDFE3 !important;
            color: #000 !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-12 mb-4">

        <div class="accordion rounded shadow-sm" id="collapsibleSection">
            <!-- Delivery Address -->
            <div class="card accordion-item">
                <h2 class="accordion-header" id="headingDeliveryAddress">
                    <button class="accordion-button collapsed bg-label-primary fw-bold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseDeliveryAddress" aria-expanded="false"
                        aria-controls="collapseDeliveryAddress">
                        Data Kontrak Induk
                    </button>
                </h2>
                <div id="collapseDeliveryAddress" class="accordion-collapse collapse"
                    aria-labelledby="headingDeliveryAddress" data-bs-parent="#collapsibleSection">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Judul Kontrak Induk</label>
                                <input type="text" class="form-control border-primary" readonly
                                    value="{{ $kontrakinduk->judul }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Kontrak Induk</label>
                                <input type="text" class="form-control border-primary" readonly
                                    value="{{ $kontrakinduk->nomor }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kontrak Induk</label>
                                <input type="text" class="form-control border-primary" readonly
                                    value="{{ $kontrakinduk->jenis_kontrakinduk }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kontrak Dari</label>
                                <input type="text" class="form-control border-primary" readonly
                                    value="{{ strtoupper(Carbon::parse($kontrakinduk->start)->translatedFormat('d F Y')) }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kontrak Sampai</label>
                                <input type="text" class="form-control border-primary" readonly
                                    value="{{ strtoupper(Carbon::parse($kontrakinduk->end)->translatedFormat('d F Y')) }}" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Lokasi Setting Project</label>
                                <div class="input-group">
                                    <span class="form-control lokasisetting"></span>
                                    <span class="message-actions input-group-text" id="text-to-speech-addon">
                                        <i class="fas fa-street-view cursor-pointer text-info"></i>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (count($kontrakinduk->adendum) > 0)
            <div class="accordion rounded shadow-sm mt-2" id="collapsAdendum">
                <!-- Delivery Address -->
                <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingAdendum">
                        <button class="accordion-button collapsed bg-label-warning fw-bold" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAdendum" aria-expanded="false"
                            aria-controls="collapseAdendum">
                            Data Adendum
                        </button>
                    </h2>
                    <div id="collapseAdendum" class="accordion-collapse collapse" aria-labelledby="headingAdendum"
                        data-bs-parent="#collapsAdendum">
                        <div class="accordion-body">
                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-hover table-bordered"
                                    style="width: 100%;font-size:12px">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>Nomor Adendum</th>
                                            <th>Tanggal Terbit</th>
                                            <th>Tanggal Periode</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kontrakinduk->adendum as $item)
                                            <tr>
                                                <td>{{ $item->nomor_adendum }}</td>
                                                <td class="text-center">
                                                    {{ Carbon::parse($item->tanggal_terbit)->translatedFormat('d F Y') }}
                                                </td>
                                                <td class="text-center">
                                                    {{ Carbon::parse($item->tanggal_mulai_berlaku)->translatedFormat('d F Y') }}
                                                    <br>s/d<br>
                                                    {{ Carbon::parse($item->tanggal_akhir_berlaku)->translatedFormat('d F Y') }}
                                                </td>
                                                <td>{{ $item->keterangan }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div id="wizard-validation" class="bs-stepper mt-3">
            <div class="bs-stepper-header bg-light">
                <div class="step" data-target="#account-details-validation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Informasi</span>
                            <span class="bs-stepper-subtitle">Adendum / Berita Acara</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="ti ti-chevron-right"></i>
                </div>
                <div class="step" data-target="#personal-info-validation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Komponen</span>
                            <span class="bs-stepper-subtitle">Nama Header dan Biaya</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="ti ti-chevron-right"></i>
                </div>
                <div class="step" data-target="#social-links-validation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Harga</span>
                            <span class="bs-stepper-subtitle">Setting Harga</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="bs-stepper-content">
                <form id="wizard-validation-form" onSubmit="return false">
                    @csrf
                    <!-- Account Details -->
                    {{-- @dd($payloadArray) --}}
                    <div id="account-details-validation" class="content">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Judul Adendum / Berita Acara</label>
                                <input type="hidden" class="form-control" id="idinduk" name="idinduk"
                                    value="{{ $kontrakinduk->id ?? null }}" readonly />
                                <input type="hidden" class="form-control" id="idsetting" name="idsetting"
                                    value="{{ $adendum->id ?? null }}" readonly />
                                <input type="hidden" class="form-control" id="idadendum" name="idadendum"
                                    value="{{ $payloadArray['adendum_id'] ?? null }}" readonly />
                                <input type="hidden" class="form-control" id="status" name="status"
                                    value="tambah-adendum" readonly />
                                <input type="text" class="form-control text-uppercase" id="judul" name="judul"
                                    placeholder="Judul Adendum / BA" autocomplete="off"
                                    value="{{ $payloadArray['judul'] ?? null }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Adendum / Berita Acara</label>
                                <input type="text" class="form-control text-uppercase" id="nomor" name="nomor"
                                    placeholder="Nomor Adendum / BA" autocomplete="off"
                                    value="{{ $payloadArray['nomor'] ?? null }}"
                                    oninput="this.value = this.value.replace(/\s+/g, '')" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Terbit</label>
                                <input type="text" name="tglterbit" id="tglterbit" class="form-control"
                                    placeholder="YYYY-MM-DD" value="{{ $payloadArray['tglterbit'] ?? null }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Berlaku Dari</label>
                                <input type="text" name="dari" id="dari" class="form-control"
                                    placeholder="YYYY-MM-DD" value="{{ $payloadArray['dari'] ?? null }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Berlaku Sampai</label>
                                <input type="text" name="sampai" id="sampai" class="form-control"
                                    placeholder="YYYY-MM-DD" value="{{ $payloadArray['sampai'] ?? null }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control text-uppercase" name="keterangan" id="keterangan" rows="3" autocomplete="off"
                                    placeholder="Keterangan">{{ $payloadArray['keterangan'] ?? null }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary btn-prev" disabled>
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
                    <!-- Personal Info -->
                    <div id="personal-info-validation" class="content">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="card shadow bg-transparent border border-info mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-center text-uppercase">Nama Header / Unitkerja</h5>
                                        <div class="row">
                                            @forelse ($unitkerjaNames as $index => $namaUnit)
                                                @if ($index == 0)
                                                    <div class="col-md-12 nopadding mb-2">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control text-uppercase"
                                                                name="unitkerja[]" list="list_unitkerja"
                                                                placeholder="Ketik / pilih unitkerja" autocomplete="off"
                                                                value="{{ $namaUnit }}">
                                                            <button class="btn btn-info unitkerja_fields" type="button">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- 💡 Gunakan class statis 'dynamic-unitkerja-row' agar mudah dihitung indeksnya saat dihapus/ditambah --}}
                                                    <div
                                                        class="form-group dynamic-unitkerja-row removeclass_unitkerja{{ $index + 1 }}">
                                                        <div class="col-md-12 nopadding mb-2">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control text-uppercase"
                                                                    name="unitkerja[]" list="list_unitkerja"
                                                                    placeholder="Ketik / pilih unitkerja"
                                                                    autocomplete="off" value="{{ $namaUnit }}">
                                                                <button class="btn btn-danger remove_unitkerja_fields"
                                                                    data-room="{{ $index + 1 }}" type="button">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="col-md-12 nopadding mb-2">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control text-uppercase"
                                                            name="unitkerja[]" list="list_unitkerja"
                                                            placeholder="Ketik / pilih unitkerja" autocomplete="off"
                                                            value="">
                                                        <button class="btn btn-info unitkerja_fields" type="button">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforelse
                                            <div id="unitkerja_fields"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card shadow bg-transparent border border-primary mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-center text-uppercase">Jenis Biaya</h5>
                                        <div class="row">
                                            @forelse ($tunjanganArray as $index => $namaBiaya)
                                                @if ($index == 0)
                                                    <div class="col-md-12 nopadding mb-2">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control text-uppercase"
                                                                name="tunjangan[]" list="list_tunjangan"
                                                                placeholder="Jenis Biaya" autocomplete="off"
                                                                value="{{ $namaBiaya }}">
                                                            <button class="btn btn-primary tunjangan_fields"
                                                                type="button">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div
                                                        class="form-group dynamic-tunjangan-row removeclass_tunjangan{{ $index + 1 }}">
                                                        <div class="col-md-12 nopadding mb-2">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control text-uppercase"
                                                                    name="tunjangan[]" list="list_tunjangan"
                                                                    placeholder="Jenis Biaya" autocomplete="off"
                                                                    value="{{ $namaBiaya }}">
                                                                <button class="btn btn-danger remove_tunjangan_fields"
                                                                    data-room="{{ $index + 1 }}" type="button">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="col-md-12 nopadding mb-2">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control text-uppercase"
                                                            name="tunjangan[]" list="list_tunjangan"
                                                            placeholder="Jenis Biaya" autocomplete="off">
                                                        <button class="btn btn-primary tunjangan_fields" type="button">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforelse
                                            <div id="tunjangan_fields"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between">
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
                    <!-- Social Links -->
                    <div id="social-links-validation" class="content">
                        <div class="row g-3">
                            <div class="col-md-12 mt-2">
                                <label class="form-label">Lampiran Adendum / Berita Acara</label>
                                <input type="file" class="form-control" id="file" name="file"
                                    accept="application/pdf" />
                                <input type="hidden" class="form-control" id="file_lama" name="file_lama"
                                    value="{{ $payloadArray['file_upload_name'] ?? null }}" />
                            </div>
                            <hr />
                            <div class="col-12">
                                <div id="kontainer-setting-project" class="mb-0">
                                    @foreach ($settingsFormatted as $index => $setting)
                                        <div class="project-setting-block p-4 mb-4 border border-secondary rounded shadow"
                                            id="block-setting-{{ $index }}" data-index="{{ $index }}">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5>SETTING PROJECT #{{ $index + 1 }}</h5>
                                                @if ($index > 0)
                                                    <div class="card-action-element">
                                                        <ul class="list-inline mb-0">
                                                            <li class="list-inline-item">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm btn-hapus-setting"
                                                                    data-id="{{ $index }}">
                                                                    <i class="ti ti-trash me-1"></i>Hapus</button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                            <label class="form-label">Project</label>
                                            <div class="fv-row-matrix">
                                                <div class="input-group">
                                                    <span class="form-control lokasipekerjaan-text"
                                                        id="lokasipekerjaan-{{ $index }}"></span>
                                                    <span class="message-actions input-group-text"
                                                        id="text-to-speech-addon">
                                                        <i class="fas fa-street-view cursor-pointer text-info"></i>
                                                    </span>
                                                    {{-- <div id="btnLokasi">
                                                        <button class="btn btn-dark changeLokasi-btn" type="button"
                                                            data-id="{{ $index }}">📍
                                                        </button>
                                                    </div> --}}
                                                </div>
                                                <input type="hidden" class="form-control"
                                                    id="project-{{ $index }}"
                                                    name="setting[{{ $index }}][project_id]" readonly>
                                                <input type="hidden" class="form-control"
                                                    id="dtproject-{{ $index }}"
                                                    name="setting[{{ $index }}][project_detail]" readonly>
                                            </div>

                                            <div class="table-responsive mt-2">
                                                <!-- Tabel yang akan diisi secara dinamis -->
                                                <table
                                                    class="table table-sm table-bordered text-nowrap align-middle small fs-xs table-biaya-dinamis"
                                                    id="table-biaya-{{ $index }}">
                                                    <thead class="table-light">
                                                        <!-- Header digenerate oleh JS -->
                                                    </thead>
                                                    <tbody>
                                                        <!-- Body digenerate oleh JS -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <hr />

                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary btn-prev">
                                    <i class="ti ti-arrow-left me-sm-1 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                                </button>
                                <button class="btn btn-success btn-next btn-submit"><i
                                        class="ti ti-cloud-up me-1"></i>Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <datalist id="list_unitkerja"></datalist>
    <datalist id="list_tunjangan"></datalist>
@endsection

@push('extra_script')
    <script>
        // 1. Oper data dari PHP/Blade ke JavaScript secara aman
        const dbSettingsData = {!! json_encode($settingsFormatted) !!};

        // 2. Set index global agar ketika klik "+ Tambah" kelanjutan dari jumlah data DB
        let globalSettingIndex = dbSettingsData.length > 0 ? dbSettingsData.length - 1 : 0;
    </script>

    <script src="{{ asset('assets/customjs/kontrakinduk/adendum-add.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/jstree/jstree.js"></script>
@endpush
