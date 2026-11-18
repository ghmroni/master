$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#formSlider #judul").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    _loading();

    setTimeout(function () {
        $.unblockUI();
    }, 100);

    const bsValidationForms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener(
            'submit',
            function (event) {

                // Validasi manual untuk Dropify
                const fileInput = form.querySelector('.dropify');

                if (!fileInput.files.length) {
                    event.preventDefault();
                    event.stopPropagation();

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

                // Validasi manual konten wysihtml5

                const editor1 = $('#longdesc').data('wysihtml5').editor;
                const content1 = editor1.getValue().trim(); // Ambil konten HTML

                if (content1 === '' || content1 === '<br>' || content1 === '<p><br></p>') {
                    event.preventDefault();
                    event.stopPropagation();

                    $('#longdesc').addClass('is-invalid');
                } else {
                    $('#longdesc').removeClass('is-invalid');
                }



                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Submit your form

                    event.preventDefault();
                    const formData = new FormData(this);

                    Swal.fire({
                        title: "Konfirmasi!",
                        text: "Simpan Data Slider",
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
                                url: "/slider",
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
                                            window.location.href = '/slider';
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

                }

                form.classList.add('was-validated');
            },
            false
        );
    });

    $('.dropify').dropify();

    // $('#formSlider').submit(function (event) {
    //     event.preventDefault();
    //     const formData = new FormData(this);

    //     Swal.fire({
    //         title: "Konfirmasi!",
    //         text: "Simpan Data Slider",
    //         icon: "warning",
    //         allowOutsideClick: false,
    //         allowEscapeKey: false,
    //         showCancelButton: true,
    //         confirmButtonText: "Ya, Simpan",
    //         cancelButtonText: "Batal",
    //         customClass: {
    //             confirmButton: "btn btn-primary me-3 waves-effect waves-light",
    //             cancelButton: "btn btn-outline-secondary waves-effect",
    //         },
    //         buttonsStyling: false,
    //     }).then(function (result) {
    //     });
    // })
})