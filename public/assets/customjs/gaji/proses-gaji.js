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

    $("#periode").flatpickr({
        disableMobile: "true",
        minDate: new Date().setMonth(new Date().getMonth() - 1),
        maxDate: new Date().setMonth(new Date().getMonth() + 1),
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            }),
        ]
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

    const formValidationSchedule = document.getElementById("formGaji");
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
                var formData = $("#formGaji").serialize();
                $.ajax({
                    url: "/prosesGaji",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        setTimeout(() => {
                            $.unblockUI();
                        }, 300);
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
    });

    table = $("#tableGaji").DataTable({
        ajax: "/prosesgaji",
        processing: true,
        serverSide: true,
        aaSorting: [[1, "desc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },

        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 3, 4],
                className: "text-center",
            },
        ],
    });
});
