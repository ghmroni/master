<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="{{ '/assets/' }}" data-template="vertical-menu-template">

<head>

    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>HRIS</title>

    <meta name="description" content="Human Resource Information System" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/logohr.png') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('/assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    @include('template/partials/styles')


    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            @include('template/partials/sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('template/partials/navbar')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        {{-- TOAST --}}
                        <div class="bs-toast toast toast-ex animate__animated my-2" role="alert" aria-live="assertive"
                            aria-atomic="true" data-bs-delay="2500"
                            style="background-color: lightblue; color: #000; border-color: #bce8f1;">
                            <div class="toast-header"
                                style="background-color: lightblue; color: #000; border-color: lightslategray;">
                                <i class="ti ti-bell ti-xs me-2 text-success"></i>
                                <div class="me-auto fw-medium toast-title">Berhasil</div>
                                <small
                                    class="text-muted">{{ Carbon\Carbon::now()->subMinute()->diffForHumans() }}</small>
                                <button type="button" class="btn-close" data-bs-dismiss="toast"
                                    aria-label="Close"></button>
                            </div>
                            <div class="toast-body">Berhasil</div>
                        </div>
                        {{-- END TOAST --}}

                        <div class="tourPage">

                            @yield('content')
                        </div>

                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                <div>
                                    © {{ date('Y') }} ❤️ R2N
                                    {{-- <a href="https://r2n.co.id/" target="_blank"
                                        class="footer-link text-primary fw-medium">R2N</a> --}}
                                </div>

                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>

    <form id="logout-form" action="{{ route('actionlogout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    @include('template/partials/scripts')
</body>

</html>
