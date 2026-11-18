$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    _loading();

    setTimeout(function () {
        $.unblockUI();
    }, 300);

    table = $("#dataTable").DataTable({
        ajax: "/layanan",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },

        columns: [
            {
                data: "gambar",
                name: "gambar",
                orderable: false,
                searchable: false,
            },
            {
                data: "title",
            },
            {
                data: "deskripsi_singkat",
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
                targets: [0, 3],
                className: "text-center",
            },

        ],
        // responsive: true,
    });

    // Re-initialize GLightbox after table redraw
    table.on('draw', function () {
        GLightbox({ selector: '.glightbox' });
    });

    // Initial load
    GLightbox({ selector: '.glightbox' });

    $("#formLayanan #title").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $(document).on('click', '.tambah', function () {
        _loading();
        $('.dropify').dropify();
        $('#formLayanan')[0].reset();
        $('#myModal').modal('show');
        $('#myModal .tombol-submit').html('<i class="fa fa-save me-1"></i>Simpan');
        $('#gambar').attr('required', true);
        $.unblockUI();
    })

    $(document).on('click', '.edit', function () {
        var slug = $(this).data('slug');
        _loading();
        $('#formLayanan')[0].reset();
        $.getJSON('/layanan-detail/' + slug, function (res) {
            $('#idlayanan').val(res.id);
            $('#kategori').val(res.kategori);
            $('#gambarlama').val(res.gambar_layanan);
            $('#title').val(res.title);
            $('#shortdesc').val(res.deskripsi_singkat);
            $("#longdesc").data("wysihtml5").editor.setValue(res.deskripsi_lengkap);

            $('#gambar').attr('data-default-file', '/storage/website/layanan/' + res.gambar_layanan);
            $('#gambar').val('');
            $('#gambar').dropify();

            $('#myModal').modal('show');
            $('#myModal .tombol-submit').html('<i class="fa fa-save me-1"></i>Simpan Perubahan');
            $('#gambar').removeAttr('required');
        })
        $.unblockUI();
    })

    $(document).on('click', '.tombol-submit', function () {
        const form = document.getElementById('formLayanan');

        const fileInput = form.querySelector('.dropify');
        if (fileInput.required) {
            if (!fileInput.files.length) {
                // Tambahkan class error dan pesan
                const wrapper = fileInput.closest('.dropify-wrapper');
                wrapper.classList.add('has-error');

                // Tambahkan pesan error jika belum ada
                if (!wrapper.querySelector('.dropify-error')) {
                    const errorMsg = document.createElement('span');
                    errorMsg.classList.add('dropify-error');
                    errorMsg.textContent = fileInput.getAttribute('data-error') || 'Gambar masih kosong';
                    wrapper.appendChild(errorMsg);
                }
            }
        }

        // Validasi manual konten wysihtml5

        const editor1 = $('#longdesc').data('wysihtml5').editor;
        const content1 = editor1.getValue().trim(); // Ambil konten HTML

        if (content1 === '' || content1 === '<br>' || content1 === '<p><br></p>') {

            $('#longdesc').addClass('is-invalid');
        } else {
            $('#longdesc').removeClass('is-invalid');
        }

        if (form.checkValidity()) {
            form.classList.remove('was-validated'); // reset jika sebelumnya gagal

            // Ambil data form
            const formData = new FormData(form);

            Swal.fire({
                title: "Konfirmasi!",
                text: "Simpan Data Layanan",
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
                        url: "/layanan",
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
                                    $('#myModal').modal('hide');
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
        } else {
            form.classList.add('was-validated'); // tampilkan error
        }

    })

    $(document).on('click', '.hapus', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Layanan",
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
                    url: "/layanan-hapus/" + id,
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

})