$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#formTambah #nama").keyup(function () {
        this.value = this.value.toUpperCase();
    });


    _loading();

    setTimeout(function () {
        $.getJSON('/data-nilai-korporasi', function (res) {
            if ($.isEmptyObject(res)) {
                $('.dropify').dropify();
                $('#formNilai')[0].reset();
                $('#gambar').attr('required', true);
                $('#title').val('NILAI PERUSAHAAN');
                $('#kategori').val('NILAI');
                $('.btn-simpan').html('<i class="fas fa-save me-1"></i> Simpan')
            } else {
                $('#title').val(res.title);
                $('#kategori').val(res.kategori);
                $('#gambar_lama').val(res.gambar_layanan);
                $('#idnilai').val(res.id);
                $('#shortdesc').val(res.deskripsi_singkat);

                $('#gambar').removeAttr('required');
                $('#gambar').attr('data-default-file', '/storage/website/layanan/' + res.gambar_layanan);
                $('#gambar').val('');
                $('#gambar').dropify();
                $('.btn-simpan').html('<i class="fas fa-save me-1"></i>Simpan Perubahan')
            }
        })
        $.unblockUI();
    }, 300);


    table = $("#dataTable").DataTable({
        ajax: "/nilai-korporasi",
        processing: true,
        serverSide: true,
        aaSorting: [[1, "asc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },

        columns: [
            {
                data: "logo",
                name: "logo",
                orderable: true,
                searchable: false,
            },
            {
                data: "nourut",
            },
            {
                data: "nama",
            },
            {
                data: "quote",
                render: function (data, type, row) {
                    if (type === 'display') {
                        // Bersihkan HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = data;
                        const cleanText = temp.textContent || temp.innerText || '';

                        // Potong jika lebih dari 100 karakter
                        return cleanText.length > 50
                            ? cleanText.substring(0, 50) + ' . . . '
                            : cleanText;
                    }
                    return data;
                }


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
                targets: [0, 1, 4],
                className: "text-center",
            },

        ],
        // responsive: true,
    });

    $('#formNilai').submit(function (event) {
        event.preventDefault();

        // Cek DataTable sudah terisi belum
        var table = $('#dataTable').DataTable();
        var rowCount = table.data().count();

        if (rowCount === 0) {
            event.preventDefault(); // hentikan submit
            Swal.fire({
                title: 'Oops!',
                html: 'Data Nilai masih kosong.<br> Silakan tambahkan Nilai Perusahaan terlebih dahulu.',
                icon: "warning",
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

            return false;
        }
        // EndCheck

        const formData = new FormData(this);

        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data",
            icon: "warning",
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
                    url: "/nilai-korporasi",
                    type: "POST",
                    dataType: "JSON",
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: false,
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
                            window.location.href = '/tentang';
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
                                    "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    },
                });
            }
        });

    })


    $(document).on('click', '.addNilai', function () {
        _loading();
        $('#formTambah')[0].reset();
        $('#logo').val(null).trigger('change');
        $('#modalNilai').modal('show');
        $('#modalNilai .modal-title').text('Tambah Nilai Perusahaan');
        $.unblockUI();
    })

    $(document).on('click', '.editNilai', function () {
        var id = $(this).data('id');
        _loading();
        $('#formTambah')[0].reset();
        $.getJSON('/tambah-nilai/?id=' + id, function (res) {
            $('#idtampung').val(res.id);
            $('#logo').val(res.photo).trigger('change');
            $('#nama').val(res.nama);
            $('#nourut').val(res.nourut);
            $('#quote').val(res.quote);

            $('#modalNilai').modal('show');
            $('#modalNilai .modal-title').text('Edit Nilai Perusahaan');
        })
        $.unblockUI();
    })

    $('#formTambah').submit(function (event) {
        event.preventDefault();

        Swal.fire({
            title: "Konfirmasi!",
            text: "Tambah Data Nilai Perusahaan",
            icon: "warning",
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
                    url: "/tambah-nilai",
                    type: "POST",
                    dataType: "JSON",
                    data: $('#formTambah').serialize(),
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
                            $('#modalNilai').modal('hide');
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
                                    "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    },
                });
            }
        });

    })

    $(document).on('click', '.hapusNilai', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data Nilai Perusahaan",
            icon: "warning",
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
                    url: "/hapus-nilai-tampung/?id=" + id,
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
                            $('#modalNilai').modal('hide');
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