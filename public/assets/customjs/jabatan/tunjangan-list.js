_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    var myOffcanvas = new bootstrap.Offcanvas(
        document.getElementById("offcanvasTunjangan")
    );


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#dataTable").DataTable({
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

    $(document).on("click", ".Tambah", function () {
        _loading();
        $("#formTunjangan")[0].reset();
        $('#id').val(null);
        $("#offcanvasTunjangan .offcanvas-title").text("Tambah Tunjangan");
        myOffcanvas.show();
        $.unblockUI();
    });

    $(document).on("click", ".Edit", function () {
        var token = $(this).data("token");
        _loading();
        $.getJSON("/tunjangan/edit?token=" + token, function (res) {
            $("#formTunjangan")[0].reset();
            $("#formTunjangan #id").val(res.id);
            $("#formTunjangan #name").val(res.name);
            $("#offcanvasTunjangan .offcanvas-title").text("Edit Tunjangan");
            myOffcanvas.show();
        });
        $.unblockUI();
    });

    $(document).on("click", ".Hapus", function () {
        var token = $(this).data("token");
        var nama = $(this).data("name");
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Tunjangan " + nama + " ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.post({
                    url: "/tunjangan/delete/?token=" + encodeURIComponent(token),
                    type: "POST",
                    dataType: "JSON",

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
                        let errorMessage = error.statusText;
                        let errorTitle = `Error ${error.status}`;

                        // Coba parse JSON jika ada responseText
                        if (error.responseText) {
                            try {
                                let response = JSON.parse(error.responseText);
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {

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

    $("#formTunjangan").submit(function (event) {
        event.preventDefault();
        _loading();
        $.ajax({
            url: "/tunjangan",
            type: "POST",
            dataType: "JSON",
            data: $("#formTunjangan").serialize(),
            success: function (response) {
                myOffcanvas.hide();
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
    });
});
