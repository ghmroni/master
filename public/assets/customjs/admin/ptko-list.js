_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#formPtko :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });


    setTimeout(function () {
        $.unblockUI();
    }, 200);

    table = $("#tablePtko").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[5, "asc"]],
        columns: [
            {
                data: "nomor",
            },
            {
                data: "tanggal",
                name: "tanggal",
                orderable: true,
                searchable: true,
            },
            {
                data: "lokasi",
                name: "lokasi",
                orderable: true,
                searchable: true,
            },
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
            },
            {
                data: 'posisi'
            },
            {
                data: "catatan",
                name: "catatan",
                orderable: true,
                searchable: true,
            },
            {
                data: "keterangan",
                name: "keterangan",
                orderable: true,
                searchable: true,
            },
            {
                data: "action",
                name: "action",
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
                targets: [0, 1, 3, 5, 6, 7],
                className: "dt-center",
            },
        ],
    });

    $(document).on("click", ".Tambah", function () {
        $("#formPtko")[0].reset();
        $("#formPtko #idptko").val(null);
        $("#formPtko #status").val(null).trigger('change');
        $("#formPtko #posisi").val(null).trigger('change');
        fv.resetForm(true);
        $("#formPtko #keperluanproject").html("");
        $("#myModal .modal-title").text("Tambah PTKO");
        $("#myModal").modal("show");
    });

    var theme = $("html").hasClass("light-style") ? "default" : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", "#changeLokasi", function () {
        _loading();
        basicTree.jstree({
            core: {
                data: {
                    url: "/lokasi/ptko/?project=" + 14,
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
                var color = [
                    "bg-success",
                    "bg-warning",
                    "bg-primary",
                    "bg-info",
                    "bg-secondary",
                    "bg-dark",
                ];
                $.each(lok, function (idx, data) {
                    lokasi.push(
                        '<span class="badge ' +
                        color[idx] +
                        ' me-1 text-black">' +
                        data.name +
                        "</span>",
                    );
                });
                $("#keperluanproject").html(lokasi.join(" "));
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

    const formValidationExamples = document.getElementById("formPtko");
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            project: {
                validators: {
                    notEmpty: {
                        message: "Keperluan Project masih kosong",
                    },
                },
            },
            nomor: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            tanggal: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            status: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            posisi: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },

        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                // Use this for enabling/changing valid/invalid class
                // eleInvalidClass: '',
                eleValidClass: "",
                rowSelector: function (field, ele) {
                    // field is the field name & ele is the field element
                    switch (field) {
                        default:
                            return ".row";
                    }
                },
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            // Submit the form when all fields are valid
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
        init: (instance) => {
            instance.on("plugins.message.placed", function (e) {
                //* Move the error message out of the `input-group` element
                if (e.element.parentElement.classList.contains("input-group")) {
                    // `e.field`: The field name
                    // `e.messageElement`: The message element
                    // `e.element`: The field element
                    e.element.parentElement.insertAdjacentElement(
                        "afterend",
                        e.messageElement,
                    );
                }
                //* Move the error message out of the `row` element for custom-options
                if (
                    e.element.parentElement.parentElement.classList.contains(
                        "custom-option",
                    )
                ) {
                    e.element
                        .closest(".row")
                        .insertAdjacentElement("afterend", e.messageElement);
                }
            });
        },
    }).on("core.form.valid", function () {
        // Form valid → submit via jQuery/AJAX
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data PTKO",
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
                $.ajax({
                    url: "/ptko",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formPtko").serialize(),
                    success: function (response) {
                        $("#myModal").modal("hide");
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
                                let response = JSON.parse(error.responseText);
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {
                                    // Gabungkan semua pesan error jadi satu string
                                    // errorMessage = Object.values(response.errors)
                                    //     .flat()
                                    //     .join('\n');
                                    var errors = $.parseJSON(error.responseText);
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

    $(document).on('click', '.Edit', function () {
        _loading();
        var token = $(this).data('token');
        $.getJSON('/ptko/edit/?token=' + encodeURIComponent(token), function (res) {
            $("#formPtko")[0].reset();

            $.getJSON("/lokasi-project?lokasi=" + res.project_id, function (lok) {
                var lokasi = [];
                var color = [
                    "bg-success",
                    "bg-warning",
                    "bg-primary",
                    "bg-info",
                    "bg-secondary",
                    "bg-dark",
                ];
                $.each(lok, function (id, data) {
                    lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black">' + data.name + "</span>");
                });

                $("#keperluanproject").html(lokasi.join(" "));
            });

            $("#formPtko #idptko").val(res.id);
            $("#formPtko #project").val(res.project_id);
            $("#formPtko #tanggal").val(res.tanggal_ptko);
            $("#formPtko #nomor").val(res.nomor);
            $("#formPtko #status").val(res.status).trigger('change');
            $("#formPtko #posisi").val(res.posisi).trigger('change');

            $("#myModal .modal-title").text("Edit PTKO");
            $("#myModal").modal("show");
            $.unblockUI();
        })
    })

    $(document).on('click', '.Detail', function () {
        _loading();
        var token = $(this).data('token');
        $.getJSON('/ptko/detail/?token=' + encodeURIComponent(token), function (res) {
            var datas = res.karyawan;

            $('#vnomor').html(res.nomor)
            $('#vnip').html(datas.nip)
            $('#vnama').html(datas.nama)

            var lokasi = [];
            var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
            $.each(datas.lokasi_penempatan, function (id, data) {
                lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black">' + data + '</span>');
            })
            $('#vlokasi').html(lokasi.join(' '));

            $('#vstatus').html(datas.tanggal_keluar == null
                ? '<span class="badge rounded-pill bg-label-success">AKTIF</span>'
                : '<span class="badge rounded-pill bg-label-danger">KELUAR</span>'
            );

            $('#modalDetail .modal-title').html('Detail Nomor PTKO');
            $('#modalDetail').modal('show');
            $.unblockUI();
        })
    })

    $(document).on('click', '.Batal', function () {

        var id = $(this).data('id');
        Swal.fire({
            title: 'BATALKAN PTKO',
            input: 'textarea',
            inputPlaceholder: 'Alasan pembatalan...',
            inputAttributes: {
                'aria-label': 'Tuliskan alasan Anda'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan wajib diisi!';
                }
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: 'Proses',
            cancelButtonText: 'Close',
            customClass: {
                confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                cancelButton: 'btn btn-outline-secondary waves-effect waves-light'
            },
            buttonsStyling: false
        }).then((res) => {
            if (res.value) {
                _loading();
                $.ajax({
                    url: "/ptko/batal",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        id: id,
                        alasan: res.value
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $.unblockUI();
                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1700,
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
                        }).then(() => {
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
                                let response = JSON.parse(error.responseText);
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {
                                    // Gabungkan semua pesan error jadi satu string
                                    // errorMessage = Object.values(response.errors)
                                    //     .flat()
                                    //     .join('\n');
                                    var errors = $.parseJSON(error.responseText);
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
    })

    $(document).on('click', '.Input', function () {
        var token = $(this).data('token');
        var nomor = $(this).data('nomor');
        Swal.fire({
            title: "Konfirmasi!",
            html: "Pastikan Nomor Ptko : <span class='fw-bold'>" + nomor + " </span><br>Sudah diinput ke HRApps!",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Sudah!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.post({
                    // url: "/ptko/hrapps/?token=" + encodeURIComponent(token),
                    url: "/ptko/hrapps",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        token: token,
                        hrapps: 'SUDAH',
                    },

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
                            table.ajax.reload();
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
            }
        });
    })

    $(document).on('click', '.Cancel', function () {
        var token = $(this).data('token');
        var nomor = $(this).data('nomor');
        Swal.fire({
            title: "Konfirmasi!",
            html: "Nomor Ptko : <span class='fw-bold'>" + nomor + " </span><br>Akan dilakukan pembatalan input HRApps!",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Batalkan!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.post({
                    // url: "/ptko/hrapps/?token=" + encodeURIComponent(token),
                    url: "/ptko/hrapps",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        token: token,
                        hrapps: 'BELUM',
                    },

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
                            table.ajax.reload();
                        });
                    },
                    error: function (error) {
                        console.log(error);

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
                                    var errors = $.parseJSON(error.responseText);
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
    })
})