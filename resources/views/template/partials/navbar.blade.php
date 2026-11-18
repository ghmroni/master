<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme "
    id="layout-navbar">

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center tourJudul">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="d-none d-md-inline-block nav-item nav-link d-flex align-items-center px-0"
                    href="javascript:void(0)">
                    <i class="ti ti-link ti-md me-2"></i>
                    <span class="d-none d-md-inline-block">{{ $judul ?? 'Judul Halaman' }}</span>
                </a>
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Tour -->
            <li class="nav-item dropdown-language dropdown me-2 me-xl-0" data-bs-toggle="tooltip"
                data-bs-placement="bottom" title="Tour">
                <a class="d-none d-md-inline-block nav-link dropdown-toggle hide-arrow" id="mulaiTour"
                    href="javascript:void(0);">
                    <i class="ti ti-rocket rounded-circle ti-md"></i>
                </a>
            </li>
            <!--/ Tour -->

            <!-- Style Switcher -->
            <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0 tourTampilan">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="ti ti-md"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-styles animate__animated animate__flipInY">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                            <span class="align-middle"><i class="ti ti-sun me-2"></i>Light</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                            <span class="align-middle"><i class="ti ti-moon me-2"></i>Dark</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                            <span class="align-middle"><i class="ti ti-device-desktop me-2"></i>System</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!-- / Style Switcher-->


            <!-- Notification -->
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1 tourNotifikasi">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false">
                    <i class="ti ti-bell ti-md"></i>
                    @if (count(auth()->user()->unreadNotifications) > 0)
                        <span
                            class="badge bg-danger rounded-pill badge-notifications">{{ count(auth()->user()->unreadNotifications) }}</span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0 animate__animated animate__flipInX loading-notifikasi">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">
                                <i class="ti ti-bell me-2 text-warning ti-xl"></i>
                                Notifikasi
                            </h5>
                            {{-- <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">
                                <i class="ti ti-mail-opened fs-4"></i>
                            </a> --}}
                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container">
                        <ul class="list-group list-group-flush">
                            @foreach (auth()->user()->unreadNotifications as $notification)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item "
                                    id="notif-{{ $notification->id }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <span class="avatar-initial rounded-circle bg-label-warning">
                                                    <i class="ti ti-bell "></i></span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1"
                                            onclick="window.location='{{ route('notif.asRead', $notification->id) }}'">
                                            <h6 class="mb-1 fw-bold">{{ $notification->data[0]['judul'] }}</h6>
                                            <p class="mb-0 text-justify">{!! $notification->data[0]['pesan'] !!}</p>

                                            <small
                                                class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>

                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="javascript:void(0)"
                                                class="dropdown-notifications-archive hapusNotifikasi"
                                                data-id="{{ $notification->id }}">
                                                <i class="ti ti-trash text-danger"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="dropdown-menu-footer border-top markAllAsReadFooter">
                        @if (count(auth()->user()->unreadNotifications) > 0)
                            <a href="javascript:void(0)"
                                class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center markAllAsRead">
                                <i class="ti ti-mail-opened fs-4 me-1"></i>Tandai semua dibaca
                            </a>
                        @else
                            <span
                                class="d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                                Oops! Tidak ada notifikasi</span>
                        @endif
                    </li>
                </ul>
            </li>
            <!--/ Notification -->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown tourProfile">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ auth()->user()->image == null ? '/assets/img/avatars/face.png' : '/storage/hris/profile/' . auth()->user()->image }}"
                            alt="User" class="h-auto rounded-circle" />

                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end animate__animated animate__flipInY">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ strtoupper(auth()->user()->name) }}</span>
                                    <small class="text-muted">{{ auth()->user()->roles[0]['name'] ?? '' }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/profile">
                            <i class="ti ti-user-check me-2 ti-sm text-success"></i>
                            <span class="align-middle">Profile</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="ti ti-logout me-2 ti-sm text-danger"></i>
                            <span class="align-middle">Keluar</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
    {{-- <div id="import-progressbar" style="display: none">
        <div class="progress" style="position:fixed;top:0;left:0;width:100%;z-index:9999;">
            <div id="import-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                style="width: 0%" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <label class="labelProgress text-dark">0%</label>
            </div>
        </div>
    </div> --}}

</nav>
