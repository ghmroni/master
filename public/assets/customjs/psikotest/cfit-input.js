_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 300);


    $(".jawaban-cfit").on("keydown", function (e) {
        // Ambil semua elemen input dengan class yang sama
        var $inputs = $(".jawaban-cfit");

        // Cari posisi indeks input yang sedang aktif saat ini
        var idx = $inputs.index(this);

        // Jika tombol arah panah bawah (keyCode 40) ditekan
        if (e.which === 40 || e.which === 13) {
            e.preventDefault(); // Mencegah fungsi bawaan browser
            // Pindahkan fokus ke input berikutnya jika masih ada
            if (idx + 1 < $inputs.length) {
                $inputs
                    .eq(idx + 1)
                    .focus()
                    .select();
            }
        }
        // Opsional: Jika tombol arah panah atas (keyCode 38) ditekan
        else if (e.which === 38) {
            e.preventDefault();
            // Pindahkan fokus ke input sebelumnya jika bukan input pertama
            if (idx - 1 >= 0) {
                $inputs
                    .eq(idx - 1)
                    .focus()
                    .select();
            }
        }
    });

    $(document).on("input", ".jawaban-cfit", function () {
        var dataJawaban = {};
        $(".jawaban-cfit").each(function () {
            var idSoal = $(this).data("soal-id");
            var isiJawaban = $(this).val().trim().toUpperCase();

            if (isiJawaban !== "") {
                dataJawaban[idSoal] = isiJawaban;
            }
        });

        // 2. Kirim data ke Controller menggunakan AJAX jika ada jawaban yang terisi
        $.ajax({
            url: "/cfit-realtime",
            type: "GET",
            data: { jawaban: dataJawaban },
            success: function (response) {
                $("#hasil-iq").text(response.iq);
                $("#hasil-nilai").html(response.iq); // Nilai Bersih / Weighted Score / RS
                $("#hasil-kategori").text(response.kategori);
            },
            error: function () {
                console.log("Gagal memproses hitung norma.");
            },
        });
    });

    $("#cfitForm").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Hasil Test !",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-success me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/cfit-store",
                    data: $("#cfitForm").serialize(),
                    type: "POST",
                    success: function (response) {
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
                            window.location.href = '/data-pemanggilan';
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
                                if (response.errors) {
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
                                    errorMessage = errors.errors;
                                } else if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
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
});
