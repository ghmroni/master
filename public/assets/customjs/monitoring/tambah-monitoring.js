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

    $("#tanggalmonitoring").flatpickr({
        dateFormat: "Y-m-d",
        maxDate: "today",
        minDate: new Date().setMonth(new Date().getMonth() - 1),
    });

    $("#karyawanid").on("change", function () {
        _loading();
        var token = $(this).val();
        $.getJSON("/cek-absensi-person/?token=" + token, function (res) {
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

            // $("#penempatan").val(res.penempatan);
            $("#penempatan").val(JSON.stringify(res.penempatan));

            setTimeout(() => {
                $(".disabled").val("");
                $(".disabled").prop("disabled", false);
                $.unblockUI();
            }, 300);
        });
    });

    // Inisialisasi dropify
    let drEvent = $("#gambar").dropify();

    // Reset manual ketika validasi gagal
    $("#gambar").on("change", function () {
        const file = this.files[0];
        if (file) {
            const allowedTypes = ["image/jpeg", "image/png"];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Oops!",
                    text: "File harus berupa gambar.",
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

                // reset dropify dengan event clear
                drEvent = $(this).dropify();
                drEvent = drEvent.data("dropify");
                drEvent.resetPreview();
                drEvent.clearElement();
            }
        }
    });

    const formValidationMonitoring = document.getElementById("formMonitoring");
    const fv = FormValidation.formValidation(formValidationMonitoring, {
        fields: {
            karyawanid: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih karyawan",
                    },
                },
            },
            tanggalmonitoring: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId =
                                formValidationMonitoring.querySelector(
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
            keterangan: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId =
                                formValidationMonitoring.querySelector(
                                    '[name="karyawanid"]',
                                ).value;
                            if (karId && karId.trim() !== "") {
                                // Cek: tidak kosong dan tidak diawali spasi
                                return (
                                    input.value.trim() !== "" &&
                                    !/^\s/.test(input.value)
                                );
                            }
                            return true;
                        },
                    },
                },
            },
            prioritas: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId =
                                formValidationMonitoring.querySelector(
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
            gambar: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId =
                                formValidationMonitoring.querySelector(
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

    formValidationMonitoring.addEventListener("submit", function (e) {
        e.preventDefault();
        const formElement = this;

        fv.validate().then(function (status) {
            if (status === "Valid") {
                // submit form jika semua valid
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Simpan Monitoring Karyawan",
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

                        // const formElement = document.getElementById('formValidationMonitoring');
                        const formData = new FormData(formElement);

                        $.ajax({
                            url: "/tambah-monitoring",
                            type: "POST",
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
                                    window.location.href = "/monitoring";
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
});
