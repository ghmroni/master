_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#formEdit :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $('#formTambah :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });

    setTimeout(function () {
        getData();
        $.unblockUI();
    }, 100);

    const btnEdit = $(".edit"),
        btnHapus = $(".hapus"),
        btnTambah = $(".tambahSub");

    var theme = $("html").hasClass("light-style") ? "default" : "default-dark",
        basicTree = $("#jstree-project");

    function getData() {
        basicTree
            .jstree({
                core: {
                    data: {
                        url: "/project",
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
            })
            .on("model.jstree", function (e, data) {
                // Ambil instance jsTree yang sedang berjalan
                var instance = $(this).jstree(true);
                // Perulangan berdasarkan array ID node
                $.each(data.nodes, function (index, nodeId) {
                    // Ambil objek node utuh berdasarkan ID nya
                    var node = instance.get_node(nodeId);
                    // Mengecek properti status dari data asli (server)
                    // Sesuaikan properti 'status === false' atau 'status === 0' tergantung output server Anda
                    if (node.original && node.original.status === "DISABLE") {
                        node.li_attr = node.li_attr || {};
                        node.li_attr.class =
                            (node.li_attr.class || "") + " node-tidak-aktif";
                    }
                });
            });

        basicTree.on("ready.jstree", function (e, data) {
            data.instance.close_all(); // tutup semua node
            data.instance.deselect_all(true); // hilangkan semua selected
        });
    }

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

    basicTree.on("activate_node.jstree", function (e, data) {
        var parents = data.node.parents;

        btnEdit.removeAttr("disabled");
        btnHapus.removeAttr("disabled");
        btnTambah.removeAttr("disabled");

        // Clear Data Tombol Edit and Set New Data Tombol Edit
        btnEdit.removeData("id");
        btnEdit.removeData("parent");
        btnEdit.removeData("ptid");
        btnEdit.removeData("parents");

        btnEdit.data("id", data.node.id);
        btnEdit.data("parent", data.node.parent);
        btnEdit.data("ptid", data.node.original.pt_id);
        btnEdit.data("parents", parents);
        // Clear Data Tombol Edit and Set New Data Tombol Edit
        btnHapus.removeData("id");
        btnHapus.removeData("parent");
        btnHapus.data("id", data.node.id);
        btnHapus.data("parent", data.node.parent);

        // Clear Data Tambah Edit and Set New Data Tombol Tambah
        btnTambah.removeData("parent");
        btnTambah.removeData("ptid");
        btnTambah.removeData("parents");
        btnTambah.data("parent", data.node.id);
        btnTambah.data("ptid", data.node.original.pt_id);
        btnTambah.data("parents", parents);

    });

    // $(document).on('click', '.jstree-anchor', function () {
    //     let nodeId = $(this).parent().attr('id');
    //     alert('Clicked node ID: ' + nodeId);
    // });

    btnEdit.on("click", function () {
        var id = $(this).data("id");
        var parent = $(this).data("parent");
        var parents = $(this).data("parents");
        var ptid = $(this).data("ptid");


        _loading();
        $("#modalEdit #formEdit")[0].reset();
        $("#modalTambah #formTambah")[0].reset();

        if (parent !== "#") {
            $("#modalEdit #ptid").empty();
            $("#modalEdit #ptid").val(null);

            // Jika Child Lebih dari 2
            if (parents.length > 2) {
                $.ajax({
                    url: "/get-perusahaan-all",
                    type: "GET",
                    success: function (response) {
                        $.each(response, function (name, id) {
                            $('#formEdit #ptid').append('<option value="' + id + '">' + name + '</option>');
                            $("#formEdit #ptid option[value='" + ptid + "']",).prop("selected", true);
                        });
                    },
                    error: function () {
                        $.unblockUI();
                        alert("Gagal mengambil kode otomatis");
                    },
                });

                $.getJSON("/project/editsub/?id=" + id, function (res) {
                    var data = res.data;

                    $("#formEdit #id").val(data.id);
                    $("#formEdit #parent").val(data.parent_id);
                    $("#formEdit #name").val(data.name);
                    $("#formEdit #status").val(data.status);

                    $.getJSON("/project/editsub/?id=" + id, function (list) {
                        var lists = list.list;
                        $("#formEdit #parentid").empty();
                        $.each(lists, function (Index, result) {
                            $("#formEdit #parentid").append('<option value="' + result.id + '">' + result.name + "</option>");
                            $("#formEdit #parentid option[value='" + data.parent_id + "']",).prop("selected", true);
                        });
                    });

                    // $("#modalEdit").modal("show");
                    // $("#modalEdit .modal-title").text("Edit SUB");
                    // $.unblockUI();
                });
            } else {
                $.ajax({
                    url: "/get-perusahaan-current?id=" + ptid,
                    type: "GET",
                    success: function (response) {
                        $('#formEdit #ptid').append('<option value="' + response.id + '">' + response.name + '</option>');
                    },
                    error: function () {
                        $.unblockUI();
                        alert("Gagal mengambil kode otomatis");
                    },
                });

                $.getJSON("/project/editsub/?id=" + id, function (res) {
                    var data = res.data;

                    $("#formEdit #id").val(data.id);
                    $("#formEdit #parent").val(data.parent_id);
                    $("#formEdit #name").val(data.name);
                    $("#formEdit #status").val(data.status);

                    var current = res.current;

                    $("#formEdit #parentid").empty();
                    $("#formEdit #parentid").append('<option value="' + current.id + '">' + current.name + "</option>");

                    // $("#modalEdit").modal("show");
                    // $("#modalEdit .modal-title").text("Edit SUB");
                    // $.unblockUI();
                });
            }

            setTimeout(function () {
                $("#modalEdit").modal("show");
                $("#modalEdit .modal-title").text("Edit SUB");
                $.unblockUI();
            }, 300);

        } else {
            // EDIT PROJECT ROOT
            $("#formTambah #ptid").empty();
            $("#formTambah #ptid").val(null);

            $.ajax({
                url: "/get-perusahaan-current?id=" + ptid,
                type: "GET",
                success: function (response) {
                    $('#formTambah #ptid').append('<option value="' + response.id + '">' + response.name + '</option>');
                },
                error: function () {
                    $.unblockUI();
                    alert("Gagal mengambil kode otomatis");
                },
            });

            $.getJSON("/project/edit/?id=" + id, function (res) {
                var src;
                // if (!res.logo) {
                //     src = "";
                // } else {
                //     src = "/storage/hris/project/" + res.logo;
                // }
                if (res.logo) {
                    $("#previews").show();
                    var logoUrl = "/assets/logos/" + res.logo;
                    $("#preview-logo").attr("src", logoUrl).show();
                } else {
                    $("#previews").hide();
                    $("#preview-logo").hide(); // Sembunyikan jika tidak ada logo
                }

                $("#formTambah #id").val(res.id);
                $("#formTambah #parent").val(res.parent_id);
                $("#formTambah #name").val(res.name);
                $("#formTambah #inisial").val(res.inisial);
                $("#formTambah .logo").attr("src", src);
                $("#formTambah #status").val(res.status);

                setTimeout(function () {
                    $("#modalTambah").modal("show");
                    $("#modalTambah .modal-title").text("Edit Project");
                    $.unblockUI();
                }, 300);
            });
        }
    });

    $("#formEdit").submit(function (event) {
        event.preventDefault();
        _loading();
        $.ajax({
            url: "/project",
            type: "POST",
            data: $("#formEdit").serialize(),
            success: function (response) {
                $("#modalEdit").modal("hide");
                $("#modalTambah").modal("hide");
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
                    basicTree.jstree(true).refresh();
                });
            },
            error: function (error) {
                $.unblockUI();

                var errorMessage = error.statusText;
                if (error.status == 405) {
                    // Not Acceptable
                    var errors = $.parseJSON(error.responseText);
                    errorMessage = errors.errors;
                }

                Swal.fire({
                    title: $.parseJSON(error.status),
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
    });

    btnTambah.on("click", function () {
        var parent = $(this).data("parent");
        var ptid = $(this).data("ptid");
        var parents = $(this).data("parents");

        _loading();
        $("#modalEdit #formEdit")[0].reset();
        $("#modalTambah #formTambah")[0].reset();

        $("#formEdit #id").val(null);
        $("#formEdit #parent").val(parent);

        $('#formEdit #ptid').val(null);
        $("#formEdit #ptid").empty();

        $.ajax({
            url: "/get-perusahaan-current?id=" + ptid,
            type: "GET",
            success: function (response) {
                $('#formEdit #ptid').append('<option value="' + response.id + '">' + response.name + '</option>');
            },
            error: function () {
                $.unblockUI();
                alert("Gagal mengambil kode otomatis");
            },
        });

        if (parents.length > 2) {
            // $.getJSON("/project/editsub", function (res) {
            $.getJSON("/project/editsub/?id=" + parent, function (res) {
                var res = res.query;

                $("#formEdit #parentid").empty();
                $.each(res, function (Index, result) {
                    $("#formEdit #parentid").append('<option value="' + result.id + '">' + result.name + "</option>",);
                    $("#formEdit #parentid option[value='" + parent + "']").prop("selected", true,);
                });
            });
        } else {
            $.getJSON("/project/editsub/?id=" + parent, function (res) {
                // $.getJSON("/project/editsub", function (res) {

                var data = res.data;

                $("#formEdit #parentid").empty();
                $("#formEdit #parentid").append('<option value="' + data.id + '">' + data.name + "</option>");

                // $("#modalEdit").modal("show");
                // $("#modalEdit .modal-title").text("Edit SUB");
                // $.unblockUI();
            });
        }

        $("#modalEdit").modal("show");
        $("#modalEdit .modal-title").text("Tambah SUB");
        $.unblockUI();
    });

    $(document).on("click", ".addProject", function () {
        _loading();
        $("#formTambah")[0].reset();

        $("#formTambah #id").val(null);
        $("#formTambah #parent").val(null);

        $("#formTambah #ptid")
            .empty()
            .append('<option value="">Pilih Perusahaan</option>');

        $.ajax({
            url: "/get-perusahaan-all",
            type: "GET",
            success: function (response) {
                $.each(response, function (name, id) {
                    $("#formTambah #ptid").append(
                        `<option value="${id}">${name}</option>`,
                    );
                });
                setTimeout(function () {
                    $("#modalTambah").modal("show");
                    $("#modalTambah .modal-title").text("Tambah Project");
                    $.unblockUI();
                }, 300);
            },
            error: function () {
                $.unblockUI();
                alert("Gagal mengambil kode otomatis");
            },
        });
    });

    $("#formTambah").submit(function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append("logo", $("input[type=file]")[0].files[0]);
        _loading();
        $.ajax({
            url: "/project",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
                $("#modalEdit").modal("hide");
                $("#modalTambah").modal("hide");
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
                    basicTree.jstree(true).refresh();
                });
            },
            error: function (error) {
                $.unblockUI();

                var errorMessage = error.statusText;
                if (error.status == 405) {
                    // Not Acceptable
                    var errors = $.parseJSON(error.responseText);
                    errorMessage = errors.errors;
                }

                Swal.fire({
                    title: $.parseJSON(error.status),
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
    });

    btnHapus.on("click", function () {
        var id = $(this).data("id");
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data Project",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/project/hapus/?id=" + id,
                    type: "POST",
                    dataType: "JSON",
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
                            basicTree.jstree(true).refresh();
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
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {
                                    // Gabungkan semua pesan error jadi satu string
                                    // errorMessage = Object.values(response.errors)
                                    //     .flat()
                                    //     .join('\n');
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
});
