_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 300);

    let selectedPermissions = [];

    $(document).on('change', 'input[name="permissions[]"]', function () {
        const val = $(this).val();
        if (this.checked) {
            if (!selectedPermissions.includes(val)) {
                selectedPermissions.push(val);
            }
        } else {
            selectedPermissions = selectedPermissions.filter(id => id !== val);
        }
    });

    function fetchData(page = 1) {
        let search = $('#searchInput').val();
        let token = $('#roleToken').val();

        $.ajax({
            url: "/roles/kelola/?page=" + page,
            type: "GET",
            data: {
                search: search,
                selectedPermissions: selectedPermissions,
            },
            success: function (data) {
                $('#resultPermission').html(data);

                // restore state dari selectedPermissions (interaksi user)
                selectedPermissions.forEach(id => {
                    $('input[name="permissions[]"][value="' + id + '"]').prop('checked', true);
                });

                // restore state dari rolePermissions (bawaan DB saat edit)
                rolePermissions.forEach(id => {
                    $('input[name="permissions[]"][value="' + id + '"]').prop('checked', true);
                });

            }
        });
    }

    $('#searchInput').on('input change', function () {
        fetchData();
    });

    // ketika klik "pilih semua" per module
    $(document).on('change', '.selectAll', function () {
        const group = $(this).data('group');
        const checked = $(this).is(':checked');

        // centang semua checkbox permission dalam group yang sama
        $(`.perm-checkbox[data-group="${group}"]`).prop('checked', checked).trigger('change');
    });

    // sinkronisasi: kalau semua child dicentang, otomatis selectAll ikut tercentang
    $(document).on('change', '.perm-checkbox', function () {
        const group = $(this).data('group');
        const all = $(`.perm-checkbox[data-group="${group}"]`).length;
        const checked = $(`.perm-checkbox[data-group="${group}"]:checked`).length;

        $(`.selectAll[data-group="${group}"]`).prop('checked', all === checked);
    });

    document.getElementById("formRoles").addEventListener("submit", function (e) {

        e.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan data Role / Level",
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
                    url: "/roles",
                    type: "POST",
                    dataType: "JSON",
                    data: $('#formRoles').serialize(),
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
                            window.location.href = '/roles'
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
})