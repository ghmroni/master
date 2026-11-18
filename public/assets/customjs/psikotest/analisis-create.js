_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    const fullToolbar = [
        [{ font: [] }, { size: [] }],
        ["bold", "italic", "underline", "strike"],
        [{ color: [] }, { background: [] }],
        [{ script: "sub" }, { script: "super" }],
        [{ header: 1 }, { header: 2 }, "blockquote", "code-block"],
        [
            { list: "ordered" },
            { list: "bullet" },
            { indent: "-1" },
            { indent: "+1" },
        ],
        [
            { align: "" },
            { align: "center" },
            { align: "right" },
            { align: "justify" },
        ],
        // ["link", "image", "video", "formula"],
    ];

    const discQuill = new Quill("#discQuill", {
        bounds: "#discQuill",
        placeholder: "Hasil Analisis Psikotest DISC",
        modules: {
            formula: true,
            toolbar: fullToolbar,
        },
        theme: "snow",
    });

    const papiQuill = new Quill("#papiQuill", {
        bounds: "#papiQuill",
        placeholder: "Hasil Analisis Psikotest PAPI",
        modules: {
            formula: true,
            toolbar: fullToolbar,
        },
        theme: "snow",
    });

    setTimeout(function () {
        $.getJSON("", function (res) {
            var dataDisc = res.disc;
            var disc =
                typeof dataDisc === "string" ? JSON.parse(dataDisc) : dataDisc;

            let profileCode = disc?.core_private?.profile_code
                ? `<strong>${disc.core_private.profile_code}</strong>`
                : "";

            // let kepribadian = disc?.core_private?.kepribadian
            //     ? `<p>${disc.core_private.kepribadian}</p>`
            //     : "";

            // let pekerjaan = disc?.core_private?.pekerjaan
            //     ? `<p><u>${disc.core_private.pekerjaan}</u></p>`
            //     : "";

            let kepribadian = res.kepribadian || '';
            let pekerjaan = res.job || '';

            discQuill.clipboard.dangerouslyPasteHTML(`
                <p>Berdasarkan hasil test DISC hasilnya bahwa kepribadian Karakter DISC <strong>${profileCode}</strong></p>
                 <p>${kepribadian}</p>
                 <p><u>Rekomendasi pekerjaan yang cocok dengan kepribadian <strong>${profileCode}</strong> seperti :</u></p>
                 <p><u>${pekerjaan}</u></p>
             `);
            discQuill.setSelection(0, discQuill.getLength());
            discQuill.format("align", "justify");

            var dataPapi = res.papi;
            let htmlContent = "<ul>";
            // looping semua bidang
            $.each(dataPapi, function (bidang, analisisString) {
                htmlContent += `<li class="mb-2"><strong>ASPEK ${bidang}</strong> : ${analisisString}</li>`;
            });

            htmlContent += "</ul>";

            // paste ke Quill
            papiQuill.clipboard.dangerouslyPasteHTML(htmlContent);
            papiQuill.setSelection(0, papiQuill.getLength());
            papiQuill.format("align", "justify");
        });
        $.unblockUI();
    }, 300);

    const bsValidationForms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener("submit", function (event) {
            document.getElementById("disc").value =
                discQuill.root.innerHTML.trim();
            let invalid = false;
            if ($("#disc").val() === "<p><br></p>") {
                // Quill menyimpan teks kosong sebagai ini
                $("#disc").val("");
                $(".disc").show();
                invalid = true;
            } else {
                $(".disc").hide();
            }

            document.getElementById("papi").value =
                papiQuill.root.innerHTML.trim();
            if ($("#papi").val() === "<p><br></p>") {
                // Quill menyimpan teks kosong sebagai ini
                $("#papi").val("");
                $(".papi").show();
                invalid = true;
            } else {
                $(".papi").hide();
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
                    text: "Simpan Analisis !",
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
                            url: "/create-analisis",
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
                                    window.location.href = '/data-psikotest';
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
