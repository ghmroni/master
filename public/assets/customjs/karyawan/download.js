_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    var theme = $("html").hasClass("light-style") ? "default" : "default-dark",
        basicTree = $("#jstree-project");

    setTimeout(function () {
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
                multiple: false,
            },
            // plugins: ["state", "contextmenu", "wholerow"],
            plugins: ["search", "types", "state"],
            types: {
                default: { icon: "far fa-folder-open text-warning" },
            },
        });
        // hilangkan semua selected node
        basicTree.on("ready.jstree", function (e, data) {
            data.instance.close_all(); // tutup semua node
            data.instance.deselect_all(true); // hilangkan semua selected
        });
        $.unblockUI();
    }, 300);

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

    // ketika node di-select
    basicTree.on("select_node.jstree", function (e, data) {
        _loading();
        $(".btnDownload").prop("disabled", false);
        setTimeout(() => {
            $.unblockUI();
        }, 150);
    });

    // ketika semua node di-deselect
    basicTree.on(
        "deselect_all.jstree deselect_node.jstree",
        function (e, data) {
            // cek apakah masih ada node yang terpilih
            _loading();
            var selected = basicTree.jstree("get_selected");
            if (selected.length === 0) {
                $(".btnDownload").prop("disabled", true);
                setTimeout(() => {
                    $.unblockUI();
                }, 150);
            }
        },
    );

    $(document).on("click", ".btnDownload", function () {
        var selectedIds = basicTree.jstree("get_selected"); // array id node terpilih
        if (selectedIds.length > 0) {
            var selectedId = selectedIds[0];
            _loading();
            // Kirim ke server dan terima file Excel
            $.ajax({
                url: "/download-karyawan",
                type: "POST",
                data: {
                    id: selectedId,
                    status: $("#status").val(),
                },
                xhrFields: {
                    responseType: "blob", // penting untuk file
                },
                success: function (response, status, xhr) {
                    // 3. Trik otomatis mendownload file di browser menggunakan Javascript
                    const blob = new Blob([response], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = window.URL.createObjectURL(blob);

                    // Beri nama file unduhan (bisa disesuaikan)
                    link.download = "karyawan.xlsx";

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // let link = document.createElement('a');
                    // link.href = window.URL.createObjectURL(blob);
                    // link.download = 'karyawan.xlsx';
                    // link.click();
                    // $.unblockUI();
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
