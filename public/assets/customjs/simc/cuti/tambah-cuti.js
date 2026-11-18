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

    $("#dari").change(function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
        $("#tglmasuk").val("");
    });

    var endDate = $("#sampai").flatpickr({
        dateFormat: "Y-m-d",
        onChange: function (selectedDates) {
            var selectedDate = selectedDates[0];
            $("#tglmasuk").val("");
            // minDate = selectedDate + 1 hari
            var minDate = new Date(selectedDate);
            minDate.setDate(minDate.getDate() + 1);

            // maxDate = selectedDate + 7 hari
            var maxDate = new Date(selectedDate);
            maxDate.setDate(maxDate.getDate() + 7);

            // set batasan ke flatpickr lain (misalnya onDate)
            onDate.set("minDate", minDate);
            onDate.set("maxDate", maxDate);
        },
    });

    var onDate = $("#tglmasuk").flatpickr({
        dateFormat: "Y-m-d",
    });

    var startDate = $("#dari").flatpickr({
        dateFormat: "Y-m-d",
        maxDate: "today",
        minDate: new Date().setMonth(new Date().getMonth() - 1),
        onChange: function (selectedDates) {
            if (selectedDates.length === 0) return;

            var selectedDate = selectedDates[0];
            var kategori = $("#kategori").val();

            // tentukan range berdasarkan kategori
            var maxRange = new Date(selectedDate);
            if (kategori === "TAHUNAN") {
                maxRange.setDate(maxRange.getDate() + 7); // 7 hari
            } else {
                maxRange.setMonth(maxRange.getMonth() + 3); // 3 bulan
            }

            // update batasan untuk endDate
            endDate.set("minDate", selectedDate);
            endDate.set("maxDate", maxRange);
        },
    });

    let totalSaldo = 0;

    $("#kategori").on("change", function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
        $("#dari").val("");

        if ($(this).val() == "TAHUNAN") {
            $("#saldoCutiContainer").html(totalSaldo);
            $("#hari").prop("maxlength", 1);
        } else {
            $("#saldoCutiContainer").html(90);
            $("#hari").prop("maxlength", 2);
        }
    });

    $("#karyawanid").on("change", function () {
        _loading();
        var token = $(this).val();
        $("#saldoCutiContainer").empty();
        $.getJSON("/cuti-tambah?token=" + token, function (res) {
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
                lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black mb-1">' + data + "</span>");
            });
            $("#lokasipekerjaan").html(lokasi.join(""));

            $("#penempatan").val(JSON.stringify(res.penempatan));
            $("#namalengkap").val(res.nama);

            totalSaldo = res.sisaCuti;

            $("#saldoCutiContainer").html(totalSaldo);
            window.totalSaldoCuti = totalSaldo;

            $("#kategori").trigger("change");

            setTimeout(() => {
                fv.revalidateField("karyawanid");
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

    const formValidationCuti = document.getElementById("formCuti");
    const fv = FormValidation.formValidation(formValidationCuti, {
        fields: {
            karyawanid: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih karyawan",
                    },
                },
            },
            dari: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationCuti.querySelector(
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
            sampai: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationCuti.querySelector(
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
            hari: {
                validators: {
                    callback: {
                        message: "Jumlah hari tidak valid",
                        callback: function (input) {
                            const karId = formValidationCuti.querySelector(
                                '[name="karyawanid"]',
                            ).value;
                            const kategori =
                                formValidationCuti.querySelector(
                                    '[name="kategori"]',
                                ).value;
                            const val = parseInt(input.value, 10);

                            // kalau karyawanid belum dipilih → skip validasi
                            if (!karId || karId.trim() === "") {
                                return true;
                            }

                            // kalau input kosong → invalid
                            if (!val) {
                                return {
                                    valid: false,
                                    message: "Inputan masih kosong",
                                };
                            }

                            // tentukan batas maksimal
                            const maxSaldo =
                                kategori === "TAHUNAN"
                                    ? window.totalSaldoCuti || 0
                                    : 90;

                            // validasi terhadap maxSaldo
                            if (val > maxSaldo) {
                                return {
                                    valid: false,
                                    message: `Jumlah hari tidak boleh melebihi ${maxSaldo}`,
                                };
                            }

                            return true; // lolos semua aturan
                        },
                    },
                },
            },
            keterangan: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationCuti.querySelector(
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
            gambar: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            const karId = formValidationCuti.querySelector(
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

    formValidationCuti.addEventListener("submit", function (e) {
        e.preventDefault();
        const formElement = this;
        var namalengkap = $("#namalengkap").val();

        fv.validate().then(function (status) {
            if (status === "Valid") {
                // submit form jika semua valid
                Swal.fire({
                    title: "Konfirmasi!",
                    html:
                        "Proses Data Cuti a.n <strong>" +
                        namalengkap +
                        "</strong>",
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

                        // const formElement = document.getElementById('formValidationCuti');
                        const formData = new FormData(formElement);

                        $.ajax({
                            url: "/cuti-tambah",
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
                                    window.location.href = "/cuti";
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

    $(document).on("click", ".btnBack", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/cuti";
        }, 300);
    });
});
