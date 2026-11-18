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

    table = $("#tableUnitkerja").DataTable({
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
                data: "name",
            },
            {
                data: "user.name",
                name: "user.name",
            },
            {
                data: "updated",
                name: "updated",
                orderable: false,
                searchable: false,
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
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 3, 4],
                className: "text-center",
            },
        ],
    });

    const bsValidationForms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Simpan Data Unitkerja",
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
                            url: "/unitkerja",
                            type: "POST",
                            dataType: "JSON",
                            data: $(form).serialize(),
                            success: function (response) {
                                $('#myModal').modal('hide');
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
                                            errorMessage = Object.values(response.errors)
                                                .flat()
                                                .join('\n');
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
                event.preventDefault();
            }
            form.classList.add("was-validated");
        });
    });

    $(document).on('click', '.Tambah', function () {
        $('#formUnitkerja')[0].reset();
        $('#idunit').val(null);
        $('#myModal .modal-title').text('Tambah Unitkerja');
        $('#myModal').modal('show');
    })

    $(document).on('click', '.Edit', function () {
        var id = $(this).data('id');
        $.getJSON('/edit-unitkerja/?token=' + id, function (res) {
            $('#formUnitkerja')[0].reset();
            $('#idunit').val(res.id);
            $('#nama').val(res.name);
            $('#myModal .modal-title').text('Edit Unitkerja');
            $('#myModal').modal('show');

        });
    })

    $(document).on('click', '.Hapus', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data Unitkerja",
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
                    url: "/hapus-unitkerja/?token=" + id,
                    type: "POST",
                    dataType: "JSON",
                    success: function (response) {
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

})