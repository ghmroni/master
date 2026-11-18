_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $(".disabled").prop("disabled", true);
        $.unblockUI();
    }, 300);

    $("#tanggalabsensi").flatpickr({
        dateFormat: "Y-m-d",
        maxDate: "today",
        minDate: new Date().setMonth(new Date().getMonth() - 1),
    });

    $("#waktuabsensi").flatpickr({
        enableTime: true,
        noCalendar: true,
        time_24hr: true, // Mengaktifkan format 24 jam
        dateFormat: "H:i",
    });

    $("#karyawanid").on("change", function () {
        _loading();
        var token = $(this).val();
        $.getJSON("/cek-absensi-person/?token=" + token, function (res) {
            var lastAbsensi = res.lastAbsensi;
            var lokasi = [];
            var color = [
                "bg-success",
                "bg-warning",
                "bg-primary",
                "bg-info",
                "bg-secondary",
                "bg-dark",
            ];
            $.each(res.penempatan, function (id, data) {
                lokasi.push(
                    '<span class="badge ' +
                    color[id] +
                    ' me-1 text-black mb-1">' +
                    data +
                    "</span>",
                );
            });
            $("#lokasipekerjaan").html(lokasi.join(""));

            $("#penempatan").val(JSON.stringify(res.penempatan));

            $("#typeabsensi").empty();
            if (lastAbsensi) {
                if (lastAbsensi.check_type == "O") {
                    $("#typeabsensi").append(
                        '<option value="">-pilih-</option>',
                    );
                    $("#typeabsensi").append(
                        '<option value="I">Absen Masuk</option>',
                    );
                } else {
                    $("#typeabsensi").append(
                        '<option value="">-pilih-</option>',
                    );
                    $("#typeabsensi").append(
                        '<option value="O">Absen Pulang</option>',
                    );
                }
            } else {
                $("#typeabsensi").append('<option value="">-pilih-</option>');
                $("#typeabsensi").append(
                    '<option value="I">Absen Masuk</option>',
                );
                $("#typeabsensi").append(
                    '<option value="O">Absen Pulang</option>',
                );
            }

            setTimeout(() => {
                $(".disabled").val("");
                $(".disabled").prop("disabled", false);
                $.unblockUI();
            }, 300);
        });
    });

    const formValidationAbsensi = document.getElementById("formAbsensi");
    const fv = FormValidation.formValidation(formValidationAbsensi, {
        fields: {
            karyawanid: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih karyawan",
                    },
                },
            },
            tanggalabsensi: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationAbsensi.querySelector(
                                '[name="karyawanid"]',
                            ).value;
                            if (karId && karId.trim() !== "") {
                                return input.value !== "";
                            }
                            return true;
                        },
                    },
                },
            },
            waktuabsensi: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationAbsensi.querySelector(
                                '[name="karyawanid"]',
                            ).value;
                            if (karId && karId.trim() !== "") {
                                return input.value !== "";
                            }
                            return true;
                        },
                    },
                },
            },
            typeabsensi: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationAbsensi.querySelector(
                                '[name="karyawanid"]',
                            ).value;
                            if (karId && karId.trim() !== "") {
                                return input.value !== "";
                            }
                            return true;
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

    formValidationAbsensi.addEventListener("submit", function (e) {
        e.preventDefault();

        fv.validate().then(function (status) {
            if (status === "Valid") {
                // submit form jika semua valid
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Proses Penambahan Absensi Karyawan",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Proses",
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
                        var formData = $("#formAbsensi").serialize();

                        $.ajax({
                            url: "/absensi-tambah",
                            type: "POST",
                            data: formData,
                            success: function (response) {
                                $.unblockUI();
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    showConfirmButton: false,
                                    timer: 1500,
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
                                    window.location.href = "/absensi";
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
    });
});
