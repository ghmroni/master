_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#formPermission #name").keyup(function () {
        this.value = this.value.toLowerCase();
    });


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    // Typeahead
    const moduleName = [];
    $.getJSON("/permissions-groups", function (response) {
        $.each(response, function (index) {
            moduleName.push(index);
        });
    });

    const substringMatcher = (strs) => {
        return (q, cb) => {
            const matches = [];
            const substrRegex = new RegExp(q, "i");

            strs.forEach((str) => {
                if (substrRegex.test(str)) {
                    matches.push(str);
                }
            });

            cb(matches);
        };
    };

    $(".typeahead").typeahead(
        {
            highlight: true,
            minLength: 1,
        },
        {
            name: "module",
            source: substringMatcher(moduleName),
        }
    );

    // UcFirst
    function ucfirst(str, force) {
        str = force ? str.toLowerCase() : str;
        return str.replace(/(\b)([a-zA-Z])/, function (firstLetter) {
            return firstLetter.toUpperCase();
        });
    }

    $("#formPermission #module").keyup(function (evt) {
        var cp_value = ucfirst($(this).val(), true);
        $(this).val(cp_value);
    });

    // Not Space
    $("#formPermission #module, #name").keypress(function (e) {
        if (e.which === 32) return false;
    });

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
                data: "module",
            },
            {
                data: "updated_at",
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
                targets: 0,
                className: "dt-center",
            },
            {
                targets: 3,
                className: "dt-center",
                render: function (data) {
                    return moment(data).format('DD-MM-YYYY HH:mm');
                },
            },
            {
                targets: 4,
                className: "dt-center",
            },
        ],
    });

    $(document).on("click", ".Tambah", function () {
        var id = $(this).data("id");
        _loading();
        $("#formPermission")[0].reset();
        $("#modalPermissions .modal-title").text("Tambah Permission");
        $("#modalPermissions").modal("show");
        $.unblockUI();
    });

    $(document).on("click", ".Edit", function () {
        var id = $(this).data("id");
        _loading();
        $.getJSON("/permissions/edit?id=" + id, function (res) {
            $("#formPermission")[0].reset();

            $("#formPermission #id").val(res.id);
            $("#formPermission #name").val(res.name);
            $("#formPermission #oldname").val(res.name);
            $("#formPermission #module").val(res.module);

            $("#modalPermissions .modal-title").text("Edit Permission");
            $("#modalPermissions").modal("show");
            $.unblockUI();
        });
    });

    $("#formPermission").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data Permission ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/permissions",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formPermission").serialize(),
                    success: function (response) {
                        $("#modalPermissions").modal("hide");
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
});
