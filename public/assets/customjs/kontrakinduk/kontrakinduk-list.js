_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 200);

    table = $("#tableInduk").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('#tableInduk [data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        },
        columns: [
            {
                data: "nomor",
                name: "nomor",
                orderable: false,
                searchable: true,
            },
            {
                data: "judul",
                name: "judul",
                orderable: true,
                searchable: true,
            },
            {
                data: "periode",
                name: "periode",
                orderable: true,
                searchable: true,
            },
            {
                data: "setting",
                name: "setting",
                orderable: false,
                searchable: true,
            },
            {
                data: "action",
                name: "action",
                orderable: true,
                searchable: false,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 2, 4],
                className: "dt-center",
            },
        ],
    });

    $(document).on('click', '.Edit', function () {
        var token = $(this).data('token');
        _loading();
        window.location.href = '/kontrakinduk/edit/?token=' + encodeURIComponent(token);
        // $.unblockUI();
    })

    $(document).on('click', '.Perpanjang', function () {
        var token = $(this).data('token');
        _loading();
        window.location.href = '/kontrakinduk/perpanjang/?token=' + encodeURIComponent(token);
        // $.unblockUI();
    })

    $(document).on('click', '.Adendum', function () {
        var token = $(this).data('token');
        _loading();
        window.location.href = '/adendum/tambah/?token=' + encodeURIComponent(token);
        // $.unblockUI();
    })

    $(document).on('click', '.EditAdendum', function () {
        var token = $(this).data('token');
        var setting = $(this).data('setting');
        _loading();
        window.location.href = '/adendum/edit/?token=' + encodeURIComponent(token) + '&setting=' + setting;
        // $.unblockUI();
    })

    $(document).on('click', '.Nonaktifkan', function () {
        $('#formNonaktif')[0].reset();

        var drEvent = $('.dropify').dropify();
        drEvent = drEvent.data('dropify');
        if (drEvent !== undefined) {
            drEvent.clearElement();
        }

        var token = $(this).data('token');
        var nomor = $(this).data('nomor');
        $('#nomor').val(nomor);
        $('#idkontrak').val(token);
        $('#modalNonaktif').modal('show');

    })

    const bsValidationForms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener('submit', function (event) {

            let isFormValid = true;

            // 2. Validasi manual untuk Dropify
            const fileInput = form.querySelector('.dropify');
            if (fileInput && !fileInput.files.length) {
                // Tambahkan class error dan pesan
                const wrapper = fileInput.closest('.dropify-wrapper');
                wrapper.classList.add('has-error');

                if (!wrapper.querySelector('.dropify-error')) {
                    const errorMsg = document.createElement('span');
                    errorMsg.classList.add('dropify-error');
                    errorMsg.textContent = fileInput.getAttribute('data-error') || 'File masih kosong';
                    wrapper.appendChild(errorMsg);
                }

                // Set penanda menjadi false jika Dropify kosong
                isFormValid = false;
            }

            // 3. Gabungkan validasi standar Bootstrap dan Dropify
            if (!form.checkValidity() || !isFormValid) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated'); // Menampilkan error visual Bootstrap
            } else {
                // Submit your form

                event.preventDefault();
                const formData = new FormData(this);

                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Proses Pengajuan Penonaktifan Kontrak Induk",
                    icon: "warning",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Ajukan",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        _loading();
                        $.ajax({
                            url: "/kontrakInduk/nonaktif",
                            type: "POST",
                            dataType: "JSON",
                            data: formData,
                            contentType: false,
                            processData: false,
                            cache: false,
                            success: function (response) {
                                $('#modalNonaktif').modal('hide');
                                $.unblockUI();
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    showConfirmButton: false,
                                    timer: 1000,
                                    allowOutsideClick: false,
                                    showClass: {
                                        popup: "animate__animated animate__rubberBand",
                                    },
                                    hideClass: {
                                        popup: "animate__animated animate__bounceOut",
                                    },
                                    customClass: {
                                        confirmButton: "",
                                    },
                                    buttonsStyling: false,
                                    willOpen: function () {
                                        Swal.showLoading();
                                    },
                                }).then(function (result) {
                                    table.ajax.reload(null, false);
                                });
                            },
                            error: function (error) {
                                $.unblockUI();
                                let errorMessage = error.statusText;
                                let errorTitle = `Error ${error.status}`;

                                // Coba parse JSON jika ada responseText
                                if (error.responseText) {
                                    try {
                                        let response = JSON.parse(error.responseText);
                                        if (response.errors) {
                                            var errors = $.parseJSON(error.responseText);
                                            errorMessage = errors.errors;
                                        } else if (response.message) {
                                            errorMessage = response.message;
                                        }
                                    } catch (e) {
                                        errorMessage = error.statusText;
                                    }
                                }

                                Swal.fire({
                                    title: errorTitle,
                                    text: errorMessage,
                                    icon: "error",
                                    showClass: {
                                        popup: "animate__animated animate__shakeX",
                                    },
                                    hideClass: {
                                        popup: "animate__animated animate__bounceOut",
                                    },
                                    customClass: {
                                        confirmButton:
                                            "btn btn-primary waves-effect waves-light",
                                    },
                                    buttonsStyling: false,
                                });
                            },
                        });
                    }
                });

            }

            form.classList.add('was-validated');
        }, false);
    });
});
