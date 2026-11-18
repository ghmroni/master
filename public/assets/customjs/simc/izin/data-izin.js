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

    table = $("#tableIzin").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "desc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "karyawan.nip",
                name: "karyawan.nip",
            },
            {
                data: "karyawan.nama",
                name: "karyawan.nama",
            },
            {
                data: "penempatan",
                name: "penempatan",
                orderable: true,
                searchable: true,
            },
            {
                data: "tanggal",
                name: "tanggal",
                orderable: true,
                searchable: true,
            },
            {
                data: "jumlah_hari",
                name: "jumlah_hari",
                render: function (data, type, row) {
                    return data + " hari";
                },
            },
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
            },
            {
                data: "keterangan",
                name: "keterangan",
                className: "wrap-text",
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
                targets: [0, 1, 4, 5, 6, 8],
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".btnApproval", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/izin-approval";
        }, 300);
    });

    $(document).on("click", ".btnTambah", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/izin-tambah";
        }, 300);
    });

    $(document).on("click", ".btnRecycle", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/izin-recycle";
        }, 300);
    });

    $(document).on("click", ".Hapus", function () {
        var token = $(this).data("token");
        var nama = $(this).data("nama");
        Swal.fire({
            html: "Hapus Data Izin a.n <strong>" + nama + "</strong><br><br>Data Rekap Kehadiran Pada Tanggal Tersebut Akan Ikut Terhapus.",
            icon: "warning",
            input: "textarea",
            inputPlaceholder: "Alasan Dihapus...",
            inputAttributes: {
                "aria-label": "Tuliskan alasan Anda",
            },
            inputValidator: (value) => {
                if (!value) {
                    return "Alasan wajib diisi!";
                }
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Close",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then((res) => {
            if (res.value) {
                _loading();
                $.ajax({
                    url: "/izin-destroy",
                    type: "POST",
                    data: {
                        token: encodeURIComponent(token),
                        alasan: res.value,
                    },

                    success: function (response) {
                        $.unblockUI();
                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1700,
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
                        }).then(() => {
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
                                if (response.errors) {
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
                                    errorMessage = errors.errors;
                                } else if (response.message) {
                                    // Jika ada message langsung
                                    errorMessage = response.message;
                                }
                            } catch (e) {
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
