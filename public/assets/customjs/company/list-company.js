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

    table = $("#tableCompany").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            $("table.dataTable tbody td").css("vertical-align", "top");
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "logo",
                name: "logo",
                orderable: false,
                searchable: false,
            },
            {
                data: "name",
                name: "name",
                orderable: true,
                searchable: true,
            },
            {
                data: "code",
                name: "code",
                orderable: true,
                searchable: true,
            },
            {
                data: "inisial",
                name: "inisial",
                orderable: true,
                searchable: true,
            },
            {
                data: "dibuat",
                name: "dibuat",
                orderable: true,
                searchable: true,
            },
            {
                data: "action",
                name: "action",
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
                targets: [0, 1, 3, 4, 6],
                className: "text-center",
            },
        ],
    });

    // Re-initialize GLightbox after table redraw
    table.on("draw", function () {
        // GLightbox({ selector: '.glightbox' });
        GLightbox({ selector: ".glightbox" });
    });

    // Initial load
    GLightbox({ selector: ".glightbox" });

    $(document).on("click", ".Tambah", function () {
        _loading();
        $("#previews").hide();
        $("#preview-logo").attr("src", "").hide();
        $("#formCompany")[0].reset();
        $("#idcompany").val(null);
        $.ajax({
            url: "/next-code-company", // Sesuaikan dengan route Anda
            type: "GET",
            success: function (response) {
                // Asumsikan input field Anda memiliki id="code"
                $("#code").val(response.next_code);

                setTimeout(function () {
                    $("#myModal .modal-title").text("Tambah Perusahaan");
                    $("#myModal").modal("show");
                    $.unblockUI();
                }, 300);
            },
            error: function () {
                $.unblockUI();
                alert("Gagal mengambil kode otomatis");
            },
        });
    });

    $(document).on("click", ".Edit", function () {
        var token = $(this).data("token");
        _loading();
        $("#formCompany")[0].reset();
        $("#idcompany").val(null);
        $.ajax({
            url: "/detail-company?token=" + encodeURIComponent(token),
            type: "GET",
            success: function (response) {
                $("#idcompany").val(response.id);
                $("#name").val(response.name);
                $("#code").val(response.code);
                $("#inisial").val(response.inisial);

                if (response.logo) {
                    $("#previews").show();
                    var logoUrl = "/assets/logos/" + response.logo;
                    $("#preview-logo").attr("src", logoUrl).show();
                } else {
                    $("#previews").hide();
                    $("#preview-logo").hide(); // Sembunyikan jika tidak ada logo
                }

                setTimeout(function () {
                    $("#myModal .modal-title").text("Detail Perusahaan");
                    $("#myModal").modal("show");
                    $.unblockUI();
                }, 300);
            },
            error: function () {
                $.unblockUI();
                alert("Gagal mengambil kode otomatis");
            },
        });
    });

    $("#formCompany").submit(function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        _loading();
        $.ajax({
            url: "/company",
            type: "POST",
            dataType: "JSON",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
                $("#myModal").modal("hide");
                $.unblockUI();
                Swal.fire({
                    title: "Berhasil!",
                    text: response.message,
                    icon: "success",
                    showConfirmButton: false,
                    timer: 1000,
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
                    table.ajax.reload();
                });
            },
            error: function (error) {
                $.unblockUI();

                var errorMessage = error.statusText;
                if (error.status == 405) {
                    // Not Acceptable
                    var errors = $.parseJSON(error.responseText);
                    errorMessage = errors.errors;
                }

                Swal.fire({
                    title: $.parseJSON(error.status),
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
    });
});
