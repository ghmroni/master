@extends('template.core')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableGaji" class="table table-hover table-striped text-nowrap"
                            style="width: 100%;font-size: 12px">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Periode Penggajian</th>
                                    <th>Project</th>
                                    <th>Tanggal Proses</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/gaji/pengajuan-gaji.js') }}"></script>
@endpush
