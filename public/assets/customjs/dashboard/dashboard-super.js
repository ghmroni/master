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

    // 1. Inisialisasi SSE
    const eventSource = new EventSource("/stream-active-sessions");

    // 2. Dengarkan pesan dari server
    eventSource.onmessage = function (event) {
        const res = JSON.parse(event.data);

        $("#dtActivity").empty(); // Kosongkan container

        if (Array.isArray(res) && res.length > 0) {
            $.each(res, function (id, data) {
                var url = "/assets/img/avatars/no-image.png";
                if (data.image !== null) {
                    url = "/storage/master/profile/" + data.image;
                }

                $("#dtActivity").append(`
                <div class="col-xl-4 col-lg-6 col-md-6 mb-2">
                    <div class="card card-border-shadow-success border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-normal mb-0">${data.email}</h6>
                                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                    <li class="avatar avatar-sm pull-up">
                                        <div class="avatar me-2 avatar-online">
                                            <img class="rounded-circle" src="${url}" alt="Avatar" />
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="d-flex justify-content-between align-items-end mt-0">
                                <div class="role-heading">
                                    <h5 class="mb-1">${data.name}</h5>
                                    <p class="mb-0">
                                        <small class="text-muted fw-medium">${data.role_name}</small>
                                    </p>
                                    <small class="text-muted mb-0">${data.last_activity_human}</small>
                                </div>
                                <a href="javascript:void(0);" class="text-danger btn-force-logout" data-user-id="${data.id}" data-bs-toggle="tooltip" title="Logout"><i class="fas fa-sign-out-alt ti-sm"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            });

            // Inisialisasi ulang tooltip
            const tooltipTriggerList = document.querySelectorAll(
                '#dtActivity [data-bs-toggle="tooltip"]',
            );
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
            });
        }
    };

    $(document).on("click", ".btn-force-logout", function () {
        const userId = $(this).data("user-id");

        Swal.fire({
            title: "Konfirmasi!",
            text: "Apakah anda yakin ingin mengeluarkan pengguna ini?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Keluarkan",
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
                    url: "/force-logout",
                    type: "POST",
                    data: {
                        user_id: userId,
                    },
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
                            // table.ajax.reload(null, false);
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
});
