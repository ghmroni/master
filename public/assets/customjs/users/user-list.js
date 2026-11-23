_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.getJSON("/listroles", function (res) {
            $("#formTambah #roles").empty();
            $("#formTambah #roles").append('<option value="">-pilih-</option>');
            $.each(res, function (nama, id) {
                $("#formTambah #roles").append(
                    '<option value="' + id + '">' + nama + "</option>"
                );
            });
        });

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
                data: "email",
            },
            {
                data: "level",
                name: "level",
                orderable: true,
                searchable: true,
            },
            {
                data: "status",
                name: "status",
                orderable: true,
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
                className: "dt-head-center",
            },
            {
                targets: 0,
                className: "dt-center",
            },
            {
                targets: 4,
                className: "dt-center",
            },
            {
                targets: 5,
                className: "dt-center",
            },
        ],
    });

    // Tambah User
    $(document).on("click", ".Tambah", function () {
        _loading();
        $("#formTambah")[0].reset();
        $("#formTambah #id").val(null);
        $('#formTambah #roles').val(null).trigger('change');
        $("#modalTambah .modal-title").text("Tambah Pengguna");
        $("#modalTambah").modal("show");
        $.unblockUI();
    });

    // Form Submit
    $("#formTambah").submit(function (event) {
        event.preventDefault();

        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan data pengguna ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-warning me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-danger waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/pengguna",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formTambah").serialize(),
                    success: function (response) {
                        $("#modalTambah").modal("hide");
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

    // Edit User
    $(document).on("click", ".Edit", function () {
        var token = $(this).data("token");
        _loading();
        $.getJSON('/pengguna/edit/?token=' + token, function (response) {

            $("#formTambah")[0].reset();
            $("#formTambah #id").val(response.id);
            $("#formTambah #name").val(response.nama);
            $("#formTambah #email").val(response.email);
            $("#formTambah #status").val(response.status);

            $.getJSON("/listroles", function (res) {
                $("#formTambah #roles").empty();
                $("#formTambah #roles").append(
                    '<option value="">-pilih-</option>'
                );

                $.each(res, function (nama, id) {
                    $("#formTambah #roles").append('<option value="' + id + '">' + nama + "</option>");
                    $("#formTambah #roles option[value='" + response.role + "']").prop("selected", true);
                });
                $.unblockUI();
            });


            $("#modalTambah .modal-title").text("Edit Pengguna");
            $("#modalTambah").modal("show");
        })
        $.unblockUI();

    });

    // Direct Permissions
    $(document).on("click", ".AdditionalAkses", function () {
        _loading();
        var token = $(this).data("token");
        window.location.href = '/pengguna/permissions/' + encodeURIComponent(token);
        // $.getJSON('/pengguna/permissions/?token=' + token, function (response) {
        //     var user = response.user;
        //     var direct = response.direct;
        //     var permissions = response.permissions;
        //     var permissionGroup = response.permissionGroup;

        //     $("#formRoles")[0].reset();
        //     // ROLE
        //     $("#formRoles #id").val(user.id);
        //     $("#formRoles #name").val(user.name);

        //     // PERMISSIONS
        //     $("#dtPermissionGroups").empty();
        //     var no = 0;
        //     $.each(permissionGroup, function (groups, name) {
        //         no += 1;
        //         var html = [];
        //         $.each(name, function (index, value) {
        //             var namaPermission = value.name.split("-");
        //             // CHEKED PERMISSION BY ROLE HAS PERMISSIONS
        //             var checkedPermissions = [];
        //             var classPermissions = [];
        //             permissions.forEach(function (val) {
        //                 if (value.name == val.name) {
        //                     checkedPermissions += "checked disabled";
        //                     classPermissions += "form-check-warning";
        //                 }
        //             });
        //             // CHEKED DIRECT PERMISSION
        //             direct.forEach(function (val) {
        //                 if (value.name == val.name) {
        //                     checkedPermissions += "checked";
        //                     classPermissions = "form-check-success";
        //                 }
        //             });

        //             html +=
        //                 `
        //                     <div class="col-md-3">
        //                         <div class="form-check-success me-3 me-lg-3 ` +
        //                 classPermissions +
        //                 `">
        //                             <label class="form-check-label">
        //                                 <input class="form-check-input" type="checkbox" id="parent` +
        //                 no +
        //                 `" name="permissions[]" value="` +
        //                 value.name +
        //                 `"  ` +
        //                 checkedPermissions +
        //                 ` >
        //                                     ` +
        //                 namaPermission[0].substr(0, 1).toUpperCase() +
        //                 namaPermission[0].substr(1).toLowerCase() +
        //                 `
        //                             </label>
        //                         </div>
        //                     </div>
        //                 `;
        //         });

        //         $("#dtPermissionGroups").append(
        //             `
        //                 <div class="col-md-6 mb-4">
        //                     <div class="ribbon-wrapper card" style="border: 1px solid #3B5998">
        //                         <div class="ribbon ribbon-success"><i class="fas fa-key me-1"></i>` +
        //             groups.toUpperCase() +
        //             `</div>
        //                         <a href="javascript:void(0);" class="editpermissions text-success mb-3">
        //                             <span class="selectAll" data-check=` +
        //             no +
        //             `>Pilih semua</span>
        //                         </a>
        //                         <div class="row">
        //                             ` +
        //             html +
        //             `
        //                         <div>
        //                     </div>
        //                 </div>
        //             `
        //         );
        //     });

        //     $("#ModalRole .modal-title").text("Edit Level");
        //     $("#ModalRole").modal("show");
        //     $.unblockUI();
        // })

        // $.ajax({
        //     url: "/pengguna/permissions",
        //     type: "GET",
        //     dataType: "JSON",
        //     data: {
        //         id: id,
        //     },
        //     success: function (response) {
        //         var user = response.user;
        //         var direct = response.direct;
        //         var permissions = response.permissions;
        //         var permissionGroup = response.permissionGroup;

        //         $("#formRoles")[0].reset();
        //         // ROLE
        //         $("#formRoles #id").val(user.id);
        //         $("#formRoles #name").val(user.name);

        //         // PERMISSIONS
        //         $("#dtPermissionGroups").empty();
        //         var no = 0;
        //         $.each(permissionGroup, function (groups, name) {
        //             no += 1;
        //             var html = [];
        //             $.each(name, function (index, value) {
        //                 var namaPermission = value.name.split("-");
        //                 // CHEKED PERMISSION BY ROLE HAS PERMISSIONS
        //                 var checkedPermissions = [];
        //                 var classPermissions = [];
        //                 permissions.forEach(function (val) {
        //                     if (value.name == val.name) {
        //                         checkedPermissions += "checked disabled";
        //                         classPermissions += "form-check-warning";
        //                     }
        //                 });
        //                 // CHEKED DIRECT PERMISSION
        //                 direct.forEach(function (val) {
        //                     if (value.name == val.name) {
        //                         checkedPermissions += "checked";
        //                         classPermissions = "form-check-success";
        //                     }
        //                 });

        //                 html +=
        //                     `
        //                     <div class="col-md-3">
        //                         <div class="form-check-success me-3 me-lg-3 ` +
        //                     classPermissions +
        //                     `">
        //                             <label class="form-check-label">
        //                                 <input class="form-check-input" type="checkbox" id="parent` +
        //                     no +
        //                     `" name="permissions[]" value="` +
        //                     value.name +
        //                     `"  ` +
        //                     checkedPermissions +
        //                     ` >
        //                                     ` +
        //                     namaPermission[0].substr(0, 1).toUpperCase() +
        //                     namaPermission[0].substr(1).toLowerCase() +
        //                     `
        //                             </label>
        //                         </div>
        //                     </div>
        //                 `;
        //             });

        //             $("#dtPermissionGroups").append(
        //                 `
        //                 <div class="col-md-6 mb-4">
        //                     <div class="ribbon-wrapper card" style="border: 1px solid #3B5998">
        //                         <div class="ribbon ribbon-success"><i class="fas fa-key me-1"></i>` +
        //                 groups.toUpperCase() +
        //                 `</div>
        //                         <a href="javascript:void(0);" class="editpermissions text-success mb-3">
        //                             <span class="selectAll" data-check=` +
        //                 no +
        //                 `>Pilih semua</span>
        //                         </a>
        //                         <div class="row">
        //                             ` +
        //                 html +
        //                 `
        //                         <div>
        //                     </div>
        //                 </div>
        //             `
        //             );
        //         });

        //         $("#ModalRole .modal-title").text("Edit Level");
        //         $("#ModalRole").modal("show");
        //         $.unblockUI();
        //     },
        // });
    });


});
