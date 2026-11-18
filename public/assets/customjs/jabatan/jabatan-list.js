_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    var myOffcanvas = new bootstrap.Offcanvas(
        document.getElementById("offcanvasJabatan")
    );

    $('#formJabatan :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#tableJabatan").DataTable({
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
        $("#formJabatan")[0].reset();
        $("#offcanvasJabatan .offcanvas-title").text("Tambah Jabatan");
        myOffcanvas.show();
        $.unblockUI();
    });

    $(document).on("click", ".Edit", function () {
        var id = $(this).data("id");
        _loading();
        $.getJSON("/jabatan/edit?id=" + id, function (res) {
            $("#formJabatan")[0].reset();
            $("#formJabatan #id").val(res.id);
            $("#formJabatan #name").val(res.name);
            $("#offcanvasJabatan .offcanvas-title").text("Edit Jabatan");
            myOffcanvas.show();
        });
        $.unblockUI();
    });

    $(document).on("click", ".Hapus", function () {
        var id = $(this).data("id");
        var nama = $(this).data("name");
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Jabatan " + nama + " ?",
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
                    url: "/jabatan/delete",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        id: id,
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
    });

    $("#formJabatan").submit(function (event) {
        event.preventDefault();
        _loading();
        $.ajax({
            url: "/jabatan",
            type: "POST",
            dataType: "JSON",
            data: $("#formJabatan").serialize(),
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
