<!-- build:js assets/vendor/js/core.js -->

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('assets/vendor/libs/quill/katex.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/quill/quill.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('') }}assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
{{-- <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script> --}}

<script src="{{ asset('') }}assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/tagify/tagify.js"></script>


<!-- Date -->
<script src="{{ asset('') }}assets/vendor/libs/moment/moment.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/pickr/pickr.js"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/customjs/main.js') }}"></script>

{{-- TOUR --}}
<script src="{{ asset('assets/customjs/dashboard/tour.js') }}"></script>
<script src="{{ asset('') }}assets/vendor/libs/shepherd/shepherd.js"></script>

{{-- Gallery --}}
<script src="{{ asset('') }}assets/vendor/glightbox/glightbox.min.js"></script>
<script src="{{ asset('') }}assets/vendor/glightbox/glightbox.js"></script>

<script src="{{ asset('assets/customjs/html5-editor/wysihtml5-0.3.0.js') }}"></script>
<script src="{{ asset('assets/customjs/html5-editor/bootstrap-wysihtml5.js') }}"></script>
<script src="{{ asset('') }}assets/vendor/libs/jquery-sticky/jquery-sticky.js"></script>

<!-- Page JS -->
@stack('extra_script')
