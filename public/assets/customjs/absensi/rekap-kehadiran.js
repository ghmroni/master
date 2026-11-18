_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        // $("#tableRekap").DataTable({ ordering: false });
        $.unblockUI();
    }, 300);

    $("#dari").change(function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
        resetRekapTable();
    });

    $("#sampai").change(function () {
        resetRekapTable();
    });

    $("#schedule").on("change", function () {
        resetRekapTable();
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

    const formValidationKehadiran = document.getElementById("formKehadiran");
    const fv = FormValidation.formValidation(formValidationKehadiran, {
        fields: {
            projectid: {
                validators: {
                    notEmpty: {
                        message: "Lokasi Penempatan masih kosong.",
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

    let clickedAction = null;

    // Tangkap tombol mana yang diklik
    $(formValidationKehadiran).on("click", "button[type=submit]", function () {
        clickedAction = $(this).val(); // "view" atau "download"
    });

    formValidationKehadiran.addEventListener("submit", function (e) {
        e.preventDefault();
        fv.validate().then(function (status) {
            if (status === "Valid") {
                if (clickedAction === "view") {
                    // 👉 Jalankan fungsi DataTable
                    loadDataTable();
                } else if (clickedAction === "download") {
                    // 👉 Jalankan fungsi download
                    downloadRekap();
                }
            }
        });
    });

    function downloadRekap() {
        _loading();
        var formData = $("#formKehadiran").serialize();
        $.post("/rekap-kehadiran", formData, function (res) {
            if (res.status === "queued") {
                pollJob(res.job_id);
            }
        }, "json");
    }

    function loadDataTable() {
        $("#dtViews").css("display", "block");
        var formData = $("#formKehadiran").serialize();

        $.get("/rekap-kehadiran?" + formData, function (res) {
            // 1. Bangun header dinamis
            let thead = `
                    <tr class="text-center">
                        <th rowspan="2">No</th>
                        <th rowspan="2">NIP</th>
                        <th rowspan="2">Nama Lengkap</th>
                        <th colspan="${res.periode.length}" class="text-center">Tanggal Rekap</th>
                    </tr>
                    <tr class="text-center">
                `;
            res.periode.forEach((tgl) => {
                thead += `<th>${moment(tgl).format("DD-MM-YYYY")}</th>`;
            });
            thead += "</tr>";
            $("#tableRekap thead").html(thead);

            // 2. Bangun kolom DataTables
            let columns = [
                { data: null, render: (data, type, row, meta) => meta.row + 1 },
                { data: "nip" },
                { data: "nama" },
            ];
            res.periode.forEach((tgl) => {
                // columns.push({ data: tgl });
                columns.push({
                    data: tgl,
                    render: function (data) {
                        if (
                            data === null ||
                            data === "" ||
                            typeof data === "undefined"
                        ) {
                            return `<div class="text-center">-</div>`; // tampil strip kalau kosong
                        }
                        return `<div class="text-center fw-bold">${data}</div>`;
                    },
                });
            });

            // 3. Inisialisasi DataTables
            $("#tableRekap").DataTable({
                destroy: true,
                data: res.data,
                columns: columns,
                ordering: false,
                responsive: true,
            });
        });
    }

    function resetRekapTable() {
        $("#dtViews").css("display", "none");
        if ($.fn.DataTable.isDataTable("#tableRekap")) {
            $("#tableRekap").DataTable().clear().destroy();
        }
        $("#tableRekap thead").empty();
        $("#tableRekap tbody").empty();
    }

    function pollJob(jobId) {
        const interval = setInterval(() => {
            $.ajax({
                url: `/download-status/${jobId}`,
                type: "GET",
                dataType: "json",
                timeout: 30000,
                success: function (res) {

                    if (res.status === "done") {
                        clearInterval(interval);
                        // fetchFile(res.url);  // pakai URL dari server
                        clearInterval(interval);
                        fetchFile(`/download-file/${jobId}`);
                    }

                    if (res.status === "failed") {
                        clearInterval(interval);
                        $.unblockUI();

                        Swal.fire({
                            title: 'Error',
                            text: "Proses Download gagal. Silakan coba lagi.",
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

                error: function (xhr, status, error) {
                    clearInterval(interval);
                    $.unblockUI();
                    console.log(status);

                    Swal.fire({
                        title: 'Error',
                        text: status === "timeout"
                            ? "Server tidak merespon, coba lagi nanti."
                            : "Gagal cek status job: " + error,
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
            });
        }, 3000);
    }

    function fetchFile(url) {
        $.ajax({
            url: url,
            type: "GET",
            xhrFields: { responseType: "blob" },
            timeout: 60000,
            success: function (response) {
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = "Rekap_kehadiran.pdf";
                link.click();
            },
            error: function (xhr, status, error) {
                $.unblockUI();
                Swal.fire({
                    title: "Download gagal",
                    text: status === "timeout"
                        ? "Server tidak merespon saat download file."
                        : "Error: " + error,
                    icon: "error"
                });
            },
            complete: function () {
                $.unblockUI();
            }
        });
    }
});
