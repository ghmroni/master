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

    table = $("#tableApproval").DataTable({
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
                }
            },
            {
                data: null,
                name: "keterangan",
                className: "wrap-text",
                render: function (data, type, row) {
                    let html = "";
                    if (row.lampiran) {
                        html += '<a href="/storage/hris/simc/cuti/' + row.lampiran + '" class="glightbox" data-glightbox="type: image; zoomable: true;" >' +
                            '<img src = "/storage/hris/simc/cuti/' + row.lampiran + '" class="img-thumbnail border shadow" alt = "image" width = "70" >' +
                            '</a > '
                    }

                    if (row.keterangan) {
                        html += row.keterangan;
                    }
                    return html || "-";
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
                targets: [0, 1, 4, 5, 7],
                className: "text-center",
            },
        ],
    });

    // Re-initialize GLightbox after table redraw
    table.on("draw", function () {
        // GLightbox({ selector: '.glightbox' });
        GLightbox({ selector: ".glightbox" });
    });

    // Initial load
    GLightbox({ selector: ".glightbox" });

    $(document).on('click', '.btnBack', function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/cuti";
        }, 300);
    })

    $("#dari").change(function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
        $("#tglmasuk").val("");
    });

    var endDate = $("#sampai").flatpickr({
        dateFormat: "Y-m-d",
        onChange: function (selectedDates) {
            var selectedDate = selectedDates[0];
            $("#tglmasuk").val("");
            // minDate = selectedDate + 1 hari
            var minDate = new Date(selectedDate);
            minDate.setDate(minDate.getDate() + 1);

            // maxDate = selectedDate + 7 hari
            var maxDate = new Date(selectedDate);
            maxDate.setDate(maxDate.getDate() + 7);

            // set batasan ke flatpickr lain (misalnya onDate)
            onDate.set("minDate", minDate);
            onDate.set("maxDate", maxDate);
        },
    });

    var onDate = $("#tglmasuk").flatpickr({
        dateFormat: "Y-m-d",
    });

    var startDate = $("#dari").flatpickr({
        dateFormat: "Y-m-d",
        maxDate: "today",
        minDate: new Date().setMonth(new Date().getMonth() - 1),
        onChange: function (selectedDates) {
            if (selectedDates.length === 0) return;

            var selectedDate = selectedDates[0];
            var kategori = $("#kategori").val();

            // tentukan range berdasarkan kategori
            var maxRange = new Date(selectedDate);
            if (kategori === "TAHUNAN") {
                maxRange.setDate(maxRange.getDate() + 7); // 7 hari
            } else {
                maxRange.setMonth(maxRange.getMonth() + 3); // 3 bulan
            }

            // update batasan untuk endDate
            endDate.set("minDate", selectedDate);
            endDate.set("maxDate", maxRange);
        },
    });

    let totalSaldo = 0;

    $("#kategori").on("change", function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
        $("#dari").val("");

        if ($(this).val() == "TAHUNAN") {
            $("#saldoCutiContainer").html(totalSaldo);
            $("#hari").prop("maxlength", 1);
        } else {
            $("#saldoCutiContainer").html(90);
            $("#hari").prop("maxlength", 2);
        }
    });

    $(document).on('click', '.detailApproval', function () {
        _loading();
        var token = $(this).data('token');
        var nama = $(this).data('nama');
        $.getJSON('/cuti-approval/detail?token=' + encodeURIComponent(token))
            .done(function (response) {
                var datas = response.datas;

                $('#formApproval')[0].reset();
                $('#myModal .modal-title').text('Detail Approval Cuti');

                $('#idcuti').val(datas.id);
                $('#idkar').val(datas.karyawan.id);
                $('#nip').val(datas.karyawan.nip);
                $('#nama').val(datas.karyawan.nama);
                $('#hari').val(datas.jumlah_hari);
                $('#keterangan').val(datas.keterangan);

                var lokasi = [];
                var color = ["bg-success", "bg-warning", "bg-primary", "bg-info", "bg-secondary", "bg-dark"];
                $.each(datas.karyawan.lokasi_penempatan, function (id, data) {
                    lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black mb-1">' + data + "</span>");
                });
                $("#lokasipekerjaan").html(lokasi.join(""));

                totalSaldo = response.sisaCuti;

                $("#saldoCutiContainer").html(totalSaldo);
                window.totalSaldoCuti = totalSaldo;

                $("#kategori").val(datas.kategori).trigger("change");

                var maxRange = new Date(datas.tanggal_dari);
                maxRange.setDate(maxRange.getDate() + 7);

                startDate.setDate(datas.tanggal_dari, true);
                endDate.setDate(datas.tanggal_sampai, true);
                endDate.set("minDate", datas.tanggal_dari);

                startDate.set("maxDate", 'today');
                endDate.set("maxDate", maxRange);

                $("#tglmasuk").val(datas.tanggal_masuk);

                var myLightbox;

                var src = datas.lampiran
                    ? "/storage/hris/simc/cuti/" + datas.lampiran
                    : "/assets/img/avatars/no-image.png";

                $('#suket').html(`
                    <a href="`+ src + `" class="glightbox"
                        data-glightbox="type: image; zoomable: true;">
                        <img src = "`+ src + `" class="img-thumbnail border shadow"
                            alt ="lampiran" width ="80">
                    </a>
                `);

                if (myLightbox) {
                    myLightbox.destroy();
                }

                // 4. Inisialisasi ulang Glightbox agar membaca href yang baru
                myLightbox = GLightbox({
                    selector: '.glightbox',
                    // loop: false,
                    touchNavigation: false,
                    // keyboardNavigation: false
                });

                $('#myModal').modal('show');

                setTimeout(() => {
                    fv.revalidateField("hari");
                    $.unblockUI();
                }, 300);

            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // gagal → tampilkan pesan error
                setTimeout(() => {
                    $.unblockUI();
                }, 300);
                Swal.fire({
                    title: 'Error',
                    text: 'Gagal Mengambil Data.',
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
            });
    })

    const formValidationSakit = document.getElementById("formApproval");
    const fv = FormValidation.formValidation(formValidationSakit, {
        fields: {
            idkar: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih karyawan",
                    },
                },
            },
            dari: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            sampai: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            tglmasuk: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            hari: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },

        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleInvalidClass: "is-invalid",
                eleValidClass: "",
                rowSelector: ".row, .col-md-4, .col-md-6, .col-md-12",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    formValidationSakit.addEventListener("submit", function (e) {
        e.preventDefault();
        const formElement = this;
        var namalengkap = $('#nama').val();

        fv.validate().then(function (status) {
            if (status === "Valid") {
                // submit form jika semua valid
                Swal.fire({
                    title: "Konfirmasi!",
                    html: "Proses Approval Cuti a.n <strong>" + namalengkap + "</strong>",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Proses",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton:
                            "btn btn-primary me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        _loading();

                        // const formElement = document.getElementById('formValidationSakit');
                        const formData = new FormData(formElement);

                        $.ajax({
                            url: "/cuti-approval",
                            type: "POST",
                            data: formData,
                            contentType: false,
                            processData: false,
                            cache: false,
                            success: function (response) {
                                $('#myModal').modal('hide');
                                $.unblockUI();
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    showConfirmButton: false,
                                    timer: 1500,
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
                                        let response = JSON.parse(
                                            error.responseText,
                                        );
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
            }
        });
    });

    $(document).on('click', '.reject', function () {
        var token = $(this).data('token');
        var nama = $(this).data('nama');

        Swal.fire({
            html: 'Tolak Pengajuan a.n <strong>' + nama + '</strong>',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Alasan Ditolak...',
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
            confirmButtonText: 'Proses Tolak',
            cancelButtonText: 'Close',
            customClass: {
                confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
                cancelButton: 'btn btn-outline-secondary waves-effect waves-light'
            },
            buttonsStyling: false
        }).then((res) => {
            if (res.value) {
                _loading();
                $.ajax({
                    url: "/rejct-simc",
                    type: "POST",
                    data: {
                        token: encodeURIComponent(token),
                        alasan: res.value
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
                                let response = JSON.parse(
                                    error.responseText,
                                );
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
    })
});
