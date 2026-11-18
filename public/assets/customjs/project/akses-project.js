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

    function fetchData(page = 1) {
        let search = $('#searchInput').val();

        $.ajax({
            url: "/akses-projectuser/?page=" + page,
            type: "GET",
            data: {
                search: search,
            },
            success: function (data) {
                $('#resultContainer').html(data);
            }
        });

    }

    $('#searchInput').on('input change', function () {
        fetchData();
    });


})