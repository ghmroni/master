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

    table = $("#dataTable").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        // responsive: true,
        columns: [
            {
                data: "status",
                name: "status",
                orderable: false,
                searchable: true,
            },
            {
                data: "nama",
                name: 'nama'
            },
            {
                data: "lokasi",
                name: "lokasi",
                orderable: true,
                searchable: true,
            },
            {
                data: "programtk",
                name: "programtk",
                orderable: false,
                searchable: true,
            },
            {
                data: "upahtk",
                name: "upahtk",
                orderable: false,
                searchable: false,
            },
            {
                data: "programkes",
                name: "programkes",
                orderable: false,
                searchable: false,
            },
            {
                data: "upahkes",
                name: "upahkes",
                orderable: false,
                searchable: false,
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
                className: "dt-head-center align-top", // Header di tengah, isi rata tengah secara vertikal
            },
            {
                targets: [0, 7],
                className: "text-center",
            },
            {
                targets: [4, 6],
                className: "text-end",
            },
        ],
    });

    $(document).on('click', '.Detail', function () {
        var token = $(this).data('token');
        _loading();
        $.getJSON('/setting-bpjs/detail/?token=' + encodeURIComponent(token), function (response) {
            $('#myModal .modal-body').html(response.html);

            var penempatan = response.penempatan;
            var lokasi = [];
            var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
            $.each(penempatan, function (id, nama) {
                lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black">' + nama + '</span>');
            })

            $("#lokasipekerjaan").html(lokasi.join(" "));


            if ($('#upahtk').length) {
                new Cleave('#upahtk', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    delimiter: '.',
                    numeralDecimalMark: ','
                });
            }

            if ($('#upahkes').length) {
                new Cleave('#upahkes', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    delimiter: '.',
                    numeralDecimalMark: ','
                });
            }

            fv.addField('project', {
                validators: {
                    notEmpty: {
                        message: "Lokasi masih kosong",
                    },
                },
            });
            fv.addField('upahtk', {
                validators: {
                    notEmpty: {
                        message: "Gapok BPJSTK masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Gapok BPJSTK hanya boleh angka",
                    }
                },
            });
            fv.addField('upahkes', {
                validators: {
                    notEmpty: {
                        message: "Gapok Kesehatan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Gapok BPJSTK hanya boleh angka",
                    }
                },
            });

            $('#checkAll').prop('checked', false);
            $('#checkAll').prop('disabled', false);

            $('#myModal .modal-title').html('Detail Setting Bpjs');
            $('#myModal').modal('show');
            $.unblockUI();
        })
            .fail(function (error) {
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
            });
    })

    $(document).on('click', '.Tambah', function () {
        _loading();

        $.get('/setting-bpjs/create', function (response) {
            $('#myModal .modal-body').html(response.html);

            // 2. Render Badge Lokasi
            var lokasi = [];
            var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
            $.each(response.penempatan, function (id, nama) {
                lokasi.push('<span class="badge ' + color[id % color.length] + ' me-1 text-black">' + nama + '</span>');
            });
            $("#lokasipekerjaan").html(lokasi.join(" "));

            $('#btnLokasi').css('display', 'block');
            // 3. Re-inisialisasi Cleave karena input baru saja di-render
            if ($('#upahtk').length) {
                new Cleave('#upahtk', { numeral: true, thousandsGroupStyle: 'thousand', delimiter: '.', numeralDecimalMark: ',' });
            }
            if ($('#upahkes').length) {
                new Cleave('#upahkes', { numeral: true, thousandsGroupStyle: 'thousand', delimiter: '.', numeralDecimalMark: ',' });
            }

            fv.addField('project', {
                validators: {
                    notEmpty: {
                        message: "Lokasi masih kosong",
                    },
                },
            });
            fv.addField('upahtk', {
                validators: {
                    notEmpty: {
                        message: "Gapok BPJSTK masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Gapok BPJSTK hanya boleh angka",
                    }
                },
            });
            fv.addField('upahkes', {
                validators: {
                    notEmpty: {
                        message: "Gapok Kesehatan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Gapok BPJSTK hanya boleh angka",
                    }
                },
            });

            $('#checkAll').prop('checked', true);
            // $('#checkAll').css('display','disabled');
            // Cegah user mengubah statusnya
            $('#checkAll').on("click", function (e) {
                e.preventDefault();
            });

            $('#myModal .modal-title').html('Tambah Setting Bpjs');
            $('#myModal').modal('show');
            $.unblockUI();
        })
            .fail(function (error) {
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
            });
    })

    const formValidationExamples = document.getElementById("formSetting");
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {

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
            text: "Simpan Setting Bpjs",
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
                    url: "/setting-bpjs",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formSetting").serialize(),
                    success: function (response) {
                        $('#myModal').modal('hide')
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
                            table.ajax.reload(null, false);
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

    // TAMPILKAN MODAL PERUBAHAN PROJECT
    const btnChange = $(".changeProject");

    var theme = $("html").hasClass("light-style")
        ? "default"
        : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", "#changeLokasi", function () {
        _loading();
        basicTree.jstree({
            core: {
                data: {
                    url: "/setting-bpjs/lokasi",
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


    basicTree.on("activate_node.jstree", function (e, data) {
        btnChange.removeAttr("disabled");

        // Clear Data Tombol Edit and Set New Data Tombol Edit
        btnChange.removeData("id");
        btnChange.data("id", data.node.id);
    });

    // PERUBAHAN PROJECT
    $(document).on("click", ".changeProject", function () {
        var id = $(this).data("id") ?? $("#project").val();

        if (id) {
            _loading();

            $("#formSetting #project").val(id);

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

                $("#formSetting #dtproject").val(dtId.join(","));
                $("#formSetting #lokasipekerjaan").html(lokasi.join(" "));
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
                    confirmButton:
                        "btn btn-primary waves-effect waves-light",
                },
                buttonsStyling: false,
            });
        }
    });
    // END PERUBAHAN PROJECT
})