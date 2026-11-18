$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    _loading();

    setTimeout(function () {
        $.getJSON('/visi-misi', function (res) {
            if (Object.keys(res).length === 0) {
                $('#vFokus').html(`
                    <p style="text-align: justify" class="text-muted">
                        Oops! Moto perusahaan masih kosong
                    </p>
                `);

                $('#vVisi').html(`
                    <ul style="text-align: justify">
                        <li>Visi perusahaan masih kosong.</li>
                    </ul>
                `);

                $('#vMisi').html(`
                    <ul style="text-align: justify">
                        <li>Misi perusahaan masih kosong.</li>
                    </ul>
                `);

                $('.btnVisi').html('<i class="fas fa-plus-square me-1"></i>Tambah Visi Misi')
            } else {
                var arr = res.deskripsi_lengkap;
                var data = arr.split('|');

                $('#vFokus').html(res.deskripsi_singkat);
                $('#vVisi').html(data[0]);
                $('#vMisi').html(data[1]);
                $('.btnVisi').html('<i class="fas fa-edit me-1"></i>Edit Visi Misi');
            }
        })

        $.unblockUI();

    }, 300);

    $('.textarea_editor2').css('height', '430px');

    $(document).on('click', '.btnVisi', function () {
        _loading();
        setTimeout(function () {
            $('#formVisi')[0].reset();
            $.getJSON('/visi-misi', function (res) {
                if (Object.keys(res).length === 0) {
                    $('#idvisi').val(null);
                } else {
                    var arr = res.deskripsi_lengkap;
                    var data = arr.split('|');

                    $('#idvisi').val(res.id);
                    // $('#shortdesc').val(res.deskripsi_singkat);
                    $("#shortdesc").data("wysihtml5").editor.setValue(res.deskripsi_singkat);
                    $("#visi").data("wysihtml5").editor.setValue(data[0]);
                    $("#misi").data("wysihtml5").editor.setValue(data[1]);
                }

                $('#viewVisi').css('display', 'none');
                $('#formView').css('display', 'block');
            })

            $.unblockUI();
        }, 350);
    })

    const bsValidationForms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener(
            'submit',
            function (event) {
                // Validasi manual konten wysihtml5
                const editor = $('#visi').data('wysihtml5').editor;
                const content = editor.getValue().trim(); // Ambil konten HTML

                if (content === '' || content === '<br>' || content === '<p><br></p>') {
                    event.preventDefault();
                    event.stopPropagation();

                    $('#visi').addClass('is-invalid');
                } else {
                    $('#visi').removeClass('is-invalid');
                }

                const editor1 = $('#misi').data('wysihtml5').editor;
                const content1 = editor1.getValue().trim(); // Ambil konten HTML

                if (content1 === '' || content1 === '<br>' || content1 === '<p><br></p>') {
                    event.preventDefault();
                    event.stopPropagation();

                    $('#misi').addClass('is-invalid');
                } else {
                    $('#misi').removeClass('is-invalid');
                }



                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Submit your form

                    event.preventDefault();

                    Swal.fire({
                        title: "Konfirmasi!",
                        text: "Simpan Data Visi Misi",
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
                                url: "/visi-misi",
                                type: "POST",
                                dataType: "JSON",
                                data: $('#formVisi').serialize(),
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
                                            window.location.href = '/visi-misi';
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
})