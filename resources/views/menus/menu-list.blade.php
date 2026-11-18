@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="col-md mb-4 mb-md-2">
        <div class="accordion mt-3" id="accordionExample">
            @can('tambah-menu')
                <div class="modal-footer mt-1">
                    <button class="btn btn-dark btn-sm AddMenu"><i class="fas fa-plus-circle me-1"></i>Tambah
                        Menu</button>
                </div>
            @endcan
            {{-- ============================================================================================================================ MENU --}}
            <div class="divider divider-primary">
                <div class="divider-text">MENU UTAMA</div>
            </div>
            <div class="card accordion-item shadow bg-transparent border border-primary">
                <h2 class="accordion-header" id="headingOne">
                    <button type="button" class="accordion-button collapsed text-uppercase btn-label-primary"
                        data-bs-toggle="collapse" data-bs-target="#accordionOne" aria-expanded="true"
                        aria-controls="accordionOne">
                        Menu Utama
                    </button>
                </h2>

                <div id="accordionOne" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
                    <div class="accordion-body">

                        <div class="table-responsive mt-3">
                            <table id="tableMenu" class="table table-hover table-striped table-bordered nowrap"
                                style="width: 100%;font-size: 12px">
                                <thead class="table-light">
                                    <tr>
                                        <th>Icon</th>
                                        <th>Nama Menu</th>
                                        <th>Sort</th>
                                        <th>Url</th>
                                        <th>Permissions</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ============================================================================================================================ SUB MENU --}}
            <div class="divider divider-warning">
                <div class="divider-text">MENU SUB</div>
            </div>
            <div class="card accordion-item shadow bg-transparent border border-warning">
                <h2 class="accordion-header" id="headingTwo">
                    <button type="button" class="accordion-button collapsed text-uppercase btnTwo btn-label-warning"
                        data-bs-toggle="collapse" data-bs-target="#accordionTwo" aria-expanded="false"
                        aria-controls="accordionTwo">
                        Menu Sub
                    </button>
                </h2>
                <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="table-responsive mt-3">
                            <table id="tableSubMenu" class="table table-hover table-striped table-bordered nowrap"
                                style="width: 100%;font-size: 12px">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Menu</th>
                                        <th>Sort</th>
                                        <th>Url</th>
                                        <th>Permissions</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalMenu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formMenu">
                    <div class="modal-body">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label>Nama Menu</label>
                                <input type="hidden" id="idmenu" name="idmenu" class="form-control" readonly>
                                <input type="text" id="namamenu" name="namamenu" class="form-control text-uppercase"
                                    autocomplete="off" placeholder="Nama Menu">
                                <div class="form-check mt-1">
                                    <label class="form-check-label fw-medium text-primary">
                                        <input type="checkbox" class="form-check-input" value="1" id="utama"
                                            name="utama" />
                                        Jadikan menu utama
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label>Url</label>
                                <input type="text" id="url" name="url" class="form-control" autocomplete="off"
                                    placeholder="url">
                            </div>
                            <div class="col-md-12">
                                <label for="select2Icons" class="form-label">Icon</label>
                                <select id="select2Icons" name="icon" class="select2-icons form-select" required>
                                    <option value="ti ti-home" data-icon="ti ti-home">Home</option>
                                    <option value="ti ti-settings" data-icon="ti ti-settings">Setting</option>
                                    <option value="fas fa-user-plus" data-icon="fas fa-user-plus">User</option>
                                    <option value="ti ti-user" data-icon="ti ti-user">User</option>
                                    <option value="ti ti-users" data-icon="ti ti-users">Users</option>
                                    <option value="fas fa-database" data-icon="fas fa-database">Server</option>
                                    <option value="ti ti-printer" data-icon="ti ti-printer">Print</option>
                                    <option value="ti ti-reload" data-icon="ti ti-reload">reload</option>
                                    <option value="ti ti-pencil" data-icon="ti ti-pencil">pencil</option>
                                    <option value="ti ti-world" data-icon="ti ti-world">world</option>
                                    <option value="ti ti-map" data-icon="ti ti-map">map</option>
                                    <option value="fas fa-street-view" data-icon="fas fa-street-view">Street
                                    </option>
                                    <option value="ti ti-shield" data-icon="ti ti-shield">Shield</option>
                                    <option value="ti ti-package" data-icon="ti ti-package">package</option>
                                    <option value="ti ti-menu" data-icon="ti ti-menu">menu</option>
                                    <option value="ti ti-id-badge" data-icon="ti ti-id-badge">id-badge</option>
                                    <option value="ti ti-file" data-icon="ti ti-file">file</option>
                                    <option value="far fa-file-alt" data-icon="far fa-file-alt">file List</option>
                                    <option value="ti ti-dashboard" data-icon="ti ti-dashboard">dashboard</option>
                                    <option value="fas fa-desktop" data-icon="fas fa-desktop">Desktop</option>
                                    <option value="ti ti-cloud-up" data-icon="ti ti-cloud-up">cloud-up</option>
                                    <option value="ti ti-cloud-down" data-icon="ti ti-cloud-down">cloud-down
                                    </option>
                                    <option value="ti ti-upload" data-icon="ti ti-upload">upload</option>
                                    <option value="ti ti-layout" data-icon="ti ti-layout">layout</option>
                                    <option value="ti ti-heart" data-icon="ti ti-heart">heart</option>
                                    <option value="ti ti-heart-broken" data-icon="ti ti-heart-broken">heart broken
                                    </option>
                                    <option value="ti ti-download" data-icon="ti ti-download">download</option>
                                    <option value="far fa-images" data-icon="far fa-images">gallery</option>
                                    <option value="far fa-image" data-icon="far fa-image">image</option>
                                    <option value="ti ti-clipboard" data-icon="ti ti-clipboard">clipboard</option>
                                    <option value="ti ti-calendar" data-icon="ti ti-calendar">calendar</option>
                                    <option value="ti ti-book" data-icon="ti ti-book">book</option>
                                    <option value="ti ti-receipt" data-icon="ti ti-receipt">receipt</option>
                                    <option value="ti ti-credit-card" data-icon="ti ti-credit-card">credit-card
                                    </option>
                                    <option value="ti ti-wallet" data-icon="ti ti-wallet">wallet</option>
                                    <option value="ti ti-folder" data-icon="ti ti-folder">folder</option>
                                    <option value="fas fa-folder-open" data-icon="fas fa-folder-open">folder open
                                    </option>
                                    <option value="ti ti-list" data-icon="ti ti-list">list</option>
                                    <option value="far fa-list-alt" data-icon="far fa-list-alt">list</option>
                                    <option value="fas fa-chart-bar" data-icon="fas fa-chart-bar">Chart</option>
                                    <option value="fas fa-umbrella" data-icon="fas fa-umbrella">Umbrella</option>
                                    <option value="fas fa-university" data-icon="fas fa-university">University
                                    </option>
                                    <option value="ti ti-ticket" data-icon="ti ti-ticket">Ticket</option>
                                    <option value="ti ti-lock" data-icon="ti ti-lock">lock</option>
                                    <option value="ti ti-mail" data-icon="ti ti-mail">email</option>
                                    <option value="fas fa-plane" data-icon="fas fa-plane">Plane</option>
                                    <option value="ti ti-target" data-icon="ti ti-target">target</option>
                                    <option value="ti ti-link" data-icon="ti ti-link">Link</option>
                                    <option value="fas fa-qrcode" data-icon="fas fa-qrcode">QrCode</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label>Sort</label>
                                <input type="text" id="sort" name="sort" class="form-control numberOnly"
                                    value="0" maxlength="2" autocomplete="off">
                            </div>
                            <div class="col-md-12">
                                <label>Permissions</label><br>
                                <select id="permissionsid" name="permissionsid[]" class="select2 form-select" multiple>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label>Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="ENABLE">AKTIF</option>
                                    <option value="DISABLE">TIDAK AKTIF</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalSub" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formSub">
                    <div class="modal-body">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label>Nama Menu Utama</label>
                                <input type="hidden" id="idsub" name="idsub" class="form-control" readonly>
                                <select id="menuid" name="menuid" class="select2 form-select"
                                    data-allow-clear="true">
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label>Nama Submenu</label>
                                <input type="text" id="namasub" name="namasub" class="form-control text-uppercase"
                                    autocomplete="off" placeholder="Nama Submenu">
                            </div>
                            <div class="col-md-12">
                                <label>Url</label>
                                <input type="text" id="url" name="url" class="form-control"
                                    autocomplete="off" placeholder="url">
                            </div>

                            <div class="col-md-12">
                                <label>Sort</label>
                                <input type="text" id="sort" name="sort" class="form-control numberOnly"
                                    value="0" maxlength="2" autocomplete="off">
                            </div>
                            <div class="col-md-12">
                                <label>Permissions</label><br>
                                <select id="idpermissions" name="idpermissions[]" class="select2 form-select" multiple>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label>Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="ENABLE">AKTIF</option>
                                    <option value="DISABLE">TIDAK AKTIF</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/menus/menu-list.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endpush
