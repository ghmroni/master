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

    $(".jawaban-papi").on("keydown", function (e) {
        // Ambil semua elemen input dengan class yang sama
        var $inputs = $(".jawaban-papi");

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

    $(document).on("input keyup change", ".jawaban-papi", function () {
        let val = $(this).val().trim().toUpperCase();

        // 1. Validasi input: Batasi hanya boleh huruf A atau B
        if (val !== "A" && val !== "B") {
            $(this).val(""); // Kosongkan jika user mengetik huruf lain atau menekan backspace/delete
        } else {
            $(this).val(val); // Pastikan huruf otomatis berubah menjadi kapital
        }

        // 2. Kumpulkan semua jawaban (termasuk yang kosong karena baru saja dihapus)
        var dataJawaban = {};
        $(".jawaban-papi").each(function () {
            var idSoal = $(this).data("soal-id");
            var isiJawaban = $(this).val().trim().toUpperCase();

            // Tetap masukkan ke object meskipun kosong agar server tahu jawaban tersebut DIHAPUS
            dataJawaban["jawaban[" + idSoal + "]"] = isiJawaban;
        });

        // 3. Kirim ke server via AJAX
        // Menggunakan $.param() agar object JavaScript dikonversi menjadi format query string POST yang dipahami CI3
        var dataSerialized = $.param(dataJawaban);

        $.ajax({
            url: "/papi-realtime",
            type: "GET",
            data: dataSerialized,
            success: function (response) {
                $("#totalAtas").text(response.total_atas);
                $("#totalBawah").text(response.total_bawah);
                // Loop hasil JSON dari controller dan perbarui text tabel secara real-time
                $.each(response.faktor, function (aspek, data) {
                    $("#skor-" + aspek).text(data.skor);
                    $("#analisis-" + aspek).text(data.analisis);
                });

                $("#hasil_atas").val(JSON.stringify(response.total_atas));
                $("#hasil_bawah").val(JSON.stringify(response.total_bawah));
                $("#hasil_faktor").val(JSON.stringify(response.faktor));
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            },
        });
    });

    $("#papiForm").submit(function (event) {
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
                    url: "/papi-store",
                    data: $("#papiForm").serialize(),
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
                            window.location.href = "/data-pemanggilan";
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

    const {
        chars
    } = splitText('.card-title', {
        chars: true
    });


});
