@extends('template.core')

@push('extra_style')
    <style>
        /* Membuat latar belakang penampung teks menjadi transparan */
        .glightbox-container .gslide-description {
            background: transparent !important;
        }

        /* Mengubah warna teks judul menjadi putih agar tetap terbaca di latar gelap */
        .glightbox-container .gslide-title {
            color: #ffffff !important;
        }

        /* Mengubah warna teks deskripsi menjadi putih terang atau abu-abu transparan */
        .glightbox-container .gslide-desc {
            color: #e0e0e0 !important;
        }

        .glightbox-container .gslide-title {
            color: #ffffff !important;
            text-align: center !important;
            margin-bottom: 10px !important;
            /* Jarak antara judul dan deskripsi */
        }
    </style>
@endpush

@section('content')
    <div class="card card-body">
        @can('monitoring-create')
            <div class="modal-footer">
                <button class="btn btn-sm btn-primary tambahMonitoring"><i class="fas fa-plus me-1"></i>Monitoring</button>
            </div>
        @endcan
        <div class="table-responsive">
            <table id="tableAbsensi" class="table table-bordered table-hover table-xs text-nowrap"
                style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr>
                        <th>NO</th>
                        <th>NIP</th>
                        <th>NAMA LENGKAP</th>
                        <th>LOKASI PENEMPATAN</th>
                        <th>KETERANGAN</th>
                        <th>PHOTO</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/monitoring/realtime.js') }}"></script>
@endpush
