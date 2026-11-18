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

    $(".dropify").dropify();

    $("#periodePotongan, #periodeLembur").flatpickr({
        disableMobile: "true",
        // minDate: flatpickr.formatDate(new Date(), "Y-m"),
        minDate: flatpickr.formatDate(
            new Date(new Date().getFullYear(), new Date().getMonth(), 1),
            "Y-m",
        ),
        maxDate: flatpickr.formatDate(
            new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1),
            "Y-m",
        ),
        altInput: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
            }),
        ],
    });

});
