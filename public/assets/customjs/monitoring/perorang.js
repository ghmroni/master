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

    $("#dari").change(function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
        table.clear();
        table.draw();
        $(".btnDownload").prop("disabled", true);
        $("#dtList").css("display", "none");
    });

    $("#sampai").change(function () {
        table.clear();
        table.draw();
        $(".btnDownload").prop("disabled", true);
        $("#dtList").css("display", "none");
    });

    var endDate = $("#sampai").flatpickr({
        dateFormat: "Y-m-d",
    });

    var startDate = $("#dari").flatpickr({
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function (selectedDates) {
            var selectedDate = selectedDates[0]; // tanggal dari dipilih

            // Hitung max range = 30 hari dari start
            var maxRange = new Date(selectedDate);
            maxRange.setDate(maxRange.getDate() + 30);

            // Pastikan tidak lewat hari ini
            var today = new Date();
            if (maxRange > today) {
                maxRange = today;
            }

            // Set batasan untuk endDate
            endDate.set("minDate", selectedDate);
            endDate.set("maxDate", maxRange);
        },
    });

    let table = $("#tablePerorang").DataTable({
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            { data: "tanggal", name: "tanggal" },
            {
                data: "prioritas",
                name: "prioritas",
                render: function (data, type, row) {
                    if (data && data.trim() !== "") {
                        if (row.prioritas == "NORMAL") {
                            return (
                                '<span class="badge bg-label-primary fw-bold">' +
                                row.prioritas +
                                "</span>"
                            );
                        }
                        return (
                            '<span class="badge bg-label-danger fw-bold">' +
                            row.prioritas +
                            "</span>"
                        );
                    }
                    return data;
                },
            },
            { data: "keterangan", name: "keterangan" },
            {
                data: "photo",
                name: "photo",
                render: function (data, type, row) {
                    if (!data || data.trim() === "") {
                        return "-"; // tampilkan tanda strip
                    }
                    return (
                        '<img src="' +
                        data +
                        '" class="img-thumbnail d-block mx-auto" alt="image" width="70">'
                    );
                },
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
        // order: [[1, "desc"]],
        ordering: false,
        rowCallback: function (row, data) {
            if (data.hari === "Minggu") {
                $(row).css("background-color", "#f8d7da"); // merah muda
                $(row).css("color", "#721c24"); // teks merah tua biar kontras
            }
        },
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

            setTimeout(() => {
                $.unblockUI();
            }, 300);
        });
        fv.revalidateField("karyawanid");
        table.clear();
        table.draw();
        $(".btnDownload").prop("disabled", true);
        $("#dtList").css("display", "none");
    });

    const formValidationPerorang = document.getElementById("formPerorang");
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
                var formData = $("#formPerorang").serialize();

                $.ajax({
                    url: "/get-monitoring-perorang",
                    type: "GET",
                    data: formData,
                    success: function (response) {
                        setTimeout(() => {
                            $("#dtList").css("display", "block");
                            $.unblockUI();
                        }, 300);
                        // response dari controller biasanya punya key 'data'
                        let data = response.data ? response.data : response;

                        // Masukkan data ke DataTable
                        table.clear();
                        table.rows.add(data);
                        table.draw();

                        $(".btnDownload").prop("disabled", false);
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
    });

    $(document).on("click", ".btnDownload", function () {
        _loading();
        var formData = $("#formPerorang").serialize();
        $.ajax({
            url: "/monitoring-perorang",
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
