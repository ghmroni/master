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

    table = $("#tableHasil").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll(
                    '#tableHasil [data-bs-toggle="tooltip"]',
                ),
            );
            var tooltipList = tooltipTriggerList.map(
                function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                },
            );
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "tglpanggil",
                name: "tglpanggil",
                orderable: true,
                searchable: true,
            },
            {
                data: "lowongan_apply.pelamar.nama",
            },
            {
                data: "cfit",
                name: "cfit",
                orderable: true,
                searchable: true,
            },
            {
                data: "mbti",
                name: "mbti",
                orderable: true,
                searchable: true,
            },
            {
                data: "papi",
                name: "papi",
                orderable: true,
                searchable: true,
            },
            {
                data: "disc",
                name: "disc",
                orderable: true,
                searchable: true,
            },
            {
                data: "wawancara",
                name: "wawancara",
                orderable: true,
                searchable: true,
            },
            {
                data: "analisis",
                name: "analisis",
                orderable: true,
                searchable: true,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 3, 4, 5, 6, 7, 8],
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".createAnalisis", function () {
        var token = $(this).data("token");
        _loading();
        setTimeout(function () {
            window.location.href =
                "/create-analisis?token=" + encodeURIComponent(token);
        }, 300);
    });

    $(document).on("click", ".cetakAnalisis", function () {
        var token = $(this).data("token");
        _loading();
        $.ajax({
            url: "/cetak-hasil-analisis",
            type: "POST",
            data: { token: token },
            xhrFields: {
                responseType: "blob", // penting untuk file
            },
            success: function (response, status, xhr) {
                // Ambil nama file dari header kalau ada
                let filename = "Hasil Psikotest.pdf";
                const disposition = xhr.getResponseHeader(
                    "Content-Disposition",
                );
                if (disposition && disposition.indexOf("filename=") !== -1) {
                    filename = disposition
                        .split("filename=")[1]
                        .replace(/"/g, "");
                }

                // Buat blob sesuai tipe file
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    $.unblockUI();
                }, 300);
            },
            error: function (error) {
                $.unblockUI();
                Swal.fire({
                    title: "500",
                    text: "Gagal Download Data.",
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
            complete: function () {
                $.unblockUI();
            },
        });
    });

    $(document).on("click", ".lihatHasil", function () {
        var token = $(this).data("token");
        var url = $(this).data("url");

        _loading();
        $.ajax({
            url: url,
            type: "POST",
            data: { token: token },
            xhrFields: {
                responseType: "blob", // penting untuk file
            },
            success: function (response, status, xhr) {
                // Ambil nama file dari header kalau ada
                let filename = "Hasil Psikotest.pdf";
                const disposition = xhr.getResponseHeader(
                    "Content-Disposition",
                );
                if (disposition && disposition.indexOf("filename=") !== -1) {
                    filename = disposition
                        .split("filename=")[1]
                        .replace(/"/g, "");
                }

                // Buat blob sesuai tipe file
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => {
                    $.unblockUI();
                }, 300);
            },
            error: function (error) {
                $.unblockUI();
                Swal.fire({
                    title: "500",
                    text: "Gagal Download Data.",
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
            complete: function () {
                $.unblockUI();
            },
        });
    });
});
