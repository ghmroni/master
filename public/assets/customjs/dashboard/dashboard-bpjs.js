_loading();
$(function () {
    const btnIn = $('#btnIn'),
        btnOut = $('#btnOut');

    let table, table1;


    setTimeout(function () {
        btnIn.click();
        // $.unblockUI();
    }, 100);


    $(document).on('click', '#btnIn', function () {
        _loading();

        if ($.fn.DataTable.isDataTable('#tableMasuk')) {
            table.destroy();
        }

        table = $("#tableMasuk").DataTable({
            ajax: "/bpjs-dashboard-in/",
            processing: true,
            serverSide: true,
            aaSorting: [[0, "asc"]],
            // responsive: true,
            columns: [
                {
                    data: "DT_RowIndex",
                    name: "DT_RowIndex",
                },
                {
                    data: "nip",
                    name: "nip",
                },
                {
                    data: "ktp",
                    name: "ktp",
                },
                {
                    data: "nama",
                    name: "nama",
                },
                {
                    data: "lokasi",
                    name: "lokasi",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "tglmasuk",
                    name: "tglmasuk",
                    orderable: true,
                    searchable: true,
                },

            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center align-top", // Header di tengah, isi rata tengah secara vertikal
                },
                {
                    targets: [0, 1, 2, 5],
                    className: "text-center",
                },

            ],
        });
        $.unblockUI();
    });

    $(document).on('click', '#btnOut', function () {
        _loading();
        if ($.fn.DataTable.isDataTable('#tableKeluar')) {
            table1.destroy();
        }

        table1 = $("#tableKeluar").DataTable({
            ajax: "/bpjs-dashboard-out/",
            processing: true,
            serverSide: true,
            aaSorting: [[0, "asc"]],
            drawCallback: function () {
                // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('#tableKeluar [data-bs-toggle="tooltip"]'))
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
                    data: "nip",
                    name: "nip",
                },
                {
                    data: "nama",
                    name: "nama",
                },
                {
                    data: "lokasi",
                    name: "lokasi",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "tglkeluar",
                    name: "tglkeluar",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "alasan",
                    name: "alasan",
                    orderable: true,
                    searchable: true,
                },

            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center align-top", // Header di tengah, isi rata tengah secara vertikal
                },
                {
                    targets: [0, 1, 2, 4],
                    className: "text-center",
                },

            ],
        });
        $.unblockUI();
    });
})