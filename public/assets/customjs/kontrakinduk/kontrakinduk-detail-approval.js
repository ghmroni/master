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

    $(document).on('click', '.btn-aksi-approve', function () {
        var aksi = $(this).data('action');

        // 1. Set nilai status pada input hidden
        $('#approval-status').val(aksi);

        var formData = new FormData($('#form-approval')[0]);
        // 2. Logika pemisahan antara APPROVED dan REJECTED
        if (aksi === 'APPROVED') {
            // Jika disetujui, langsung konfirmasi dan kirim
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Perubahan kontrak akan langsung diterapkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                cancelButtonText: "Batal",
                confirmButtonText: 'Ya, Setujui!',
                customClass: {
                    confirmButton: "btn btn-success me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: '/approval-kontrakinduk',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = '/approval-kontrakinduk';
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {

                                        var errors = $.parseJSON(error.responseText);
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
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

        } else if (aksi === 'REJECTED') {
            // Jika ditolak, munculkan popup input teks untuk alasan
            Swal.fire({
                title: 'Alasan Penolakan',
                input: 'textarea',
                inputLabel: 'Masukkan alasan mengapa perubahan ini ditolak:',
                inputPlaceholder: 'Tulis alasan di sini...',
                inputAttributes: {
                    'aria-label': 'Tulis alasan di sini'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tolak Perubahan',
                cancelButtonText: "Batal",
                inputValidator: (value) => {
                    // Validasi agar user wajib mengisi alasan
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!'
                    }
                },
                customClass: {
                    confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                // if (result.value) {
                if (result.isConfirmed) {
                    _loading();
                    formData.append('alasan_reject', result.value);
                    $.ajax({
                        url: '/tolak-kontrakinduk',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = '/approval-kontrakinduk';
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {

                                        var errors = $.parseJSON(error.responseText);
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
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
    });

    $(document).on('click', '.btn-approve-off', function () {
        var aksi = $(this).data('action');

        // 1. Set nilai status pada input hidden
        $('#form-approval-nonaktif').val(aksi);

        var formData = new FormData($('#form-approval-nonaktif')[0]);
        // 2. Logika pemisahan antara APPROVED dan REJECTED
        if (aksi === 'APPROVED') {
            // Jika disetujui, langsung konfirmasi dan kirim
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Proses Penonaktifan Kontrak Induk!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                cancelButtonText: "Batal",
                confirmButtonText: 'Ya, Proses!',
                customClass: {
                    confirmButton: "btn btn-success me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: '/approval-kontrakinduk-off',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = '/approval-kontrakinduk';
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {

                                        var errors = $.parseJSON(error.responseText);
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
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

        } else if (aksi === 'REJECTED') {
            // Jika ditolak, munculkan popup input teks untuk alasan
            Swal.fire({
                title: 'Alasan Penolakan',
                input: 'textarea',
                inputLabel: 'Masukkan alasan mengapa perubahan ini ditolak:',
                inputPlaceholder: 'Tulis alasan di sini...',
                inputAttributes: {
                    'aria-label': 'Tulis alasan di sini'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tolak',
                cancelButtonText: "Batal",
                inputValidator: (value) => {
                    // Validasi agar user wajib mengisi alasan
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!'
                    }
                },
                customClass: {
                    confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                // if (result.value) {
                if (result.isConfirmed) {
                    _loading();
                    formData.append('alasan_reject', result.value);
                    $.ajax({
                        url: '/tolak-kontrakinduk-off',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = '/approval-kontrakinduk';
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {

                                        var errors = $.parseJSON(error.responseText);
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
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
    });

    $(document).on('click', '.btn-new-adendum', function () {
        var aksi = $(this).data('action');

        // 1. Set nilai status pada input hidden
        $('#approval-status').val(aksi);

        var formData = new FormData($('#form-approval')[0]);
        // 2. Logika pemisahan antara APPROVED dan REJECTED
        if (aksi === 'APPROVED') {
            // Jika disetujui, langsung konfirmasi dan kirim
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Setujui dan Terapkan Adendum Baru!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                cancelButtonText: "Batal",
                confirmButtonText: 'Ya, Setujui!',
                customClass: {
                    confirmButton: "btn btn-success me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: '/adendum-add-new',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = '/approval-kontrakinduk';
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {

                                        var errors = $.parseJSON(error.responseText);
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
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

        } else if (aksi === 'REJECTED') {
            // Jika ditolak, munculkan popup input teks untuk alasan
            Swal.fire({
                title: 'Alasan Penolakan',
                input: 'textarea',
                inputLabel: 'Masukkan alasan mengapa perubahan ini ditolak:',
                inputPlaceholder: 'Tulis alasan di sini...',
                inputAttributes: {
                    'aria-label': 'Tulis alasan di sini'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tolak Perubahan',
                cancelButtonText: "Batal",
                inputValidator: (value) => {
                    // Validasi agar user wajib mengisi alasan
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!'
                    }
                },
                customClass: {
                    confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                // if (result.value) {
                if (result.isConfirmed) {
                    _loading();
                    formData.append('alasan_reject', result.value);
                    $.ajax({
                        url: '/adendum-reject',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = '/approval-kontrakinduk';
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {

                                        var errors = $.parseJSON(error.responseText);
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
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
    });

    $(document).on('click', '.kembali', function () {
        _loading();
        window.location.href = '/approval-kontrakinduk';
    })

})