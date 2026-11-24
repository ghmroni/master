<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-wide" dir="ltr" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="front-pages">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Upgrade Aplikasi</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/tabler-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/rtl/core.css"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/rtl/theme-default.css"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/css/demo.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/pages/front-page.css" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/node-waves/node-waves.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/pages/front-page-pricing.css" />

    <!-- Helpers -->
    <script src="{{ asset('') }}assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('') }}assets/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('') }}assets/js/front-config.js"></script>
</head>

<body>
    <script src="{{ asset('') }}assets/vendor/js/dropdown-hover.js"></script>
    <script src="{{ asset('') }}assets/vendor/js/mega-dropdown.js"></script>

    <!-- Navbar: Start -->
    <nav class="layout-navbar shadow-none py-0">
        <div class="container">
            <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-4">
                <!-- Menu logo wrapper: Start -->
                <div class="navbar-brand app-brand demo d-flex py-0 py-lg-2 me-4">
                    <!-- Mobile menu toggle: Start-->
                    <button class="navbar-toggler border-0 px-0 me-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="ti ti-menu-2 ti-sm align-middle"></i>
                    </button>
                    <!-- Mobile menu toggle: End-->
                    <a href="javascript:void(0)" class="app-brand-link">
                        <img src="{{ asset('assets/img/favicon/logohr.png') }}" alt="Logo" width="41px"
                            class="me-2">

                        <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">HRIS</span>
                    </a>
                </div>
                <!-- Menu logo wrapper: End -->

                <div class="landing-menu-overlay d-lg-none"></div>
                <!-- Menu wrapper: End -->
                <!-- Toolbar: Start -->
                <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <!-- Style Switcher -->
                    <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <i class="ti ti-sm"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
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

                    <!-- navbar button: Start -->
                    <li>
                        <a href="/" class="btn btn-primary"><span
                                class="tf-icons ti ti-login scaleX-n1-rtl me-md-1"></span><span
                                class="d-none d-md-block">Kembali ke Beranda</span></a>
                    </li>
                    <!-- navbar button: End -->
                </ul>
                <!-- Toolbar: End -->
            </div>
        </div>
    </nav>
    <!-- Navbar: End -->

    <!-- Sections:Start -->
    <!-- Pricing Plans -->
    <section class="section-py first-section-pt">
        <section class="pricing-free-trial bg-label-danger">
            <div class="container">
                <div class="position-relative">
                    <div
                        class="d-flex justify-content-between flex-column-reverse flex-lg-row align-items-center py-4 px-5">
                        <div class="text-center text-lg-start mt-2 ms-3">
                            <h3 class="text-danger mb-1">Oops! Masa aktif penggunaan aplikasi telah berakhir.</h3>
                            {{-- <p class="text-body mb-1">You will get full access to with all the features for enjoy.</p> --}}
                        </div>

                    </div>
                </div>
            </div>
        </section>
        <div class="container">
            <h2 class="text-center mb-2">Pricing Plans</h2>
            <p class="text-center">
                Get started with us - it's perfect for individuals and teams. Choose a subscription plan that meets your
                needs.
            </p>
            <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 pb-5 pt-3 mb-0 mb-md-4">
                <label class="switch switch-primary ms-3 ms-sm-0 mt-2">
                    <span class="switch-label">Monthly</span>
                    <input type="checkbox" class="switch-input price-duration-toggler" checked />
                    <span class="switch-toggle-slider">
                        <span class="switch-on"></span>
                        <span class="switch-off"></span>
                    </span>
                    <span class="switch-label">Annual</span>
                </label>
                <div class="mt-n5 ms-n5 d-none d-sm-block">
                    <i class="ti ti-corner-left-down ti-sm text-muted me-1 scaleX-n1-rtl"></i>
                    <span class="badge badge-sm bg-label-primary">Save up to 10%</span>
                </div>
            </div>

            <div class="row mx-0 gy-3 px-lg-5">
                <!-- Basic -->
                <div class="col-lg mb-md-0 mb-4">
                    <div class="card border rounded shadow-none">
                        <div class="card-body">
                            <div class="my-3 pt-2 text-center">
                                <img src="{{ asset('') }}assets/img/illustrations/page-pricing-basic.png"
                                    alt="Basic Image" height="140" />
                            </div>
                            <h3 class="card-title text-center text-capitalize mb-1">Basic</h3>
                            <p class="text-center">A simple start for everyone</p>
                            <div class="text-center">
                                <div class="d-flex justify-content-center">
                                    <sup class="h6 pricing-currency mt-3 mb-0 me-1 text-primary">$</sup>
                                    <h1 class="display-4 mb-0 text-primary">0</h1>
                                    <sub class="h6 pricing-duration mt-auto mb-2 text-muted fw-normal">/month</sub>
                                </div>
                                <small class="price-yearly price-yearly-toggle text-muted">$ 0 / year</small>
                            </div>

                            <ul class="ps-0 my-4 pt-2 circle-bullets">
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>100
                                    responses a month</li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ti ti-point ti-lg"></i>Unlimited forms and surveys
                                </li>
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Unlimited
                                    fields</li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ti ti-point ti-lg"></i>Basic form creation tools
                                </li>
                                <li class="mb-0 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Up to 2
                                    subdomains</li>
                            </ul>
                            <a href="javascript:void(0)" class="btn btn-label-success d-grid w-100">Your Current
                                Plan</a>
                        </div>
                    </div>
                </div>

                <!-- Pro -->
                <div class="col-lg mb-md-0 mb-4">
                    <div class="card border-primary border shadow-none">
                        <div class="card-body position-relative">
                            <div class="position-absolute end-0 me-4 top-0 mt-4">
                                <span class="badge bg-label-primary">Popular</span>
                            </div>
                            <div class="my-3 pt-2 text-center">
                                <img src="{{ asset('') }}assets/img/illustrations/page-pricing-standard.png"
                                    alt="Standard Image" height="140" />
                            </div>
                            <h3 class="card-title text-center text-capitalize mb-1">Pro</h3>
                            <p class="text-center">For small to medium businesses</p>
                            <div class="text-center">
                                <div class="d-flex justify-content-center">
                                    <sup class="h6 pricing-currency mt-3 mb-0 me-1 text-primary">$</sup>
                                    <h1 class="price-toggle price-yearly display-4 text-primary mb-0">7</h1>
                                    <h1 class="price-toggle price-monthly display-4 text-primary mb-0 d-none">9</h1>
                                    <sub class="h6 text-muted pricing-duration mt-auto mb-2 fw-normal">/month</sub>
                                </div>
                                <small class="price-yearly price-yearly-toggle text-muted">$ 90 / year</small>
                            </div>

                            <ul class="ps-0 my-4 pt-2 circle-bullets">
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Up to 5
                                    users</li>
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>120+
                                    components</li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ti ti-point ti-lg"></i>Basic support on Github
                                </li>
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Monthly
                                    updates</li>
                                <li class="mb-0 d-flex align-items-center"><i
                                        class="ti ti-point ti-lg"></i>Integrations</li>
                            </ul>

                            <a href="javascript:void(0)" class="btn btn-primary d-grid w-100">Upgrade</a>
                        </div>
                    </div>
                </div>

                <!-- Enterprise -->
                <div class="col-lg">
                    <div class="card border rounded shadow-none">
                        <div class="card-body">
                            <div class="my-3 pt-2 text-center">
                                <img src="{{ asset('') }}assets/img/illustrations/page-pricing-enterprise.png"
                                    alt="Enterprise Image" height="140" />
                            </div>
                            <h3 class="card-title text-center text-capitalize mb-1">Enterprise</h3>
                            <p class="text-center">Solution for big organizations</p>

                            <div class="text-center">
                                <div class="d-flex justify-content-center">
                                    <sup class="h6 text-primary pricing-currency mt-3 mb-0 me-1">$</sup>
                                    <h1 class="price-toggle price-yearly display-4 text-primary mb-0">16</h1>
                                    <h1 class="price-toggle price-monthly display-4 text-primary mb-0 d-none">19</h1>
                                    <sub class="h6 pricing-duration mt-auto mb-2 fw-normal text-muted">/month</sub>
                                </div>
                                <small class="price-yearly price-yearly-toggle text-muted">$ 190 / year</small>
                            </div>

                            <ul class="ps-0 my-4 pt-2 circle-bullets">
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Up to 10
                                    users</li>
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>150+
                                    components</li>
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ti ti-point ti-lg"></i>Basic support on Github
                                </li>
                                <li class="mb-2 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Monthly
                                    updates</li>
                                <li class="mb-0 d-flex align-items-center"><i class="ti ti-point ti-lg"></i>Speedy
                                    build tooling</li>
                            </ul>

                            <a href="javascript:void(0)" class="btn btn-label-primary d-grid w-100">Upgrade</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ Pricing Plans -->
    <!-- Pricing Free Trial -->
    <section class="pricing-free-trial bg-label-primary">
        <div class="container">
            <div class="position-relative">
                <div
                    class="d-flex justify-content-between flex-column-reverse flex-lg-row align-items-center py-4 px-5">
                    <div class="text-center text-lg-start mt-2 ms-3">
                        <h3 class="text-primary mb-1">Silahkan Hubungi Super Administrator!</h3>
                        <p class="text-body mb-1">You will get full access to with all the features for enjoy.</p>
                    </div>

                </div>
            </div>
        </div>
    </section>

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
    <!-- Footer: End -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('') }}assets/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('') }}assets/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('') }}assets/vendor/libs/node-waves/node-waves.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('') }}assets/js/front-main.js"></script>

    <!-- Page JS -->
    <script src="{{ asset('') }}assets/js/front-page-pricing.js"></script>
</body>

</html>
