_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 200);

    table = $("#tableApprove").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('#tableApprove [data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        },
        columns: [
            {
                data: "jenis",
                name: "jenis",
                orderable: false,
                searchable: true,
            },
            {
                data: "nomor",
                name: "nomor",
                orderable: true,
                searchable: true,
            },
            {
                data: "peminta",
                name: "peminta",
                orderable: true,
                searchable: true,
            },
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
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
                targets: [3, 4],
                className: "dt-center",
            },
        ],
    });

    $(document).on('click', '.Detail', function () {
        var token = $(this).data('token');
        _loading();
        window.location.href = '/approval-kontrakinduk/detail/?token=' + encodeURIComponent(token);
        // $.unblockUI();
    })

    $(document).on('click', '.Perpanjang', function () {
        var token = $(this).data('token');
        _loading();
        window.location.href = '/kontrakinduk/perpanjang/?token=' + encodeURIComponent(token);
        // $.unblockUI();
    })

    $(document).on('click', '.tambah-adendum', function () {
        var token = $(this).data('token');
        _loading();
        window.location.href = '/adendum/tambah/?token=' + encodeURIComponent(token);
        // $.unblockUI();
    })

    $(document).on('click', '.Hapus', function () {
        var token = $(this).data('token');
        var nomor = $(this).data('nomor');
        Swal.fire({
            title: "Konfirmasi!",
            html: "Hapus Kontrak induk dengan nomor <br><span class='fw-bold'>" + nomor + " </span>",
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
                    url: "/kontrakinduk/delete/?token=" + encodeURIComponent(token),
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
});
