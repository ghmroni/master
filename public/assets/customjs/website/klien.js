$(function () {
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"), },
    });

    Dropzone.autoDiscover = false;
    const previewTemplate = `
        <div class="dz-preview dz-file-preview">
            <div class="dz-details">
                <div class="dz-thumbnail">
                    <img data-dz-thumbnail>
                    <span class="dz-nopreview">No preview</span>
                    <div class="dz-success-mark"></div>
                    <div class="dz-error-mark"></div>
                    <div class="dz-error-message"><span data-dz-errormessage></span></div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
                    </div>
                </div>
                <div class="dz-filename" data-dz-name></div>
                <div class="dz-size" data-dz-size></div>
            </div>
        </div>
    `;

    // Inisialisasi Dropzone untuk #dropzone-multi
    const myDropzone = new Dropzone('#dropzone-multi', {
        url: '/klien',
        method: 'POST',
        autoProcessQueue: false,
        previewTemplate: previewTemplate,
        parallelUploads: 1,
        maxFilesize: 5,
        acceptedFiles: ".jpg,.jpeg,.png",
        addRemoveLinks: true,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        init: function () {
            const dz = this;

            // Tombol submit manual
            $('#form-upload').on('click', function (e) {
                e.preventDefault();
                if (dz.getQueuedFiles().length > 0) {
                    dz.processQueue();
                } else {
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Tidak ada gambar yang dipilih.',
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
                }
            });

            dz.on("sending", function (file) {
                _loading();
            });

            dz.on("success", function (files, response) {
                const removeButton = files.previewElement.querySelector(".dz-remove");
                if (removeButton) {
                    removeButton.style.display = "none";
                }

            });

            dz.on("error", function (files, response) {
                let message = "Gagal upload";

                // Jika response adalah objek, ambil message-nya
                if (typeof response === "object" && response.message) {
                    message = response.message;
                } else if (typeof response === "string") {
                    message = response;
                }

                // Tampilkan pesan error di preview Dropzone
                files.previewElement.querySelector("[data-dz-errormessage]").textContent = message;
            });

            dz.on("complete", function (file) {
                if (dz.getQueuedFiles().length > 0) {
                    dz.processQueue();
                }
            });

            dz.on("queuecomplete", function () {
                getData();
                $.unblockUI();
            });

        }

    });

    _loading();

    setTimeout(function () {
        getData();
        $.unblockUI();
    }, 300);



    function getData() {

        $.getJSON('/klien', function (response) {
            $('#dtKlien').empty();

            if (response.length > 0) {
                $('#daftarKlien').css('display', 'block');
                $.each(response, function (id, data) {
                    $('#dtKlien').append(`
                        <div class="swiper-slide position-relative">
                            <img src="/storage/website/klien/${data.name}" alt="Klien ${id}" class="img-fluid" width="400" height="300px"/>
                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 hapusKlien" data-id="${data.id}" title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    `);
                });

                // Inisialisasi Swiper setelah data dimasukkan
                const swiperMultipleSlides = document.querySelector('#swiper-multiple-slides');
                if (swiperMultipleSlides) {
                    new Swiper(swiperMultipleSlides, {
                        slidesPerView: 4,
                        spaceBetween: 15,
                        pagination: {
                            clickable: true,
                            el: '.swiper-pagination'
                        }
                    });
                }
            } else {
                $('#daftarKlien').css('display', 'none');
            }
        });
    }

    $(document).on('click', '.hapusKlien', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus data dari daftar klien",
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
                    url: "/hapus-klien/" + id,
                    type: "POST",
                    dataType: "JSON",
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
                            getData();
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