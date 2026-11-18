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
        ajax: "/portofolio",
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
        // GLightbox({ selector: '.glightbox' });
        GLightbox({ selector: '.glightbox' });
    });

    // Initial load
    GLightbox({ selector: '.glightbox' });

    $("#formPortofolio #title").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $(document).on('click', '.tambah', function () {
        $('.dropify').dropify();
        $('#formPortofolio')[0].reset();
        $('#gambar').attr('required', true);
        $('#myModal').modal('show');
        $('#myModal .btn-simpan').html('<i class="fa fa-save me-1"></i>Simpan');
    })

    $(document).on('click', '.edit', function () {
        var slug = $(this).data('slug');
        _loading();
        $.getJSON('/portofolio-detail/?slug=' + slug, function (res) {

            $('#formPortofolio')[0].reset();

            $('#gambar').removeAttr('required');
            $('#idport').val(res.id);
            $('#gambarlama').val(res.gambar_layanan);
            $('#title').val(res.title);
            $('#shortdesc').val(res.deskripsi_singkat);

            $('#gambar').attr('data-default-file', '/storage/website/layanan/' + res.gambar_layanan);
            $('#gambar').val('');
            $('#gambar').dropify();

            $('#myModal').modal('show');
            $('#myModal .btn-simpan').html('<i class="fa fa-save me-1"></i>Simpan Perubahan');
        })
        $.unblockUI();
    })

    $('#formPortofolio').submit(function (event) {
        event.preventDefault();

        const formData = new FormData(this);

        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Portofolio",
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
                    url: "/portofolio",
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
                            $('#myModal').modal('hide');
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

    $(document).on('click', '.hapus', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Portofolio",
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
                    url: "/portofolio-hapus/?id=" + id,
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