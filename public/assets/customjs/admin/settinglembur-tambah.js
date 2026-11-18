_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    $('#um, #ut, #uml, #utl, #nilaibiasa, #nilailibur').each(function () {
        new Cleave(this, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: '.',
            numeralDecimalMark: ','
        });
    });

    const formValidationExamples = document.getElementById("formLembur");

    const commonValidator = {
        validators: {
            callback: {
                message: "Inputan tidak valid",
                callback: function (params) {
                    const value = params.value;
                    const typeValue = formValidationExamples.querySelector('[name="typelembur"]:checked')?.value;

                    if (typeValue === "NORMATIVE") {
                        if (value === "") {
                            return { valid: false, message: "Wajib diisi" };
                        }
                    }
                    return true;
                },
            },
            regexp: {
                regexp: /^[0-9.]+$/,
                message: "Inputan hanya boleh berisi angka dan titik (.)",
            },
            stringLength: {
                max: 3,
                message: 'Maksimal 3 digits'
            },
        },
    };


    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            project: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih ulang keperluan project",
                    },
                },
            },
            um: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Inputan hanya boleh angka",
                    },
                    stringLength: {
                        max: 10,
                        message: 'Maksimal 10 digits'
                    },
                },
            },
            ut: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Inputan hanya boleh angka",
                    },
                    stringLength: {
                        max: 10,
                        message: 'Maksimal 10 digits'
                    },
                },
            },
            uml: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Inputan hanya boleh angka",
                    },
                    stringLength: {
                        max: 10,
                        message: 'Maksimal 10 digits'
                    },
                },
            },
            utl: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9.]+$/,
                        message: "Inputan hanya boleh angka",
                    },
                    stringLength: {
                        max: 10,
                        message: 'Maksimal 10 digits'
                    },
                },
            },
            harikerja: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh angka",
                    },
                    stringLength: {
                        max: 1,
                        message: 'Maksimal 1 digits'
                    },
                },
            },
            dari: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            sampai: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            bulan: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            typelembur: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih type lembur",
                    },
                },
            },
            nilaibiasa: {
                validators: {
                    callback: {
                        message: "Inputan tidak valid",
                        callback: function (params) {
                            const value = params.value;
                            const typeValue = formValidationExamples.querySelector('[name="typelembur"]:checked')?.value;

                            // 1. Jika FLAT: Wajib isi, harus angka, maksimal 2 digit
                            if (typeValue === "FLAT") {
                                if (value === "") {
                                    return { valid: false, message: "Inputan tidak boleh kosong" };
                                }
                                const isNumeric = /^[0-9.]+$/.test(value);
                                if (!isNumeric) {
                                    return { valid: false, message: "Hanya boleh angka" };
                                }
                                if (value.length > 10) {
                                    return { valid: false, message: "Maksimal 10 digit" };
                                }
                                return true;
                            }
                            return true;
                        },
                    },
                },
            },
            nilailibur: {
                validators: {
                    callback: {
                        message: "Inputan tidak valid",
                        callback: function (params) {
                            const value = params.value;
                            const typeValue = formValidationExamples.querySelector('[name="typelembur"]:checked')?.value;

                            // 1. Jika FLAT: Wajib isi, harus angka, maksimal 2 digit
                            if (typeValue === "FLAT") {
                                if (value === "") {
                                    return { valid: false, message: "Inputan tidak boleh kosong" };
                                }
                                const isNumeric = /^[0-9.]+$/.test(value);
                                if (!isNumeric) {
                                    return { valid: false, message: "Hanya boleh angka" };
                                }
                                if (value.length > 10) {
                                    return { valid: false, message: "Maksimal 10 digit" };
                                }
                                return true;
                            }
                            return true;
                        },
                    },
                },
            },
            typenilai: {
                validators: {
                    callback: {
                        message: "Inputan tidak valid",
                        callback: function (params) {
                            const value = params.value;
                            const typeValue = formValidationExamples.querySelector('[name="typelembur"]:checked')?.value;

                            // 1. Jika FLAT: Wajib isi, harus angka, maksimal 2 digit
                            if (typeValue === "FLAT") {
                                if (value === "") {
                                    return { valid: false, message: "Wajib diisi" };
                                }
                            }
                            return true;
                        },
                    },
                },
            },
            b1: commonValidator,
            b2: commonValidator,
            nb1: commonValidator,
            nb2: commonValidator,
            l2: commonValidator,
            l3: commonValidator,
            l4: commonValidator,
            nl2: commonValidator,
            nl3: commonValidator,
            nl4: commonValidator,
            nln2: commonValidator,
            nln3: commonValidator,
            nln4: commonValidator,
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
                        default:
                            return ".row, .input-group-text";
                    }
                },
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            // Submit the form when all fields are valid
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
        init: (instance) => {
            instance.on("plugins.message.placed", function (e) {
                //* Move the error message out of the `input-group` element
                if (e.element.parentElement.classList.contains("input-group")) {
                    // `e.field`: The field name
                    // `e.messageElement`: The message element
                    // `e.element`: The field element
                    e.element.parentElement.insertAdjacentElement(
                        "afterend",
                        e.messageElement,
                    );
                }
                //* Move the error message out of the `row` element for custom-options
                if (
                    e.element.parentElement.parentElement.classList.contains(
                        "custom-option",
                    )
                ) {
                    e.element
                        .closest(".row")
                        .insertAdjacentElement("afterend", e.messageElement);
                }
            });

        },
    }).on("core.form.valid", function () {
        // Form valid → submit via jQuery/AJAX
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Setting Lembur",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/settinglembur",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formLembur").serialize(),
                    success: function (response) {
                        $('#myModal').modal('hide')
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
                            window.location.href = '/settinglembur';
                        });
                    },
                    error: function (error) {
                        $.unblockUI();
                        let errorMessage = error.statusText;
                        let errorTitle = `Error ${error.status}`;

                        // Coba parse JSON jika ada responseText
                        if (error.responseText) {
                            try {
                                let response = JSON.parse(error.responseText);
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {
                                    // Gabungkan semua pesan error jadi satu string
                                    // errorMessage = Object.values(response.errors)
                                    //     .flat()
                                    //     .join('\n');
                                    var errors = $.parseJSON(error.responseText);
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

    $('#harikerja').on('input', function () {
        var nilai = $(this).val();
        $('#l2').val(nilai <= 5 ? '7' : '8');
        // fv.revalidateField('l2');
    });

    const radioElements = formValidationExamples.querySelectorAll('[name="typelembur"]');
    const flatContainer = document.querySelector('#flat');
    const normativeContainer = document.querySelector('#normative');
    const fieldsType = [
        'nilaibiasa', 'nilailibur', 'typenilai', 'b1', 'b2', 'nb1', 'nb2',
        'l2', 'l3', 'l4', 'nl2', 'nl3', 'nl4',
        'nln2', 'nln3', 'nln4'
    ];

    radioElements.forEach((radio) => {
        radio.addEventListener('change', function () {
            _loading();

            const selectedType = this.value; // Ambil nilai radio yang diklik
            $('#nilaibiasa').val('');
            $('#nilailibur').val('');
            $('input[name="typenilai"]').prop('checked', false);
            // 1. Logika Show/Hide
            if (selectedType === 'FLAT') {
                flatContainer.style.display = 'block';
                normativeContainer.style.display = 'none';
            } else if (selectedType === 'NORMATIVE') {
                flatContainer.style.display = 'none';
                normativeContainer.style.display = 'block';
            }
            // Loop untuk revalidasi setiap field di dalam list
            fieldsType.forEach(field => {
                fv.revalidateField(field);
            });
            $.unblockUI();
        });
    });

    // TAMPILKAN MODAL PERUBAHAN PROJECT
    const btnChange = $(".changeProject");

    var theme = $("html").hasClass("light-style")
        ? "default"
        : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", "#changeLokasi", function () {
        _loading();
        basicTree.jstree({
            core: {
                data: {
                    url: "/settinggaji/lokasi",
                    data: function (node) {
                        return { id: node.id };
                    },
                    dataType: "json",
                },
                check_callback: true,
                themes: {
                    responsive: false,
                    name: theme,
                },
            },
            // plugins: ["state", "contextmenu", "wholerow"],
            plugins: ["search", "types", "state"],
            types: {
                default: { icon: "far fa-folder-open text-warning" },
            },
        });
        // hilangkan semua selected node
        basicTree.jstree("deselect_all", true);
        $("#modalLokasi").modal("show");
        $.unblockUI();
    });

    var to = false;
    $("#cari").keyup(function () {
        if (to) {
            clearTimeout(to);
        }
        to = setTimeout(function () {
            var v = $("#cari").val();
            basicTree.jstree(true).search(v);
        }, 250);
    });


    basicTree.on("activate_node.jstree", function (e, data) {
        btnChange.removeAttr("disabled");

        // Clear Data Tombol Edit and Set New Data Tombol Edit
        btnChange.removeData("id");
        btnChange.data("id", data.node.id);
    });

    // PERUBAHAN PROJECT
    $(document).on("click", ".changeProject", function () {
        var id = $(this).data("id") ?? $("#project").val();

        if (id) {
            _loading();

            $("#formLembur #project").val(id);

            $.getJSON("/lokasi-project?lokasi=" + id, function (lok) {
                var lokasi = [];
                var dtId = [];
                var color = [
                    "bg-success",
                    "bg-warning",
                    "bg-primary",
                    "bg-info",
                    "bg-secondary",
                    "bg-dark",
                ];
                $.each(lok, function (idx, data) {
                    dtId.push(data.id);
                    lokasi.push(
                        '<span class="badge ' +
                        color[idx] +
                        ' me-1 text-black">' +
                        data.name +
                        "</span>",
                    );
                });

                $("#formLembur #dtproject").val(dtId.join(","));
                $("#formLembur #lokasipekerjaan").html(lokasi.join(" "));
            });

            fv.revalidateField('project');

            $("#modalLokasi").modal("hide");
            $.unblockUI();
        } else {
            $.unblockUI();
            Swal.fire({
                title: "Oops!",
                text: "Silahkan pilih lokasi terlebih dahulu.",
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
        }
    });
    // END PERUBAHAN PROJECT
})