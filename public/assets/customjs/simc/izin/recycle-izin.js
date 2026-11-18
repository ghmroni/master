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

    table = $("#tableRecycle").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "desc"]],
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
                orderable: true,
                searchable: true,
            },
            {
                data: "tanggal",
                name: "tanggal",
                orderable: true,
                searchable: true,
            },
            {
                data: "jumlah_hari",
                name: "jumlah_hari",
                render: function (data, type, row) {
                    return data + " hari";
                },
            },
            {
                data: "alasan",
                name: "alasan",
                className: "wrap-text",
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 4, 5],
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".btnBack", function () {
        _loading();
        setTimeout(() => {
            window.location.href = "/izin";
        }, 300);
    });
});
