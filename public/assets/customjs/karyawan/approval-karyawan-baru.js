_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#wizard-validation-form :input[type="text"], textarea').keyup(
        function () {
            this.value = this.value.toUpperCase();
        },
    );

    $('#wizard-validation-form :input[type="email"]').keyup(function () {
        this.value = this.value.toLowerCase();
    });

    const selectPicker = $(".selectpicker"),
        tglptko = $("#tglptko").flatpickr(),
        unitkerja = document.getElementById("unitkerja"),
        tunjgrade = document.getElementById("tunjgrade"),
        tunjgolongan = document.getElementById("tunjgolongan"),
        btnChange = $(".changeProject");

    // pilih semua elemen dengan class tglkeluarga
    document.querySelectorAll(".tglkeluarga").forEach(function (el) {
        flatpickr(el, {
            dateFormat: "Y-m-d", // contoh format
            // opsi lain sesuai kebutuhan
        });
    });

    // LOAD STATUS KAWIN
    $.getJSON("/getStatusKawin", function (res) {
        $("#kawin").empty();
        $("#kawin").append('<option value="">pilih</option>');
        $.each(res, function (nama, id) {
            $("#kawin").append(
                '<option value="' + id + '">' + nama + "</option>",
            );
        });
    });
    // LOAD KONTRAK
    $.getJSON("/getKontrak", function (res) {
        $("#kontrak").empty();
        $("#kontrak").append('<option value="">pilih</option>');
        $.each(res, function (nama, id) {
            $("#kontrak").append(
                '<option value="' + id + '">' + nama + "</option>",
            );
        });
    });

    // LOAD JABATAN
    $.getJSON("/getJabatan", function (res) {
        $("#jabatan").empty();
        $("#jabatan").append('<option value="">pilih</option>');
        $.each(res, function (nama, id) {
            $("#jabatan").append(
                '<option value="' + id + '">' + nama + "</option>",
            );
        });
    });

    // LOAD BANK
    $.getJSON("/getBank", function (res) {
        $("#bankid").empty();
        $("#bankid").append('<option value="">pilih</option>');
        $.each(res, function (nama, id) {
            $("#bankid").append(
                '<option value="' + id + '">' + nama + "</option>",
            );
        });
    });

    setTimeout(function () {
        $.getJSON("", function (response) {
            var lokasi = [];
            var color = [
                "bg-success",
                "bg-warning",
                "bg-primary",
                "bg-info",
                "bg-secondary",
                "bg-dark",
            ];
            $.each(response.penempatan, function (id, nama) {
                lokasi.push(
                    '<span class="badge ' +
                    color[id] +
                    ' me-1 text-black">' +
                    nama +
                    "</span>",
                );
            });
            $("#project").val(response.penempatanId.split(",").pop());
            $("#dtproject").val(response.penempatanId);
            $("#lokasipekerjaan").html(lokasi.join(" "));
            // btnChange.trigger('click', { id: response.penempatanId.split(',').pop() });
            btnChange.click();

            $("#provinsi").val(response.data.provinsi_ktp).trigger("change");
            $.getJSON(
                "/getKabKot?provId=" + response.data.provinsi_ktp,
                function (res) {
                    $.each(res, function (nama, id) {
                        $("#kabkot").append(
                            '<option value="' + id + '">' + nama + "</option>",
                        );
                        $(
                            "#kabkot option[value=" +
                            response.data.kabkot_ktp +
                            "]",
                        ).prop("selected", true);
                    });
                },
            );

            $.getJSON(
                "/getKecamatan?kabId=" + response.data.kabkot_ktp,
                function (res) {
                    $.each(res, function (nama, id) {
                        $("#kecamatan").append(
                            '<option value="' + id + '">' + nama + "</option>",
                        );
                        $(
                            "#kecamatan option[value=" +
                            response.data.kecamatan_ktp +
                            " ]",
                        ).prop("selected", true);
                    });
                },
            );

            $.getJSON(
                "/getKelurahan?kecId=" + response.data.kecamatan_ktp,
                function (res) {
                    $.each(res, function (nama, id) {
                        $("#desa").append(
                            '<option value="' + id + '">' + nama + "</option>",
                        );
                        $(
                            "#desa option[value=" +
                            response.data.keluarahan_ktp +
                            " ]",
                        ).prop("selected", true);
                    });
                },
            );

            // SET STATUS KELUARGA
            $("#kelamin").val(response.data.jenis_kelamin).trigger("change");
            $("#kawin").val(response.data.status_kawin).trigger("change");
        });

        $.unblockUI();
    }, 300);

    // VALIDASI STEP
    // Wizard Validation
    // --------------------------------------------------------------------
    const wizardValidation = document.querySelector("#wizard-validation");
    if (typeof wizardValidation !== undefined && wizardValidation !== null) {
        // Wizard form
        const wizardValidationForm = wizardValidation.querySelector(
            "#wizard-validation-form",
        );
        // Wizard steps
        const wizardValidationFormStep1 = wizardValidationForm.querySelector(
            "#kontrak-details-validation",
        );
        const wizardValidationFormStep2 = wizardValidationForm.querySelector(
            "#personal-info-validation",
        );
        const wizardValidationFormStep3 = wizardValidationForm.querySelector(
            "#keluarga-links-validation",
        );
        const wizardValidationFormStep4 = wizardValidationForm.querySelector(
            "#pendidikan-links-validation",
        );
        const wizardValidationFormStep5 = wizardValidationForm.querySelector(
            "#bank-links-validation",
        );
        // Wizard next prev button
        const wizardValidationNext = [].slice.call(
            wizardValidationForm.querySelectorAll(".btn-next"),
        );
        const wizardValidationPrev = [].slice.call(
            wizardValidationForm.querySelectorAll(".btn-prev"),
        );

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
        });

        // ==================================================================================================================== KONTRAK
        const FormValidation1 = FormValidation.formValidation(
            wizardValidationFormStep1,
            {
                fields: {
                    kontrak: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    project: {
                        validators: {
                            notEmpty: {
                                message:
                                    "Silahkan pilih ulang keperluan project",
                            },
                        },
                    },
                    tglkontrak: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    jabatan: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    lokasikontrak: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    unitkerja: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const projectValue =
                                        wizardValidationFormStep1.querySelector(
                                            '[name="dtproject"]',
                                        ).value;
                                    const arr = projectValue.split(",");

                                    // Jika projectnya CGK
                                    // if (arr[0] === "149") {
                                    //     return input.value !== "";
                                    // }
                                    if (["149", "138"].includes(arr[0])) {
                                        return input.value.trim() !== "";
                                    }

                                    // Jika projectnya bukan GHM, maka unitkerja dianggap valid (boleh kosong)
                                    return true;
                                },
                            },
                        },
                    },
                    grade: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const projectValue =
                                        wizardValidationFormStep1.querySelector(
                                            '[name="dtproject"]',
                                        ).value;
                                    const arr = projectValue.split(",");

                                    // Jika projectnya CGK
                                    if (arr[0] === "149") {
                                        return input.value !== "";
                                    }

                                    // Jika projectnya bukan GHM, maka grade dianggap valid (boleh kosong)
                                    return true;
                                },
                            },
                            regexp: {
                                regexp: /^[A-Za-z+\-]+$/,
                                message: "Grade hanya boleh huruf, +, atau -",
                            },
                            stringLength: {
                                max: 2,
                                message: "Grade maksimal 2 karakter",
                            },
                        },
                    },
                    golongan: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const projectValue =
                                        wizardValidationFormStep1.querySelector(
                                            '[name="dtproject"]',
                                        ).value;
                                    const arr = projectValue.split(",");

                                    // Jika projectnya 1 / GHM
                                    if (arr[0] === "1") {
                                        return input.value !== "";
                                    }

                                    // Jika projectnya bukan GHM, maka grade dianggap valid (boleh kosong)
                                    return true;
                                },
                            },
                            regexp: {
                                regexp: /^[0-9]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                            stringLength: {
                                max: 1,
                                message: "Maksimal 1 digits",
                            },
                        },
                    },
                    tunjgolongan: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const projectValue =
                                        wizardValidationFormStep1.querySelector(
                                            '[name="dtproject"]',
                                        ).value;
                                    const arr = projectValue.split(",");

                                    // Jika projectnya 1 / GHM
                                    if (arr[0] === "1") {
                                        return input.value !== "";
                                    }

                                    // Jika projectnya bukan GHM, maka grade dianggap valid (boleh kosong)
                                    return true;
                                },
                            },
                        },
                    },
                    noptko: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const projectValue =
                                        wizardValidationFormStep1.querySelector(
                                            '[name="dtproject"]',
                                        ).value;
                                    const arr = projectValue.split(",");
                                    // Jika projectnya 14 / AUTO
                                    if (arr[0] === "14") {
                                        return input.value !== "";
                                    }
                                    // Jika projectnya bukan GHM, maka grade dianggap valid (boleh kosong)
                                    return true;
                                },
                            },
                        },
                    },
                    tglptko: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    ketptko: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
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
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const kontrakValue =
                                        wizardValidationFormStep1.querySelector(
                                            '[name="kontrak"]',
                                        ).value;
                                    // Jika projectnya 1 / GHM
                                    if (kontrakValue !== "1") {
                                        return input.value !== "";
                                    }

                                    // Jika projectnya bukan GHM, maka grade dianggap valid (boleh kosong)
                                    return true;
                                },
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
                        rowSelector: ".col-sm-6, .col-sm-12",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
                init: (instance) => {
                    instance.on("plugins.message.placed", function (e) {
                        //* Move the error message out of the `input-group` element
                        if (
                            e.element.parentElement.classList.contains(
                                "input-group",
                            )
                        ) {
                            e.element.parentElement.insertAdjacentElement(
                                "afterend",
                                e.messageElement,
                            );
                        }
                    });
                },
            },
        ).on("core.form.valid", function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });

        // GRADE DAN GOLONGAN
        var cleaveGrade = new Cleave(tunjgrade, {
            numeral: true,
            numeralThousandsGroupStyle: "thousand",
            numeralDecimalScale: 0,
        });

        var cleaveGolongan = new Cleave(tunjgolongan, {
            numeral: true,
            numeralThousandsGroupStyle: "thousand",
            numeralDecimalScale: 0,
        });

        $("#dari").change(function () {
            $("#sampai").removeAttr("disabled");
            $("#sampai").val("");
        });

        // Inisialisasi flatpickr untuk input "sampai"
        // Inisialisasi flatpickr untuk input "dari"
        var endDate = $("#sampai").flatpickr({
            dateFormat: "Y-m-d",
        });

        var startDate = $("#dari").flatpickr({
            dateFormat: "Y-m-d",
            onChange: function (selectedDates) {
                var selectedDate = selectedDates[0]; // tanggal dari dipilih
                var kontrak = $("#kontrak").val(); // ambil value selectbox kontrak

                if (kontrak == "2") {
                    // PKWT Maksimal 1thn
                    var maxDate = new Date(selectedDate);
                    maxDate.setFullYear(maxDate.getFullYear() + 1);

                    endDate.set("minDate", selectedDate);
                    endDate.set("maxDate", maxDate);
                } else if (kontrak == "3" || kontrak == "4" || kontrak == "5") {
                    // Maksimal 3 bulan dari tanggal "dari"
                    var maxDate = new Date(selectedDate);
                    maxDate.setMonth(maxDate.getMonth() + 3);

                    endDate.set("minDate", selectedDate);
                    endDate.set("maxDate", maxDate);
                } else {
                    endDate.set("minDate", selectedDate);
                    endDate.set("maxDate", null);
                }
            },
        });

        // Event ketika selectbox kontrak berubah

        $("#kontrak").change(function () {
            $("#sampai").removeAttr("disabled");
            $("#dari").val("");
            $("#sampai").val("");

            endDate = $("#sampai").flatpickr({
                dateFormat: "Y-m-d",
            });
        });

        // TAMPILKAN MODAL PERUBAHAN PROJECT
        var theme = $("html").hasClass("light-style")
            ? "default"
            : "default-dark",
            basicTree = $("#jstree-project");

        $(document).on("click", "#changeLokasi", function () {
            _loading();
            basicTree.jstree({
                core: {
                    data: {
                        // url: "/approvalIn/lokasi",
                        url: "/akses-project-tree",
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

        $("#modalLokasi").on("shown.bs.modal", function () {
            var tree = $("#jstree-project").jstree(true);
            // tutup semua node
            tree.close_all();
            // hilangkan semua selected node
            tree.deselect_all(true);
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

                $("#project").val(id);

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
                    $("#dtproject").val(dtId.join(","));
                    $("#lokasipekerjaan").html(lokasi.join(" "));

                    // const token = btoa(dtId.join(","));
                    const token = dtId.join(",");
                    var projectId = token.split(",");

                    // Cek Kontrak Induk
                    $.getJSON(
                        "/approvalIn/kontrakinduk/?token=" +
                        encodeURIComponent(btoa(token)),
                        function (res) {
                            $("#btnKontrak").prop("disabled", false);
                            if (res) {
                                $("#notifInduk").css("display", "none");
                            } else {
                                $("#notifInduk").css("display", "block");
                                $("#btnKontrak").prop("disabled", true);
                            }
                        },
                    );

                    if (projectId[0] != 1) {
                        // Cek Kontrak Uitkerja
                        $("#unitkerja").empty();
                        $("#unitkerja").append(
                            '<option value="">pilih</option>',
                        );
                        $.getJSON(
                            "/unitkerja-project/?token=" +
                            encodeURIComponent(btoa(token)),
                            function (res) {
                                $.each(res, function (index, data) {
                                    $("#unitkerja").append(
                                        '<option value="' +
                                        data.id +
                                        '">' +
                                        data.name +
                                        "</option>",
                                    );
                                });
                            },
                        );

                        $(
                            "#gapok, #tunjab, #tunjtetap, #tunjoprs, #tunjkom, #tunjmasakerja, #tunjtransport, #tunjmakan, #tunjketerampilan",
                        ).val("");
                        $.getJSON(
                            "/getSettingGaji-project?token=" +
                            encodeURIComponent(btoa(token)),
                            function (res) {
                                if (res.status === "success") {
                                    $("#notifSetting").css("display", "none");

                                    if (res.settinggaji !== undefined) {
                                        let field = $("#gapok");
                                        field.val(res.settinggaji.gapok);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }

                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjab");
                                        field.val(res.biaya.tunjab);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjtetap");
                                        field.val(res.biaya.tunjtetap);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjoprs");
                                        field.val(res.biaya.tunjoprs);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjkom");
                                        field.val(res.biaya.tunjkom);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjmasakerja");
                                        field.val(res.biaya.tunjmasakerja);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjketerampilan");
                                        field.val(res.biaya.tunjketerampilan);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjtransport");
                                        field.val(res.biaya.tunjtransport);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                    if (res.biaya !== undefined) {
                                        let field = $("#tunjmakan");
                                        field.val(res.biaya.tunjmakan);
                                        field[0].dispatchEvent(
                                            new Event("input", {
                                                bubbles: true,
                                            }),
                                        ); // Paksa Cleave format ulang
                                    }
                                }
                            },
                        ).fail(function (xhr, textStatus, errorThrown) {
                            // 1. Munculkan notifikasi merah
                            $("#notifSetting").css("display", "block");
                        });
                    } else {
                        //Cek Kontrak Uitkerja
                        $.getJSON("/getUnitkerja", function (res) {
                            $("#unitkerja").empty();
                            $("#unitkerja").append(
                                '<option value="">pilih</option>',
                            );
                            $.each(res, function (nama, id) {
                                $("#unitkerja").append(
                                    '<option value="' +
                                    id +
                                    '">' +
                                    nama +
                                    "</option>",
                                );
                            });
                        });
                    }

                    // Cek PTKO
                    $("#noptko").empty();
                    if (token[0] == 5) {
                        $.getJSON(
                            "/approvalIn/ptko/?token=" +
                            encodeURIComponent(btoa(id)),
                            function (response) {
                                $("#noptko").append(
                                    '<option value="">pilih</option>',
                                );
                                $.each(response, function (idX, data) {
                                    $("#noptko").append(
                                        '<option value="' +
                                        data.id +
                                        '">' +
                                        data.nomor +
                                        "</option>",
                                    );
                                });
                            },
                        );
                    }
                });

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

        // ==================================================================================================================== BIODATA
        const FormValidation2 = FormValidation.formValidation(
            wizardValidationFormStep2,
            {
                fields: {
                    nokk: {
                        validators: {
                            notEmpty: {
                                message: "Nomor KK masih kosong",
                            },
                            stringLength: {
                                min: 16,
                                max: 16,
                                message: "Nomor KK harus 16 Digit",
                            },
                        },
                    },
                    noktp: {
                        validators: {
                            notEmpty: {
                                message: "Nomor KTP masih kosong",
                            },
                            stringLength: {
                                min: 16,
                                max: 16,
                                message: "Nomor KTP harus 16 Digit",
                            },
                        },
                    },
                    namalengkap: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    kelamin: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    tmptlahir: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    tgllahir: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    alamatktp: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    alamatdomisili: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    provinsi: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    kabkot: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    kecamatan: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    desa: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    kodepos: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            stringLength: {
                                min: 5,
                                max: 5,
                                message: "Kodepos harus 5 Digit",
                            },
                        },
                    },
                    hp: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            stringLength: {
                                min: 10,
                                max: 12,
                                message: "Nomor Handphone harus 12 Digit",
                            },
                        },
                    },
                    email: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            emailAddress: {
                                message: "Format email tidak valid",
                            },
                        },
                    },
                    agama: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    baju: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    celana: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    tb: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    bb: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    sepatu: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
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
                        rowSelector: ".col-sm-6, .col-sm-4",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            },
        ).on("core.form.valid", function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });

        $("#tgllahir").flatpickr({
            maxDate: new Date().setFullYear(new Date().getFullYear() - 1),
        });

        // PROVINSI
        $.getJSON("/getProvinsi", function (res) {
            _loading();
            $("#provinsi").empty();
            $("#provinsi").append('<option value="">pilih</option>');
            $.each(res, function (nama, id) {
                $("#provinsi").append(
                    '<option value="' + id + '">' + nama + "</option>",
                );
            });
            $.unblockUI();
        });
        // KABKOT
        $(document).on("change", "#provinsi", function () {
            _loading();
            var idprov = $(this).val();
            if (idprov) {
                $("#kabkot").empty();
                $("#kecamatan").empty();
                $("#desa").empty();

                $("#kabkot").append('<option value="">pilih</option>');
                $.getJSON("/getKabKot?provId=" + idprov, function (res) {
                    $.each(res, function (nama, id) {
                        $("#kabkot").append(
                            '<option value="' + id + '">' + nama + "</option>",
                        );
                    });
                });
                $.unblockUI();
            } else {
                $("#kabkot").empty();
                $("#kecamatan").empty();
                $("#desa").empty();
                $.unblockUI();
            }
        });
        // KECAMATAN
        $(document).on("change", "#kabkot", function () {
            _loading();
            var idkab = $(this).val();
            if (idkab) {
                $("#kecamatan").empty();
                $("#desa").empty();

                $("#kecamatan").append('<option value="">pilih</option>');
                $.getJSON("/getKecamatan?kabId=" + idkab, function (res) {
                    $.each(res, function (nama, id) {
                        $("#kecamatan").append(
                            '<option value="' + id + '">' + nama + "</option>",
                        );
                    });
                });
                $.unblockUI();
            } else {
                $("#kecamatan").empty();
                $("#desa").empty();
                $.unblockUI();
            }
        });
        // KELURAHAN
        $(document).on("change", "#kecamatan", function () {
            _loading();
            var idkec = $(this).val();
            if (idkec) {
                $("#desa").empty();

                $("#desa").append('<option value="">pilih</option>');
                $.getJSON("/getKelurahan?kecId=" + idkec, function (res) {
                    $.each(res, function (nama, id) {
                        $("#desa").append(
                            '<option value="' + id + '">' + nama + "</option>",
                        );
                    });
                });
                $.unblockUI();
            } else {
                $("#desa").empty();
                $.unblockUI();
            }
        });

        // ==================================================================================================================== KELUARGA
        const FormValidation3 = FormValidation.formValidation(
            wizardValidationFormStep3,
            {
                fields: {
                    kawin: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    // SUIS

                    ktpsuis: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (
                                        ["2", "3", "4", "5"].includes(
                                            kawinValue,
                                        )
                                    ) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                            stringLength: {
                                min: 16,
                                max: 16,
                                message: "Nomor KTP harus 16 Digit",
                            },
                        },
                    },
                    namasuis: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (
                                        ["2", "3", "4", "5"].includes(
                                            kawinValue,
                                        )
                                    ) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    lahirsuis: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (
                                        ["2", "3", "4", "5"].includes(
                                            kawinValue,
                                        )
                                    ) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    tglsuis: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (
                                        ["2", "3", "4", "5"].includes(
                                            kawinValue,
                                        )
                                    ) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    kelaminsuis: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (
                                        ["2", "3", "4", "5"].includes(
                                            kawinValue,
                                        )
                                    ) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    // ANAK 1
                    ktpanak1: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["3", "4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                            stringLength: {
                                min: 16,
                                max: 16,
                                message: "Nomor KTP harus 16 Digit",
                            },
                        },
                    },
                    namaanak1: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["3", "4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    lahiranak1: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["3", "4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    tglanak1: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["3", "4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    kelaminanak1: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["3", "4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    // ANAK 2
                    ktpanak2: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                            stringLength: {
                                min: 16,
                                max: 16,
                                message: "Nomor KTP harus 16 Digit",
                            },
                        },
                    },
                    namaanak2: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    lahiranak2: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    tglanak2: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    kelaminanak2: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["4", "5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    // ANAK 3
                    ktpanak3: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                            stringLength: {
                                min: 16,
                                max: 16,
                                message: "Nomor KTP harus 16 Digit",
                            },
                        },
                    },
                    namaanak3: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    lahiranak3: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    tglanak3: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                    kelaminanak3: {
                        validators: {
                            callback: {
                                message: "Harus diisi",
                                callback: function (input) {
                                    const kawinValue =
                                        wizardValidationFormStep3.querySelector(
                                            '[name="kawin"]',
                                        ).value;
                                    if (["5"].includes(kawinValue)) {
                                        return input.value !== "";
                                    }
                                    return true;
                                },
                            },
                        },
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        // Use this for enabling/changing valid/invalid class
                        eleInvalidClass: "is-invalid",
                        eleValidClass: "",
                        rowSelector: ".col-sm-12, td",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            },
        ).on("core.form.valid", function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });

        // STATUS KELUARGA
        $(document).on("change", "#kelamin", function () {
            if ($(this).val() == "P") {
                $("#dtSuis #statussuis").val("SUAMI");
            } else {
                $("#dtSuis #statussuis").val("ISTRI");
            }
        });
        // STATUS PERKAWINAN
        $(document).on("change", "#kawin", function () {
            // Reset dulu semua row agar terlihat
            $("#tblKeluarga, #dtSuis, #dtAnak1, #dtAnak2, #dtAnak3").show();

            // Fungsi bantu untuk reset semua input di dalam row tertentu
            function resetRow(rowId) {
                // $('#' + rowId).find('input, select, textarea').each(function () {
                $("#" + rowId)
                    .find(
                        "#ktpkeluarga, #namakeluarga, #lahirkeluarga, #tglkeluarga, select",
                    )
                    .each(function () {
                        if ($(this).is("select")) {
                            $(this).prop("selectedIndex", 0); // reset ke option pertama
                        } else {
                            $(this).val(""); // kosongkan value
                        }
                    });
            }

            if ($(this).val() == 1) {
                $("#tblKeluarga, #dtSuis, #dtAnak1, #dtAnak2, #dtAnak3").hide();
                resetRow("dtSuis");
                resetRow("dtAnak1");
                resetRow("dtAnak2");
                resetRow("dtAnak3");
            } else if ($(this).val() == 2) {
                $("#dtAnak1, #dtAnak2, #dtAnak3").hide();
                resetRow("dtAnak1");
                resetRow("dtAnak2");
                resetRow("dtAnak3");
            } else if ($(this).val() == 3) {
                $("#dtAnak2, #dtAnak3").hide();
                resetRow("dtAnak2");
                resetRow("dtAnak3");
            } else if ($(this).val() == 4) {
                $("#dtAnak3").hide();
                resetRow("dtAnak3");
            }
        });

        // ==================================================================================================================== PENDIDIKAN
        const FormValidation4 = FormValidation.formValidation(
            wizardValidationFormStep4,
            {
                fields: {
                    pendidikan: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    namasekolah: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    jurusansekolah: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    tahunlulus: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            stringLength: {
                                min: 4,
                                max: 4,
                                message: "Inputan harus 4 Digit",
                            },
                        },
                    },
                    nilaisekolah: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            stringLength: {
                                min: 1,
                                max: 5,
                                message: "Inputan harus 5 Digit",
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
                        rowSelector: ".col-sm-6,.col-sm-3",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            },
        ).on("core.form.valid", function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });

        // ==================================================================================================================== BANK

        $("#percepatan").on("change", function () {
            if ($(this).is(":checked")) {
                // kosongkan input1, input2, dan selectbox
                $("#bankid").val("").trigger("change");
                $("#namabank").val("");
                $("#norek").val("");
                $("#bankid, #namabank, #norek").removeClass("is-invalid");
                $("#bankid, #namabank, #norek").attr("readonly", true);
            } else {
                $("#bankid, #namabank, #norek").removeAttr("readonly");
            }
        });

        $(
            "#gapok, #tunjab, #tunjtetap, #tunjoprs, #tunjkom, #tunjmasakerja, #tunjtransport, #tunjmakan, #tunjketerampilan",
        ).each(function () {
            let cleaveInstance = new Cleave(this, {
                numeral: true,
                numeralThousandsGroupStyle: "thousand",
                delimiter: ".",
                numeralDecimalMark: ",",
            });

            // Baris tambahan untuk menyimpan objeknya
            $(this).data("cleave", cleaveInstance);
        });

        const FormValidation5 = FormValidation.formValidation(
            wizardValidationFormStep5,
            {
                fields: {
                    bankid: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const percepatan =
                                        wizardValidationFormStep5.querySelector(
                                            '[name="percepatan"]',
                                        );
                                    // jika checkbox percepatan TIDAK dicentang, maka bankid harus diisi
                                    if (!percepatan.checked) {
                                        return input.value !== "";
                                    }

                                    // kondisi lain, tidak wajib
                                    return true;
                                },
                            },
                        },
                    },
                    namabank: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const percepatan =
                                        wizardValidationFormStep5.querySelector(
                                            '[name="percepatan"]',
                                        );
                                    // jika checkbox percepatan TIDAK dicentang, maka bankid harus diisi
                                    if (!percepatan.checked) {
                                        return input.value !== "";
                                    }

                                    // kondisi lain, tidak wajib
                                    return true;
                                },
                            },
                            regexp: {
                                regexp: /^[A-Za-z\s]+$/,
                                message: "Inputan hanya boleh huruf dan spasi",
                            },
                        },
                    },
                    norek: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const percepatan =
                                        wizardValidationFormStep5.querySelector(
                                            '[name="percepatan"]',
                                        );
                                    // jika checkbox percepatan TIDAK dicentang, maka bankid harus diisi
                                    if (!percepatan.checked) {
                                        return input.value !== "";
                                    }

                                    // kondisi lain, tidak wajib
                                    return true;
                                },
                            },
                            regexp: {
                                regexp: /^[0-9]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    gapok: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjab: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjtetap: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjoprs: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjkom: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjmasakerja: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjtransport: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjmakan: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
                            },
                        },
                    },
                    tunjketerampilan: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                            regexp: {
                                regexp: /^[0-9.]+$/,
                                message: "Inputan hanya boleh berisi angka",
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
                        rowSelector: ".col-sm-4, .col-sm-6, .col-sm-3, .mb-3",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            },
        ).on("core.form.valid", function () {
            // You can submit the form
            // wizardValidationForm.submit()
            // or send the form data to server via an Ajax request
            // To make the demo simple, I just placed an alert
            Swal.fire({
                title: "Konfirmasi!",
                text: "Proses Approval Karyawan",
                icon: "question",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Proses",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton:
                        "btn btn-success me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    const form = document.getElementById(
                        "wizard-validation-form",
                    );
                    const formData = new FormData(form);
                    // const formData = new FormData(this);
                    _loading();
                    $.ajax({
                        url: "/approvalIn",
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
                                window.history.back();
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

        const ruleNamaTunjangan = {
            validators: {
                callback: {
                    message:
                        "Nama tunjangan wajib diisi jika nilai tunjangan diisi",
                    callback: function (input) {
                        const row = input.element.closest(".row");
                        const nilaiInput =
                            row.querySelector(".tunjangan-nilai");
                        if (nilaiInput && nilaiInput.value.trim() !== "") {
                            return input.value.trim() !== "";
                        }
                        return true;
                    },
                },
            },
        };

        const ruleNilaiTunjangan = {
            validators: {
                callback: {
                    message:
                        "Nilai tunjangan wajib diisi jika nama tunjangan diisi",
                    callback: function (input) {
                        const row = input.element.closest(".row");
                        const namaInput = row.querySelector(".tunjangan-nama");
                        if (namaInput && namaInput.value.trim() !== "") {
                            return input.value.trim() !== "";
                        }
                        return true;
                    },
                },
            },
        };

        wizardValidationFormStep5.addEventListener("input", function (e) {
            if (
                e.target.classList.contains("tunjangan-nama") ||
                e.target.classList.contains("tunjangan-nilai")
            ) {
                // Cari nama dinamis yang diberikan oleh repeater pada input yang sedang aktif
                const row = e.target.closest(".row");
                const namaField = row.querySelector(".tunjangan-nama").name;
                const nilaiField = row.querySelector(".tunjangan-nilai").name;

                FormValidation5.revalidateField(namaField);
                FormValidation5.revalidateField(nilaiField);
            }
        });

        var row = 2;
        var col = 1;

        $(".form-repeater").repeater({
            show: function () {
                var fromControl = $(this).find(".form-control, .form-select");
                var formLabel = $(this).find(".form-label");

                fromControl.each(function (i) {
                    var id = "form-repeater-" + row + "-" + col;
                    $(fromControl[i]).attr("id", id);
                    $(formLabel[i]).attr("for", id);
                    col++;
                });
                row++;

                $(this).slideDown();

                // --- 1. PROSES VALIDASI DINAMIS ---
                const namaInputBaru = $(this)
                    .find(".tunjangan-nama")
                    .attr("name");
                const nilaiInputBaru = $(this)
                    .find(".tunjangan-nilai")
                    .attr("name");

                if (namaInputBaru && nilaiInputBaru) {
                    FormValidation5.addField(namaInputBaru, ruleNamaTunjangan);
                    FormValidation5.addField(
                        nilaiInputBaru,
                        ruleNilaiTunjangan,
                    );
                }

                // --- 2. PROSES CLEAVE.JS UNTUK BARIS BARU (SOLUSI ANDA) ---
                // Cari elemen ber-class .input-rupiah di dalam baris yang BARU dibuat ini saja
                $(this)
                    .find(".input-rupiah")
                    .each(function () {
                        let cleaveInstance = new Cleave(this, {
                            numeral: true,
                            numeralThousandsGroupStyle: "thousand",
                            delimiter: ".",
                            numeralDecimalMark: ",",
                        });
                        $(this).data("cleave", cleaveInstance);
                    });
            },
            hide: function (e) {
                if (confirm("Are you sure you want to delete this element?")) {
                    const namaInputLama = $(this)
                        .find(".tunjangan-nama")
                        .attr("name");
                    const nilaiInputLama = $(this)
                        .find(".tunjangan-nilai")
                        .attr("name");

                    if (namaInputLama && nilaiInputLama) {
                        FormValidation5.removeField(namaInputLama);
                        FormValidation5.removeField(nilaiInputLama);
                    }
                    $(this).slideUp(e);
                }
            },
            ready: function (setIndexes) {
                const namaAwal = $(".tunjangan-nama").attr("name");
                const nilaiAwal = $(".tunjangan-nilai").attr("name");

                if (namaAwal && nilaiAwal) {
                    FormValidation5.addField(namaAwal, ruleNamaTunjangan);
                    FormValidation5.addField(nilaiAwal, ruleNilaiTunjangan);
                }

                // Jalankan Cleave.js untuk baris pertama saat halaman selesai dimuat
                $(".tunjangan-nilai").addClass("input-rupiah"); // memastikan baris pertama punya class-nya
                $(".input-rupiah").each(function () {
                    let cleaveInstance = new Cleave(this, {
                        numeral: true,
                        numeralThousandsGroupStyle: "thousand",
                        delimiter: ".",
                        numeralDecimalMark: ",",
                    });
                    $(this).data("cleave", cleaveInstance);
                });
            },
        });

        wizardValidationNext.forEach((item) => {
            item.addEventListener("click", (event) => {
                // When click the Next button, we will validate the current step
                switch (validationStepper._currentIndex) {
                    case 0:
                        FormValidation1.validate();
                        break;

                    case 1:
                        FormValidation2.validate();
                        break;

                    case 2:
                        FormValidation3.validate();
                        break;

                    case 3:
                        FormValidation4.validate();
                        break;

                    case 4:
                        FormValidation5.validate();
                        break;

                    default:
                        break;
                }
            });
        });

        wizardValidationPrev.forEach((item) => {
            item.addEventListener("click", (event) => {
                switch (validationStepper._currentIndex) {
                    case 4:
                        validationStepper.previous();
                        break;

                    case 3:
                        validationStepper.previous();
                        break;

                    case 2:
                        validationStepper.previous();
                        break;

                    case 1:
                        validationStepper.previous();
                        break;

                    case 0:

                    default:
                        break;
                }
            });
        });
    }

    $(document).on("click", ".lihatLampiran", function () {
        var lampiran = $(this).data("lampiran");
        _loading();
        var url = "/lampiran/view/" + lampiran;
        // buka window baru dengan ukuran tertentu
        window.open(
            url,
            "PaklaringWindow", // nama window
            "width=800,height=600,scrollbars=yes,resizable=yes",
        );
        $.unblockUI();
    });
});
