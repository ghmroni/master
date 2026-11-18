@extends('template.core')
@push('extra_style')
    <style>
        .sticky-element {
            position: sticky;
            top: 79px;
            z-index: 8;

            .window-scrolled & {
                inset-block-start: 0;
            }

            .layout-navbar-fixed & {
                inset-block-start: calc($navbar-height - .4375rem);
            }

            .layout-menu-fixed .layout-horizontal & {
                inset-block-start: calc($navbar-height * 2 - .25rem);
            }

            .layout-menu-fixed.layout-navbar-fixed .layout-horizontal & {
                inset-block-start: $navbar-height;
            }
        }

        td.ceklisPanggil {
            cursor: pointer !important;
        }

        td.ceklisPanggil:hover {
            /* background-color: #E68F3C; */
            background-color: #FFF1E3;
            /* contoh warna highlight */
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <form id="formPanggil">
                @csrf
                <div class="card">
                    <div class="card-header sticky-element bg-label-primary">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Tanggal</label>
                                <input type="text" name="tglpanggil" id="tglpanggil" required class="form-control"
                                    placeholder="YYYY-MM-DD">
                                <div id="errorMsg" class="invalid-feedback">
                                    Tanggal pemanggilan wajib diisi!
                                </div>

                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Project</label>
                                <select name="project" id="project" required class="select2 form-control">
                                    <option value=""></option>
                                    @foreach ($project as $id => $nmbagian)
                                        <option value="{{ $id }}">{{ $nmbagian }}</option>
                                    @endforeach
                                </select>
                                <div id="errorMsg" class="invalid-feedback">
                                    Project masih kosong.
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Area</label>
                                <select name="area" id="area" required class="select2 form-control">
                                    <option value=""></option>
                                </select>
                                <div id="errorMsg" class="invalid-feedback">
                                    Area masih kosong.
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Lokasi</label>
                                <select name="lokasi" id="lokasi" required class="select2 form-control">
                                    <option value=""></option>
                                </select>
                                <div id="errorMsg" class="invalid-feedback">
                                    Lokasi masih kosong.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="col-md-2 text-md-center me-2">
                                <span id="totalDipilih" class="badge bg-label-warning">0 dipilih</span>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary"><i
                                            class="far fa-calendar-plus me-1"></i>Proses
                                        Panggil</button>
                                </div>
                            </div>

                        </div>
                        <div class="row mt-2 mb-0">
                            <div class="col-12 text-md-center">
                                <div id="errorChecklist" style="display: none">
                                    <div class="alert alert-danger alert-dismissible mb-0" role="alert">
                                        Silakan pilih pelamar untuk dipanggil.
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="table-responsive">
                                <table id="tabelPemanggilan" class="table table-hover table-bordered text-nowrap"
                                    style="width: 100%;font-size: 12px">
                                    <thead class="table-light">
                                        <tr>
                                            <th>
                                                {{-- pilih --}}
                                                <div class="form-check-primary">
                                                    <input type="checkbox" id="select-allpanggil" class="form-check-input">
                                                </div>
                                            </th>
                                            <th>Status</th>
                                            <th>Nomor KTP</th>
                                            <th>Nama Lengkap</th>
                                            <th>Posisi</th>
                                            <th>Tinggi</th>
                                            <th>Pendidikan</th>
                                            <th>Nama Sekolah</th>
                                            <th>Jurusan</th>
                                            <th>Tgl Lamaran</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/external/rect/pemanggilan.js') }}"></script>
@endpush
