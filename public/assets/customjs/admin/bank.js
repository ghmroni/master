_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#formBank :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#tableBank").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "logo",
                name: "logo",
                orderable: false,
                searchable: false,
            },
            {
                data: "name",
            },
            {
                data: "codebank",
            },
            {
                data: "action",
                name: "action",
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
                targets: [0, 1, 4],
                className: "dt-center",
            },
        ],
    });

    const formValidationExamples = document.getElementById('formBank');
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            logoid: {
                validators: {
                    notEmpty: {
                        message: 'Logo Bank masih kosong'
                    }
                }
            },
            nama: {
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
    }).on('core.form.valid', function (e) {

        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data Bank",
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
                // Ambil form element
                const form = document.getElementById('formBank');
                const formData = new FormData(form);

                _loading();
                $.ajax({
                    url: "/bank",
                    type: "POST",
                    dataType: "JSON",
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function (response) {
                        $('#myModal').modal('hide');
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
    });

    $(document).on('change', '#logobank', function () {
        var value = $(this).val();
        $('#logoid').val(value);
    })

    // Tambah
    $(document).on('click', '.Tambah', function () {
        $('.dropify').dropify();

        $('#formBank')[0].reset();
        $('#logoid').val(null);
        $('#bankid').val(null);
        $('#gambarlogo').css('display', 'none');

        $('#myModal .modal-title').text('Tambah Data Bank');
        $('#myModal').modal('show');
    });

    // Edit
    $(document).on('click', '.Edit', function () {
        var id = $(this).data('id');

        $.getJSON('/edit-bank/?token=' + id, function (res) {
            $('#formBank')[0].reset();
            $('.dropify').dropify();
            $('#logoid').val(res.logobank);
            $('#bankid').val(res.id);
            $('#nama').val(res.name);
            $('#kode').val(res.codebank);

            // set default file baru
            $('#gambarlogo').css('display', 'block');
            $('#gambar').attr('src', res.url);
            // $('#logo').attr('data-default-file', res.url);
            // $('#logo').val('');
            // $('#logo').dropify();

            $('#myModal .modal-title').text('Edit Data Bank');
            $('#myModal').modal('show');
        });
    });

    // Hapus
    $(document).on('click', '.Hapus', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data Bank",
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
                    url: "/destroy-bank/?token=" + id,
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
    });

})