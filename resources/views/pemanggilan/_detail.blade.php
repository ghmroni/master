@php
    use Carbon\Carbon;
@endphp
@can('edit-pemanggilan')
    <div class="modal-footer mb-2" id="modalFooter"></div>
    {{-- <div class="modal-footer mb-2">
        @if ($status == 'PENDING')
            <div class="btn-group mt-0">
                @if ($tanggal && Carbon::parse($tanggal)->isToday() && $total > 0)
                    <button class="btn btn-dark btn-sm cetakAbsensi" data-tgl="{{ $tanggal }}"
                        data-user="{{ $user }}">
                        <i class="ti ti-printer me-1"></i>Print Absensi
                    </button>
                    <button class="btn btn-info btn-sm mulaiTest text-black" data-tgl="{{ $tanggal }}"
                        data-user="{{ $user }}">
                        <i class="ti ti-palette me-1"></i>Mulai Test
                    </button>
                @endif
                @if ($tanggal && Carbon::parse($tanggal)->greaterThanOrEqualTo(Carbon::today()))
                    <button class="btn btn-warning btn-sm text-black editTanggalAll" data-tgl="{{ $tanggal }}"
                        data-user="{{ $user }}">
                        <i class="ti ti-calendar me-1"></i>Edit Tanggal Panggil (semua)
                    </button>
                @endif
            </div>
        @endif

        @if ($status == 'PROSES')
            <div class="btn-group" role="group">
                <button id="btnNilai" type="button" class="btn btn-info btn-sm dropdown-toggle text-black"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ti ti-receipt me-1"></i>
                    Input Nilai
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <a class="dropdown-item" href="javascript:void(0);">Psikotes</a>
                    <a class="dropdown-item" href="javascript:void(0);">Wawancara</a>
                </div>
            </div>
        @endif
    </div> --}}
@endcan
<h5 class="text-muted mt-0 mb-0 text-center">{{ Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</h5>
<div id="keterangan"></div>


<div class="table-responsive">
    <table id="tabelDetail" class="table table-hover table-bordered" style="width:100%;font-size:12px">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama Pelamar</th>
                <th>Project</th>
                <th>Posisi</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>
