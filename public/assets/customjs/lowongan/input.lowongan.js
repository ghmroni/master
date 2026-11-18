_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // $('#formLowongan :input[type="text"]').keyup(function () {
    //     this.value = this.value.toUpperCase();
    // });

    setTimeout(function () {
        $.getJSON("", function (res) {
            $("#formLowongan #projectid").empty();
            $("#formLowongan #projectid").append(
                '<option value="">-pilih project-</option>',
            );
            $.each(res, function (index, data) {
                $("#formLowongan #projectid").append(
                    '<option value="' +
                    data.id +
                    '">' +
                    data.name +
                    "</option>",
                );
            });
        });
        $.unblockUI();
    }, 100);

    const tagifyBasicEl = document.querySelector("#kualifikasi");
    const TagifyBasic = new Tagify(tagifyBasicEl);

    const fullToolbar = [
        [
            {
                font: [],
            },
            {
                size: [],
            },
        ],
        ["bold", "italic", "underline", "strike"],
        [
            {
                list: "ordered",
            },
            {
                list: "bullet",
            },
            {
                indent: "-1",
            },
            {
                indent: "+1",
            },
        ],
    ];

    const deskripsi = new Quill("#deskripsiQuill", {
        bounds: "#deskripsiQuill",
        placeholder: "Masukan deskripsi pekerjaan...",
        modules: {
            formula: true,
            toolbar: fullToolbar,
        },
        theme: "snow",
    });

    const persyaratan = new Quill("#persyaratanQuill", {
        bounds: "#persyaratanQuill",
        placeholder: "Masukan persyaratan...",
        modules: {
            formula: true,
            toolbar: fullToolbar,
        },
        theme: "snow",
    });

    $("#dari").flatpickr({
        minDate: "today",
        onChange: function (selectedDates) {
            var selectedDate = selectedDates[0]; // Ambil tanggal yang dipilih
            endDate.set("minDate", selectedDate); // Set minDate untuk input kedua
        },
    });

    var endDate = $("#sampai").flatpickr();

    $("#dari").change(function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
    });

    const bsValidationForms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener("submit", function (event) {
            // Persyaratan
            document.getElementById("persyaratan").value =
                persyaratan.root.innerHTML.trim();
            if ($("#persyaratan").val() === "<p><br></p>") {
                // Quill menyimpan teks kosong sebagai ini
                $("#persyaratan").val("");
                $(".persyaratan").show();
                event.preventDefault();
            } else {
                $(".persyaratan").hide();
            }
            // Deskripsi
            document.getElementById("deskripsi").value =
                deskripsi.root.innerHTML.trim();
            if ($("#deskripsi").val() === "<p><br></p>") {
                // Quill menyimpan teks kosong sebagai ini
                $("#deskripsi").val("");
                $(".deskripsi").show();
                event.preventDefault();
            } else {
                $(".deskripsi").hide();
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add("was-validated"); // biar Bootstrap kasih feedback
                return;
            } else {
                event.preventDefault();
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Simpan Lowongan !",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Simpan",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton:
                            "btn btn-success me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        _loading();
                        $.ajax({
                            url: "/lowongan",
                            type: "POST",
                            dataType: "JSON",
                            data: $(form).serialize(),
                            success: function (response) {
                                $.unblockUI();
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    showConfirmButton: false,
                                    timer: 1500,
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
                                    window.location.reload();
                                });
                            },
                            error: function (error) {
                                $.unblockUI();

                                var errorMessage = error.statusText;
                                if (error.status == 405) {
                                    // Not Acceptable
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
                                    errorMessage = errors.errors;
                                }

                                Swal.fire({
                                    title: $.parseJSON(error.status),
                                    text: errorMessage,
                                    icon: "error",
                                    showClass: {
                                        popup: "animate__animated animate__shakeX",
                                    },
                                    customClass: {
                                        confirmButton:
                                            "btn btn-primary waves-effect waves-light",
                                    },
                                    buttonsStyling: false,
                                });
                            },
                        });
                        event.preventDefault();
                    }
                    form.classList.add("was-validated");
                });
            }
        });
    });
});
