_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 300);

    var startDate = $("#dari").flatpickr({
        disableMobile: "true",
        minDate: new Date().setMonth(new Date().getMonth() - 3),
        maxDate: "today",
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            })
        ],
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                var selectedMonth = selectedDates[0];
                endDate.set("minDate", selectedMonth);
                endDate.clear();
            }
        }
    });

    var endDate = $("#sampai").flatpickr({
        disableMobile: "true",
        maxDate: "today",
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            })
        ]
    });


    const formValidationPerorang = document.getElementById("formRekap");
    const fv = FormValidation.formValidation(formValidationPerorang, {
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
                    notEmpty: {
                        message: "Inputan masih kosong.",
                    },
                },
            },
            sampai: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong.",
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

    formValidationPerorang.addEventListener("submit", function (e) {
        e.preventDefault();
        $("#dtList").css("display", "none");
        fv.validate().then(function (status) {
            if (status === "Valid") {
                // submit form jika semua valid
                _loading();
                var formData = $("#formRekap").serialize();
                $.ajax({
                    url: "/rekapcuti",
                    type: "POST",
                    data: formData,
                    xhrFields: {
                        responseType: "blob", // penting untuk file
                    },
                    success: function (response, status, xhr) {
                        // Ambil nama file dari header kalau ada
                        let filename = "karyawan.pdf";
                        const disposition = xhr.getResponseHeader(
                            "Content-Disposition",
                        );
                        if (disposition && disposition.indexOf("filename=") !== -1) {
                            filename = disposition
                                .split("filename=")[1]
                                .replace(/"/g, "");
                        }

                        // Buat blob sesuai tipe file
                        const blob = new Blob([response], { type: "application/pdf" });
                        const link = document.createElement("a");
                        link.href = window.URL.createObjectURL(blob);
                        link.download = filename;

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        setTimeout(() => {
                            $.unblockUI();
                        }, 300);
                    },
                    error: function (error) {
                        $.unblockUI();
                        Swal.fire({
                            title: "500",
                            text: "Gagal Download Data.",
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
                    complete: function () {
                        $.unblockUI();
                    },
                });
            }
        });
    });

    $(document).on("click", ".btnDownload", function () {
        _loading();
        var formData = $("#formPerorang").serialize();
        $.ajax({
            url: "/perorang",
            type: "POST",
            data: formData,
            xhrFields: {
                responseType: "blob", // penting untuk file
            },
            success: function (response, status, xhr) {
                // Ambil nama file dari header kalau ada
                let filename = "karyawan.pdf";
                const disposition = xhr.getResponseHeader(
                    "Content-Disposition",
                );
                if (disposition && disposition.indexOf("filename=") !== -1) {
                    filename = disposition
                        .split("filename=")[1]
                        .replace(/"/g, "");
                }

                // Buat blob sesuai tipe file
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    $.unblockUI();
                }, 300);
            },
            error: function (error) {
                $.unblockUI();
                Swal.fire({
                    title: "500",
                    text: "Gagal Download Data.",
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
            complete: function () {
                $.unblockUI();
            },
        });
    });
});
