_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 500);

    // Fungsi untuk mencentang semua anak di bawahnya secara rekursif
    function toggleChildren(parentId, isChecked) {
        // Cari elemen checkbox yang memiliki data-parent sesuai ID saat ini
        var $children = $('.perm-checkbox[data-parent="' + parentId + '"]');

        $children.each(function () {
            var $child = $(this);
            $child.prop("checked", isChecked);

            // Panggil kembali fungsi ini untuk mencari anak dari anak (cucu/cicit)
            toggleChildren($child.val(), isChecked);
        });
    }

    // Aksi ketika checkbox "Pilih Semua" (di bagian atas accordion) diklik
    $(".selectAll").on("change", function () {
        var groupId = $(this).data("group");
        var isChecked = $(this).is(":checked");

        // Centang semua checkbox di dalam grup tersebut
        $('.perm-checkbox[data-group="' + groupId + '"]').prop(
            "checked",
            isChecked,
        );
    });

    // Aksi ketika salah satu checkbox sub-parent / anak diklik
    $(document).on("change", ".perm-checkbox", function () {
        var parentId = $(this).val();
        var isChecked = $(this).is(":checked");

        // Jalankan fungsi rekursif ke bawah
        toggleChildren(parentId, isChecked);
    });

    document
        .getElementById("formAkses")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            Swal.fire({
                title: "Konfirmasi!",
                text: "Simpan Akses Project Pengguna",
                icon: "question",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Simpan",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton:
                        "btn btn-primary me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: "/akses-projectuser",
                        type: "POST",
                        dataType: "JSON",
                        data: $("#formAkses").serialize(),
                        success: function (response) {
                            $.unblockUI();
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200,
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
                                window.location.href = "/akses-projectuser";
                            });
                        },
                        error: function (error) {
                            $.unblockUI();
                            let errorMessage = error.statusText;
                            let errorTitle = `Error ${error.status}`;

                            // Coba parse JSON jika ada responseText
                            if (error.responseText) {
                                try {
                                    let response = JSON.parse(
                                        error.responseText,
                                    );
                                    // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                    if (response.errors) {
                                        // Gabungkan semua pesan error jadi satu string
                                        // errorMessage = Object.values(response.errors)
                                        //     .flat()
                                        //     .join('\n');
                                        var errors = $.parseJSON(
                                            error.responseText,
                                        );
                                        errorMessage = errors.errors;
                                    } else if (response.message) {
                                        // Jika ada message langsung
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Jika gagal parse, pakai responseText mentah
                                    // errorMessage = error.responseText;
                                    errorMessage = error.statusText;
                                }
                            }

                            Swal.fire({
                                title: errorTitle,
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
