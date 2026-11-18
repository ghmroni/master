_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#pelamarUser").DataTable({
        ajax: "/banksdm",
        processing: true,
        serverSide: true,
        responsive: true,
        aaSorting: [[7, "asc"]],
        columns: [
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
            },
            {
                data: "panggil",
                name: "panggil",
                orderable: true,
                searchable: true,
            },
            {
                data: "lowongan_apply.lowongan.project.inisial",
            },
            {
                data: "lowongan_apply.pelamar.nama",
            },
            {
                data: "lowongan_apply.pelamar.jenjang_pendidikan",
            },
            {
                data: "lowongan_apply.pelamar.jurusan_sekolah",
            },
            {
                data: "usia",
                name: "usia",
                orderable: true,
                searchable: true,
            },
            {
                data: "ceklis",
                name: "ceklis",
                orderable: false,
                searchable: false,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center",
            },
            {
                targets: [0, 1, 4, 6, 7],
                className: "dt-center",
            },
        ],
    });

    // Array global untuk menampung semua ID yang dipilih
    let selectedIds = [];

    $(document).on("change", ".check-kirim", function () {
        const id = $(this).val();
        if ($(this).is(":checked")) {
            if (!selectedIds.includes(id)) selectedIds.push(id);
        } else {
            selectedIds = selectedIds.filter((item) => item !== id);
        }
        // update jumlah ke Blade
        $("#totalDipilih").text(selectedIds.length + " dipilih");
    });

    // cek dan checked di page berbeda apabila sudah di checked sebelumnya
    $("#pelamarUser").on("draw.dt", function () {
        $(".check-kirim").each(function () {
            const id = $(this).val();
            $(this).prop("checked", selectedIds.includes(id));
        });

        // update header select-all sesuai kondisi baris
        const total = $(".check-kirim").length;
        const checked = $(".check-kirim:checked").length;
        $("#select-all").prop("checked", total > 0 && total === checked);
    });

    // ketika header select-all diubah
    $(document).on("change", "#select-all", function () {
        const checked = $(this).is(":checked");

        // centang/uncentang semua checkbox di halaman saat ini
        $(".check-kirim").prop("checked", checked);

        if (checked) {
            // tambahkan semua ID yang ada di halaman ke selectedIds
            $(".check-kirim").each(function () {
                const id = $(this).val();
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
            });
        } else {
            // hapus semua ID yang ada di halaman dari selectedIds
            const pageIds = $(".check-kirim")
                .map(function () {
                    return $(this).val();
                })
                .get();

            selectedIds = selectedIds.filter((id) => !pageIds.includes(id));
        }
        // update jumlah
        $("#totalDipilih").text(selectedIds.length + " dipilih");
    });

    document
        .getElementById("formUser")
        .addEventListener("submit", function (e) {
            // Keperluan Project
            if (!$("#keperluanid").val()) {
                e.preventDefault(); // cegah submit
                $("#keperluan").addClass("is-invalid"); // border merah
                $("#errorMsg").css("display", "block"); // Pesan
                return;
            } else {
                $("#keperluan").removeClass("is-invalid"); // hapus border merah kalau sudah diisi
                $("#errorMsg").css("display", "none"); // Pesan
            }

            // Cek Checklis Pelamar
            if (selectedIds.length === 0) {
                e.preventDefault();
                $("#errorChecklist").show();
                return;
            } else {
                $("#errorChecklist").hide();
            }

            // Tambahkan hidden input ke form
            selectedIds.forEach(function (id) {
                $("<input>")
                    .attr({
                        type: "hidden",
                        name: "noreg[]",
                        value: id,
                    })
                    .appendTo("form");
            });

            e.preventDefault();
            Swal.fire({
                title: "Konfirmasi!",
                text:
                    "Sebanyak " + selectedIds.length + " akan dikirim ke user.",
                icon: "question",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Kirim",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-info me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: "/banksdm",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            noreg: selectedIds,
                            keperluan: $("#keperluanid").val(),
                        },
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.reload();
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {
                                        // Gabungkan semua pesan error jadi satu string
                                        errorMessage = Object.values(
                                            response.errors,
                                        )
                                            .flat()
                                            .join("\n");
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
                                        "btn btn-danger waves-effect waves-light",
                                },
                                buttonsStyling: false,
                            });
                        },
                    });
                }
            });
        });

    let selectedResend = [];
    let catatanResend = [];
    let table1;
    $(document).on("click", ".resendKandidat", function () {
        _loading();
        $("#kirimUlang").modal("show");
        // jika tabel sudah ada, inisialisasi DataTable
        if ($.fn.DataTable.isDataTable("#tabelResend")) {
            table1.destroy();
        }

        table1 = $("#tabelResend").DataTable({
            ajax: "/banksdm/resend",
            processing: true,
            serverSide: true,
            // responsive: true,
            aaSorting: [[4, "desc"]],
            columns: [
                {
                    data: "ceklis",
                    name: "ceklis",
                    orderable: false,
                    searchable: false,
                },
                {
                    data: "catatan",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        // tampilkan textarea dengan value dari data
                        return '<textarea class="form-control catatan" rows="2" style="width:300px;"></textarea>';
                    },
                },
                {
                    data: "nama",
                },
                {
                    data: "lokasi",
                    name: "lokasi",
                    orderable: true,
                    searchable: false,
                },
                {
                    data: "tanggal",
                    name: "tanggal",
                    orderable: true,
                    searchable: false,
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [0, 4],
                    className: "dt-center",
                },
            ],
        });
        $.unblockUI();
    });

    $(document).on("change", ".check-resend", function () {
        const id = $(this).val();
        if ($(this).is(":checked")) {
            if (!selectedResend.includes(id)) selectedResend.push(id);
        } else {
            selectedResend = selectedResend.filter((item) => item !== id);
        }
    });

    // cek dan checked di page berbeda apabila sudah di checked sebelumnya
    $("#tabelResend").on("draw.dt", function () {
        $(".check-resend").each(function () {
            const id = $(this).val();
            $(this).prop("checked", selectedResend.includes(id));
        });
    });

    $("#kirimUlang").on("hidden.bs.modal", function () {
        // reset array global
        selectedResend = [];

        // reset semua checkbox ketika modal ditutup
        $('#tabelResend input[type="checkbox"]').prop("checked", false);
    });

    // event listener untuk ceklis
    $("#tabelResend").on("change", ".check-resend", function () {
        let $row = $(this).closest("tr"); // ambil row
        let $textarea = $row.find("textarea"); // ambil textarea di row tsb

        if ($(this).is(":checked")) {
            $textarea.attr("required", true); // tambahkan required
        } else {
            $textarea.removeAttr("required"); // hapus required
            $textarea.val(""); // hapus required
        }
    });

    document
        .getElementById("formResend")
        .addEventListener("submit", function (e) {
            // Cek Checklis Pelamar
            if (selectedResend.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: "Error",
                    text: "Tidak ada kandidat yang dipilih",
                    icon: "error",
                    showClass: {
                        popup: "animate__animated animate__shakeX",
                    },
                    hideClass: {
                        popup: "animate__animated animate__bounceOut",
                    },
                    customClass: {
                        confirmButton:
                            "btn btn-danger waves-effect waves-light",
                    },
                    buttonsStyling: false,
                });
                return;
            }

            // bersihkan hidden input lama
            $('#formResend input[type="hidden"]').remove();

            // tambahkan hidden input untuk noreg dan catatan
            $("#tabelResend tbody tr").each(function () {
                let $row = $(this);
                let $checkbox = $row.find(".check-resend");

                if ($checkbox.is(":checked")) {
                    let id = $checkbox.val();
                    let catatan = $row.find("textarea.catatan").val();

                    // hidden input untuk noreg[]
                    $("<input>")
                        .attr({
                            type: "hidden",
                            name: "noreg[]",
                            value: id,
                        })
                        .appendTo("#formResend");

                    // hidden input untuk catatan[id]
                    $("<input>")
                        .attr({
                            type: "hidden",
                            name: "catatan[" + id + "]",
                            value: catatan,
                        })
                        .appendTo("#formResend");
                }
            });

            e.preventDefault();
            Swal.fire({
                title: "Konfirmasi!",
                text:
                    "Sebanyak " +
                    selectedResend.length +
                    " pelamar akan dilakukan pengiriman ulang ke user.",
                icon: "question",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Kirim",
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
                        url: "/banksdm/resend",
                        type: "POST",
                        dataType: "JSON",
                        data: $("#formResend").serialize(),
                        success: function (response) {
                            $("#kirimUlang").modal("hide");
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                table.ajax.reload();
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
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {
                                        // Gabungkan semua pesan error jadi satu string
                                        errorMessage = response.errors;
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
                                        "btn btn-danger waves-effect waves-light",
                                },
                                buttonsStyling: false,
                            });
                        },
                    });
                }
            });
        });

    // perubahan array children_recursive menjadi children
    function renameChildrenRecursive(node) {
        const { children_recursive, ...rest } = node;
        return {
            ...rest,
            children: children_recursive
                ? children_recursive.map(renameChildrenRecursive)
                : [],
        };
    }

    // TAMPILKAN MODAL PERUBAHAN PROJECT
    var theme = $("html").hasClass("light-style") ? "default" : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", "#changeLokasi, #keperluan", function () {
        _loading();
        basicTree.jstree({
            core: {
                data: {
                    url: "/akses-project-tree",
                    data: function (node) {
                        return { id: node.id };
                    },
                    dataType: "json",
                    // success: function (data) {
                    //     var transformed = data.map(renameChildrenRecursive);
                    //     console.log(transformed);
                    //     return transformed;

                    // }
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
        $.unblockUI();
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

    $("#modalLokasi").on("shown.bs.modal", function () {
        var tree = $("#jstree-project").jstree(true);
        // tutup semua node
        tree.close_all();
        // hilangkan semua selected node
        tree.deselect_all(true);
    });

    const btnChange = $(".changeProject");
    basicTree.on("activate_node.jstree", function (e, data) {
        btnChange.removeAttr("disabled");

        // Clear Data Tombol Edit and Set New Data Tombol Edit
        btnChange.removeData("id");
        btnChange.data("id", data.node.id);
    });

    // PERUBAHAN PROJECT
    $(document).on("click", ".changeProject", function () {
        var id = $(this).data("id");
        if (id) {
            _loading();

            $("#project").val(id);

            $.getJSON("/lokasi-project?lokasi=" + id, function (lok) {
                var lokasi = [];
                var dtId = [];
                var color = [
                    "bg-success",
                    "bg-warning",
                    "bg-primary",
                    "bg-info",
                    "bg-secondary",
                    "bg-dark",
                ];
                $.each(lok, function (idx, data) {
                    dtId.push(data.id);
                    lokasi.push(
                        '<span class="badge ' +
                            color[idx] +
                            ' me-1 text-black">' +
                            data.name +
                            "</span>",
                    );
                });
                $("#keperluanid").val(dtId.join(","));
                $("#keperluan").html(lokasi.join(" "));
            });

            $("#modalLokasi").modal("hide");
            $.unblockUI();
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
    // END PERUBAHAN PROJECT
});
