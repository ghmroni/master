_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#formRoles :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });


    setTimeout(function () {
        $.unblockUI();
    }, 300);

    function fetchData(page = 1) {
        let search = $('#searchInput').val();

        $.ajax({
            url: "/roles/?page=" + page,
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

    $(document).on("click", "#selectAllModule", function () {
        $('#formRoles input[type="checkbox"]').each(function () {
            $("#formRoles input:checkbox")
                .not(this)
                .prop("checked", this.checked);
        });
    });

    $(document).on("click", ".selectAll", function () {

        var id = $(this).data("check");
        var $checkboxes = $("#formRoles #parent" + id);

        var allChecked = $checkboxes.filter(":checked").length === $checkboxes.length;

        if (allChecked) {
            $checkboxes.prop("checked", false);
            $(this).text("Pilih semua");
        } else {
            $checkboxes.prop("checked", true);
            $(this).text("Batalkan semua");
        }

    });

    $("#formRoles").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan data Level ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-danger waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/roles",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formRoles").serialize(),
                    success: function (response) {
                        $("#ModalRole").modal("hide");
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
                            setTimeout(function () {
                                window.location.reload();
                            }, 300);
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
            }
        });
    });
});
