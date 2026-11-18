$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#formTentang #judul").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    _loading();

    setTimeout(function () {

        $('#tentang').empty();
        $('#tim').empty();
        $('#nilai').empty();

        $.getJSON('', function (response) {
            var tentang = response.tentang,
                tim = response.tim,
                nilai = response.nilai;

            // TENTANG
            setTimeout(function () {
                if (!tentang) {
                    $('#tentang').append(`
                        <div class="card animate__animated animate__shakeX">
                            <img class="card-img-top" src="/assets/img/banner/pic1.jpg" alt="Tentang" width="720" />
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Tentang GHM</h5>
                                <p class="card-text">
                                    Deskripsi Singkat
                                </p>
                                <button class="btn btn-dark tambahTentang"><i class="fas fa-plus me-1"></i>Tambah</button>
                            </div>
                        </div>
                    `);
                } else {
                    $('#tentang').append(`
                        <div class="card animate__animated animate__fadeInDownBig">
                            <img class="card-img-top" src="/storage/website/layanan/`+ tentang.gambar_layanan + `" alt="Tentang" width="720" />
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">`+ tentang.title + `</h5>
                                <p class="card-text">
                                    `+ tentang.deskripsi_singkat + `
                                </p>
                                <button class="btn btn-primary detailTentang" data-slug="`+ tentang.slug + `"><i class="fas fa-info-circle me-1"></i>Detail</button>
                                </div>
                                </div>
                                `);
                    // <button class="btn btn-warning editTentang" data-slug="`+ tentang.slug + `"><i class="far fa-edit me-1"></i>Edit</button>
                }
            }, 150);

            // TIM
            setTimeout(function () {
                if (!tim) {
                    $('#tim').append(`
                        <div class="card animate__animated animate__shakeX">
                            <img class="card-img-top" src="/assets/img/banner/pic1.jpg" alt="Tim" width="720" />
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Tim GHM</h5>
                                <p class="card-text">
                                    Deskripsi Singkat
                                </p>
                                <button class="btn btn-dark tambahTim"><i class="fas fa-plus me-1"></i>Tambah</button>
                            </div>
                        </div>
                    `);
                } else {
                    $('#tim').append(`
                        <div class="card animate__animated animate__fadeInDownBig">
                            <img class="card-img-top" src="/storage/website/layanan/`+ tim.gambar_layanan + `" alt="Tim" width="720" />
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">`+ tim.title + `</h5>
                                <p class="card-text">
                                    `+ tim.deskripsi_singkat + `
                                </p>
                                <button class="btn btn-primary detailTim" data-slug="`+ tim.slug + `"><i class="fas fa-info-circle me-1"></i>Detail</button>
                            </div>
                        </div>
                    `);
                }
            }, 300);

            // NILAI
            setTimeout(function () {
                if (!nilai) {
                    $('#nilai').append(`
                        <div class="card animate__animated animate__shakeX">
                            <img class="card-img-top" src="/assets/img/banner/pic1.jpg" alt="Nilai" width="720" />
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">Nilai Perusahaan</h5>
                                <p class="card-text">
                                    Deskripsi Singkat
                                </p>
                                <button class="btn btn-dark tambahNilai"><i class="fas fa-plus me-1"></i>Tambah</button>
                            </div>
                        </div>
                    `);
                } else {
                    $('#nilai').append(`
                        <div class="card animate__animated animate__fadeInDownBig">
                            <img class="card-img-top" src="/storage/website/layanan/`+ nilai.gambar_layanan + `" alt="Nilai" width="720" />
                            <div class="card-body">
                                <h5 class="card-title text-uppercase">`+ nilai.title + `</h5>
                                <p class="card-text">
                                    `+ nilai.deskripsi_singkat + `
                                </p>
                                <button class="btn btn-primary detailNilai" data-slug="`+ nilai.slug + `"><i class="fas fa-info-circle me-1"></i>Detail</button>
                            </div>
                        </div>
                    `);
                }
                $.unblockUI();
            }, 500);

        });

    }, 300);

    $(document).on('click', '.tambahTentang', function () {
        $('.dropify').dropify();
        _loading();
        $('#modalTentang .modal-title').text('Form Tentang GHM');
        $('#formTentang')[0].reset();
        $('#title').val('TENTANG GHM');
        $('#kategori').val('TENTANG');
        $('#gambar').attr('required', true);
        $('#modalTentang').modal('show');
        $.unblockUI();
    })

    $(document).on('click', '.editTentang', function () {
        _loading();
        var slug = $(this).data('slug');
        $('#modalDetail').modal('hide');
        $('#formTentang')[0].reset();

        $.getJSON('/edit-tentang/' + slug, function (res) {
            $('#idtentang').val(res.id);
            $('#title').val(res.title);
            $('#kategori').val(res.kategori);
            $('#shortdesc').val(res.deskripsi_singkat);
            $("#longdesc").data("wysihtml5").editor.setValue(res.deskripsi_lengkap);
            $('#gambar_lama').val(res.gambar_layanan);
            $('#gambar').removeAttr('required');

            $('#gambar').attr('data-default-file', '/storage/website/layanan/' + res.gambar_layanan);
            $('#gambar').val('');
            $('#gambar').dropify();

        })
        $('#modalTentang .modal-title').text('Form Edit Tentang GHM');
        $('#modalTentang').modal('show');
        $.unblockUI();
    })


    const bsValidationForms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener(
            'submit',
            function (event) {

                // Validasi manual untuk Dropify
                const fileInput = form.querySelector('.dropify');
                if (fileInput.required) {

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
                                url: "/tentang",
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
                                            window.location.href = '/tentang';
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

    $(document).on('click', '.detailTentang', function () {
        var slug = $(this).data('slug');
        _loading();
        $.getJSON('/edit-tentang/' + slug, function (res) {
            if (!res.gambar_layanan) {
                src = "/assets/img/banner/pic1.jpg";
            } else {
                src = "/storage/website/layanan/" + res.gambar_layanan;
            }

            $("#cardGambar").attr("src", src);

            $('#cardTitle').html(res.title);
            $('#cardDesc').html(res.deskripsi_lengkap);
            $('.editTentang').attr('data-slug', res.slug);
        })

        $('#modalDetail').modal('show');
        $.unblockUI();
    })

    // TIM GHM
    $(document).on('click', '.tambahTim', function () {
        _loading();
        window.location.href = '/tim-kami';
        $.unblockUI();
    })

    $(document).on('click', '.detailTim', function () {
        _loading();
        window.location.href = '/tim-kami';
        $.unblockUI();
    })

    // NILAI PERUSAHAAN
    $(document).on('click', '.tambahNilai', function () {
        _loading();
        window.location.href = '/nilai-korporasi';
        $.unblockUI();
    })

    $(document).on('click', '.detailNilai', function () {
        _loading();
        window.location.href = '/nilai-korporasi';
        $.unblockUI();
    })
})