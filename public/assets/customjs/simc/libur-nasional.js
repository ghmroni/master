_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#tanggalCuti").flatpickr({
        weekNumbers: false,
        enableTime: false,
        mode: "multiple",
        minDate: new Date(new Date().getFullYear(), 0, 1), // 1 Januari tahun ini
        maxDate: new Date(new Date().getFullYear() + 1, 11, 31), // 31 Desember tahun depan
        onChange: function (selectedDates, dateStr, instance) {
            instance.close(); // otomatis close setelah pilih
        },
    });

    setTimeout(function () {
        $("#formAdd")[0].reset();
        $("#idlibur").val("");
        $(".btnSubmit").html('<i class="fas fa-save me-1"></i>Simpan');
        $.unblockUI();
    }, 300);

    table = $("#tableKalender").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "tahun",
                name: "tahun",
            },
            {
                data: "tanggal",
                name: "tanggal",
                orderable: true,
                searchable: true,
            },
            {
                data: "keterangan",
                name: "keterangan",
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 2, 4],
                className: "text-center",
            },
        ],
    });

    const formValidationAdd = document.getElementById("formAdd");
    const fv = FormValidation.formValidation(formValidationAdd, {
        fields: {
            tanggalCuti: {
                validators: {
                    notEmpty: {
                        message: "Tanggal masih kosong",
                    },
                },
            },
            keterangan: {
                validators: {
                    callback: {
                        message: "Keterangan masih kosong",
                        callback: function (input) {
                            // cek: tidak kosong dan tidak diawali spasi
                            return (
                                input.value.trim() !== "" &&
                                !/^\s/.test(input.value)
                            );
                        },
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleInvalidClass: "is-invalid",
                eleValidClass: "",
                rowSelector: ".row, .col-md-6, .col-md-12",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    formValidationAdd.addEventListener("submit", function (e) {
        e.preventDefault();
        fv.validate().then(function (status) {
            if (status === "Valid") {
                // submit form jika semua valid
                Swal.fire({
                    title: "Konfirmasi!",
                    html: "Simpan Tanggal",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Simpan",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton:
                            "btn btn-primary me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        _loading();

                        $.ajax({
                            url: "/libur-nasional",
                            type: "POST",
                            data: $("#formAdd").serialize(),
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
                                    table.ajax.reload(null, false);
                                    $("#formAdd")[0].reset();
                                    $("#idlibur").val("");
                                    // reset flatpickr
                                    if ($("#tanggalCuti")[0]._flatpickr) {
                                        $("#tanggalCuti")[0]._flatpickr.clear();
                                    }

                                    $("#tanggalCuti").flatpickr({
                                        weekNumbers: false,
                                        enableTime: false,
                                        mode: "multiple",
                                        minDate: new Date(
                                            new Date().getFullYear(),
                                            0,
                                            1,
                                        ), // 1 Januari tahun ini
                                        maxDate: new Date(
                                            new Date().getFullYear() + 1,
                                            11,
                                            31,
                                        ), // 31 Desember tahun depan
                                        onChange: function (
                                            selectedDates,
                                            dateStr,
                                            instance,
                                        ) {
                                            instance.close(); // otomatis close setelah pilih
                                        },
                                    });

                                    $(".btnSubmit").html(
                                        '<i class="fas fa-save me-1"></i>Simpan',
                                    );
                                    $("#btnReset").css("display", "none");
                                });
                            },
                            error: function (error) {
                                $.unblockUI();
                                let errorMessage = error.statusText;
                                let errorTitle = `Error ${error.status}`;

                                // Coba parse JSON jika ada responseText
                                if (error.responseText) {
                                    try {
                                        let response = JSON.parse(
                                            error.responseText,
                                        );
                                        if (response.errors) {
                                            var errors = $.parseJSON(
                                                error.responseText,
                                            );
                                            errorMessage = errors.errors;
                                        } else if (response.message) {
                                            // Jika ada message langsung
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
        });
    });

    $(document).on("click", ".Edit", function () {
        _loading();
        var token = $(this).data("token");
        $.getJSON("/detail-liburnasioanal?token=" + token, function (response) {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            $("#formAdd")[0].reset();
            if ($("#tanggalCuti")[0]._flatpickr) {
                // clear dulu biar bersih
                $("#tanggalCuti")[0]._flatpickr.destroy();
            }

            $("#tanggalCuti").flatpickr({
                dateFormat: "Y-m-d",
                minDate: new Date(new Date().getFullYear(), 0, 1), // 1 Januari tahun ini
                maxDate: new Date(new Date().getFullYear() + 1, 11, 31), // 31 Desember tahun depan
                defaultDate: response.tanggal, // otomatis select tanggal edit
            });

            $("#keterangan").val(response.keterangan);
            $("#idlibur").val(response.id);

            setTimeout(function () {
                $(".btnSubmit").html(
                    '<i class="fas fa-retweet me-1"></i>Perbaharui',
                );
                $("#btnReset").css("display", "block");
                $.unblockUI();
            }, 300);
        });
    });

    $(document).on("click", ".btnNew", function (e) {
        e.preventDefault();
        _loading();
        $("#formAdd")[0].reset();
        $("#idlibur").val("");
        if ($("#tanggalCuti")[0]._flatpickr) {
            $("#tanggalCuti")[0]._flatpickr.clear();
        }

        $("#tanggalCuti").flatpickr({
            weekNumbers: false,
            enableTime: false,
            mode: "multiple",
            minDate: new Date(new Date().getFullYear(), 0, 1), // 1 Januari tahun ini
            maxDate: new Date(new Date().getFullYear() + 1, 11, 31), // 31 Desember tahun depan
        });

        setTimeout(function () {
            $(".btnSubmit").html('<i class="fas fa-save me-1"></i>Simpan');
            $("#btnReset").css("display", "none");
            $.unblockUI();
        }, 300);
    });

    $(document).on("click", ".Hapus", function () {
        var token = $(this).data("token");
        var tanggal = $(this).data("tanggal");
        Swal.fire({
            title: "Konfirmasi!",
            html: "Hapus Data Libur Nasional Tanggal " + tanggal + " ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();

                $.ajax({
                    url: "/destroy-liburnasional?token=" + token,
                    type: "POST",

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
                            table.ajax.reload(null, false);
                            $("#formAdd")[0].reset();
                            $("#idlibur").val("");
                            // reset flatpickr
                            if ($("#tanggalCuti")[0]._flatpickr) {
                                $("#tanggalCuti")[0]._flatpickr.clear();
                            }

                            $("#tanggalCuti").flatpickr({
                                weekNumbers: false,
                                enableTime: false,
                                mode: "multiple",
                                minDate: new Date(
                                    new Date().getFullYear(),
                                    0,
                                    1,
                                ), // 1 Januari tahun ini
                                maxDate: new Date(
                                    new Date().getFullYear() + 1,
                                    11,
                                    31,
                                ), // 31 Desember tahun depan
                                onChange: function (
                                    selectedDates,
                                    dateStr,
                                    instance,
                                ) {
                                    instance.close(); // otomatis close setelah pilih
                                },
                            });

                            $(".btnSubmit").html(
                                '<i class="fas fa-save me-1"></i>Simpan',
                            );
                            $("#btnReset").css("display", "none");
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
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
                                    errorMessage = errors.errors;
                                } else if (response.message) {
                                    // Jika ada message langsung
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
    });
});
