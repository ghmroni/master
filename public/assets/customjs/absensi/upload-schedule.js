_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $(".dropify").dropify();

    $("#periode").flatpickr({
        disableMobile: "true",
        // minDate: flatpickr.formatDate(new Date(), "Y-m"),
        minDate: flatpickr.formatDate(
            new Date(new Date().getFullYear(), new Date().getMonth(), 1),
            "Y-m",
        ),
        maxDate: flatpickr.formatDate(
            new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1),
            "Y-m",
        ),
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            }),
        ],
    });

    setTimeout(function () {
        monitoringImport();
        $.unblockUI();
    }, 300);

    $(document).on("click", ".dataSchedule", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/data-schedule";
        }, 300);
    });

    const formValidationSchedule = document.getElementById("formSchedule");
    const fv = FormValidation.formValidation(formValidationSchedule, {
        fields: {
            periode: {
                validators: {
                    notEmpty: {
                        message: "Periode Schedule masih kosong",
                    },
                },
            },
            file: {
                validators: {
                    notEmpty: {
                        message: "File upload masih kosong",
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleInvalidClass: "is-invalid",
                eleValidClass: "",
                rowSelector: ".row, .col-md-12",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    formValidationSchedule.addEventListener("submit", function (e) {
        e.preventDefault();

        fv.validate().then(function (status) {
            if (status === "Valid") {
                // Submit your form
                const formData = new FormData(formValidationSchedule);
                var periode = $("#periode").val();
                // Pisahkan tahun dan bulan
                var parts = periode.split("-");
                var tahun = parts[0];
                var bulan = parts[1];

                // Array nama bulan
                var namaBulan = [
                    "Januari",
                    "Februari",
                    "Maret",
                    "April",
                    "Mei",
                    "Juni",
                    "Juli",
                    "Agustus",
                    "September",
                    "Oktober",
                    "November",
                    "Desember",
                ];

                // Format jadi "September 2026"
                var periodeFormat =
                    namaBulan[parseInt(bulan, 10) - 1] + " " + tahun;

                Swal.fire({
                    title: "Konfirmasi!",
                    html:
                        "Upload Schedule Periode <strong>" +
                        periodeFormat +
                        "</strong> ?",
                    icon: "warning",
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
                        $.ajax({
                            url: "/import-schedule",
                            type: "POST",
                            dataType: "JSON",
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
                                        var drEvent = $("#file").dropify();
                                        // clear value
                                        drEvent = drEvent.data("dropify");
                                        drEvent.clearElement();
                                        monitoringImport();
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
                                        let response = JSON.parse(
                                            error.responseText,
                                        );
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
            }
        });
    });

    function monitoringImport() {
        $.getJSON("/import/cek-import", function (response) {
            $("#statuBar").empty();
            if (!$.isEmptyObject(response)) {
                // buat wrapper card
                var $card = $(
                    '<div class="card card-body mt-3 border shadow" style="display:none"></div>',
                );

                $.each(response, function (index, data) {
                    var logId = data.id;

                    // Pastikan tidak append duplikat jika fungsi ini dipanggil berulang
                    if ($("#import-item-" + logId).length === 0) {
                        $card.append(`
                        <div id="import-item-${logId}" class="text-light small fw-medium mb-2 mt-1">
                            <label class="label-title mb-1"><span class="text-info">${data.title}</span> : <span class="status-text ">${data.status}</span></label>
                            <div class="progress">
                                <div id="bar-${logId}" class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                                    style="width: 0%" role="progressbar">
                                    <label class="label-percent text-black">0%</label>
                                </div>
                            </div>
                        </div>
                    `);
                    }

                    // Jalankan monitoring untuk ID ini
                    startMonitoring(logId);
                });

                // masukkan card ke statuBar
                $("#statuBar").append($card);
                $card.slideDown(600);
            }
        });
    }

    function startMonitoring(logId) {
        var interval = setInterval(function () {
            $.get("/import/status/" + logId, function (status) {
                var $wrapper = $("#import-item-" + logId);
                var $bar = $("#bar-" + logId);

                // 1. Update Persentase & Label
                $bar.css("width", status.progress + "%");
                $bar.find(".label-percent").text(status.progress + "%");
                $wrapper.find(".status-text").text(status.status);

                // 2. Logika Berhasil
                if (status.progress >= 100 || status.status === "success") {
                    $bar.removeClass(
                        "bg-warning progress-bar-animated",
                    ).addClass("bg-success");
                    $wrapper.find(".status-text").text("Selesai");

                    clearInterval(interval);
                    showToast(
                        "Berhasil",
                        status.title + "Selesai Diimport.",
                        "success",
                    );
                    // Hilangkan bar setelah 5 detik agar rapi
                    setTimeout(
                        () =>
                            $wrapper.fadeOut(1000, function () {
                                $(this).remove();
                            }),
                        5000,
                    );
                }

                // 3. Logika Gagal
                if (status.status === "failed") {
                    $bar.removeClass(
                        "bg-warning progress-bar-animated",
                    ).addClass("bg-danger");
                    $wrapper
                        .find(".status-text")
                        .text("Gagal: " + (status.message || "Error"));
                    showToast("Gagal", status.message, "error");
                    clearInterval(interval);
                }
            });
        }, 2000); // Jangan terlalu cepat (100ms), 2 detik cukup agar tidak membebani server
    }

    $(document).on("click", ".templateUpload", function () {
        _loading();
        $("#formTemplate")[0].reset();
        $("#jenis").val("").trigger("change");
        $("#lokasipekerjaan").html("");
        setTimeout(function () {
            $("#modalTemplate").modal("show");
            $.unblockUI();
        }, 300);
    });

    var theme = $("html").hasClass("light-style") ? "default" : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", "#changeLokasi, #lokasipekerjaan", function () {
        _loading();
        basicTree.jstree({
            core: {
                data: {
                    url: "/akses-project-tree",
                    data: function (node) {
                        return { id: node.id };
                    },
                    dataType: "json",
                },
                check_callback: true,
                themes: {
                    responsive: false,
                    name: theme,
                },
            },
            // plugins: ["state", "contextmenu", "wholerow"],
            plugins: ["search", "types", "state"],
            types: {
                default: { icon: "far fa-folder-open text-warning" },
            },
        });
        // hilangkan semua selected node
        basicTree.jstree("deselect_all", true);
        $("#modalLokasi").modal("show");
        setTimeout(() => {
            $.unblockUI();
        }, 300);
    });

    $("#modalLokasi").on("shown.bs.modal", function () {
        var tree = $("#jstree-project").jstree(true);
        // tutup semua node
        tree.close_all();
        // hilangkan semua selected node
        tree.deselect_all(true);
    });

    var to = false;
    $("#cari").keyup(function () {
        if (to) {
            clearTimeout(to);
        }
        to = setTimeout(function () {
            var v = $("#cari").val();
            basicTree.jstree(true).search(v);
        }, 250);
    });

    $(document).on("click", ".changeProject", function () {
        _loading();
        var tree = $("#jstree-project").jstree(true);
        var selectedId = tree.get_selected()[0];

        if (selectedId) {
            var selectedNode = tree.get_node(selectedId);
            // ambil semua parent id dari node ini
            var parentIds = selectedNode.parents.slice().reverse();
            // tambahkan id node yang dipilih
            parentIds.push(selectedNode.id);
            // filter supaya '#' tidak ikut
            var cleanIds = parentIds.filter(function (id) {
                return id !== "#";
            });
            // gabungkan jadi string dengan koma
            var idsString = cleanIds.join(",");
            // lempar ke input
            $("#projectid").val(selectedId);
            $("#dtproject").val(idsString);

            // buat badge untuk setiap node di path
            var color = [
                "bg-success",
                "bg-warning",
                "bg-primary",
                "bg-info",
                "bg-secondary",
                "bg-dark",
            ];
            var badges = [];
            $.each(cleanIds, function (idx, id) {
                var node = tree.get_node(id);
                badges.push(
                    '<span class="badge ' +
                    color[idx % color.length] + // looping warna
                    ' me-1 mt-1 text-black">' +
                    node.text +
                    "</span>",
                );
            });

            // tampilkan badge di form
            $("#lokasipekerjaan").html(badges.join(" "));

            setTimeout(() => {
                $("#modalLokasi").modal("hide");
                $.unblockUI();
            }, 300);
        } else {
            $.unblockUI();
            Swal.fire({
                title: "Oops!",
                text: "Silahkan pilih lokasi terlebih dahulu.",
                icon: "error",
                allowOutsideClick: false,
                showClass: {
                    popup: "animate__animated animate__shakeX",
                },
                hideClass: {
                    popup: `
                        animate__animated
                        animate__bounceOut
                        animate__faster
                    `,
                },
                customClass: {
                    confirmButton: "btn btn-primary waves-effect waves-light",
                },
                buttonsStyling: false,
            });
        }
    });

    $("#formTemplate").submit(function (event) {
        event.preventDefault();
        _loading();
        $.ajax({
            url: "/template-upload",
            type: "POST",
            data: $("#formTemplate").serialize(),
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
});
