@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <div class="card card-body border border-dark">
                <div class="user-avatar-section">
                    <div class="d-flex align-items-center flex-column">
                        {{-- <div class="col-sm-6 col-lg-4"> --}}
                        <div class="imagebox mb-3">
                            <a href="{{ auth()->user()->image == null ? '/assets/img/avatars/no-image.png' : '/storage/hris/profile/' . auth()->user()->image }}"
                                class="glightbox" data-glightbox="type: image; zoomable: true;">
                                <img src="" class="img-fluid rounded mb-3 pt-1 mt-4" alt="image" id="foto"
                                    height="100">
                            </a>
                            <div class="caption-content">
                                <p>{{ auth()->user()->name }}</p>
                            </div>
                        </div>
                        {{-- </div> --}}

                        {{-- <img class="img-fluid rounded mb-3 pt-1 mt-4" id="foto" src="" height="100"
                            width="100" alt="Foto Profile" /> --}}

                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                            <form id="upload_foto">
                                @csrf
                                <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0" title="Upload Foto">
                                    <i class="tf-icons fa fa-cloud-upload me-1"></i>
                                    <span class="d-none d-sm-block">Upload</span>
                                    <input type="file" id="upload" name="foto" class="account-file-input" hidden
                                        accept="image/png, image/jpeg" />
                                </label>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active buttonAccount" role="tab" data-bs-toggle="tab"
                            data-bs-target="#account" aria-controls="account" aria-selected="true">
                            <i class="tf-icons ti ti-user me-2"></i> Account
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btnsScurity" role="tab" data-bs-toggle="tab"
                            data-bs-target="#security" aria-controls="security" aria-selected="false">
                            <i class="tf-icons ti ti-lock me-2"></i> Security
                        </button>
                    </li>
                </ul>
                <div class="tab-content border border-dark">
                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                        <form id="formAccount" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="hidden" class="form-control" id="id" name="id">
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Nama Lengkap" required>

                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">
                                        <i class="ti ti-crown"></i></span>
                                    <input type="text" class="form-control" id="level" readonly />
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3"><i
                                    class="fa fa-save me-2"></i>Simpan</button>
                        </form>

                    </div>
                    <div class="tab-pane fade" id="security" role="tabpanel">

                        <form id="formPass">
                            @csrf
                            <div class="row mx-auto">
                                <div class="col-md-12 mb-3">
                                    <div class="form-password-toggle">
                                        <label class="form-label" for="oldPass">Password Lama</label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="password" id="oldPass" name="oldPass"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                aria-describedby="multicol-password2" />
                                            <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                                    class="ti ti-eye-off"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-password-toggle">
                                        <label class="form-label" for="password">Password Baru</label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="password" id="password" name="password"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                aria-describedby="multicol-password2" />
                                            <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                                    class="ti ti-eye-off"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-password-toggle">
                                        <label class="form-label" for="confirmed">Confirm Password</label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="password" id="confirmed" name="confirmed"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                aria-describedby="multicol-confirm-password2" />
                                            <span class="input-group-text cursor-pointer"
                                                id="multicol-confirm-password2"><i class="ti ti-eye-off"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <button class="btn btn-primary" type="submit" id="resetPassword">
                                        <i class="fa fa-save me-2"></i>Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/profile/profile.js') }}"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endpush
