_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        // $("#tableSchedule").DataTable({ ordering: false });
        $.unblockUI();
    }, 300);

    var startDate = $("#periode").flatpickr({
        disableMobile: "true",
        // minDate: new Date().setMonth(new Date().getMonth() - 3),
        maxDate: new Date().setMonth(new Date().getMonth() + 1),
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            }),
        ],
        onChange: function (selectedDates) {
            loadDataTable();
        },
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
            resetRekapTable();
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
                        ' me-1 text-black">' +
                        node.text +
                        "</span>",
                );
            });

            // tampilkan badge di form
            $("#lokasipekerjaan").html(badges.join(" "));

            setTimeout(() => {
                fv.revalidateField("projectid");
                $("#modalLokasi").modal("hide");
                loadDataTable();
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

    const formValidationSchedule = document.getElementById("formShcedule");
    const fv = FormValidation.formValidation(formValidationSchedule, {
        fields: {
            projectid: {
                validators: {
                    notEmpty: {
                        message: "Lokasi Penempatan masih kosong.",
                    },
                },
            },
            periode: {
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

    formValidationSchedule.addEventListener("submit", function (e) {
        e.preventDefault();
        fv.validate().then(function (status) {
            if (status === "Valid") {
                _loading();
                var formData = $("#formShcedule").serialize();
                $.ajax({
                    url: "/data-schedule",
                    type: "POST",
                    data: formData,
                    xhrFields: {
                        responseType: "blob", // penting untuk file
                    },
                    success: function (response, status, xhr) {
                        let filename = "schedule.pdf";
                        const disposition = xhr.getResponseHeader(
                            "Content-Disposition",
                        );
                        if (
                            disposition &&
                            disposition.indexOf("filename=") !== -1
                        ) {
                            filename = disposition
                                .split("filename=")[1]
                                .replace(/"/g, "");
                        }

                        const blob = new Blob([response], {
                            type: "application/pdf",
                        });
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
                    error: function (xhr, status, error) {
                        $.unblockUI();
                        if (status === "timeout") {
                            Swal.fire({
                                title: "Timeout",
                                html: "Tidak ada respon dari server. <br>Silahkan persempit lagi lokasi penempatannya",
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
                        } else {
                            Swal.fire({
                                title: status,
                                text: error,
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
                        }
                    },
                    complete: function () {
                        setTimeout(() => {
                            $.unblockUI();
                        }, 300);
                    },
                });
            }
        });
    });

    function loadDataTable() {
        fv.validate().then(function (status) {
            if (status === "Valid") {
                _loading();
                var formData = $("#formShcedule").serialize();

                resetRekapTable();

                $.get("/data-schedule?" + formData, function (res) {
                    if (res.data.length > 0) {
                        $("#btnDownload").removeAttr("disabled", "disabled");
                    } else {
                        $("#btnDownload").attr("disabled", "disabled");
                    }

                    // 1. Bangun header dinamis
                    let thead = `
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                        `;

                    res.periode.forEach((tgl) => {
                        thead += `<th>Tgl ${moment(tgl).format("D")}</th>`;
                        // thead += `<th>Tgl ${tgl}</th>`;
                    });
                    thead += "</tr>";

                    $("#tableSchedule thead").html(thead);

                    // 2. Bangun kolom DataTables
                    let columns = [
                        {
                            data: null,
                            render: (data, type, row, meta) => meta.row + 1,
                        },
                        { data: "nip" },
                        { data: "nama" },
                    ];

                    res.periode.forEach((tgl) => {
                        columns.push({
                            data: tgl,
                            render: function (data, type, row) {
                                // kalau id = 0 → tidak clickable
                                if (row.id === 0) {
                                    // cek apakah hari libur (L)
                                    if (data === "L") {
                                        return `<div class="text-center fw-bold bg-danger text-white">${data}</div>`;
                                    }
                                    return `<div class="text-center fw-bold">${data}</div>`;
                                }

                                // kalau id ≠ 0 → clickable
                                if (data === "L") {
                                    return `<div class="text-center fw-bold clickable bg-danger text-white" 
                                                data-id="${row.id}" 
                                                data-tgl="${tgl}"
                                                data-nip="${row.nip}"
                                                data-nama="${row.nama}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                data-bs-custom-class="tooltip-danger"
                                                title="Click to Action">
                                                ${data}
                                            </div>
                                    `;
                                }

                                if (data === "-" || data === null) {
                                    return `<div class="text-center fw-bold">${data ?? "-"}</div>`;
                                }

                                return `<div class="text-center fw-bold ${data == "-" ? "" : "clickable"}"
                                            data-id="${row.id}" 
                                            data-tgl="${tgl}"
                                            data-nip="${row.nip}"
                                            data-nama="${row.nama}"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-bs-custom-class="tooltip-primary"
                                            title="Click to Action">
                                            ${data}
                                        </div>
                                `;
                            },
                        });
                    });

                    // 3. Inisialisasi DataTables
                    $("#tableSchedule").DataTable({
                        // destroy: true,
                        data: res.data,
                        columns: columns,
                        ordering: true,
                        responsive: true,
                        drawCallback: function () {
                            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
                            var tooltipTriggerList = [].slice.call(
                                document.querySelectorAll(
                                    '#tableSchedule [data-bs-toggle="tooltip"]',
                                ),
                            );
                            var tooltipList = tooltipTriggerList.map(
                                function (tooltipTriggerEl) {
                                    return new bootstrap.Tooltip(
                                        tooltipTriggerEl,
                                    );
                                },
                            );
                        },
                    });
                });

                setTimeout(() => {
                    $.unblockUI();
                }, 200);
            }
        });
    }

    function resetRekapTable() {
        if ($.fn.DataTable.isDataTable("#tableSchedule")) {
            $("#tableSchedule").DataTable().clear().destroy();
        }
        $("#tableSchedule thead").empty();
        $("#tableSchedule tbody").empty();
        $("#tableSchedule").removeAttr("style");
    }

    $("#tableSchedule").on("click", ".clickable", function () {
        _loading();
        let id = $(this).data("id");
        let tgl = $(this).data("tgl");
        let nip = $(this).data("nip");
        let nama = $(this).data("nama");

        let disabledPergantian = tgl < new Date().toLocaleDateString("sv-SE");

        let modalChoise = `
            <div class="col-12 mb-3">
                <div class="form-check custom-option custom-option-basic ${disabledPergantian ? "bg-label-secondary" : "bg-label-primary"}">
                    <label class="form-check-label custom-option-content ps-3 ${disabledPergantian ? "disabled" : ""}" for="customRadioTemp1"
                        ${disabledPergantian ? "" : `data-action="pergantian" data-token="${id}" data-tanggal="${tgl}" `}>
                        <input name="customRadioTemp" class="form-check-input d-none" type="radio"
                            value="" id="customRadioTemp1" ${disabledPergantian ? "disabled" : ""}/>
                        <span class="d-flex align-items-start">
                            <i class="ti ti-arrows-vertical ti-xl me-3"></i>
                            <span>
                                <span class="custom-option-header">
                                    <span class="h4 mb-2">Pergantian Schedule</span>
                                </span>
                                <span class="custom-option-body">
                                    <span class="mb-0">
                                        Pergantian schedule antar karyawan.
                                        ${disabledPergantian ? "<br><small class='text-danger'>Tidak tersedia untuk tanggal yang sudah terlewat</small>" : ""}
                                    </span>
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-check custom-option custom-option-basic bg-label-warning">
                    <label class="form-check-label custom-option-content ps-3" for="customRadioTemp2"
                        data-action="perubahan" data-token="${id}" data-tanggal="${tgl}">
                        <input name="customRadioTemp" class="form-check-input d-none" type="radio"
                            value="" id="customRadioTemp2" />
                        <span class="d-flex align-items-start">
                            <i class="ti ti-pencil ti-xl me-3"></i>
                            <span>
                                <span class="custom-option-header">
                                    <span class="h4 mb-2">Perubahan Schedule</span>
                                </span>
                                <span class="custom-option-body">
                                    <span class="mb-0">
                                        Perubahan schedule karyawan.
                                    </span>
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        `;

        $("#myModal h3").html("Tanggal : " + moment(tgl).format("DD-MM-YYYY"));
        $("#myModal .p-title").html(nip + " | " + nama);
        $("#myModal #choise").html(modalChoise);
        $("#myModal").modal("show");
        setTimeout(() => {
            $.unblockUI();
        }, 200);
    });

    $(document).on("click", " #myModal label[data-action]", function (e) {
        e.preventDefault();
        let action = $(this).data("action");
        let token = $(this).data("token");
        let tgl = $(this).data("tanggal");

        _loading();
        if (action === "pergantian") {
            $("#formPergantian")[0].reset();
            $.get(
                "/pergantian-schedule?token=" +
                    encodeURIComponent(token) +
                    "&tgl=" +
                    tgl,
                function (res) {
                    var data = res.data;
                    $("#formPergantian #namaKaryawan").html(
                        data.karyawan.nip + " | " + data.karyawan.nama,
                    );
                    $("#formPergantian #fromId").val(data.id);
                    $("#formPergantian #fromScLama").val(data.valKolom);
                    $("#formPergantian #toScLama").val(data.valKolom);

                    $("#formPergantian #karyawan").empty();
                    $("#formPergantian #karyawan").append(
                        '<option value="">pilih</option>',
                    );

                    res.karyawans.forEach((kar) => {
                        $("#formPergantian #karyawan").append(
                            '<option value="' +
                                kar.token_id +
                                '" data-tanggal="' +
                                tgl +
                                '">' +
                                kar.nip +
                                " | " +
                                kar.nama +
                                "</option>",
                        );
                    });

                    setTimeout(() => {
                        $("#myModal").modal("hide");
                        $("#modalPergantian .modal-title").html(
                            "Detail Schedule " +
                                moment(tgl).format("DD-MM-YYYY"),
                        );
                        $("#modalPergantian").modal("show");
                        $.unblockUI();
                    }, 200);
                },
            );

            setTimeout(() => {
                $.unblockUI();
            }, 200);
        } else if (action === "perubahan") {
            $("#formEdit")[0].reset();
            $.get(
                "/detail-schedule?token=" +
                    encodeURIComponent(token) +
                    "&tanggal=" +
                    tgl,
                function (res) {
                    var lokasi = [];
                    var color = [
                        "bg-success",
                        "bg-warning",
                        "bg-primary",
                        "bg-info",
                        "bg-secondary",
                        "bg-dark",
                    ];
                    $.each(res.lokasi, function (id, data) {
                        lokasi.push(
                            '<span class="badge ' +
                                color[id] +
                                ' me-1 text-black mt-1 mb-1">' +
                                data +
                                "</span>",
                        );
                    });
                    $("#formEdit #lokasi").html(lokasi.join(" "));
                    $("#formEdit #nip").val(res.karyawan.nip);
                    $("#formEdit #nama").val(res.karyawan.nama);
                    $("#formEdit #oldSchedule").val(res.valKolom);
                    $("#formEdit #ids").val(res.id);
                    $("#formEdit #kolom").val(res.namaKolom);
                },
            );

            setTimeout(() => {
                $("#modalEdit .modal-title").html(
                    "Detail Schedule " + moment(tgl).format("DD-MM-YYYY"),
                );
                $("#myModal").modal("hide");
                $("#modalEdit").modal("show");
                $.unblockUI();
            }, 200);
        }
    });

    $(document).on("change", "#karyawan", function () {
        var tokenId = $(this).val();
        let tanggal = $(this).find(":selected").data("tanggal");
        _loading();
        $.getJSON(
            "/penggati-schedule/?token=" +
                encodeURIComponent(tokenId) +
                "&tanggal=" +
                tanggal,
            function (res) {
                $("#formPergantian #toId").val(res.id);
                $("#formPergantian #fromScBaru").val(res.valKolom);
                $("#formPergantian #toScBaru").val(res.valKolom);
                $("#formPergantian #namaKolom").val(res.namaKolom);
            },
        );
        setTimeout(() => {
            $.unblockUI();
        }, 200);
    });

    const formValidationPergantian = document.getElementById("formPergantian");
    const fp = FormValidation.formValidation(formValidationPergantian, {
        fields: {
            karyawan: {
                validators: {
                    notEmpty: {
                        message: "silahkan pilih karyawan.",
                    },
                },
            },
            fromScBaru: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong.",
                    },
                },
            },
            toScBaru: {
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
                rowSelector: ".fv-row",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    formValidationPergantian.addEventListener("submit", function (e) {
        e.preventDefault();
        fp.validate().then(function (status) {
            if (status === "Valid") {
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Simpan Perubahan Pergantian Schedule?",
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
                        var formData = $("#formPergantian").serialize();

                        $.ajax({
                            url: "/pergantian-schedule",
                            type: "POST",
                            data: formData,
                            success: function (response) {
                                $("#modalPergantian").modal("hide");
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
                                    loadDataTable();
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

    $("#formEdit").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Perubahan Schedule?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                var formData = $("#formEdit").serialize();

                $.ajax({
                    url: "/update-schedule",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        $("#modalEdit").modal("hide");
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
                            loadDataTable();
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

    $(document).on("click", ".uploadSchedule", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/upload-schedule";
            $.unblockUI();
        }, 200);
    });
});
