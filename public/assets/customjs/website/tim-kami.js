$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#formTentang #judul").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    var myOffcanvas = new bootstrap.Offcanvas(
        document.getElementById("offcanvasTop")
    );

    _loading();

    setTimeout(function () {
        $.getJSON('/get-tim-kami', function (res) {
            if ($.isEmptyObject(res)) {
                $('.dropify').dropify();
                $('#formTim')[0].reset();
                $('#gambar').attr('required', true);
                $('#title').val('TIM GHM');
                $('#kategori').val('TIM');
                $('.btn-simpan').html('<i class="fas fa-save me-1"></i>Simpan');
            } else {
                $('#title').val(res.title);
                $('#kategori').val(res.kategori);
                $('#gambar_lama').val(res.gambar_layanan);
                $('#idtim').val(res.id);
                $('#shortdesc').val(res.deskripsi_singkat);

                $('#gambar').removeAttr('required');
                $('#gambar').attr('data-default-file', '/storage/website/layanan/' + res.gambar_layanan);
                $('#gambar').val('');
                $('#gambar').dropify();
                $('.btn-simpan').html('<i class="fas fa-save me-1"></i>Simpan Perubahan');
            }
        })
        $.unblockUI();
    }, 300);

    table = $("#dataTable").DataTable({
        ajax: "/tim-kami",
        processing: true,
        serverSide: true,
        aaSorting: [[1, "asc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },

        columns: [
            {
                data: "gambar",
                name: "image",
                orderable: false,
                searchable: false,
            },
            {
                data: "nourut",
            },
            {
                data: "nama",
            },
            {
                data: "jabatan",
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
                targets: [0, 1, 5],
                className: "text-center",
            },

        ],
        // responsive: true,
    });

    $("#formTambah #name").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $(document).on("click", ".tambahUser", function () {
        _loading();
        $('#formTambah')[0].reset();
        $('#idsementara').val(null);
        $('#photo').attr('required', true);
        myOffcanvas.show();
        $.unblockUI();
    });

    $(document).on("click", ".editUser", function () {
        var id = $(this).data('id');
        _loading();
        $('#formTambah')[0].reset();
        $('#photo').removeAttr('required');

        $.getJSON('/add-tim/?id=' + id, function (res) {
            $('#idsementara').val(res.id);
            $('#photo_lama').val(res.photo);
            $('#name').val(res.nama);
            $('#jabatan').val(res.jabatan);
            $('#nourut').val(res.nourut);
            $('#quote').val(res.quote);
        })
        myOffcanvas.show();
        $.unblockUI();
    });

    $('#formTambah').submit(function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        Swal.fire({
            title: "Konfirmasi!",
            text: "Tambah Data Tim",
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
                    url: "/add-tim",
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
                            setTimeout(function () {
                                myOffcanvas.hide();
                                table.ajax.reload();
                            }, 300);
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

    $(document).on('click', '.hapusUser', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data",
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
                    url: "/hapus-tim/?id=" + id,
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
                            setTimeout(function () {
                                myOffcanvas.hide();
                                table.ajax.reload();
                            }, 300);
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

    $('#formTim').submit(function (event) {
        event.preventDefault();

        // Cek DataTable sudah terisi belum
        var table = $('#dataTable').DataTable();
        var rowCount = table.data().count();

        if (rowCount === 0) {
            event.preventDefault(); // hentikan submit
            Swal.fire({
                title: 'Oops!',
                html: 'Data tim masih kosong.<br> Silakan tambahkan anggota tim terlebih dahulu.',
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
                    url: "/tim-kami",
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
})