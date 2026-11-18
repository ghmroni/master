@extends('template.core')

@php
    use Carbon\Carbon;
    use Firebase\JWT\JWT;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12 order-0 order-md-1">
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
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-keluarga" aria-controls="navs-keluarga" aria-selected="false">
                            Keluarga
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link " role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pendidikan" aria-controls="navs-pendidikan" aria-selected="false">
                            Pendidikan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnRiwayat"
                            data-token="{{ JWT::encode(['id' => $pelamar->id_pelamar], config('jwt.secret'), 'HS256') }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-riwayat" aria-controls="navs-riwayat"
                            aria-selected="false">
                            Riwayat
                        </button>
                    </li>
                </ul>
                <div class="tab-content shadow">
                    <div class="tab-pane fade show active" id="data-diri" role="tabpanel">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor KK</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->no_kk ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nama Lengkap</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->nm_lengkap ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor KTP</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->no_ktp ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Tempat Lahir</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->tempat_lahir ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor Handphone</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->no_handphone ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Tanggal Lahir</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ Carbon::parse($pelamar->tgl_lahir)->format('d-m-Y') ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Jenis Kelamin</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->jenis_kelamin == 1 ? 'LAKI - LAKI' : 'PEREMPUAN' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Alamat Email</label>
                                    <input type="text" class="form-control"
                                        value="{{ strtolower($pelamar->email) ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Status Perkawinan</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->status_perkawinan == 1 ? 'Belum Kawin' : 'Kawin' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Agama</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ strtolower($pelamar->nmagama) ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Alamat Sesuai KTP</label>
                                    <textarea class="form-control text-uppercase" rows="6" readonly>{{ $pelamar->alamat_ktp ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Alamat Sesuai Domisili</label>
                                    <textarea class="form-control text-uppercase" rows="6" readonly>{{ $pelamar->alamat_domisili ?? '' }}</textarea>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Tinggi Badan (cm)</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->tinggi_badan ?? null }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Berat Badan (cm)</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->berat_badan ?? null }}" readonly>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-muted">Ukuran Baju</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->ukuran_baju ?? null }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor Celana</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->ukuran_celana ?? null }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted">Nomor Sepatu</label>
                                    <input type="text" class="form-control text-uppercase"
                                        value="{{ $pelamar->nomor_sepatu ?? null }}" readonly>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- ========================================================================= KELUARGA ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-keluarga" role="tabpanel">
                        <table class="table table-hover table-striped table-bordered nowrap"
                            style="width: 100%;font-size:12px">
                            <thead class="table-light">
                                <tr>
                                    <th>Status Keluarga</th>
                                    <th>Nomor KTP</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tempat Lahir</th>
                                    <th>Tanggal Lahir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datakeluarga as $item)
                                    <tr>
                                        <th>{{ strtoupper($item['urutan']) }}</th>
                                        <td class="text-center">{{ $item['no_ktp'] }}</td>
                                        <td>{{ $item['nama'] }}</td>
                                        <td class="text-center">
                                            {{ $item['jenis_kelamin'] != '' ? ($item['jenis_kelamin'] == 1 ? 'LAKI - LAKI' : 'PEREMPUAN') : '' }}
                                        </td>
                                        <td class="text-center">{{ $item['tempat_lahir'] }}</td>
                                        <td class="text-center">{{ $item['tanggal_lahir'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- ========================================================================= PENDIDIKAN ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-pendidikan" role="tabpanel">
                        <table class="table table-hover table-striped table-bordered nowrap"
                            style="width: 100%;font-size:12px">
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
                                @foreach ($datasekolah as $item)
                                    <tr>
                                        <th>{{ $item['jenjang'] }}</th>
                                        <td>{{ $item['sekolah'] }}</td>
                                        <td>{{ $item['jurusan'] }}</td>
                                        <td class="text-center">{{ $item['tahun'] }}</td>
                                        <td class="text-center">{{ $item['ipk'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- ========================================================================= RIWAYAT ========================================================================= --}}
                    <div class="tab-pane fade" id="navs-riwayat" role="tabpanel">
                        <div class="accordion mt-3" id="accordionExample">
                            <div id="dtRiwayat">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/external/pelamar/detailpelamar.js') }}"></script>
@endpush
