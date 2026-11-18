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
        // responsive: true,
        columns: [
            // {
            //     data: "DT_RowIndex",
            //     name: "DT_RowIndex",
            // },
            {
                data: "status",
                name: "status",
                orderable: false,
                searchable: true,
            },
            {
                data: "namalengkap",
                name: 'namalengkap',
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
                data: 'statuskawin.kode_status'
            },
            {
                data: "bpjstk",
                name: "bpjstk",
                orderable: false,
                searchable: true,
            },
            {
                data: "bpjskes",
                name: "bpjskes",
                orderable: false,
                searchable: true,
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
                className: "dt-head-center align-top", // Header di tengah, isi rata tengah secara vertikal
            },
            {
                targets: [0, 3, 4, 5, 6],
                className: "text-center",
            },
            {
                targets: 5, // Kolom BPJS Kesehatan
                className: "text-nowrap", // Mencegah teks "Karyawan:" pecah baris jika kolom sempit
            }
        ],
    });

    $(document).on('click', '.Detail', function () {
        var token = $(this).data('token');
        _loading();
        $.getJSON('/bpjs-detail/?token=' + encodeURIComponent(token), function (response) {
            $('#modalDetail .modal-body').html(response.view);

            $('#status_bpjskar').data('status-awal', response.status);
            var inputUpah = $('#upahkes');

            if (response.status == null) {
                inputUpah.removeClass('d-none');
            } else {
                inputUpah.addClass('d-none');
            }

            if ($('#upahtk').length) { new Cleave('#upahtk', { numeral: true, thousandsGroupStyle: 'thousand', delimiter: '.', numeralDecimalMark: ',' }); }
            if ($('#upahkes').length) { new Cleave('#upahkes', { numeral: true, thousandsGroupStyle: 'thousand', delimiter: '.', numeralDecimalMark: ',' }); }

            if (response.is_deleted) {
                $('#btnSimpan').addClass('d-none'); // Gunakan class d-none (Bootstrap) agar benar-benar hilang
            } else {
                $('#btnSimpan').removeClass('d-none');
            }


        })
        $('#modalDetail .modal-title').html('BPJS KARYAWAN');
        $('#modalDetail').modal('show');
        $.unblockUI();
    })

    $(document).on('change', '#status_bpjskar', function () {
        var statusTerpilih = $(this).val();
        var statusAwal = $(this).data('status-awal');
        var inputUpah = $('#upahkes');

        // Jika status awal null ATAU tidak aktif
        if (statusAwal == null || statusAwal != "AKTIF") {
            if (statusTerpilih == "AKTIF") {
                inputUpah.removeClass('d-none');
            } else {
                inputUpah.addClass('d-none');
            }
        }
    });


    $('#formBpjs').submit(function (event) {
        _loading();
        event.preventDefault();
        $.ajax({
            url: "/bpjs",
            type: "POST",
            dataType: "JSON",
            data: $('#formBpjs').serialize(),
            success: function (response) {
                $('#modalDetail').modal('hide');
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
    })

    let table1;

    $(document).on('click', '.Tambahan', function () {
        var token = $(this).data('token');
        _loading();
        $('#collapseExample').collapse('hide');
        $.getJSON('/bpjs-tambahan/?token=' + encodeURIComponent(token), function (response) {

            $('#modalTambahan #input-tambahan').html(response.html);
            $('#modalTambahan #dtKar').html(response.identitasKar);

            fv.addField('hubungan', {
                validators: {
                    notEmpty: {
                        message: "Hubungan keluarga masih kosong",
                    },
                },
            });
            fv.addField('namalengkap', {
                validators: {
                    notEmpty: {
                        message: "Nama lengkap masih kosong",
                    },
                },
            });
            fv.addField('noktp', {
                validators: {
                    notEmpty: {
                        message: "Nomor KTP masih kosong",
                    },
                    stringLength: {
                        min: 16,
                        max: 16,
                        message: "Nomor KTP harus 16 Digit",
                    },
                },
            });
            fv.addField('nobpjskes', {
                validators: {
                    notEmpty: {
                        message: "Nomor BPJS masih kosong",
                    },
                    stringLength: {
                        min: 13,
                        max: 13,
                        message: "Nomor BPJS harus 13 Digit",
                    },
                },
            });
            fv.addField('statusbpjs', {
                validators: {
                    notEmpty: {
                        message: "Status masih kosong",
                    },
                },
            });

            $('#modalTambahan .modal-title').html('BPJS TAMBAHAN');
            $('#modalTambahan').modal('show');

            if ($.fn.DataTable.isDataTable('#tabelInvite')) {
                table1.destroy();
            }

            table1 = $("#tableTambah").DataTable({
                destroy: true, // Tambahkan baris ini
                ajax: "/bpjs-tambahan/list/?token=" + encodeURIComponent(token),
                processing: true,
                serverSide: true,
                responsive: true,
                aaSorting: [[0, "asc"]],
                columns: [
                    {
                        data: "status_hubungan",
                        name: "status_hubungan",
                    },
                    {
                        data: "nama_keluarga",
                        name: "nama_keluarga",
                    },
                    {
                        data: "ktp_keluarga",
                        name: "ktp_keluarga",
                    },
                    {
                        data: "no_bpjskes",
                        name: "no_bpjskes",
                    },
                    {
                        data: "status",
                        name: "status",
                        orderable: false,
                        searchable: false,
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
                        targets: [2, 3, 4, 5],
                        className: "dt-center",
                    }
                ],
            });
            $.unblockUI();
        })
    })

    const formValidationExamples = document.getElementById("formTambahan");
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {

        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                // Use this for enabling/changing valid/invalid class
                // eleInvalidClass: '',
                eleValidClass: "",
                rowSelector: function (field, ele) {
                    // field is the field name & ele is the field element
                    switch (field) {
                        default:
                            return ".row";
                    }
                },
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            // Submit the form when all fields are valid
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
        init: (instance) => {
            instance.on("plugins.message.placed", function (e) {
                //* Move the error message out of the `input-group` element
                if (e.element.parentElement.classList.contains("input-group")) {
                    // `e.field`: The field name
                    // `e.messageElement`: The message element
                    // `e.element`: The field element
                    e.element.parentElement.insertAdjacentElement(
                        "afterend",
                        e.messageElement,
                    );
                }
                //* Move the error message out of the `row` element for custom-options
                if (
                    e.element.parentElement.parentElement.classList.contains(
                        "custom-option",
                    )
                ) {
                    e.element
                        .closest(".row")
                        .insertAdjacentElement("afterend", e.messageElement);
                }
            });
        },
    }).on("core.form.valid", function () {
        // Form valid → submit via jQuery/AJAX
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data BPJS Tambahan",
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
                    url: "/bpjs-tambahan",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formTambahan").serialize(),
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
                            $('#formTambahan')[0].reset();
                            $('#idkeluarga').val(null);
                            $('#collapseExample').collapse('hide');
                            table.ajax.reload(null, false);
                            table1.ajax.reload(null, false);
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
    });

    $('#tableTambah tbody').on('click', 'tr', function () {
        // 1. Ambil data dari baris yang diklik
        var data = table1.row(this).data();

        if (data) {
            _loading();
            // 2. Masukkan data ke dalam field form
            $('#idkeluarga').val(data.id); // ID untuk updateOrCreate
            $('#hubungan').val(data.status_hubungan);
            $('#namalengkap').val(data.nama_keluarga);
            $('#noktp').val(data.ktp_keluarga);
            $('#nobpjskes').val(data.no_bpjskes);
            $('#statusbpjs').val(data.status_bpjskes);

            // Buka Collapse dengan animasi smooth
            var myCollapse = document.getElementById('collapseExample');
            $('#collapseExample').collapse('show');

            // Scroll otomatis setelah animasi buka mungkin butuh sedikit delay
            setTimeout(function () {
                myCollapse.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300); // Delay 300ms menyesuaikan durasi transisi bootstrap

            // Scroll otomatis ke bagian form di dalam modal
            document.getElementById('formTambahan').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Tambahan: beri highlight warna pada baris yang diklik
            $('#tableTambah tbody tr').removeClass('table-active');
            $(this).addClass('table-active');

            $.unblockUI();
        }
    });

    $('[data-bs-target="#collapseExample"]').on('click', function () {
        // Reset form dengan ID formTambahan
        $('#formTambahan')[0].reset();
        $('#idkeluarga').val(null);
    });

})