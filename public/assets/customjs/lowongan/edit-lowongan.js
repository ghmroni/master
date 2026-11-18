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
            var detail = res.detail;

            $("#judul").val(detail.judul);

            $("#projectid").empty();
            $("#projectid").append('<option value="">pilih</option>');
            $.each(res.projects, function (id, project) {
                $("#projectid").append(
                    '<option value="' +
                    project.id +
                    '">' +
                    project.name +
                    "</option>",
                );
                $("#projectid option[value=" + detail.project_id + "]").prop(
                    "selected",
                    true,
                );
            });

            $("#penempatan").val(detail.penempatan);
            $("#pekerjaandilamar").val(detail.pekerjaan_dilamar);
            $("#pengalaman").val(detail.pengalaman);
            $("#kualifikasi").val(detail.kualifikasi);

            // Saat modal edit ditampilkan
            startDate.setDate(res.start, true); // isi tanggal awal
            startDate.set("minDate", res.start);
            endDate.setDate(res.end, true); // isi tanggal akhir
            // Atur minDate endDate sesuai res.start
            endDate.set("minDate", res.start);
            endDate.set("maxDate", null); // optional reset batas atas
            $.unblockUI();
        });
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

    // Tampilkan dari id deskripsi yang hidden ke quill deskripsi
    var deskripsiValue = document.getElementById("deskripsi").value;
    if (deskripsiValue) {
        deskripsi.root.innerHTML = deskripsiValue;
    }

    const persyaratan = new Quill("#persyaratanQuill", {
        bounds: "#persyaratanQuill",
        placeholder: "Masukan persyaratan...",
        modules: {
            formula: true,
            toolbar: fullToolbar,
        },
        theme: "snow",
    });

    var persyaratanValue = document.getElementById("persyaratan").value;
    if (persyaratanValue) {
        persyaratan.root.innerHTML = persyaratanValue;
    }

    $("#dari").change(function () {
        $("#sampai").removeAttr("disabled");
        $("#sampai").val("");
    });

    var endDate = $("#sampai").flatpickr({
        dateFormat: "Y-m-d",
    });

    var startDate = $("#dari").flatpickr({
        dateFormat: "Y-m-d",
        onChange: function (selectedDates) {
            var selectedDate = selectedDates[0]; // tanggal dari dipilih

            endDate.set("minDate", selectedDate);
            endDate.set("maxDate", null);
        },
    });

    $("#projectid").select2({
        placeholder: "pilih project",
        allowClear: true,
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

            // sinkronisasi ke select2
            $(form)
                .find("select")
                .each(function () {
                    if (!this.checkValidity()) {
                        $(this)
                            .next(".select2")
                            .find(".select2-selection")
                            .addClass("is-invalid");
                    } else {
                        $(this)
                            .next(".select2")
                            .find(".select2-selection")
                            .removeClass("is-invalid");
                    }
                });

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
                                    window.location.href = "/lowongan";
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
