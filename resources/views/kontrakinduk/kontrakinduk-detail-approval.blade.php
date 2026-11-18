@extends('template.core')

@push('extra_style')
    <style>
        /* Kontainer utama pembungkus watermark */
        .watermark-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            pointer-events: none;
            /* Klik tembus ke elemen di bawahnya */
            overflow: hidden;
        }

        /* Elemen teks watermark */
        .watermark {
            transform: rotate(-45deg);
            font-size: 6rem;
            font-weight: 900;
            white-space: nowrap;
            text-transform: uppercase;
            user-select: none;
            /* Teks tidak bisa diblok/diseleksi */
        }

        /* Style khusus jika status APPROVED */
        .watermark-approved {
            color: rgba(76, 175, 80, 0.15);
            -webkit-text-stroke: 2px rgba(76, 175, 80, 0.4);
        }

        /* Style khusus jika status REJECTED */
        .watermark-rejected {
            color: rgba(244, 67, 54, 0.15);
            -webkit-text-stroke: 2px rgba(244, 67, 54, 0.4);
        }
    </style>
@endpush
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
@endphp
@section('content')
    <div class="card-body">

        @if (isset($approval->status) && ($approval->status == 'APPROVED' || $approval->status == 'REJECTED'))
            <div class="watermark-container">
                <div class="watermark {{ $approval->status == 'APPROVED' ? 'watermark-approved' : 'watermark-rejected' }}">
                    {{ $approval->status == 'APPROVED' ? 'DISETUJUI' : 'DITOLAK' }}
                </div>
            </div>
        @endif

        @php
            $kelasWarna = match ($approval->aktivitas) {
                'EDIT ADENDUM' => 'text-primary',
                'TAMBAH ADENDUM' => 'text-warning',
                'NONAKTIF KONTRAK INDUK' => 'text-danger',
                default => 'text-info',
            };
        @endphp
        <h5 class="text-center fw-bold ">PERSETUJUAN
            {{ str_replace('EDIT', 'PERUBAHAN', $approval->aktivitas) }}</h5>
        <div class="accordion shadow-sm mb-3 " id="collapsibleSection">
            <!-- Delivery Address -->
            <div class="card accordion-item">
                <h2 class="accordion-header" id="headingLampiran">
                    <button class="accordion-button collapsed  bg-light {{ $kelasWarna }} fw-bold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseDeliveryAddress" aria-expanded="false"
                        aria-controls="collapseDeliveryAddress">
                        <i class="fas fa-file-pdf me-1"></i> Lihat Lampiran
                    </button>
                </h2>
                <div id="collapseDeliveryAddress" class="accordion-collapse collapse" aria-labelledby="headingLampiran"
                    data-bs-parent="#collapsibleSection">
                    <div class="accordion-body mt-2">

                        <embed src="/storage/hris/kontrakinduk/{{ $approval->payload_request['file_upload_name'] }}"
                            type="application/pdf" width="100%" height="600px" />
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================================================================================================= APPROVAL EDIT --}}
        {{-- @if (Str::startsWith($approval->aktivitas, 'EDIT')) --}}
        @if (Str::is('EDIT *KONTRAK INDUK*', $approval->aktivitas))
            <div class="card mb-4 border-danger">
                <div class="card-header bg-light text-dark fw-bold">
                    <i class="fas fa-exclamation-circle me-1"></i> Perubahan Redaksi Dokumen Kontrak Induk / Adendum
                </div>
                <div class="card-body">
                    @if (count($redaksiDiff) > 0)
                        <ul class="list-group list-group-flush small">
                            @foreach ($redaksiDiff as $diff)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <span class="fw-bold text-secondary">{{ $diff['label'] }} :</span>
                                        <span
                                            class="text-danger text-decoration-line-through mx-2">{{ $diff['dari'] }}</span>
                                        <i class="fas fa-long-arrow-alt-right text-success mx-2"></i>
                                        <span class="text-success fw-bold">{{ $diff['menjadi'] }}</span>
                                    </div>
                                    {{-- <span class="badge rounded-pill bg-label-warning fs-xs">Berubah</span> --}}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-light text-muted mb-0 py-2 small">Tidak ada perubahan pada Redaksi Kontrak
                            Induk
                            / Adendum</div>
                    @endif
                </div>
            </div>

            <!-- 📊 2. TABEL MATRIX DINAMIS (Sesuai Jumlah Setting Harga) -->
            <h5 class="fw-bold text-uppercase mb-3"><i class="fas fa-cog me-2"></i>Setting Biaya</h5>

            @foreach ($matrixTables as $table)
                <!-- 1. JIKA PROYEK STATUSNYA DIHAPUS -->
                @if ($table['status_proyek'] === 'DIHAPUS')
                    <div class="card mb-4 border-danger">
                        <div
                            class="card-header bg-light text-danger fw-bold py-2 small d-flex justify-content-between align-items-center">
                            <span>📍 LOKASI : {{ $table['project_name'] }}</span>
                            <span class="badge text-danger fw-bold fs-xs"> LOKASI INI DIHAPUS / DIBUANG</span>
                        </div>
                        <div class="card-body py-2 text-muted small">
                            Setting untuk lokasi ini sudah dihapus.
                        </div>
                    </div>

                    <!-- 2. JIKA PROYEK BARU ATAU LAMA YANG DI-MAINTAIN -->
                @else
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-label-primary fw-bold text-uppercase py-2 small">
                            📍 Lokasi Kerja: {{ $table['project_name'] }}
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <!-- Menggunakan class 'small' atau 'fs-xs' custom agar muat banyak kolom -->
                                <table
                                    class="table table-sm table-bordered table-striped table-hover align-middle mb-0 small text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="align-middle text-uppercase" style="width: 25%;">Jenis Biaya</th>
                                            @foreach ($table['headers'] as $unitHeader)
                                                <th style="font-size: 0.85rem;">{{ strtoupper($unitHeader) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($table['rows'] as $namaBiaya)
                                            <tr>
                                                <!-- Kolom Baris Jenis Tunjangan -->
                                                <td class="text-start fw-bold text-uppercase" style="font-size: 0.85rem;">
                                                    {{ $namaBiaya }}
                                                </td>

                                                <!-- Loop Nilai Matriks Per Unit Kerja -->
                                                @foreach ($table['headers'] as $namaUnit)
                                                    @php
                                                        $cell = $table['grid'][$namaBiaya][$namaUnit] ?? [
                                                            'lama' => 0,
                                                            'baru' => 0,
                                                            'is_changed' => false,
                                                        ];
                                                        $lamaFmt = number_format($cell['lama'], 0, ',', '.');
                                                        $baruFmt = number_format($cell['baru'], 0, ',', '.');
                                                    @endphp

                                                    <!-- Jika ada perubahan sel nilai, beri warna latar belakang (highlight) -->
                                                    <td class="p-1" style="min-width: 130px;">
                                                        @if ($cell['is_changed'] || $table['status_proyek'] === 'BARU')
                                                            @if ($cell['lama'] > 0)
                                                                <div class="text-danger text-decoration-line-through"
                                                                    style="font-size: 0.75rem;">
                                                                    Rp {{ $lamaFmt }}
                                                                </div>
                                                            @endif
                                                            <div class="text-success" style="font-size: 0.85rem;">
                                                                Rp {{ $baruFmt }}
                                                            </div>
                                                        @else
                                                            <span style="font-size: 0.85rem;">Rp
                                                                {{ $baruFmt }}</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($approval->status == 'PENDING' && auth()->user()->can('approval-kontrakinduk'))
                <form id="form-approval">
                    @csrf
                    <input type="hidden" name="status" id="approval-status" value="">
                    <input type="hidden" name="idreq" id="idreq" value="{{ $approval->token }}">

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-danger btn-aksi-approve" data-action="REJECTED">
                            <i class="fas fa-times me-1"></i> Tolak Perubahan
                        </button>
                        <button type="button" class="btn btn-success btn-aksi-approve" data-action="APPROVED">
                            <i class="fas fa-check me-1"></i> Setujui & Terapkan
                        </button>
                    </div>
                </form>
            @else
                <!-- Jika status BUKAN pending ATAU user TIDAK punya akses, otomatis ke sini -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-dark kembali">Close</button>
                </div>
            @endif

            {{-- ============================================================================================================================================= APPROVAL NONAKTIF --}}
        @elseif(Str::startsWith($approval->aktivitas, 'NONAKTIF'))
            {{-- @elseif(Str::startsWith($approval->aktivitas, 'NONAKTIF KONTRAK INDUK')) --}}
            <div class="card mb-4 border-danger">
                <div class="card-header bg-light text-dark fw-bold">
                    <i class="fas fa-exclamation-circle me-1"></i> Rincian Kontrak Induk
                </div>
                <div class="card-body">

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="fw-bold">Nomor Kontrak Induk :</span>
                                <i class="fas fa-long-arrow-alt-right text-danger mx-2"></i>
                                <span class="text-danger fw-bold">{{ $approval->kontrakinduk->nomor }}</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="fw-bold">Jenis Kontrak Induk :</span>
                                <i class="fas fa-long-arrow-alt-right text-danger mx-2"></i>
                                <span class="text-danger fw-bold">{{ $approval->kontrakinduk->jenis_kontrakinduk }}</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="fw-bold">Periode Kontrak Induk :</span>
                                <i class="fas fa-long-arrow-alt-right text-danger mx-2"></i>
                                <span
                                    class="text-danger fw-bold">{{ strtoupper(Carbon::parse($approval->kontrakinduk->start)->translatedFormat('d F Y')) }}
                                </span>
                                <i class="fas fa-long-arrow-alt-right text-success mx-2"></i>
                                <span
                                    class="text-danger fw-bold">{{ strtoupper(Carbon::parse($approval->kontrakinduk->end)->translatedFormat('d F Y')) }}
                                </span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="fw-bold">Alasan Dinonaktif :</span>
                                <i class="fas fa-long-arrow-alt-right text-danger mx-2"></i>
                                <span class="text-danger fw-bold">{{ $approval->payload_request['alasan'] }}</span>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>

            @if ($approval->status == 'PENDING' && auth()->user()->can('approval-kontrakinduk'))
                <form id="form-approval-nonaktif">
                    @csrf
                    <input type="hidden" name="status" id="approval-status" value="">
                    <input type="hidden" name="idreq" id="idreq" value="{{ $approval->token }}">

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-danger btn-approve-off" data-action="REJECTED">
                            <i class="fas fa-times me-1"></i> Tolak
                        </button>
                        <button type="button" class="btn btn-success btn-approve-off" data-action="APPROVED">
                            <i class="fas fa-check me-1"></i> Setujui & Terapkan
                        </button>
                    </div>
                </form>
            @else
                <!-- Jika status BUKAN pending ATAU user TIDAK punya akses, otomatis ke sini -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-dark kembali">Close</button>
                </div>
            @endif
        @else
            {{-- ============================================================================================================================================= TAMBAH ADENDUM --}}
            <div class="card mb-4 border-danger">
                <div class="card-header bg-light text-dark fw-bold">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $new['status'] == 'edit-adendum' ? 'Perubahan' : 'Penambahan' }} Adendum atau Berita Acara
                </div>
                <div class="card-body">
                    @if (count($redaksiDiff) > 0)
                        <ul class="list-group list-group-flush small">
                            @foreach ($redaksiDiff as $diff)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <span class="fw-bold text-secondary">{{ $diff['label'] }} :</span>
                                        <i class="fas fa-long-arrow-alt-right text-success mx-2"></i>
                                        <span class="text-warning fw-bold">{{ $diff['menjadi'] }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-light text-muted mb-0 py-2 small">Tidak ada perubahan pada Redaksi Kontrak
                            Induk
                            / Adendum</div>
                    @endif
                </div>
            </div>

            <!-- 📊 2. TABEL MATRIX DINAMIS (Sesuai Jumlah Setting Harga) -->
            <h5 class="fw-bold text-uppercase mb-3"><i class="fas fa-cog me-2"></i>Setting Biaya</h5>

            @foreach ($matrixTables as $table)
                <!-- 1. JIKA PROYEK STATUSNYA DIHAPUS -->
                @if ($table['status_proyek'] === 'DIHAPUS')
                    <div class="card mb-4 border-danger">
                        <div
                            class="card-header bg-light text-danger fw-bold py-2 small d-flex justify-content-between align-items-center">
                            <span>📍 LOKASI : {{ $table['project_name'] }}</span>
                            <span class="badge text-danger fw-bold fs-xs"> LOKASI INI DIHAPUS / DIBUANG</span>
                        </div>
                        <div class="card-body py-2 text-muted small">
                            Setting untuk lokasi ini sudah dihapus.
                        </div>
                    </div>

                    <!-- 2. JIKA PROYEK BARU ATAU LAMA YANG DI-MAINTAIN -->
                @else
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-light fw-bold text-uppercase py-2 small">
                            📍 Lokasi Kerja: {{ $table['project_name'] }}
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <!-- Menggunakan class 'small' atau 'fs-xs' custom agar muat banyak kolom -->
                                <table
                                    class="table table-sm table-bordered table-striped table-hover align-middle mb-0 small text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="align-middle text-uppercase" style="width: 25%;">Jenis Biaya</th>
                                            @foreach ($table['headers'] as $unitHeader)
                                                <th style="font-size: 0.85rem;">{{ strtoupper($unitHeader) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($table['rows'] as $namaBiaya)
                                            <tr>
                                                <!-- Kolom Baris Jenis Tunjangan -->
                                                <td class="text-start fw-bold text-uppercase" style="font-size: 0.85rem;">
                                                    {{ $namaBiaya }}
                                                </td>

                                                <!-- Loop Nilai Matriks Per Unit Kerja -->
                                                @foreach ($table['headers'] as $namaUnit)
                                                    @php
                                                        $cell = $table['grid'][$namaBiaya][$namaUnit] ?? [
                                                            'lama' => 0,
                                                            'baru' => 0,
                                                            'is_changed' => false,
                                                        ];
                                                        $lamaFmt = number_format($cell['lama'], 0, ',', '.');
                                                        $baruFmt = number_format($cell['baru'], 0, ',', '.');
                                                    @endphp

                                                    <!-- Jika ada perubahan sel nilai, beri warna latar belakang (highlight) -->
                                                    <td class="p-1" style="min-width: 130px;">
                                                        @if ($cell['is_changed'] || $table['status_proyek'] === 'BARU')
                                                            @if ($cell['lama'] > 0)
                                                                <div class="text-danger text-decoration-line-through"
                                                                    style="font-size: 0.75rem;">
                                                                    Rp {{ $lamaFmt }}
                                                                </div>
                                                            @endif
                                                            <div class="text-success" style="font-size: 0.85rem;">
                                                                Rp {{ $baruFmt }}
                                                            </div>
                                                        @else
                                                            <span style="font-size: 0.85rem;">Rp
                                                                {{ $baruFmt }}</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($approval->status == 'PENDING' && auth()->user()->can('approval-kontrakinduk'))
                <form id="form-approval">
                    @csrf
                    <input type="hidden" name="status" id="approval-status" value="">
                    <input type="hidden" name="idreq" id="idreq" value="{{ $approval->token }}">

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-danger btn-new-adendum" data-action="REJECTED">
                            <i class="fas fa-times me-1"></i> Tolak
                        </button>
                        <button type="button" class="btn btn-success btn-new-adendum" data-action="APPROVED">
                            <i class="fas fa-check me-1"></i> Setujui & Terapkan
                        </button>
                    </div>
                </form>
            @else
                <!-- Jika status BUKAN pending ATAU user TIDAK punya akses, otomatis ke sini -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-dark kembali">Close</button>
                </div>
            @endif
        @endif

    </div>


@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/kontrakinduk/kontrakinduk-detail-approval.js') }}"></script>
@endpush
