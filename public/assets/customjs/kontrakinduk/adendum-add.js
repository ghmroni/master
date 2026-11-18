_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $('#wizard-validation-form :input[type="text"], textarea').keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $("#tglterbit").flatpickr({
        dateFormat: "Y-m-d",
    });

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
            // endDate.set("maxDate", null);
        },
    });

    setTimeout(function () {
        $.getJSON("", function (response) {
            var details = response.data;

            // Saat modal edit ditampilkan

            startDate.set("maxDate", details.sampai);

            endDate.set("maxDate", details.sampai); // optional reset batas atas
        });

        if (!dbSettingsData || dbSettingsData.length === 0) return;

        dbSettingsData.forEach(function (settingData, currentTableIndex) {
            var lokasi = [];
            var color = ["bg-success", "bg-warning", "bg-primary", "bg-info", "bg-secondary", "bg-dark"];

            if (settingData.dtlokasi) {
                $.each(settingData.dtlokasi.lokasi_project, function (id, nama) {
                    lokasi.push('<span class="badge ' + color[id % color.length] + ' me-1 text-black">' + nama + "</span>");
                });
                $(`#lokasipekerjaan-${currentTableIndex}`).html(lokasi.join(" "));
                $(`.lokasisetting`).html(lokasi.join(" "));
            }

            $(`#project-${currentTableIndex}`).val(settingData.project_id);

            let detailValue = "";
            if (Array.isArray(settingData.project_detail)) {
                detailValue = settingData.project_detail.join(",");
            } else {
                detailValue = settingData.project_detail || "";
            }
            $(`#dtproject-${currentTableIndex}`).val(detailValue);

            // Validasi Project ID (Cukup didaftarkan sekali)
            let projectNameAttr = `setting[${currentTableIndex}][project_id]`;
            if (typeof FormValidation3 !== "undefined" && FormValidation3.getFields()[projectNameAttr]) {
                FormValidation3.removeField(projectNameAttr);
            }
            FormValidation3.addField(projectNameAttr, {
                validators: { notEmpty: { message: "Silakan pilih lokasi project terlebih dahulu" } }
            });
        });

        $.unblockUI();
    }, 200);

    const wizardValidation = document.querySelector("#wizard-validation");
    if (typeof wizardValidation !== undefined && wizardValidation !== null) {
        // Wizard form
        const wizardValidationForm = wizardValidation.querySelector(
            "#wizard-validation-form",
        );
        // Wizard steps
        const wizardValidationFormStep1 = wizardValidationForm.querySelector(
            "#account-details-validation",
        );
        const wizardValidationFormStep2 = wizardValidationForm.querySelector(
            "#personal-info-validation",
        );
        const wizardValidationFormStep3 = wizardValidationForm.querySelector(
            "#social-links-validation",
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

        setTimeout(function () {
            // Cek apakah saat ini masih di step awal sebelum membuka
            if (validationStepper._currentIndex === 0) {
                var myAccordion = new bootstrap.Collapse(document.getElementById('collapseDeliveryAddress'), {
                    show: true
                });
            }
        }, 1000);

        wizardValidation.addEventListener('show.bs-stepper', function (event) {
            // event.detail.indexStep berisi angka indeks step tujuan (dimulai dari 0)
            // index 0 = Step 1, index 1 = Step 2, dst.

            var accordionEl = document.getElementById('collapseDeliveryAddress');
            var accordionInstance = bootstrap.Collapse.getInstance(accordionEl);

            if (event.detail.indexStep === 0) {
                // Jika kembali ke Step 1, beri delay lalu tampilkan accordion
                setTimeout(function () {
                    if (validationStepper._currentIndex === 0) {
                        if (!accordionInstance) {
                            new bootstrap.Collapse(accordionEl, { show: true });
                        } else {
                            accordionInstance.show();
                        }
                    }
                }, 500); // Delay lebih cepat saat navigasi ulang
            } else {
                // Jika berpindah ke Step 2 atau selanjutnya, langsung tutup accordion (tanpa delay)
                if (accordionInstance) {
                    setTimeout(function () {
                        accordionInstance.hide();
                    }, 500);
                }
            }
        });

        // Account details
        const FormValidation1 = FormValidation.formValidation(
            wizardValidationFormStep1,
            {
                fields: {
                    judul: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    nomor: {
                        validators: {
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    jenis: {
                        validators: {
                            notEmpty: {
                                message: "Jenis kontrak induk belum dipilih",
                            },
                        },
                    },
                    tglterbit: {
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
                            notEmpty: {
                                message: "Inputan masih kosong",
                            },
                        },
                    },
                    keterangan: {
                        validators: {
                            callback: {
                                message: "Inputan masih kosong",
                                callback: function (input) {
                                    const statusValue = wizardValidation.querySelector('[name="status"]').value;
                                    if (statusValue !== "tambah") {
                                        return input.value.trim() !== "";
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
                        rowSelector: function (field, ele) {
                            if (!ele) return ".col-sm-6";
                            return ".col-sm-6, .col-md-6, .col-12, .mb-3";
                        },
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

        // Personal info
        const FormValidation2 = FormValidation.formValidation(
            wizardValidationFormStep2,
            {
                fields: {},
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        // Use this for enabling/changing valid/invalid class
                        // eleInvalidClass: '',
                        eleValidClass: "",
                        rowSelector: ".col-sm-6",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            },
        ).on("core.form.valid", function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });

        // Social links
        window.FormValidation3 = FormValidation.formValidation(
            wizardValidationFormStep3,
            {
                fields: {
                    file: {
                        validators: {
                            callback: {
                                message: "File Kontrak Induk masih kosong",
                                callback: function (input) {
                                    const statusValue = wizardValidation.querySelector('[name="status"]').value;
                                    if (statusValue == "tambah-adendum") {
                                        return input.value.trim() !== "";
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
                        // rowSelector: function (field, ele) {
                        //     if (field.startsWith('nominal')) {
                        //         return 'td'; // Feedback eror masuk ke cell tabel
                        //     }
                        //     return '.col-sm-6, .col-md-12, .fv-row-matrix';
                        // }

                        rowSelector: function (field, ele) {
                            // 💡 Pastikan polanya membaca regex string '[project_id]' dan '[nominal]'
                            if (
                                field.includes("[nominal]") ||
                                field.includes("[project_id]")
                            ) {
                                return ".fv-row-matrix";
                            }
                            return ".col-12, .mb-3";
                        },
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),

                    // Mencegah eror validasi elemen gaib di luar DOM
                    excluded: new FormValidation.plugins.Excluded({
                        ignored: function (field, element, inputs) {
                            if (
                                field.startsWith("nominal") &&
                                (!element || !document.body.contains(element))
                            ) {
                                return true;
                            }
                            return false;
                        },
                    }),
                },
            },
        ).on("core.form.valid", function () {
            // ============================================================================== SUBMIT FORM
            // 1. Inisialisasi FormData langsung dari elemen <form>
            // Ini otomatis membaca semua input teks, selectbox, termasuk file PDF Anda
            let formElement = $('#wizard-validation-form')[0];
            let formDataKirim = new FormData(formElement);

            // 2. Ambil semua nama Unit Kerja dari header tabel (mulai dari kolom ke-2)
            let unitKerjaHeaders = [];
            $('#table-biaya thead th').each(function (index) {
                if (index > 0) { // Lewati kolom pertama ("BIAYA")
                    unitKerjaHeaders.push($(this).text().trim().toUpperCase());
                }
            });

            // 3. Buat struktur data penampung untuk matriks biaya
            let dataMatriks = {};
            unitKerjaHeaders.forEach(function (unit) {
                dataMatriks[unit] = {};
            });

            // 4. Loop setiap baris untuk mengambil nama biaya dan nilai inputnya
            $('#table-biaya tbody tr').each(function () {
                let row = $(this);
                let namaBiaya = row.find('td').first().text().trim().toUpperCase();

                row.find('input').each(function (index) {
                    let namaUnit = unitKerjaHeaders[index];
                    let nilaiRaw = $(this).val() || "0";
                    let nilaiBersih = parseInt(nilaiRaw.replace(/\./g, ''), 10) || 0;

                    dataMatriks[namaUnit][namaBiaya] = nilaiBersih;
                });
            });

            // 5. Masukkan objek matriks biaya ke dalam FormData sebagai string JSON
            formDataKirim.append('data_biaya', JSON.stringify(dataMatriks));

            Swal.fire({
                title: "Konfirmasi!",
                text: "Tambah Adendum",
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
                        url: '/adendum-request', // Ganti dengan URL Route backend Anda
                        type: 'POST',
                        data: formDataKirim,
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
                                window.location.href = "/kontrakInduk";
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
            // ============================================================================== end SUBMIT
        });

        wizardValidationNext.forEach((item) => {
            item.addEventListener("click", (event) => {
                // When click the Next button, we will validate the current step
                switch (validationStepper._currentIndex) {
                    case 0:
                        FormValidation1.validate();
                        break;

                    case 1:
                        renderAllMatrixTables();

                        FormValidation2.validate();
                        break;

                    case 2:
                        FormValidation3.validate();
                        break;

                    default:
                        break;
                }
            });
        });

        wizardValidationPrev.forEach((item) => {
            item.addEventListener("click", (event) => {
                switch (validationStepper._currentIndex) {
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

    $(document).on("keyup", ".mask-rupiah", function () {
        let val = $(this)
            .val()
            .replace(/[^0-9]/g, ""); // Hapus semua karakter selain angka

        if (val === "") {
            $(this).val("");
            return;
        }

        // Format angka dengan separator titik (.)
        let formatted = parseInt(val, 10).toLocaleString("id-ID");
        $(this).val(formatted);
    });

    // ============================================================================================================== FUNGSI UTAMA UNTUK RE-RENDER TABEL (PANGGIL SETIAP MASUK STEP 3)
    function renderAllMatrixTables() {
        if (!dbSettingsData || dbSettingsData.length === 0) return;

        // Ambil data Unit Kerja dan Tunjangan TERBARU yang ada di DOM Step 2 saat ini
        let unitKerjaList = [];
        $('input[name="unitkerja[]"]').each(function () {
            let val = $(this).val().trim();
            if (val !== "") unitKerjaList.push(val.toUpperCase());
        });

        let tunjanganList = [];
        $('input[name="tunjangan[]"]').each(function () {
            let val = $(this).val().trim();
            if (val !== "") tunjanganList.push(val.toUpperCase());
        });

        // Loop sebanyak jumlah setting/tabel yang ada
        dbSettingsData.forEach(function (settingData, currentTableIndex) {
            let targetTable = $(`#table-biaya-${currentTableIndex}`);

            // A. Kosongkan tabel lawas
            targetTable.find("thead").empty();
            targetTable.find("tbody").empty();

            if (unitKerjaList.length === 0 || tunjanganList.length === 0) return;

            // B. Generate Header Tabel (Dinamis dari Step 2)
            let headerRow = '<tr class="text-center"><th class="align-middle" style="min-width: 25%;">JENIS BIAYA</th>';
            unitKerjaList.forEach(function (unit) {
                headerRow += `<th style="font-size: 0.85rem;">${unit}</th>`;
            });
            headerRow += "</tr>";
            targetTable.find("thead").append(headerRow);

            // C. Generate Body Tabel
            tunjanganList.forEach(function (biaya, indexBiaya) {
                let bodyRow = `<tr><td class="text-start fw-bold" style="font-size: 0.85rem;">${biaya}</td>`;

                unitKerjaList.forEach(function (unit, indexUnit) {
                    let fieldName = `setting[${currentTableIndex}][nominal][${indexBiaya}][${indexUnit}]`;

                    // 💡 PENCARIAN NOMINAL: 
                    // Cek 1: Apakah user sudah mengetik sesuatu di form Step 3 sebelum ini? (Agar inputan tidak hilang saat re-render)
                    let existingInputVal = $(`input[name="${fieldName}"]`).val();
                    let nominalValue = "0";

                    if (existingInputVal !== undefined) {
                        // Pakai nilai yang sedang diketik user di screen
                        nominalValue = existingInputVal.replace(/\./g, "");
                    } else if (
                        settingData.nominal &&
                        settingData.nominal[indexBiaya] &&
                        settingData.nominal[indexBiaya][indexUnit] !== undefined
                    ) {
                        // Jika input belum ada di DOM, ambil nilai awal dari Database
                        nominalValue = settingData.nominal[indexBiaya][indexUnit];
                    }

                    let nilaiFormatRupiah = nominalValue > 0 ? parseInt(nominalValue, 10).toLocaleString('id-ID') : '';

                    bodyRow += `
                        <td  class="p-1" style="font-size: 0.85rem;">
                            <div class="fv-row-matrix">
                                <input type="text" class="form-control text-end mask-rupiah input-nominal-dinamis d-inline-block w-auto" 
                                    name="${fieldName}" 
                                    value="${nilaiFormatRupiah}" 
                                    placeholder="0" 
                                    autocomplete="off">
                            </div>
                        </td>
                    `;
                });

                bodyRow += "</tr>";
                targetTable.find("tbody").append(bodyRow);
            });

            // D. Aktifkan Masking Rupiah
            targetTable.find(".mask-rupiah").each(function () {
                // Jalankan plugin masking Anda di sini, contoh:
                // $(this).mask("#.##0", { reverse: true });
                $(this).trigger("input");
            });

            // E. Daftarkan Validasi Input Nominal (Sama seperti kode Anda)
            targetTable.find(".input-nominal-dinamis").each(function () {
                let nameAttr = $(this).attr("name");
                if (typeof FormValidation3 !== "undefined" && FormValidation3.getFields()[nameAttr]) {
                    FormValidation3.removeField(nameAttr);
                }
                FormValidation3.addField(nameAttr, {
                    validators: {
                        notEmpty: { message: "Nominal wajib diisi" },
                        callback: {
                            message: "Nominal tidak boleh kosong atau minus",
                            callback: function (input) {
                                let value = input.value.trim();
                                if (value === "") return false;
                                let angkaMurni = parseInt(value.replace(/\./g, ""), 10);
                                return angkaMurni >= 0;
                            },
                        },
                    },
                });
            });
        });
    }

    //  ================================================================================================================== UNITKERJA
    let masterUnitkerja = [];

    $.getJSON("/getUnitkerja", function (response) {
        $.each(response, function (nama, id) {
            masterUnitkerja.push(nama);
        });
        renderUnitkerjalist(); // Render pertama kali
    });

    function renderUnitkerjalist() {
        // Ambil semua nilai yang sudah terpilih di semua input tunjangan[]
        let unitkerjaTerpilih = [];
        $('input[name="unitkerja[]"]').each(function () {
            let val = $(this).val().trim().toUpperCase();
            if (val) unitkerjaTerpilih.push(val);
        });

        // Filter: Hanya masukkan unitkerja yang BELUM unitkerjaTerpilih
        let options = "";
        masterUnitkerja.forEach(function (nama) {
            if (!unitkerjaTerpilih.includes(nama.toUpperCase())) {
                options += `<option value="${nama}">`;
            }
        });

        $("#list_unitkerja").html(options);
    }

    // 3. Jalankan filter setiap kali input berubah
    $(document).on("input", 'input[name="unitkerja[]"]', function () {
        renderUnitkerjalist();
    });

    var uRoom = $('input[name="unitkerja[]"]').length;
    $(document).on("click", ".unitkerja_fields", function () {
        _loading();
        uRoom += 1;
        var objTo = document.getElementById("unitkerja_fields");
        var divtest = document.createElement("div");
        divtest.setAttribute(
            "class",
            "form-group removeclass_unitkerja" + uRoom,
        );
        var rdiv = "removeclass_unitkerja" + uRoom;
        divtest.innerHTML =
            `
            <div class="row g-2 mb-2">
                <div class="col-md-12 nopadding">
                    <div class="input-group">
                        <input type="text" class="form-control text-uppercase" id="unitkerja" name="unitkerja[]" list="list_unitkerja" placeholder="Ketik / pilih unitkerja" autocomplete="off">
                        <button class="btn btn-danger remove_unitkerja_fields" data-room=` + uRoom +
            ` type="button"> <i class="ti ti-trash"></i></button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        `;

        objTo.appendChild(divtest);

        const newUnitkerjaInput = divtest.querySelector('[name="unitkerja[]"]');

        renderUnitkerjalist(); // Update list saat baris baru muncul
        $.unblockUI();
    });

    $(document).on("click", ".remove_unitkerja_fields", function () {
        _loading();
        var rid = $(this).data("room");
        $(".removeclass_unitkerja" + rid).remove();
        renderUnitkerjalist(); // Update list saat baris baru muncul
        $.unblockUI();
    });

    //  ================================================================================================================== TUNJANGAN
    let masterTunjangan = [];

    $.getJSON("/getTunjangan", function (response) {
        $.each(response, function (nama, id) {
            masterTunjangan.push(nama);
        });
        renderTunjanganlist(); // Render pertama kali
    });

    function renderTunjanganlist() {
        // Ambil semua nilai yang sudah terpilih di semua input tunjangan[]
        let tunjanganTerpilih = [];
        $('input[name="tunjangan[]"]').each(function () {
            let val = $(this).val().trim().toUpperCase();
            if (val) tunjanganTerpilih.push(val);
        });

        // Filter: Hanya masukkan unitkerja yang BELUM tunjanganTerpilih
        let options = "";
        masterTunjangan.forEach(function (nama) {
            if (!tunjanganTerpilih.includes(nama.toUpperCase())) {
                options += `<option value="${nama}">`;
            }
        });

        $("#list_tunjangan").html(options);
    }

    // 3. Jalankan filter setiap kali input berubah
    $(document).on("input", 'input[name="tunjangan[]"]', function () {
        renderTunjanganlist();
    });

    var tRoom = $('input[name="tunjangan[]"]').length;
    $(document).on("click", ".tunjangan_fields", function () {
        _loading();
        tRoom += 1;
        var objTo = document.getElementById("tunjangan_fields");
        var divtest = document.createElement("div");
        divtest.setAttribute(
            "class",
            "form-group removeclass_tunjangan" + tRoom,
        );
        var rdiv = "removeclass_tunjangan" + tRoom;
        divtest.innerHTML =
            `
            <div class="row g-2 mb-2">
                <div class="col-md-12 nopadding">
                    <div class="input-group">
                        <input type="text" class="form-control text-uppercase" id="tunjangan" name="tunjangan[]" list="list_tunjangan" placeholder="Biaya / tunjangan" autocomplete="off">
                        <button class="btn btn-danger remove_tunjangan_fields" data-room=` +
            tRoom +
            ` type="button"> <i class="ti ti-trash"></i></button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        `;

        objTo.appendChild(divtest);

        const newUnitkerjaInput = divtest.querySelector('[name="tunjangan[]"]');

        renderTunjanganlist(); // Update list saat baris baru muncul
        $.unblockUI();
    });

    $(document).on("click", ".remove_tunjangan_fields", function () {
        _loading();
        var rid = $(this).data("room");
        $(".removeclass_tunjangan" + rid).remove();
        renderTunjanganlist(); // Update list saat baris baru muncul
        $.unblockUI();
    });
})