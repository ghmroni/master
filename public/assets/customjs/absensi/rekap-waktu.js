_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        // $("#tableWaktu").DataTable({ ordering: false });
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

    const formValidationKehadiran = document.getElementById("formWaktu");
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
        var formData = $("#formWaktu").serialize();
        $.post("/rekap-waktu", formData, function (res) {
            if (res.status === "queued") {
                pollJob(res.job_id);
            }
        }, "json");
    }

    function loadDataTable() {
        $("#dtViews").css("display", "block");
        var formData = $("#formWaktu").serialize();

        $.get("/rekap-waktu?" + formData, function (res) {
            // 1. Bangun header dinamis
            let thead = `
                    <tr class="text-center">
                        <th rowspan="2">No</th>
                        <th rowspan="2">NIP</th>
                        <th rowspan="2">Nama Lengkap</th>
                        `;
            res.periode.forEach((tgl) => {
                thead += `<th class="text-center" colspan="2">Tgl ${tgl}</th>`;
            });

            thead += `</tr>
                    <tr class="text-center">`;
            res.periode.forEach((tgl) => {
                thead += `<th class="text-center">IN</th><th class="text-center">OUT</th>`;
            });
            thead += "</tr>";
            $("#tableWaktu thead").html(thead);

            // 2. Bangun kolom DataTables
            let columns = [
                { data: null, render: (data, type, row, meta) => meta.row + 1 },
                { data: "nip" },
                { data: "nama" },
            ];
            res.periode.forEach((tgl, idx) => {
                // Kolom IN
                columns.push({
                    data: `kehadiran.${idx}.masuk`,
                    render: function (data) {
                        return data && data !== "-"
                            ? `<div class="text-center fw-bold">${data}</div>`
                            : `<div class="text-center">-</div>`;
                    },
                });

                // Kolom OUT
                columns.push({
                    data: `kehadiran.${idx}.pulang`,
                    render: function (data) {
                        return data && data !== "-"
                            ? `<div class="text-center fw-bold">${data}</div>`
                            : `<div class="text-center">-</div>`;
                    },
                });
            });

            // 3. Inisialisasi DataTables
            $("#tableWaktu").DataTable({
                destroy: true,
                data: res.data,
                columns: columns,
                ordering: false,
                responsive: false,
            });
        });
    }

    function resetRekapTable() {
        $("#dtViews").css("display", "none");
        if ($.fn.DataTable.isDataTable("#tableWaktu")) {
            $("#tableWaktu").DataTable().clear().destroy();
        }
        $("#tableWaktu thead").empty();
        $("#tableWaktu tbody").empty();
    }

    function pollJob(jobId) {
        // const interval = setInterval(() => {
        //     $.getJSON(`/download-status/${jobId}`, function (res) {
        //         if (res.status === "done") {
        //             clearInterval(interval);
        //             fetchFile(`/download-file/${jobId}`);
        //         }
        //     });
        // }, 3000);

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
        // $.ajax({
        //     url: url,
        //     type: "GET",
        //     xhrFields: { responseType: "blob" },
        //     success: function (response) {
        //         const blob = new Blob([response], { type: "application/pdf" });
        //         const link = document.createElement("a");
        //         link.href = window.URL.createObjectURL(blob);
        //         link.download = "Rekap_Waktu.pdf";
        //         link.click();
        //     },
        //     complete: function () {
        //         $.unblockUI();
        //     }
        // });
        $.ajax({
            url: url,
            type: "GET",
            xhrFields: { responseType: "blob" },
            timeout: 60000,
            success: function (response) {
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = "Rekap_Waktu.pdf";
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
