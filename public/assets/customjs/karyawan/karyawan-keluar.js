_loading();

$(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $('#dataTable').DataTable({
        ajax: '',
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('#dataTable [data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            // $('table.dataTable tbody td').css('vertical-align', 'top');
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: 'nip',
            },
            {
                data: 'nama',
            },
            {
                data: 'lokasi',
                name: "lokasi",
                orderable: true,
                searchable: true,
            },
            {
                data: 'recordout.jabatan',
            },
            {
                data: 'tglout',
                name: "tglout",
                orderable: true,
                searchable: false,
            },
            {
                data: 'alasan',
                name: "alasan",
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
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 5, 7],
                className: "text-center",
            },


        ],
        // responsive: true,
    })

    $(document).on('click', '.Detail', function () {
        _loading();
        var token = $(this).data('token');
        window.location.href = '/karyawan/detail/?token=' + encodeURIComponent(token);
        $.unblockUI();
    })

    $(document).on('click', '.Paklaring', function () {
        // _loading();
        var token = $(this).data('token');
        // window.open('/karyawan/paklaring/?token=' + encodeURIComponent(token), '_blank');
        // $.unblockUI();
        _loading();
        $.ajax({
            url: '/karyawan/paklaring',
            type: "POST",
            data: { token: token },
            xhrFields: {
                responseType: "blob", // penting untuk file
            },
            success: function (response, status, xhr) {
                // Ambil nama file dari header kalau ada
                let filename = "Paklaring.pdf";
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
    })

})