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

    $(document).on("keydown", ".input-disc", function (e) {
        var currentInput = $(this);
        var inputs = $(".input-disc");
        var index = inputs.index(currentInput);
        var totalInputs = inputs.length;

        // Navigasi Panah Bawah (Lompat 2 kolom)
        if (e.which == 40) {
            e.preventDefault();
            if (index + 2 < totalInputs) {
                inputs.eq(index + 2).focus();
            }
        }
        // Navigasi Panah Atas (Mundur 2 kolom)
        else if (e.which == 38) {
            e.preventDefault();
            if (index - 2 >= 0) {
                inputs.eq(index - 2).focus();
            }
        }
        // Navigasi Panah Kanan
        else if (e.which == 39) {
            e.preventDefault();
            if (index + 1 < totalInputs) {
                inputs.eq(index + 1).focus();
            }
        }
        // Navigasi Panah Kiri
        else if (e.which == 37) {
            e.preventDefault();
            if (index - 1 >= 0) {
                inputs.eq(index - 1).focus();
            }
        }
        // Tombol Spasi (Panggil fungsi toggle terpusat)
        else if (e.which == 32) {
            e.preventDefault();
            toggleInputDisc(currentInput);
        }
    });

    // 2. Handler Klik Mouse (Dipisah agar tidak memicu double-toggle)
    $(document).on("click", ".input-disc", function (e) {
        // e.preventDefault(); // Mencegah trigger fokus bawaan yang mengacaukan nilai
        // var currentInput = $(this);
        // currentInput.focus();
        // toggleInputDisc(currentInput);
        $(this).focus();
    });

    // 3. Fungsi Logika Aturan DISC Terpusat (Single Source of Truth)
    function toggleInputDisc(currentInput) {
        var nomorSoal = currentInput.data("nomor");
        var nomorBaris = currentInput.data("baris");
        var isMost = currentInput.hasClass("chk-most");

        // Jika kotak sudah berisi X, kosongkan (Toggle Off)
        if (currentInput.val() === "X") {
            currentInput.val("");
            currentInput.trigger("change");
        }
        // Jika kotak kosong, lakukan validasi aturan DISC (Toggle On)
        else {
            var targetLawanClass = isMost ? ".chk-least" : ".chk-most";
            var pasanganSatuBaris = $(
                targetLawanClass +
                '[data-nomor="' +
                nomorSoal +
                '"][data-baris="' +
                nomorBaris +
                '"]',
            );

            // ATURAN 1: P (Most) dan K (Least) tidak boleh di baris yang sama (Horizontal)
            if (pasanganSatuBaris.val() === "X") {
                Swal.fire({
                    title: "Oops!",
                    text: "Pilihan P (Paling) dan K (Kurang) tidak boleh di baris pernyataan yang sama!",
                    icon: "info",
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
                // swal("Oops!", "Pilihan P (Paling) dan K (Kurang) tidak boleh di baris pernyataan yang sama!", "warning");
                return false;
            }

            // ATURAN 2: Hapus tanda X lain di KOLOM yang sama (Vertikal) pada nomor soal ini
            var kelasKolomSama = isMost ? ".chk-most" : ".chk-least";
            $(kelasKolomSama + '[data-nomor="' + nomorSoal + '"]')
                .not(currentInput)
                .each(function () {
                    if ($(this).val() === "X") {
                        $(this).val("");
                        $(this).trigger("change"); // Beritahu database data lama dihapus
                    }
                });

            // Isi kotak aktif dengan X
            currentInput.val("X");
            currentInput.trigger("change"); // Beritahu database data baru masuk
        }
    }

    $(document).on("change", ".input-disc", function () {
        var currentInput = $(this);
        var nomorSoal = currentInput.data("nomor");
        var nomorBaris = currentInput.data("baris");
        var tipe = currentInput.data("tipe"); // Mendapatkan teks 'most' atau 'least'
        var isMost = currentInput.hasClass("chk-most");

        if (currentInput.val() === "") {
            // Hapus input hidden pasangannya jika ada
            $("#hidden-" + tipe + "-" + nomorSoal).remove();
            if (isMost) {
                $("#m-baris-" + nomorSoal).text("-");
                $("#m-faktor-" + nomorSoal).text("-");
            } else {
                $("#k-baris-" + nomorSoal).text("-");
                $("#k-faktor-" + nomorSoal).text("-");
            }

            // Tembak AJAX kosong untuk menghapus jawaban nomor ini di database
            updateDiscRealtime(nomorSoal, "", isMost);
            return false;
        }

        var targetLawanClass = isMost ? ".chk-least" : ".chk-most";
        var pasanganSatuBaris = $(
            targetLawanClass +
            '[data-nomor="' +
            nomorSoal +
            '"][data-baris="' +
            nomorBaris +
            '"]',
        );

        // Perbaikan: Ganti .is(':checked') menjadi .val() === 'X'
        if (pasanganSatuBaris.val() === "X") {
            swal(
                "Oops!",
                "Pilihan P (Paling) dan K (Kurang) tidak boleh di baris pernyataan yang sama!",
                "warning",
            );
            currentInput.val(""); // Perbaikan: Mengosongkan kembali input text jika melanggar
            return false;
        }

        var kelasKolomSama = isMost ? ".chk-most" : ".chk-least";
        $(kelasKolomSama + '[data-nomor="' + nomorSoal + '"]')
            .not(currentInput)
            .val("");

        // =========================================================================
        // KUNCI JAWABAN: Suntik atau Perbarui Elemen Input Hidden untuk Form Serialize
        // =========================================================================
        // Hapus input hidden lama untuk nomor soal & tipe ini jika sebelumnya sudah ada
        $("#hidden-" + tipe + "-" + nomorSoal).remove();

        // Buat input hidden baru untuk penampung Form Serialize
        var inputHiddenBaru =
            '<input type="hidden" ' +
            'id="hidden-' +
            tipe +
            "-" +
            nomorSoal +
            '" ' +
            'name="' +
            tipe +
            "[" +
            nomorSoal +
            ']" ' +
            'value="' +
            nomorBaris +
            '">';

        // Masukkan ke dalam container form
        $("#hidden-answers-container").append(inputHiddenBaru);

        // Eksekusi Tembak AJAX Realtime
        updateDiscRealtime(nomorSoal, nomorBaris, isMost);
    });

    function updateDiscRealtime(nomorSoal, nomorBaris, isMost) {
        $.ajax({
            url: "/disc-realtime",
            type: "GET",
            data: {
                nomor_soal: nomorSoal,
                nomor_baris: nomorBaris,
                is_most: isMost ? 1 : 0,
            },
            success: function (response) {
                if (response) {
                    // Update data ke tabel ringkasan berdasarkan response dari database
                    if (isMost) {
                        $("#m-baris-" + nomorSoal).text(nomorBaris);
                        $("#m-faktor-" + nomorSoal).text(
                            response.faktor_paling,
                        );
                    } else {
                        $("#k-baris-" + nomorSoal).text(nomorBaris);
                        $("#k-faktor-" + nomorSoal).text(
                            response.faktor_kurang,
                        );
                    }

                    // Aktifkan visual baris tabel ringkasan
                    $("#row-summary-" + nomorSoal)
                        .removeClass("text-muted")
                        .css("opacity", "1");

                    // TAMPILKAN DATA MASK PUBLIC SELF (LINE 1)
                    if (
                        response.hasil_teks &&
                        response.hasil_teks.mask_public
                    ) {
                        var defMask = response.hasil_teks.mask_public;
                        $("#maskProfile").html(
                            "<strong>" + defMask.profile_title + "</strong>" ||
                            "-",
                        );
                        $("#maskText").html(defMask.karakter.join("<br>"));
                    }

                    // TAMPILKAN DATA CORE PRIVATE SELF (LINE 2)
                    if (
                        response.hasil_teks &&
                        response.hasil_teks.core_private
                    ) {
                        var defCore = response.hasil_teks.core_private;
                        $("#coreProfile").html(
                            "<strong>" + defCore.profile_title + "</strong>" ||
                            "-",
                        );
                        $("#coreText").html(
                            (defCore.karakter || "-").join("<br>"),
                        );

                        $('#kepribadianText').html(defCore.kepribadian || '');
                        $('#jobText').html(defCore.pekerjaan || '');

                        $("#hasil_kepribadian").val(defCore.kepribadian);
                        $("#hasil_job").val(defCore.pekerjaan);
                    }

                    // TAMPILKAN DATA MIRROR PERCEIVED SELF (LINE 3)
                    if (
                        response.hasil_teks &&
                        response.hasil_teks.mirror_perceived
                    ) {
                        var defMirror = response.hasil_teks.mirror_perceived;
                        $("#mirrorProfile").html(
                            "<strong>" +
                            defMirror.profile_title +
                            "</strong>" || "-",
                        );
                        $("#mirrorText").html(
                            (defMirror.karakter || "-").join("<br>"),
                        );
                        $("#deskPribadi").html(
                            defMirror.deskripsi_kepribadian || "-",
                        );
                        $("#jobMatch").html(
                            defMirror.deskripsi_pekerjaan || "-",
                        );
                    }

                    // UPDATE DATA GRAFIK DYNAMICALLY
                    if (response.data_grafik) {
                        if (response.data_grafik.most) {
                            maskGraph = response.data_grafik.most.map(
                                function (val, idx) {
                                    return [idx, val];
                                },
                            );
                            if (typeof maskDisc === "function") maskDisc();
                        }
                        if (response.data_grafik.least) {
                            coreGraph = response.data_grafik.least.map(
                                function (val, idx) {
                                    return [idx, val];
                                },
                            );
                            if (typeof coreDisc === "function") coreDisc();
                        }
                        if (response.data_grafik.change) {
                            mirrorGraph = response.data_grafik.change.map(
                                function (val, idx) {
                                    return [idx, val];
                                },
                            );
                            if (typeof mirrorDisc === "function") mirrorDisc();
                        }
                    }

                    if (response.data_line) {
                        renderTable(response.data_line);
                    }

                    $("#hasil_line").val(JSON.stringify(response.data_line));
                    $("#hasil_grafik").val(JSON.stringify(response.data_grafik));
                    $("#hasil_tabel").val(JSON.stringify(response.isian_tabel));
                    $("#hasil_definisi").val(JSON.stringify(response.hasil_teks));
                }
            },
        });
    }

    function maskDisc() {
        var options = {
            series: {
                lines: { show: true },
                points: { show: true, radius: 4 } // Titik diperbesar agar mudah dilihat
            },
            yaxis: {
                min: -8.0,
                max: 8.0,
                ticks: [-8.0, -6.0, -4.0, -2.0, 0, 2.0, 4.0, 6.0, 8.0]
            },
            xaxis: {
                min: -0.5,
                max: 3.5,
                ticks: [
                    [0, "D"],
                    [1, "I"],
                    [2, "S"],
                    [3, "C"]
                ]
            },
            colors: ["#009efb", "#26c6da"],
            grid: {
                color: "#AFAFAF",
                hoverable: true,
                borderWidth: 0,
                backgroundColor: 'transparent'
            },
            tooltip: true,
            tooltipOpts: {
                content: "%x : %y",
                shifts: { x: -60, y: 25 }
            }
        };

        // 2. PLOT: Masukkan variabel data ke dalam library Flot
        $.plot($("#mask-line-chart"), [{
            data: maskGraph,
        },], options);
    }

    function coreDisc() {
        var options = {
            series: {
                lines: { show: true },
                points: { show: true, radius: 4 } // Titik diperbesar agar mudah dilihat
            },
            yaxis: {
                min: -8.0,
                max: 8.0,
                ticks: [-8.0, -6.0, -4.0, -2.0, 0, 2.0, 4.0, 6.0, 8.0]
            },
            xaxis: {
                min: -0.5,
                max: 3.5,
                ticks: [
                    [0, "D"],
                    [1, "I"],
                    [2, "S"],
                    [3, "C"]
                ]
            },
            colors: ["#009efb", "#26c6da"],
            grid: {
                color: "#AFAFAF",
                hoverable: true,
                borderWidth: 0,
                backgroundColor: 'transparent'
            },
            tooltip: true,
            tooltipOpts: {
                content: "%x : %y",
                shifts: { x: -60, y: 25 }
            }
        };

        // 2. PLOT: Masukkan variabel data ke dalam library Flot
        $.plot($("#core-line-chart"), [{
            data: coreGraph,
        },], options);
    }

    function mirrorDisc() {
        var options = {
            series: {
                lines: { show: true },
                points: { show: true, radius: 4 } // Titik diperbesar agar mudah dilihat
            },
            yaxis: {
                min: -8.0,
                max: 8.0,
                ticks: [-8.0, -6.0, -4.0, -2.0, 0, 2.0, 4.0, 6.0, 8.0]
            },
            xaxis: {
                min: -0.5,
                max: 3.5,
                ticks: [
                    [0, "D"],
                    [1, "I"],
                    [2, "S"],
                    [3, "C"]
                ]
            },
            colors: ["#009efb", "#26c6da"],
            grid: {
                color: "#AFAFAF",
                hoverable: true,
                borderWidth: 0,
                backgroundColor: 'transparent'
            },
            tooltip: true,
            tooltipOpts: {
                // content: "%s: %y.1", // Mengubah format tooltip agar lebih simpel dibaca
                // content: "X: %x, Y: %y", // lebih aman
                content: "%x : %y",
                shifts: { x: -60, y: 25 }
            }
        };

        // 2. PLOT: Masukkan variabel data ke dalam library Flot
        $.plot($("#mirror-line-chart"), [{
            data: mirrorGraph,
        }], options);
    }


    function renderTable(data_line) {
        let tbody = $("#tableDisc tbody");
        tbody.empty(); // clear dulu

        Object.keys(data_line).forEach(function (lineKey, index) {
            let row = data_line[lineKey];

            // default isi kolom LINE → angka urut
            let lineCol = index + 1;
            // default style untuk kolom * dan TOTAL
            let starCol = "<td>*</td>";
            let totalCol = `<th class="text-danger"><strong>${row.TOTAL}</strong></th>`;

            // kalau line2 → kasih background hitam
            if (lineKey === "line3") {
                starCol = `<td class="table-light">&nbsp;</td>`;
                totalCol = `<td class="table-light">&nbsp;</td>`;
            }
            let html = `
                <tr class="text-center">
                    <th><strong>${lineCol}</strong></th>
                    <td>${row.D}</td>
                    <td>${row.I}</td>
                    <td>${row.S}</td>
                    <td>${row.C}</td>
                    ${starCol}
                    ${totalCol}
                </tr>
            `;

            tbody.append(html);
        });
    }

    $("#discForm").submit(function (event) {
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
                    url: "/disc-store",
                    data: $("#discForm").serialize(),
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
