$(function () {
    _loading();

    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#dataTable").DataTable({
        ajax: "/slider",
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
                data: "short_deskripsi",
                // render: function (data, type, row) {
                //     return data.length > 100 ? data.substr(0, 100) + '…' : data;
                // }
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
                data: "urutan",
                name: "urutan",
                orderable: true,
                searchable: false,
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
                targets: 0,
                className: "text-center",
            },
            {
                targets: 3,
                className: "text-center",
            },
            {
                targets: 4,
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

    $(document).on("click", ".addSlider", function () {
        _loading();
        window.location.href = '/tambah-slider';
    });

    $(document).on("click", ".Detail", function () {
        var slug = $(this).data("id");
        _loading();
        window.location.href = '/detail-slider/' + slug;
    });

    $("#formSlider").submit(function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append("image", $("input[type=file]")[0].files[0]);
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan data Slider ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-danger waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                // _loading();
                $.ajax({
                    url: "slider",
                    type: "POST",
                    dataType: "JSON",
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function (response) {
                        $("#myModal").modal("hide");
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
                                window.location.reload();
                            }, 300);
                        });
                    },
                    error: function (error) {
                        $.unblockUI();

                        var errorMessage = error.statusText;
                        if (error.status == 405) {
                            // Not Acceptable
                            var errors = $.parseJSON(error.responseText);
                            errorMessage = errors.errors;
                        }

                        Swal.fire({
                            title: $.parseJSON(error.status),
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
