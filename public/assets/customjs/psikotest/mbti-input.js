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

    $(".jawaban-mbti").on("keydown", function (e) {
        // Ambil semua elemen input dengan class yang sama
        var $inputs = $(".jawaban-mbti");

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

    $(document).on("input keyup change", ".jawaban-mbti", function () {
        let val = $(this).val().trim().toUpperCase();

        // Batasi hanya A atau B
        if (val !== "A" && val !== "B") {
            $(this).val(""); // kosongkan kalau bukan A/B
        } else {
            $(this).val(val); // pastikan huruf besar
        }

        // --- kumpulkan jawaban ---
        var dataJawaban = {};
        $(".jawaban-mbti").each(function () {
            var idSoal = $(this).data("soal-id");
            var isiJawaban = $(this).val().trim().toUpperCase();

            dataJawaban[idSoal] = isiJawaban;
        });

        $("#rowDeskripsi").empty();
        $("#rowSaran").empty();
        $("#rowProfesi").empty();

        // --- kirim ke server jika ada jawaban ---
        if (Object.keys(dataJawaban).length > 0) {
            $.ajax({
                url: "/mbti-realtime",
                type: "GET",
                data: { jawaban: dataJawaban },
                success: function (response) {
                    var dimensi =
                        Array.isArray(response.dimensi) &&
                            response.dimensi.length === 4
                            ? response.dimensi
                            : ["I", "N", "F", "P"];
                    var persentase = response.persentase;
                    var resume = response.analisis;

                    if (persentase && typeof persentase === "object") {
                        $("#persent_e").text((persentase.persent_e || 0) + "%");
                        $("#persent_i").text((persentase.persent_i || 0) + "%");
                        $("#persent_s").text((persentase.persent_s || 0) + "%");
                        $("#persent_n").text((persentase.persent_n || 0) + "%");
                        $("#persent_t").text((persentase.persent_t || 0) + "%");
                        $("#persent_f").text((persentase.persent_f || 0) + "%");
                        $("#persent_j").text((persentase.persent_j || 0) + "%");
                        $("#persent_p").text((persentase.persent_p || 0) + "%");
                    } else {
                        // Reset ke default jika tidak ada data
                        $(
                            "#persent_e, #persent_i, #persent_s, #persent_n, #persent_t, #persent_f, #persent_j, #persent_p",
                        ).text("0%");
                    }

                    $("#dimensi_1").text(dimensi[0]);
                    $("#dimensi_2").text(dimensi[1]);
                    $("#dimensi_3").text(dimensi[2]);
                    $("#dimensi_4").text(dimensi[3]);

                    if (resume) {
                        $.each(resume.deskripsi, function (index, valData) {
                            $("#rowDeskripsi").append(
                                `
                                <tr>
                                    <td width="5%" class="text-center font-weight-bold">` +
                                (index + 1) +
                                `</td>
                                    <td>` +
                                valData +
                                `</td>
                                </tr>
                            `,
                            );
                        });

                        $.each(resume.saran, function (index, valData) {
                            $("#rowSaran").append(
                                `
                                <tr>
                                    <td width="5%" class="text-center font-weight-bold">` +
                                (index + 1) +
                                `</td>
                                    <td>` +
                                valData +
                                `</td>
                                </tr>
                            `,
                            );
                        });

                        $("#rowProfesi").text(resume.profesi);
                    }

                    // SIMPAN KE INPUT
                    $("#hasil_analisis").val(JSON.stringify(response.analisis));
                    $("#hasil_dimensi").val(JSON.stringify(response.dimensi));
                    $("#hasil_persentase").val(
                        JSON.stringify(response.persentase),
                    );
                },
                error: function () {
                    console.log("Gagal memproses hitung MBTI.");
                },
            });
        }
    });

    $("#mbtiForm").submit(function (event) {
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
                    url: "/mbti-store",
                    data: $("#mbtiForm").serialize(),
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
