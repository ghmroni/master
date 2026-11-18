_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $('#formSetting')[0].reset();
        $.getJSON('/settinggaji/getKontrakinduk', function (res) {
            $('#kontrakinduk_id').empty();
            $('#kontrakinduk_id').append('<option value="">-pilih-</option>');
            $.each(res, function (Id, data) {
                var status_label = (data.deleted_at == null) ? ' [ ✔️ ]' : ' [ ❌ ]';
                $('#kontrakinduk_id').append('<option value="' + data.id + '">' + data.nomor + status_label + '</option>');
            });
        })
        $.unblockUI();
    }, 100);

    $(document).on('change', '#kontrakinduk_id', function () {
        _loading();
        var idkon = $('#kontrakinduk_id').val();
        $('#settingid').empty();
        $('#settingid').append('<option value="">-pilih-</option>');
        if (idkon) {
            $.getJSON('/settinggaji/setting-kontrakinduk?idkon=' + idkon, function (res) {
                $.each(res, function (Id, data) {
                    var lokasi = [];
                    var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
                    $.each(data.lokasi_project, function (idp, proj) {
                        lokasi.push('<span class="badge ' + color[idp] + ' me-1 text-black mt-1 mb-1">' + proj + '</span>');
                    })

                    $('#settingid').append('<option value="' + data.id + '">' + lokasi.join(' | ') + '</option>');
                });
            })
            $.unblockUI();
        } else {
            $('#settingid').empty();
            $.unblockUI();
        }
    })

    $(document).on('change', '#settingid', function () {
        _loading();
        var idsetting = $('#settingid').val();
        if (idsetting) {
            $.getJSON('/settinggaji/getHarga?idsetting=' + idsetting, function (res) {
                $.each(res, function (Id, data) {
                    $('#project').val(data.project_id);
                    $('#dtproject').val(data.project_detail);
                    if (data.biaya && data.biaya.length > 0) {
                        $('#gapok').val(data.biaya[0].harga);
                        if ($('#gapok').length) { new Cleave('#gapok', { numeral: true, thousandsGroupStyle: 'thousand', delimiter: '.', numeralDecimalMark: ',' }); }
                    }
                });
            })
            $.unblockUI();
        }
    })

    $('#gapok, #makan, #transport, #natura').each(function () {
        new Cleave(this, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: '.',
            numeralDecimalMark: ','
        });
    });

    const cleavePotongan = new Cleave('#nilaipotongan', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        delimiter: '.',
        numeralDecimalMark: ','
    });

    const formValidationExamples = document.getElementById("formSetting");
    const fv = FormValidation.formValidation(formValidationExamples, {
        fields: {
            project: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih ulang keperluan project",
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
                        message: "Inputan hanya boleh angka",
                    },
                    stringLength: {
                        max: 10,
                        message: 'Maksimal 10 digits'
                    },
                },
            },
            makan: {
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
            transport: {
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
            natura: {
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
                        max: 2,
                        message: 'Maksimal 2 digits'
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
            typepotongan: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih type potongan kehadiran",
                    },
                },
            },
            perhitungan: {
                validators: {
                    callback: {
                        message: "Inputan tidak valid",
                        callback: function (params) {
                            const value = params.value;
                            const typeValue = formValidationExamples.querySelector('[name="typepotongan"]:checked')?.value;

                            // 1. Jika STANDAR: Tidak wajib (selalu valid)
                            if (typeValue === "STANDAR") {
                                return true;
                            }

                            // 2. Jika FLAT: Wajib isi, harus angka, maksimal 2 digit
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

                            // 3. Jika CUSTOM (atau selain di atas): Wajib isi saja
                            if (typeValue === "CUSTOM") {
                                // Tagify kalau kosong isinya string kosong "" atau array kosong dalam string "[]"
                                if (!value || value === "[]" || value === "") {
                                    return { valid: false, message: "Rumus custom tidak boleh kosong" };
                                }
                                return true;
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
                // eleInvalidClass: '',
                eleValidClass: "",
                rowSelector: function (field, ele) {
                    // field is the field name & ele is the field element
                    switch (field) {
                        default:
                            return ".row";
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
            text: "Simpan Setting Gaji",
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
                    url: "/settinggaji",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formSetting").serialize(),
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
                            window.location.href = '/settinggaji';
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

    // Saat Gaji Pokok diketik
    $('#gapok').on('input', function () {
        hitungOtomatis();
    });

    // Saat Hari Kerja diubah
    $('#harikerja').on('change input', function () {
        hitungOtomatis();
    });

    // Agar pesan error hilang ketika radio button yang dipilih standar
    let cleaveInstance = null;
    const radioElements = formValidationExamples.querySelectorAll('[name="typepotongan"]');
    let tagifyInstance = null; // Tambahkan ini
    const whitelist = ['gapok', 'uang_makan', 'uang_tranport', 'hari_kerja'];

    $('#perhitungan').prop('readonly', true);
    radioElements.forEach((radio) => {
        radio.addEventListener('change', function () {
            _loading();
            // Reset nilai input
            $('#perhitungan').val('');
            $('#nilaipotongan').val(0);

            // Hancurkan instance jika sebelumnya sudah ada
            if (cleaveInstance) { cleaveInstance.destroy(); cleaveInstance = null; }
            if (tagifyInstance) { tagifyInstance.destroy(); tagifyInstance = null; }

            $('#perhitungan').off(); // Matikan semua event JQuery (input.flat, dll)
            fv.resetField('perhitungan', true);
            $('#petunjuk').css('display', 'none');

            if (radio.value == "FLAT") {
                $('#perhitungan').attr('placeholder', '0').prop('readonly', false);

                cleaveInstance = new Cleave('#perhitungan', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    delimiter: '.',
                    numeralDecimalMark: ','
                });

                $('#perhitungan').on('input.flat', function () {
                    let clean = $(this).val().replace(/\./g, '');
                    cleavePotongan.setRawValue(clean || 0);
                });
                $.unblockUI();
            } else if (radio.value == "CUSTOM") {
                $('#petunjuk').css('display', 'block');
                $('#perhitungan').attr('placeholder', 'select or create').prop('readonly', false);
                // Inisialisasi Tagify
                tagifyInstance = new Tagify(document.querySelector('#perhitungan'), {
                    whitelist: whitelist,
                    maxTags: 10,
                    enforceWhitelist: false, // PENTING: Supaya bisa ngetik operator (+, -, *, /)
                    duplicates: true,
                    delimiters: " ",         // Gunakan spasi sebagai pemisah antar tag
                    dropdown: {
                        maxItems: 20,
                        classname: 'tags-inline',
                        enabled: 0,
                        closeOnSelect: true // Lebih baik true agar user tahu tag sudah terpilih
                    },

                });

                tagifyInstance.on('add remove change', function () {
                    hitungOtomatis();
                    // Delay sedikit agar Tagify sempat menulis JSON ke input asli
                    setTimeout(() => {
                        fv.revalidateField('perhitungan');
                    }, 50);
                });

                cleavePotongan.setRawValue('');
                $.unblockUI();
            } else {
                var gapok = $('#gapok').val();
                var harikerja = $('#harikerja').val();

                $('#perhitungan').val('gapok / hari_kerja').prop('readonly', true);
                hitungOtomatis();
                $.unblockUI();
            }
            // Validasi Ulang
            fv.revalidateField('perhitungan');
        });
    });

    function getVariableValues() {
        return {
            'gapok': Number($('#gapok').val().replace(/\./g, '')) || 0,
            'uang_makan': Number($('#makan').val().replace(/\./g, '')) || 0,
            'uang_tranport': Number($('#transport').val().replace(/\./g, '')) || 0,
            'hari_kerja': Number($('#harikerja').val().replace(/\./g, '')) || 0,
        };
    }

    // PERHITUNGAN POTONGAN STANDAR
    function hitungOtomatis() {
        const typeValue = $('[name="typepotongan"]:checked').val();

        if (typeValue === "STANDAR") {
            let gapok = $('#gapok').val() || "";
            let harikerja = $('#harikerja').val() || "";

            // Bersihkan titik dari gapok
            let cleanGapok = gapok.replace(/\./g, '');

            // JIKA gapok ada isinya DAN harikerja lebih dari 0
            if (cleanGapok !== "" && harikerja !== "" && Number(harikerja) > 0) {
                let hasil = Number(cleanGapok) / Number(harikerja);
                cleavePotongan.setRawValue(hasil.toFixed(0));
            }
            // JIKA harikerja adalah 0 atau kosong, set hasil jadi 0
            else {
                cleavePotongan.setRawValue('0');
            }
        }
        else if (typeValue === "CUSTOM" && tagifyInstance) {
            const tags = tagifyInstance.value;
            const values = getVariableValues(); // Ambil angka dari input gapok, dll

            let formula = "";
            tags.forEach(tag => {
                // Jika tag adalah variabel (ada di values), ambil angkanya. Jika bukan, ambil teksnya (operator).
                formula += values[tag.value] !== undefined ? values[tag.value] : tag.value;
            });

            try {
                // Evaluasi rumus, misal: "5000000 + 50000 + 10000"
                let hasil = new Function('return ' + formula)();
                let hasilBulat = Math.round(hasil);
                cleavePotongan.setRawValue(hasilBulat);
            } catch (e) {
                // Jika rumus belum valid (misal: "5000 + "), biarkan nilaipotongan 0
                cleavePotongan.setRawValue(0);
            }
        }
    }

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

            $("#formSetting #project").val(id);

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

                $("#formSetting #dtproject").val(dtId.join(","));
                $("#formSetting #lokasipekerjaan").html(lokasi.join(" "));
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

});


