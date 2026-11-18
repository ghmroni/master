_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 500);

    // ketika klik "pilih semua" per module
    $(document).on('change', '.selectAll', function () {
        const group = $(this).data('group');
        const checked = $(this).is(':checked');

        // centang semua checkbox permission dalam group yang sama
        $(`.perm-checkbox[data-group="${group}"]`).prop('checked', checked).trigger('change');
    });

    // sinkronisasi: kalau semua child dicentang, otomatis selectAll ikut tercentang
    $(document).on('change', '.perm-checkbox', function () {
        const group = $(this).data('group');
        const all = $(`.perm-checkbox[data-group="${group}"]`).length;
        const checked = $(`.perm-checkbox[data-group="${group}"]:checked`).length;

        $(`.selectAll[data-group="${group}"]`).prop('checked', all === checked);
    });

    document.getElementById("formPerm").addEventListener("submit", function (e) {
        e.preventDefault();

        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Akses Tambahan User ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-info me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-danger waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/pengguna/direct",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formPerm").serialize(),
                    success: function (response) {
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
                            window.location.href = '/pengguna';
                        });
                    },
                    error: function (error) {
                        $.unblockUI();

                        var errorMessage = error.statusText;
                        if (error.status == 405) {
                            // Not Acceptable
                            var errors = $.parseJSON(error.responseText);
                            errorMessage = errors.errors;
                        }

                        Swal.fire({
                            title: $.parseJSON(error.status),
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
    });

})