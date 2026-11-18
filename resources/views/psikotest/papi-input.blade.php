@extends('template.core')

@php
    use Carbon\Carbon;
@endphp
@section('content')
    <form id="papiForm">
        @csrf
        <div class="card border shadow rounded">
            <div class="card-header bg-label-dark mb-3">
                <div class="col-md-12">
                    <div class="row align-items-center">
                        <div class="col-md-2 d-flex justify-content-center align-items-center">
                            <img src="{{ asset('assets/img/favicon/test.gif') }}" alt="Card image"
                                style="height:150px; width:auto;" />
                        </div>

                        <div class="col-md-10">
                            <div class="card-body">
                                <h5 class="card-title mb-2">{{ $peserta->nama }}</h5>
                                <span>
                                    {{ $peserta->jenis_kelamin == 1 ? 'LAKI - LAKI' : 'PEREMPUAN' }}</span><br>
                                <label>Tanggal Lahir :
                                    {{ Carbon::parse($peserta->tgllahir)->translatedFormat('d F Y') }}</label><br>
                                <label>Tanggal Test :
                                    {{ Carbon::parse($pemanggilan->tgl_pemanggilan)->translatedFormat('d F Y') }}</label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" name="idpel" value="{{ $peserta->id_pelamar }}" readonly>
                    <input type="hidden" class="form-control" name="nomor"
                        value="{{ Str::replace(['INVITE-', 'RESEND-'], '', $pemanggilan->no_registrasi) }}" readonly>
                    <input type="hidden" class="form-control" name="nama" value="{{ $peserta->nama }}" readonly>
                    <input type="hidden" class="form-control" name="kelamin"
                        value="{{ $peserta->jenis_kelamin == 1 ? 'LAKI - LAKI' : 'PEREMPUAN' }}" readonly>
                    <input type="hidden" class="form-control" name="tgllahir" value="{{ $peserta->tgllahir }}" readonly>
                    <input type="hidden" class="form-control" name="tgltest" value="{{ $pemanggilan->tgl_panggil }}"
                        readonly>
                </div>
                <div class="col-md-12 mb-0">
                    <center>
                        <small class="text-danger">Silahkan menggunakan <strong>Arah Panah Atas</strong> (<i
                                class="fa fa-long-arrow-up"></i>) <strong>Arah Panah Bawah</strong> (<i
                                class="fa fa-long-arrow-down"></i>) atau <strong>Enter</strong> (&#8617) untuk melakukan
                            perpindahan</small>
                    </center>
                </div>
            </div>

            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(9, 1fr); gap: 15px; font-family: sans-serif;"
                    class="mb-3">
                    @for ($box = 0; $box < 9; $box++)
                        <!-- Wrapper untuk setiap kolom (berisi maksimal 10 baris ke bawah) -->
                        <div class="kolom" style="display: flex; flex-direction: column; gap: 8px;">
                            @for ($i = 1; $i <= 10; $i++)
                                @php $nomor = $box * 10 + $i; @endphp
                                <div class="input-box" style="display: flex; align-items: center; gap: 5px;">
                                    <!-- Sesuaikan 'nomor_soal' dengan nama kolom di database Anda -->
                                    <label style="font-size: 14px; min-width: 30px;" class="fw-bold">
                                        {{ $nomor }}.
                                    </label>
                                    <input type="text" class="text-uppercase text-center jawaban-papi fw-bold"
                                        name="jawaban[{{ $nomor }}]" data-soal-id="{{ $nomor }}"
                                        style="width: 50px; text-align: center;" maxlength="2" pattern="[A-Ba-b]"
                                        autocomplete="off">
                                </div>
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="accordion mt-3" id="accordionExample">
            <div class="card accordion-item border shadow rounded">
                <h2 class="accordion-header" id="headingOne">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                        NILAI
                    </button>
                </h2>

                <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="card-body d-flex justify-content-center mb-0">
                            <div class="col-md-8">
                                <table class="table table-bordered" style="width: 100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="45%" class="fw-bold">TOTAL ATAS</th>
                                            <th width="10%" class="fw-bold text-center" id="totalAtas">0</th>
                                            <td><i class="text-muted">(Harus 45)</i></td>
                                        </tr>
                                        <tr>
                                            <th class="fw-bold">TOTAL BAWAH</th>
                                            <th class="fw-bold text-center" id="totalBawah">0</th>
                                            <td><i class="text-muted">(Harus 45)</i></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="hasil_atas" id="hasil_atas" class="form-control" readonly>
            <input type="hidden" name="hasil_bawah" id="hasil_bawah" class="form-control" readonly>
            <input type="hidden" name="hasil_faktor" id="hasil_faktor" class="form-control" readonly>

            <div class="card accordion-item border shadow rounded">
                <h2 class="accordion-header" id="headingTwo">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                        data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">
                        ANALISIS
                    </button>
                </h2>
                <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="table table-sm table-bordered" style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr>
                                    <th>Bidang</th>
                                    <th>Aspek</th>
                                    <th>Faktor</th>
                                    <th>Nilai</th>
                                    <th>Analisis</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $currentBidang = '';
                                @endphp

                                @foreach ($norma as $factorCode => $items)
                                    @php
                                        $row = $items->first();
                                    @endphp

                                    <tr>
                                        @if ($row->bidang !== $currentBidang)
                                            @php
                                                $currentBidang = $row->bidang;
                                                $rowspan = $norma
                                                    ->filter(function ($g) use ($currentBidang) {
                                                        return $g->first()->bidang === $currentBidang;
                                                    })
                                                    ->count();
                                            @endphp
                                            <td rowspan="{{ $rowspan }}">
                                                {{ ucwords(strtolower($row->bidang)) }}
                                            </td>
                                        @endif
                                        <td>{{ ucfirst(strtolower($row->aspek)) }}</td>
                                        <td class="text-center fw-bold">{{ $factorCode }}</td>
                                        <td class="text-center fw-bold" id="skor-{{ $factorCode }}">0</td>
                                        <td id="analisis-{{ $factorCode }}">{{ $row->analisis }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer mt-3">
            <button type="submit" class="btn btn-success"><i class="ti ti-cloud-up me-1"></i>Simpan Hasil</button>
        </div>
    </form>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/psikotest/papi-input.js') }}"></script>
@endpush
