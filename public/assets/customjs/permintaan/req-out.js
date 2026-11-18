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

    table = $("#dataTable").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('#dataTable [data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        },
        // responsive: true,
        columns: [
            // {
            //     data: "DT_RowIndex",
            //     name: "DT_RowIndex",
            // },
            {
                data: "nip",
                name: "nip",
            },
            {
                data: "nama",
                name: 'nama',
                orderable: true,
                searchable: true,
            },
            {
                data: "lokasi",
                name: "lokasi",
                orderable: true,
                searchable: true,
            },
            {
                data: 'catatan',
                name: 'catatan',
                orderable: true,
                searchable: true,
            },
            {
                data: "tglout",
                name: "tglout",
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
                className: "dt-head-center align-top", // Header di tengah, isi rata tengah secara vertikal
            },
            {
                targets: [0, 4, 5, 6],
                className: "text-center",
            },
            // {
            //     targets: 3, // Kolom BPJS Kesehatan
            //     className: "text-wrap", // Mencegah teks "Karyawan:" pecah baris jika kolom sempit
            // }
        ],
    });

    // $('#tglkeluar').flatpickr({
    //     monthSelectorType: "static",
    //     minDate: "today",
    //     // static: true
    // });

    $(document).on('click', '.Tambah', function () {
        _loading();
        $('#boxDetail').css('display', 'none');
        $('#formOut')[0].reset();
        $("#formOut #idreq").val(null);

        // Aktifkan kembali dropdown
        $('#karyawanid').prop('disabled', false);
        $('#karyawanid').empty().append('<option value="">pilih</option>');

        // $('#karyawanid').empty();
        // $('#karyawanid').append('<option value="">pilih</option>');
        karyawanList = {};
        $.getJSON('/list-karyawan', function (response) {
            $.each(response, function (Id, data) {
                karyawanList[data.id] = data; // simpan detail di object JS
                $('#karyawanid').append('<option value="' + data.id + '" >' + data.nip + ' - ' + data.nama + '</option>');
            })
        })

        $('#myModal .modal-title').text('Request Out Karyawan');
        $('#myModal').modal('show');
        $.unblockUI();
    })

    $(document).on('change', '#karyawanid', function () {
        // $('#boxDetail').css('display', 'none');
        $('#boxDetail').collapse('hide');
        _loading();
        var id = $(this).val();
        var detail = karyawanList[id];// ambil detail dari cache

        var lokasi = [];
        var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
        $.each(detail.lokasi_penempatan, function (id, data) {
            lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black">' + data + '</span>');
        })

        $('#boxDetail #vnip').html(detail.nip);
        $('#boxDetail #vnama').html(detail.nama);
        $('#boxDetail #vlokasi').html(lokasi.join(" "));

        $('#boxDetail').css('display', 'block');
        $('#boxDetail').collapse('show');

        $.unblockUI();
    });

    const formValidationMutasi = document.getElementById("formOut");
    const fv = FormValidation.formValidation(formValidationMutasi, {
        fields: {
            karyawanid: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih karyawan",
                    },
                },
            },
            statusout: {
                validators: {
                    notEmpty: {
                        message:
                            "Alasan Keluar masih kosong",
                    },
                },
            },
            tglkeluar: {
                validators: {
                    notEmpty: {
                        message: "Tanggal Keluar masih kosong",
                    },
                },
            },
            catatan: {
                validators: {
                    notEmpty: {
                        message: "Catatan masih kosong",
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleInvalidClass: "is-invalid",
                eleValidClass: "",
                rowSelector: ".row, .col-md-6, .col-md-12",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    formValidationMutasi.addEventListener('submit', function (e) {
        e.preventDefault();

        fv.validate().then(function (status) {
            if (status === 'Valid') {
                // submit form jika semua valid
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Proses Permintaan Karyawan Keluar",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Proses",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        _loading();
                        $('#karyawanid').prop('disabled', false); // Buka kunci sebentar
                        var formData = $('#formOut').serialize();     // Ambil data
                        $('#karyawanid').prop('disabled', true);

                        $.ajax({
                            url: "/requestOut",
                            type: "POST",
                            data: formData,
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
            }
        });
    });

    $(document).on('click', '.Edit', function () {
        var token = $(this).data('token');
        _loading();
        $.getJSON('/edit-requestout/?token=' + token, function (response) {

            $("#formOut #idreq").val(response.id);
            $("#formOut #catatan").val(response.catatan_keluar);
            $("#formOut #statusout").val(response.status_out);
            $("#formOut #tglkeluar").val(response.tanggal_keluar);

            // 2. Kosongkan selectbox lalu isi HANYA dengan karyawan ini
            $('#karyawanid').empty();
            $('#karyawanid').append('<option value="' + response.karyawan_id + '" selected>' + response.nip + ' - ' + response.nama + '</option>');

            // Simpan ke cache sementara agar fungsi change (boxDetail) tetap jalan
            karyawanList = {};
            karyawanList[response.karyawan_id] = {
                id: response.karyawan_id,
                nip: response.nip,
                nama: response.nama,
                lokasi_penempatan: response.lokasi_pekerjaan // pastikan data lokasi ada di response detail
            };

            // 3. Trigger change agar info di #boxDetail muncul secara otomatis
            $('#karyawanid').trigger('change');

            // 4. Kunci agar tidak bisa dipilih yang lain (opsional tapi disarankan)
            $('#karyawanid').prop('disabled', true);

            $('#myModal .modal-title').html('Edit Request Out');
            $('#myModal').modal('show');
            $.unblockUI();
        }).fail(function (error) {
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
        });
    })

    $(document).on('click', '.Hapus', function () {
        var token = $(this).data('token');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Batalkan Permintaan Karyawan Keluar",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Batalkan",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/hapus-requestout/?token=" + token,
                    type: "POST",
                    success: function (response) {
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