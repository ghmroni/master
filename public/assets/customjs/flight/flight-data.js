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

    table = $("#tableFlight").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll(
                    '#tableFlight [data-bs-toggle="tooltip"]',
                ),
            );
            var tooltipList = tooltipTriggerList.map(
                function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                },
            );
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "periode",
                name: "periode",
            },
            {
                data: "project",
                name: "project",
                orderable: true,
                searchable: true,
            },
            {
                data: "operators",
                name: "operators",
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `<div style="white-space:normal; word-wrap:break-word;">${data}</div>`;
                }
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
                targets: "_all",
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".detail", function () {
        var token = $(this).data("token");
        _loading();
        setTimeout(function () {
            window.location.href =
                "/detail-operator?token=" + encodeURIComponent(token);
        }, 100);
    });

    $(document).on('click', '.btnLayanan', function (e) {
        e.preventDefault();
        _loading();
        var token = $(this).data('token');

        let html = "";
        $.getJSON("/detail-layanan?token=" + encodeURIComponent(token), function (res) {

            if (res.canEdit) {
                html += `
                    <div class="mb-3 text-end">
                        <button class="btn btn-warning btn-xs add-row" data-token="${res.token}">
                        <i class="ti ti-plus me-1"></i>Tambah Layanan
                        </button>
                    </div>
                `;
            }

            if (res.items.length === 0) {
                html += '<label class="text-center text-muted">Oops! Layanan tidak ditemukan.</label>'
            } else {
                res.items.forEach(function (item, i) {
                    html += `
                        <div class="input-group mb-2">
                            <input type="text" class="form-control text-uppercase" name="titles[]" placeholder="Layanan" value="${item.title}" autocomplete="off"/>
                            <input type="hidden" class="form-control" name="ceklis[]" value="${item.ceklis}" />
                            <button class="btn btn-primary save-row" type="button" data-token="${item.token}"><i class="ti ti-cloud-up me-1"></i>Simpan</button>
                            <button class="btn btn-danger remove-row" type="button" data-token="${item.token}" data-layanan="${item.title}"><i class="ti ti-trash me-1"></i>Hapus</button>
                        </div>
                    `;
                });
            }


            $('#modalLayanan .modal-body').html(html);
            $('#modalLayanan .modal-title').text('DATA LAYANAN ' + res.sheet);
            $('#modalLayanan').modal('show');
            $.unblockUI();
        })
    })

    $(document).on("input", 'input[name="ceklis[]"]', function () {
        let val = $(this).val().toUpperCase();
        if (val !== "" && val !== "X") {
            // kalau bukan kosong dan bukan X, reset jadi kosong
            $(this).val("");
        }
    });

    $(document).on('click', '.add-row', function () {
        var token = $(this).data('token');
        _loading();
        $('#modalLayanan .modal-body').empty();
        $('#modalLayanan .modal-body').append(`
            <div class="input-group mb-2">
                <input type="text" class="form-control text-uppercase" name="titles[]" placeholder="Nama Layanan" autocomplete="off"/>
                <input type="text" class="form-control text-uppercase" name="ceklis[]" maxlength="1" pattern="X" placeholder="ceklis" autocomplete="off" />
                <button class="btn btn-primary tambahLayanan" type="button" data-token="${token}"><i class="ti ti-cloud-up me-1"></i>Simpan</button>
            </div>
        `);
        setTimeout(function () {
            $.unblockUI();
        }, 200);
    })

    // Tambah
    $(document).on('click', '.tambahLayanan', function () {
        let token = $(this).data('token');
        let parent = $(this).closest('.input-group');
        let title = parent.find('input[name="titles[]"]').val();
        let ceklis = parent.find('input[name="ceklis[]"]').val();

        Swal.fire({
            title: "Konfirmasi!",
            text: "Tambah Layanan",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Tambah",
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
                    url: '/store-layanan',
                    type: 'POST',
                    data: { token: token, title: title, ceklis: ceklis },
                    success: function (response) {
                        $("#modalLayanan").modal("hide");
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
                            table.ajax.reload(null, false);
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

    // Simpan
    $(document).on('click', '.save-row', function () {
        let token = $(this).data('token');
        let parent = $(this).closest('.input-group');
        let title = parent.find('input[name="titles[]"]').val();
        let ceklis = parent.find('input[name="ceklis[]"]').val();

        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data Layanan",
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
                    url: '/update-layanan',
                    type: 'POST',
                    data: { token: token, title: title, ceklis: ceklis },
                    success: function (response) {
                        $("#modalLayanan").modal("hide");
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
                            table.ajax.reload(null, false);
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

    // Hapus
    $(document).on('click', '.remove-row', function () {
        let token = $(this).data('token');
        let layanan = $(this).data('layanan');
        let parent = $(this).closest('.input-group');
        let title = parent.find('input[name="titles[]"]').val();
        let ceklis = parent.find('input[name="ceklis[]"]').val();

        Swal.fire({
            title: "Konfirmasi!",
            html: "Hapus Layanan <strong>" + layanan + "</strong>",
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
                    url: '/destroy-layanan',
                    type: 'POST',
                    data: { token: token, title: title, ceklis: ceklis },
                    success: function (response) {
                        $("#modalLayanan").modal("hide");
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
                            table.ajax.reload(null, false);
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

    $(document).on('click', '.hapusOperator', function () {
        var token = $(this).data('token');
        var sheet = $(this).data('sheet');
        Swal.fire({
            title: "Konfirmasi!",
            html: "Hapus Operator <strong>" + sheet + "</strong>?",
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
                    url: "/destroy-operator",
                    type: "POST",
                    dataType: "JSON",
                    data: { token: token },
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
                            table.ajax.reload(null, false);
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

    $(document).on('click', '.downloadFlight', function () {
        let $row = $(this).closest("tr");
        let token = $(this).data("token");
        let lampiran = $(this).data("lampiran");
        let isGenerateChecked = $row.find("input[name=generate]").is(":checked");

        if (!isGenerateChecked) {
            // tidak dicentang
            $('#formPetugas')[0].reset();
            if (lampiran) {
                $.getJSON('/getDataLampiran/?id=' + lampiran, function (data) {
                    $('#formPetugas #tokenid').val(token);
                    $('#formPetugas #lampiranid').val(lampiran);
                    $("#formPetugas #generate").val(isGenerateChecked ? 1 : 0);
                    $("#formPetugas #ghm").val(data.ghm);
                    $("#formPetugas #gapura").val(data.gapura);
                    $("#formPetugas #dibuat").val(data.dibuat);
                    $("#formPetugas #mengetahui").val(data.mengetahui);
                    $("#formPetugas #menyetujui").val(data.menyetujui);
                    $("#formPetugas #menyetujui").val(data.menyetujui);
                    $("#formPetugas #jab1").val(data.jab1);
                    $("#formPetugas #jab2").val(data.jab2);
                    $("#formPetugas #jab3").val(data.jab3);
                    // ada lampiran → langsung proses ke controller
                    $('#formPetugas').submit();
                    // window.location.href = '/pdf-flight/?idlampiran=' + lampiran;
                })
            } else {
                // tidak ada lampiran → tampilkan modal tanpa textarea
                $('#formPetugas #tokenid').val(token);
                $('#formPetugas #lampiranid').val(lampiran);
                $("#formPetugas #generate").val(isGenerateChecked ? 1 : 0);
                $("#modalPetugas .modal-title").text("Form Pejabat Terkait");
                $("#modalPetugas").modal("show");
            }
        } else {
            // dicentang → ambil data dulu dari controller

            $('#formPetugas #tokenid').val(token);
            $('#formPetugas #lampiranid').val(lampiran);
            $("#formPetugas #generate").val(isGenerateChecked ? 1 : 0);
            $("#modalPetugas .modal-title").text("Form Pejabat Terkait");
            $("#modalFlightBody").html(`
                <p>Silakan isi keterangan untuk generate ulang:</p>
                <textarea class="form-control" id="keterangan" name="keterangan" required></textarea>
            `);
            $("#modalPetugas").modal("show");
        }
    })

    $('#formPetugas').submit(function (event) {
        event.preventDefault();
        _loading();
        $.ajax({
            url: "/download-flight",
            type: "POST",
            data: $('#formPetugas').serialize(),
            xhrFields: {
                responseType: "blob", // penting untuk file
            },
            success: function (response, status, xhr) {
                // Ambil nama file dari header kalau ada
                let filename = "Flight.pdf";
                const disposition = xhr.getResponseHeader(
                    "Content-Disposition",
                );
                if (disposition && disposition.indexOf("filename=") !== -1) {
                    filename = disposition
                        .split("filename=")[1]
                        .replace(/"/g, "");
                }

                // Buat blob sesuai tipe file
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    $.unblockUI();
                }, 300);
            },
            error: function (error) {
                $.unblockUI();
                Swal.fire({
                    title: "500",
                    text: "Gagal Download Data.",
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
            complete: function () {
                table.ajax.reload(null, false);
                $('#modalPetugas').modal('hide');
                $.unblockUI();
            },
        });
    })
});
