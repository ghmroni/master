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

    $("#periode").flatpickr({
        disableMobile: "true",
        minDate: new Date().setMonth(new Date().getMonth() - 3),
        maxDate: new Date(), // bulan sekarang
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            }),
        ]
    });

    table = $("#tableSlip").DataTable({
        ajax: "/prosesgaji",
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

        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 2, 5],
                className: "text-center",
            },
        ],
    });
});
