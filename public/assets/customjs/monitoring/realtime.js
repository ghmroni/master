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

    table = $("#tableAbsensi").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[1, "desc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "karyawan.nip",
                name: "karyawan.nip",
            },
            {
                data: "karyawan.nama",
                name: "karyawan.nama",
            },
            {
                data: "penempatan",
                name: "penempatan",
                orderable: false,
                searchable: false,
            },
            {
                data: "keterangan",
                name: "keterangan",
            },
            {
                data: "photo",
                name: "photo",
                orderable: false,
                searchable: false,
            },

        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 5],
                className: "text-center",
            },
        ],
    });

    // Realtime Menggunakan SSE
    const source = new EventSource('/monitoring-stream');
    source.onmessage = function (event) {
        const response = JSON.parse(event.data);
        const data = response.data ? response.data : response;

        table.clear();
        table.rows.add(data);
        table.draw(false);
    }

    // Re-initialize GLightbox after table redraw
    table.on("draw", function () {
        // GLightbox({ selector: '.glightbox' });
        GLightbox({ selector: ".glightbox" });
    });

    // Initial load
    GLightbox({ selector: ".glightbox" });

    // setInterval(function () {
    //     table.ajax.reload(null, false);
    // }, 10000);

    $(document).on("click", ".tambahMonitoring", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/tambah-monitoring";
        }, 300);
    });
});
