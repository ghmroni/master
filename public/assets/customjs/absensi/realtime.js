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
        ajax: "/absensi",
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
                data: "waktu",
                name: "waktu",
                orderable: true,
                searchable: false,
            },
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
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
                data: "photo",
                name: "photo",
                orderable: false,
                searchable: false,
            },
            {
                data: "tag_location",
                name: "tag_location",
                render: function (data) {
                    if (data && data.trim() !== "") {
                        var coordinate = data.split(",");

                        if (coordinate.length === 2) {
                            let lat = coordinate[0].trim();
                            let lng = coordinate[1].trim();

                            // Menggunakan format URL Google Maps resmi (Universal & Aman)
                            let url = `https://google.com.sa/maps/place/${lat},${lng}`;

                            return `<a href="${url}" target="_blank" class="btn btn-sm btn-info text-white">
                                <i class="fas fa-street-view me-1"></i> Map
                            </a>`;
                        }
                    }
                    return '<span class="text-muted">Tidak ada lokasi</span>';
                },
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 2, 3, 6, 7],
                className: "text-center",
            },
        ],

    });

    // Realtime Menggunakan SSE
    const source = new EventSource('/absensi-stream');
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

    $(document).on("click", ".tambahAbsensi", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/absensi-tambah";
        }, 300);
    });
});
