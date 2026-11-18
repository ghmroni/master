_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    const select2Icons = $(".select2-icons");


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    // ================================================================================================ MENU
    let table;

    table = $("#tableMenu").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[2, "asc"]],
        columns: [
            // {
            //     data: "DT_RowIndex",
            //     name: "DT_RowIndex",
            // },
            {
                data: "icon",
                render: function (data, type, row) {
                    let icon =
                        data !== null
                            ? '<i class="' + data + ' text-primary"></i>'
                            : "";
                    return icon;
                },
            },
            {
                data: "nama",
                name: "nama",
                orderable: true,
                searchable: true,
            },
            {
                data: "sort",
            },
            {
                data: "url",
            },
            {
                data: "permissions",
                name: "permissions",
                orderable: true,
                searchable: true,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: true,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center",
            },
            {
                targets: [0, 2],
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".AddMenu", function () {
        _loading();
        $("#formMenu")[0].reset();
        $('#idmenu').val(null);
        fv.resetForm(true);
        $('select[name="permissionsid[]"]').val([]).trigger('change');

        if (select2Icons.length) {
            function renderIcons(option) {
                if (!option.id) {
                    return option.text;
                }
                var $icon = "<i class='" + $(option.element).data("icon") + " me-2'></i>" + option.text;

                return $icon;
            }
            select2Icons.wrap('<div class="position-relative"></div>').select2({
                dropdownParent: select2Icons.parent(),
                templateResult: renderIcons,
                templateSelection: renderIcons,
                escapeMarkup: function (es) {
                    return es;
                },
            });
        }
        $("#ModalMenu .modal-title").text("Tambah Menu");
        $("#ModalMenu").modal("show");
        $.unblockUI();
    });

    $.getJSON("/menus/akses", function (res) {
        $("#formMenu #permissionsid").empty();
        $("#formMenu #permissionsid").append(
            '<option value="">-pilih-</option>',
        );
        $.each(res, function (index, permission) {
            $("#formMenu #permissionsid").append(
                '<optgroup label="Modul ' + index + '" class="text-danger">',
            );

            $.each(permission, function (id, name) {
                $("#formMenu #permissionsid").append('<option value="' + name.id + '" >' + name.name + "</option>");
            });
            $("#formMenu #permissionsid").append("</optgroup>");
        });
    });

    // UCWORD
    // $("#namamenu").keyup(function (evt) {
    //     var cp_value = $(this).val();
    //     cp_value = cp_value
    //         .toLowerCase()
    //         .replace(/\b[a-z]/g, function (letter) {
    //             return letter.toUpperCase();
    //         });

    //     $(this).val(cp_value);
    // });

    $("#utama").on("change", function () {
        if ($(this).is(":checked")) {

            $("#url").val("").trigger("change");
            $('select[name="permissionsid[]"]').val([]).trigger('change');

            $("#permissionsid, #url").attr("readonly", true);
        } else {
            $("#permissionsid, #url").removeAttr("readonly");
        }
    });

    $(document).on("click", ".editMenu", function () {
        var token = $(this).data("token");
        _loading();
        $.getJSON("/menus/edit/?token=" + token, function (res) {
            $("#formMenu")[0].reset();

            $("#idmenu").val(res.id);
            $("#namamenu").val(res.name);
            $("#sort").val(res.sort);
            $("#status").val(res.status);
            $("#formMenu #url").val(res.url);

            // ICON
            $("#formMenu #select2Icons").val(res.icon);
            if (select2Icons.length) {
                function renderIcons(option) {
                    if (!option.id) {
                        return option.text;
                    }
                    var $icon = "<i class='" + $(option.element).data("icon") + " me-2'></i>" + option.text;

                    return $icon;
                }
                select2Icons
                    .wrap('<div class="position-relative"></div>')
                    .select2({
                        dropdownParent: select2Icons.parent(),
                        templateResult: renderIcons,
                        templateSelection: renderIcons,
                        escapeMarkup: function (es) {
                            return es;
                        },
                    });
            }

            // Set Menu Utama
            if (res.url != null) {
                $("#utama").prop("checked", false);
                $("#formMenu #url").removeAttr("readonly");
            } else {
                $("#utama").prop("checked", true);
                $("#formMenu #url").attr("readonly", true);
            }

            // Permissions
            $.getJSON("/menus/akses", function (response) {
                $("#formMenu #permissionsid").empty();
                $("#formMenu #permissionsid").append('<option value="">-pilih-</option>');

                $.each(response, function (index, permissionGroup) {
                    $("#formMenu #permissionsid").append('<optgroup label="Modul ' + index + '">');
                    $.each(permissionGroup, function (i, perm) {
                        // Buat option
                        $("#formMenu #permissionsid").append(
                            '<option value="' + perm.id + '">' + perm.name + "</option>"
                        );

                        $("#formMenu #permissionsid").append("</optgroup>");

                    });

                    // --- SET SELECTED ---
                    if (res.permissions) {
                        res.permissions.forEach(function (perm) {
                            $("#formMenu #permissionsid option[value='" + perm.id + "']").prop("selected", true);
                        });
                    }
                });
            });

            $("#ModalMenu .modal-title").text("Edit Menu Utama");
            $("#ModalMenu").modal("show");
        });
        $.unblockUI();
    });

    const formValidationExamples = document.getElementById('formMenu');
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            sort: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    },
                    stringLength: {
                        min: 1,
                        max: 2,
                        message: 'maksimal 2 digits'
                    }
                }
            },
            namamenu: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            icon: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            url: {
                validators: {
                    callback: {
                        message: "Inputan masih kosong",
                        callback: function (input) {
                            // Ambil checkbox utama
                            const utamaChecked = formValidationExamples.querySelector('[name="utama"]').checked;

                            if (!utamaChecked) {
                                // Jika checkbox dicentang, maka url wajib diisi
                                return input.value.trim() !== "";
                            }

                            // Jika checkbox tidak dicentang, validasi dilewati
                            return true;

                        },
                    },
                }
            },

        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                // Use this for enabling/changing valid/invalid class
                // eleInvalidClass: '',
                eleValidClass: '',
                rowSelector: function (field, ele) {
                    // field is the field name & ele is the field element
                    switch (field) {
                        default:
                            return '.row';
                    }
                }
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            // Submit the form when all fields are valid
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
            instance.on('plugins.message.placed', function (e) {
                //* Move the error message out of the `input-group` element
                if (e.element.parentElement.classList.contains('input-group')) {
                    // `e.field`: The field name
                    // `e.messageElement`: The message element
                    // `e.element`: The field element
                    e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                }
                //* Move the error message out of the `row` element for custom-options
                if (e.element.parentElement.parentElement.classList.contains('custom-option')) {
                    e.element.closest('.row').insertAdjacentElement('afterend', e.messageElement);
                }
            });
        }
    }).on('core.form.valid', function () {
        // Form valid → submit via jQuery/AJAX
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Menu",
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
                    url: "/menus",
                    type: "POST",
                    dataType: "JSON",
                    data: $('#formMenu').serialize(),
                    success: function (response) {
                        $('#ModalMenu').modal('hide');
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


    // ================================================================================================ MENU SUB
    let table1;
    $(document).on('click', '.btnTwo', function () {
        if ($.fn.DataTable.isDataTable('#tableSubMenu')) {
            table1.destroy();
        }

        table1 = $("#tableSubMenu").DataTable({
            ajax: "/submenus",
            processing: true,
            serverSide: true,
            // aaSorting: [[0, "asc"]],
            columns: [
                {
                    data: "sub",
                    name: "sub",
                    orderable: false,
                    searchable: true,
                },
                {
                    data: "sort",
                },
                {
                    data: "url",
                },
                {
                    data: "permissions",
                    name: "permissions",
                    orderable: false,
                    searchable: true,
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: true,
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [1, 4],
                    className: "text-center",
                },
            ],
        });
    })

    // $("#formSub #namasub").keyup(function (evt) {
    //     var cp_value = $(this).val();
    //     cp_value = cp_value
    //         .toLowerCase()
    //         .replace(/\b[a-z]/g, function (letter) {
    //             return letter.toUpperCase();
    //         });

    //     $(this).val(cp_value);
    // });

    $(document).on("click", ".addSub", function () {
        var token = $(this).data("token");
        _loading();
        fs.resetForm(true);
        $("#formSub")[0].reset();
        $('#formSub #idsub').val(null);
        $('#formSub #menuid').empty();
        $.getJSON('/submenus/menu/?token=' + token, function (res) {
            $.each(res, function (nama, id) {
                $('#formSub #menuid').append('<option value="' + id + '">' + nama + "</option>");
            })
        })

        $('select[name="idpermissions[]"]').val([]).trigger('change');

        $("#ModalSub .modal-title").text("Tambah Submenu");
        $('#ModalSub').modal('show');
        $.unblockUI();
    });

    $.getJSON("/menus/akses", function (res) {
        $("#formSub #idpermissions").empty();
        $("#formSub #idpermissions").append(
            '<option value="">-pilih-</option>',
        );
        $.each(res, function (index, permission) {
            $("#formSub #idpermissions").append(
                '<optgroup label="Modul ' + index + '" class="text-danger">',
            );

            $.each(permission, function (id, name) {
                $("#formSub #idpermissions").append('<option value="' + name.id + '" >' + name.name + "</option>");
            });
            $("#formSub #idpermissions").append("</optgroup>");
        });
    });

    $(document).on('click', '.editSub', function () {
        var token = $(this).data('token');
        $.getJSON('/submenus/edit/?token=' + token, function (response) {
            _loading();
            var menus = response.menus;
            var sub = response.sub;

            $("#formSub")[0].reset();
            $('#formSub #idsub').val(sub.id);

            $('#formSub #menuid').empty();
            $.each(menus, function (nama, id) {
                $('#formSub #menuid').append('<option value="' + id + '">' + nama + "</option>");
                $("#formSub #menuid option[value=" + sub.menu_id + " ]").prop("selected", true);
            })

            $('#formSub #namasub').val(sub.name);
            $('#formSub #url').val(sub.url);
            $('#formSub #sort').val(sub.sort);
            $('#formSub #status').val(sub.status);

            $.getJSON("/menus/akses", function (res) {
                $("#formSub #idpermissions").empty();
                $("#formSub #idpermissions").append('<option value="">-pilih-</option>');

                $.each(res, function (index, permissionGroup) {
                    $("#formSub #idpermissions").append('<optgroup label="Modul ' + index + '">');
                    $.each(permissionGroup, function (i, perm) {
                        // Buat option
                        $("#formSub #idpermissions").append(
                            '<option value="' + perm.id + '">' + perm.name + "</option>"
                        );

                        $("#formSub #idpermissions").append("</optgroup>");

                    });

                    // --- SET SELECTED ---
                    if (sub.permissions) {
                        sub.permissions.forEach(function (perm) {
                            $("#formSub #idpermissions option[value='" + perm.id + "']").prop("selected", true);
                        });
                    }
                });
            });

            $("#ModalSub .modal-title").text("Edit Submenu");
            $('#ModalSub').modal('show');
            $.unblockUI();
        })
    })

    const formValidationSub = document.getElementById('formSub');
    const fs = FormValidation.formValidation(formValidationSub, {
        fields: {
            menuid: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    },
                }
            },
            namasub: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            sort: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            url: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            'idpermissions[]': {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },

        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                // Use this for enabling/changing valid/invalid class
                // eleInvalidClass: '',
                eleValidClass: '',
                rowSelector: function (field, ele) {
                    // field is the field name & ele is the field element
                    switch (field) {
                        default:
                            return '.row';
                    }
                }
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            // Submit the form when all fields are valid
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
            instance.on('plugins.message.placed', function (e) {
                //* Move the error message out of the `input-group` element
                if (e.element.parentElement.classList.contains('input-group')) {
                    // `e.field`: The field name
                    // `e.messageElement`: The message element
                    // `e.element`: The field element
                    e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                }
                //* Move the error message out of the `row` element for custom-options
                if (e.element.parentElement.parentElement.classList.contains('custom-option')) {
                    e.element.closest('.row').insertAdjacentElement('afterend', e.messageElement);
                }
            });
        }
    }).on('core.form.valid', function () {
        // Form valid → submit via jQuery/AJAX
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Submenu",
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
                    url: "/submenus",
                    type: "POST",
                    dataType: "JSON",
                    data: $('#formSub').serialize(),
                    success: function (response) {
                        $('#ModalSub').modal('hide');
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
                            table1.ajax.reload(null, false);
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


});
