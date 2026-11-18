@extends('template.core')

@section('content')
    <div class="card card-body">
        <div class="table-responsive">
            <table id="tabelPelamar" class="table table-hover table-bordered nowrap" style="width: 100%;font-size: 12px">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Status</th>
                        <th>Nama Lengkap</th>
                        <th>Nomor KTP</th>
                        <th>Email</th>
                        <th>Pendidikan</th>
                        <th>Usia</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal History --}}
    <div class="modal fade" id="modalHistory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md mb-4 mb-md-2">
                            <div class="accordion mt-3" id="accordionRiwayat">

                                <div id="listRiwayat"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/pelamar/show-pelamar.js') }}"></script>
@endpush
