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

    table = $('#tableIn').DataTable({
        ajax: '',
        processing: true,
        serverSide: true,
        aaSorting: [[0, "desc"]],
        drawCallback: function () {
            $('table.dataTable tbody td').css('vertical-align', 'top');
        },

        columns: [
            {
                data: "tanggal",
                name: "tanggal",
                orderable: true,
                searchable: true,
            },
            {
                data: 'lowongan_apply.pelamar.nama',
            },
            {
                data: "lokasi",
                name: "lokasi",
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
                className: "dt-head-center",
            },
            {
                targets: [0, 3, 4],
                className: "text-center",
            },

        ],
    })

    $(document).on('click', '.Proses', function () {
        _loading();
        var token = $(this).data('token');
        window.location.href = '/approvalIn/approval/?token=' + encodeURIComponent(token);
        $.unblockUI();
    })

    $(document).on('click', '.Batal', function () {
        var idpel = $(this).data('id');
        var nama = $(this).data('nama');
        var token = $(this).data('token');

        Swal.fire({
            title: "Konfirmasi!",
            html: "Batalkan Approval <span class='fw-bold'>" + nama + "</span><br>Kandidat akan otomatis dikembalikan lagi ke pelamar dengan status <span class='fw-bold'>WAITING</span>",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Batalkan",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                Swal.fire({
                    title: 'Alasan Pembatalan',
                    input: 'textarea',
                    inputPlaceholder: 'Tuliskan alasan Anda...',
                    inputAttributes: {
                        'aria-label': 'Tuliskan alasan Anda'
                    },
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Alasan wajib diisi!';
                        }
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: 'Proses',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-outline-danger waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((res) => {
                    if (res.value) {
                        _loading();
                        $.ajax({
                            url: "/approvalIn/batal",
                            type: "POST",
                            dataType: "JSON",
                            data: {
                                idpel: idpel,
                                token: token,
                                alasan: res.value
                            },
                            success: function (response) {
                                $('#exLargeModal').modal('hide');
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
                                            "btn btn-danger waves-effect waves-light",
                                    },
                                    buttonsStyling: false,
                                });
                            },
                        });
                    }
                });

            }
        });
    })
})