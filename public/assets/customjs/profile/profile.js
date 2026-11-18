_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#formAccount :input[type="text"]').keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $('#formAccount :input[type="email"]').keyup(function () {
        this.value = this.value.toLowerCase();
    });

    const bsValidationForms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                _loading();
                $.ajax({
                    url: "/store-profile",
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
                            getData();
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
    });

    setTimeout(function () {
        getData();
        // $.unblockUI();
    }, 100);

    $(document).on("click", ".buttonAccount", function () {
        _loading();
        getData();
    });

    $(document).on("click", ".btnsScurity", function () {
        _loading();
        $("#formPass")[0].reset();
        $.unblockUI();
    });

    function getData() {
        $("#formAccount")[0].reset();

        $.getJSON("", function (response) {
            var src;
            var level = jQuery.isEmptyObject(response.roles)
                ? "-"
                : response.roles[0].name;
            $("#formAccount #id").val(response.id);
            $("#formAccount #name").val(response.name);
            $("#formAccount #email").val(response.email);
            $("#formAccount #level").val(level);

            if (!response.image) {
                src = "/assets/img/avatars/no-image.png";
            } else {
                src = "/storage/hris/profile/" + response.image;
            }

            $("#foto").attr("src", src);

        });
        $.unblockUI();
    }

    // CHANGE PASSWORD
    const formValidationExamples = document.getElementById("formPass");
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            oldPass: {
                validators: {
                    notEmpty: {
                        message: "Silahkan masukan password lama",
                    },
                    stringLength: {
                        min: 6,
                        message: "Password harus lebih dari 6 karakter",
                    },
                },
            },
            password: {
                validators: {
                    notEmpty: {
                        message: "Silahkan masukan password baru",
                    },
                    stringLength: {
                        min: 6,
                        message: "Password harus lebih dari 6 karakter",
                    },
                },
            },
            confirmed: {
                validators: {
                    notEmpty: {
                        message: "Konfirmasi password baru",
                    },
                    identical: {
                        compare: function () {
                            return formValidationExamples.querySelector(
                                '[name="password"]'
                            ).value;
                        },
                        message: "Konfirmasi password tidak sama",
                    },
                    stringLength: {
                        min: 6,
                        message: "Password harus lebih dari 6 karakter",
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                // Use this for enabling/changing valid/invalid class
                // eleInvalidClass: '',
                eleValidClass: "",
                rowSelector: function (field, ele) {
                    // field is the field name & ele is the field element
                    switch (field) {
                        case "oldPass":
                        case "password":
                        case "confirmed":
                            return ".col-md-12";
                        default:
                            return ".row";
                    }
                },
            }),

            // submitButton: new FormValidation.plugins.SubmitButton(),
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    }).on("core.form.validating", function (e) {
        // console.log('sedang di validasi');
    });

    // $(formValidationExamples).on('core.form.validating', function (e) {
    //     console.log('sedang di validasi');
    // });

    $(formValidationExamples).on("submit", function (e) {
        e.preventDefault();
        fv.validate().then(function (status) {
            _loading();
            if (status === "Valid")
                $.ajax({
                    url: "store-password",
                    type: "POST",
                    data: $("#formPass").serialize(),
                    success: function (response) {
                        $.unblockUI();
                        var timerInterval;
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            html: "Sistem akan otomatis keluar dalam <strong></strong> seconds.",
                            timer: 5000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            showClass: {
                                popup: `
                                  animate__animated
                                  animate__rubberBand
                                `,
                            },
                            hideClass: {
                                popup: `
                                  animate__animated
                                  animate__bounceOut
                                `,
                            },
                            customClass: {
                                confirmButton: "",
                            },
                            buttonsStyling: false,
                            willOpen: function () {
                                Swal.showLoading();
                                timerInterval = setInterval(function () {
                                    Swal.getHtmlContainer().querySelector(
                                        "strong"
                                    ).textContent = Swal.getTimerLeft();
                                }, 100);
                            },
                            willClose: function () {
                                clearInterval(timerInterval);
                            },
                        }).then(function (result) {
                            document.getElementById("logout-form").submit();
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
                            allowOutsideClick: false,
                            showClass: {
                                popup: "animate__animated animate__shakeX",
                            },
                            hideClass: {
                                popup: `
                                  animate__animated
                                  animate__bounceOut
                                  animate__faster
                                `,
                            },
                            customClass: {
                                confirmButton:
                                    "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    },
                });
            $.unblockUI();
        });
    });

    $("#upload_foto").change(function () {
        const formData = new FormData(this);
        _loading();
        formData.append("foto", $("input[type=file]")[0].files[0]);
        $.ajax({
            url: "store-image",
            type: "POST",
            dataType: "JSON",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,

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
