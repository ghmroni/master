_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#formKawin :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });

    var numeralMask = document.getElementById('nilai');

    var cleave = new Cleave(numeralMask, {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        // prefix: 'Rp ',
        // noImmediatePrefix: true,
        // rawValueTrimPrefix: true
    });





    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#tableKawin").DataTable({
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
                data: "kode_status",
            },
            {
                data: "nama_status",
            },
            {
                data: "nilai",
                name: "nilai",
                orderable: true,
                searchable: true,
            },
            {
                data: "kategori_ptkp",
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
                targets: 3,
                className: "dt-right",
            },
            {
                targets: [0, 1, 4, 5],
                className: "dt-center",
            },
        ],
    });

    const formValidationExamples = document.getElementById('formKawin');
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            kode: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    },
                    stringLength: {
                        min: 2,
                        max: 3,
                        message: 'maksimal 3 digits'
                    }
                }
            },
            keterangan: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            nilai: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    },
                    // regexp: {
                    //     regexp: /^[0-9]+$/,
                    //     message: "Inputan hanya boleh berisi angka",
                    // }
                }
            },
            kategori: {
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
            text: "Simpan Data Status Perkawinan",
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
                    url: "/statuskawin",
                    type: "POST",
                    dataType: "JSON",
                    data: $('#formKawin').serialize(),
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
                            customClass: {
                                confirmButton:
                                    "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.Tambah', function () {
        $('#formKawin')[0].reset();
        $('#idkawin').val(null);
        $('#myModal .modal-title').text('Tambah Status Perkawinan');
        $('#myModal').modal('show');
    });

    $(document).on('click', '.Edit', function () {
        var id = $(this).data('id');
        $.getJSON('/edit-statuskawin/?token=' + id, function (res) {
            $('#formKawin')[0].reset();
            $('#idkawin').val(res.id);
            $('#kode').val(res.kode_status);
            $('#keterangan').val(res.nama_status);
            $('#kategori').val(res.kategori_ptkp);
            // set value dari response dengan Cleave
            cleave.setRawValue(res.nilai_ptkp);
            $('#myModal .modal-title').text('Edit Status Perkawinan');
            $('#myModal').modal('show');
        })
    })

    $(document).on('click', '.Hapus', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data Status Perkawinan",
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
                    url: "/destroy-statuskawin/?token=" + id,
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
                            customClass: {
                                confirmButton:
                                    "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    }
                });
            }
        });
    })
})