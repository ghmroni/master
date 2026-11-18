_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $(".dropify").dropify();

    setTimeout(function () {
        $.unblockUI();
    }, 300);

    $(document).on("change", "#pt", function () {
        _loading();
        var token = $(this).val();
        $("#project")
            .empty()
            .append('<option value="">-pilih project-</option>');

        if (!token || $.trim(token) === "") {
            $.unblockUI();
            return false;
        } else {
            $.post("/project-company", { token: token }, function (res) {
                $.each(res, function (id, data) {
                    $("#project").append(
                        '<option value="' +
                        data.id +
                        '">' +
                        data.name +
                        "</option>",
                    );
                });
                $.unblockUI();
            });
        }
    });

    $(document).on("click", "#btnTemplate", function (e) {
        e.preventDefault();
        var pt = $("#pt").val();
        var project = $("#project").val();

        // Fungsi pembantu agar kode SweetAlert tidak ditulis berulang
        function showError(message) {
            Swal.fire({
                title: "Oops!",
                text: message,
                icon: "error",
                showClass: { popup: "animate__animated animate__shakeX" },
                hideClass: { popup: "animate__animated animate__bounceOut" },
                customClass: {
                    confirmButton: "btn btn-primary waves-effect waves-light",
                },
                buttonsStyling: false,
            });
        }

        // Cek Perusahaan
        if ($.trim(pt) === "") {
            showError("Perusahaan masih kosong");
            return false;
        }

        // Cek Project
        if ($.trim(project) === "") {
            showError("Project masih kosong");
            return false;
        }

        _loading();
        $.ajax({
            url: "/template-import-karyawan",
            type: "POST",
            data: {
                ptid: pt,
                projectid: project,
            },
            xhrFields: {
                responseType: "blob", // penting untuk file
            },
            success: function (response, status, xhr) {
                let filename = "default.xlsx";
                const disposition = xhr.getResponseHeader(
                    "Content-Disposition",
                );
                if (disposition && disposition.indexOf("filename=") !== -1) {
                    filename = disposition
                        .split("filename=")[1]
                        .replace(/"/g, "");
                }

                const blob = new Blob([response], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });

                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);

                link.download = filename;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
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
                $("#modalTemplate").modal("hide");
                $.unblockUI();
            },
        });

    });

    $("#formKaryawan").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Proses Migrasi Data Karyawan",
            icon: "warning",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Proses",
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
                    url: "/import-karyawan",
                    type: "POST",
                    dataType: "JSON",
                    data: new FormData($("#formKaryawan")[0]), // ambil form lengkap termasuk file
                    contentType: false, // wajib untuk FormData
                    processData: false,
                    cache: false,
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
                            setTimeout(function () {
                                $("#pt").val("").trigger("change");
                                $("#project").val("").trigger("change");
                                var drEvent = $("#file").dropify();
                                // clear value
                                drEvent = drEvent.data("dropify");
                                drEvent.clearElement();
                            }, 300);
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
