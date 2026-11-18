_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 200);

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
            endDate.set("maxDate", null);
        },
    });

    const wizardValidation = document.querySelector('#wizard-validation');
    if (typeof wizardValidation !== undefined && wizardValidation !== null) {
        // Wizard form
        const wizardValidationForm = wizardValidation.querySelector('#wizard-validation-form');
        // Wizard steps
        const wizardValidationFormStep1 = wizardValidationForm.querySelector('#account-details-validation');
        const wizardValidationFormStep2 = wizardValidationForm.querySelector('#personal-info-validation');
        const wizardValidationFormStep3 = wizardValidationForm.querySelector('#social-links-validation');
        // Wizard next prev button
        const wizardValidationNext = [].slice.call(wizardValidationForm.querySelectorAll('.btn-next'));
        const wizardValidationPrev = [].slice.call(wizardValidationForm.querySelectorAll('.btn-prev'));

        const validationStepper = new Stepper(wizardValidation, {
            linear: true
        });

        // Account details
        const FormValidation1 = FormValidation.formValidation(wizardValidationFormStep1, {
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
                    eleValidClass: '',
                    rowSelector: function (field, ele) {
                        if (!ele) return '.col-sm-6';
                        return '.col-sm-6, .col-md-6, .col-12, .mb-3';
                    }
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            },
            init: instance => {
                instance.on('plugins.message.placed', function (e) {
                    //* Move the error message out of the `input-group` element
                    if (e.element.parentElement.classList.contains('input-group')) {
                        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                    }
                });
            }
        }).on('core.form.valid', function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });

        // Personal info
        const FormValidation2 = FormValidation.formValidation(wizardValidationFormStep2, {
            fields: {

            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    // Use this for enabling/changing valid/invalid class
                    // eleInvalidClass: '',
                    eleValidClass: '',
                    rowSelector: '.col-sm-6'
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        }).on('core.form.valid', function () {
            // Jump to the next step when all fields in the current step are valid
            validationStepper.next();
        });


        // Social links
        window.FormValidation3 = FormValidation.formValidation(wizardValidationFormStep3, {
            fields: {
                file: {
                    validators: {
                        notEmpty: {
                            message: "File Kontrak Induk masih kosong",
                        },
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    // Use this for enabling/changing valid/invalid class
                    // eleInvalidClass: '',
                    eleValidClass: '',
                    // rowSelector: function (field, ele) {
                    //     if (field.startsWith('nominal')) {
                    //         return 'td'; // Feedback eror masuk ke cell tabel
                    //     }
                    //     return '.col-sm-6, .col-md-12, .fv-row-matrix';
                    // }

                    rowSelector: function (field, ele) {
                        // 💡 Pastikan polanya membaca regex string '[project_id]' dan '[nominal]'
                        if (field.includes('[nominal]') || field.includes('[project_id]')) {
                            return '.fv-row-matrix';
                        }
                        return '.col-12, .mb-3';
                    }
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton(),

                // Mencegah eror validasi elemen gaib di luar DOM
                excluded: new FormValidation.plugins.Excluded({
                    ignored: function (field, element, inputs) {
                        if (field.startsWith('nominal') && (!element || !document.body.contains(element))) {
                            return true;
                        }
                        return false;
                    },
                }),
            }
        }).on('core.form.valid', function () {

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
                text: "Simpan Kontrak Induk",
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
                        url: '/kontrakInduk-perpanjang', // Ganti dengan URL Route backend Anda
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
                                window.location.href = '/kontrakInduk';
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
            // ============================================================================== end SUBMIT
        });

        wizardValidationNext.forEach(item => {
            item.addEventListener('click', event => {
                // When click the Next button, we will validate the current step
                switch (validationStepper._currentIndex) {
                    case 0:
                        FormValidation1.validate();
                        break;

                    case 1:
                        // 1. Ambil data Unit Kerja (untuk Header Kolom)
                        let unitKerjaList = [];
                        $('input[name="unitkerja[]"]').each(function () {
                            let val = $(this).val().trim();
                            if (val !== '') {
                                unitKerjaList.push(val.toUpperCase());
                            }
                        });

                        // 2. Ambil data Tunjangan / Nama Biaya (Untuk Baris Pertama)
                        let tunjanganList = [];
                        $('input[name="tunjangan[]"]').each(function () {
                            let val = $(this).val().trim();
                            if (val !== '') {
                                tunjanganList.push(val.toUpperCase());
                            }
                        });

                        // Kosongkan tabel lama sebelum merender yang baru
                        $('#table-biaya thead').empty();
                        $('#table-biaya tbody').empty();

                        // Validasi: Jika data masih kosong, jangan generate tabel
                        if (unitKerjaList.length === 0 || tunjanganList.length === 0) {
                            return;
                        }

                        // 3. Generate Header Tabel
                        let headerRow = `<tr class="text-center align-middle">
                                            <th class="align-middle" style="min-width: 25%;" > JENIS BIAYA</th>`;
                        unitKerjaList.forEach(function (unit) {
                            headerRow += `<th style="font-size: 0.85rem;" >${unit}</th>`; // Kolom berikutnya sesuai inputan unit kerja
                        });
                        headerRow += '</tr>';
                        $('#table-biaya thead').append(headerRow);

                        // 4. Generate Body Tabel (Baris = Tunjangan, Kolom = Input Text)
                        tunjanganList.forEach(function (biaya, indexBiaya) {
                            let bodyRow = `<tr><td class="text-start fw-bold" style="font-size: 0.85rem;">${biaya}</td>`; // Kolom pertama berisi nama biaya

                            unitKerjaList.forEach(function (unit, indexUnit) {
                                console.log(unit);
                                console.log(indexUnit);

                                // Atur atribut name agar data terkirim rapi ke backend (Array Multi-dimensi)
                                bodyRow += `
                                    <td class="p-1" style="font-size: 0.85rem;">
                                        <div class="fv-row-matrix d-flex justify-content-center">
                                            <input type="text" class="form-control text-end mask-rupiah d-inline-block w-auto" name="setting[0][nominal][${indexBiaya}][${indexUnit}]" placeholder="0" autocomplete="off">
                                        </div>
                                    </td>
                                `;
                            });

                            bodyRow += '</tr>';
                            $('#table-biaya tbody').append(bodyRow);
                        });

                        // VALIDASI INPUT NILAI
                        $('#table-biaya tbody input').each(function () {
                            let fieldName = $(this).attr('name');

                            // Hapus dulu validasi lama (jika ada generate ulang) agar tidak duplikat
                            // FormValidation3.removeField(fieldName);

                            // Daftarkan field dinamis ke library FormValidation.io
                            FormValidation3.addField(fieldName, {
                                validators: {
                                    notEmpty: {
                                        message: 'Nominal wajib diisi'
                                    },
                                    callback: {
                                        message: 'Nominal tidak boleh kosong atau minus',
                                        callback: function (input) {
                                            let value = input.value.trim();

                                            // Jika input benar-benar kosong, kembalikan false (biar notEmpty/pesan ini terpicu)
                                            if (value === '') {
                                                return false;
                                            }

                                            // Bersihkan format titik Rupiah
                                            let angkaMurni = parseInt(value.replace(/\./g, ''), 10);

                                            // VALIDASI BARU: Izinkan angka 0 dan angka positif lainnya (>= 0)
                                            return angkaMurni >= 0;
                                        }
                                    }
                                }
                            });
                        });

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

        wizardValidationPrev.forEach(item => {
            item.addEventListener('click', event => {
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


    $(document).on('keyup', '.mask-rupiah', function () {
        let val = $(this).val().replace(/[^0-9]/g, ''); // Hapus semua karakter selain angka

        if (val === '') {
            $(this).val('');
            return;
        }

        // Format angka dengan separator titik (.)
        let formatted = parseInt(val, 10).toLocaleString('id-ID');
        $(this).val(formatted);
    });


    //  ================================================================================================================== UNITKERJA
    let masterUnitkerja = [];

    $.getJSON('/getUnitkerja', function (response) {
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
        let options = '';
        masterUnitkerja.forEach(function (nama) {
            if (!unitkerjaTerpilih.includes(nama.toUpperCase())) {
                options += `<option value="${nama}">`;
            }
        });

        $('#list_unitkerja').html(options);
    }

    // 3. Jalankan filter setiap kali input berubah
    $(document).on('input', 'input[name="unitkerja[]"]', function () {
        renderUnitkerjalist();
    });

    var uRoom = 1;
    $(document).on('click', '.unitkerja_fields', function () {
        _loading();
        uRoom += 1;
        var objTo = document.getElementById('unitkerja_fields')
        var divtest = document.createElement("div");
        divtest.setAttribute("class", "form-group removeclass_unitkerja" + uRoom);
        var rdiv = 'removeclass_unitkerja' + uRoom;
        divtest.innerHTML = `
            <div class="row g-2 mb-2">
                <div class="col-md-12 nopadding">
                    <div class="input-group">
                        <input type="text" class="form-control text-uppercase" id="unitkerja" name="unitkerja[]" list="list_unitkerja" placeholder="Ketik / pilih unitkerja" autocomplete="off">
                        <button class="btn btn-danger remove_unitkerja_fields" data-room=`+ uRoom + ` type="button"> <i class="ti ti-trash"></i></button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        `;

        objTo.appendChild(divtest)

        const newUnitkerjaInput = divtest.querySelector('[name="unitkerja[]"]');

        renderUnitkerjalist(); // Update list saat baris baru muncul
        $.unblockUI();
    })

    $(document).on('click', '.remove_unitkerja_fields', function () {
        _loading();
        var rid = $(this).data('room');
        $('.removeclass_unitkerja' + rid).remove();
        renderUnitkerjalist(); // Update list saat baris baru muncul
        $.unblockUI();
    })

    //  ================================================================================================================== TUNJANGAN
    let masterTunjangan = [];

    $.getJSON('/getTunjangan', function (response) {
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
        let options = '';
        masterTunjangan.forEach(function (nama) {
            if (!tunjanganTerpilih.includes(nama.toUpperCase())) {
                options += `<option value="${nama}">`;
            }
        });

        $('#list_tunjangan').html(options);
    }

    // 3. Jalankan filter setiap kali input berubah
    $(document).on('input', 'input[name="tunjangan[]"]', function () {
        renderTunjanganlist();
    });

    var tRoom = 1;
    $(document).on('click', '.tunjangan_fields', function () {
        _loading();
        tRoom += 1;
        var objTo = document.getElementById('tunjangan_fields')
        var divtest = document.createElement("div");
        divtest.setAttribute("class", "form-group removeclass_tunjangan" + tRoom);
        var rdiv = 'removeclass_tunjangan' + tRoom;
        divtest.innerHTML = `
            <div class="row g-2 mb-2">
                <div class="col-md-12 nopadding">
                    <div class="input-group">
                        <input type="text" class="form-control text-uppercase" id="tunjangan" name="tunjangan[]" list="list_tunjangan" placeholder="Biaya / tunjangan" autocomplete="off">
                        <button class="btn btn-danger remove_tunjangan_fields" data-room=`+ tRoom + ` type="button"> <i class="ti ti-trash"></i></button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        `;

        objTo.appendChild(divtest)

        const newUnitkerjaInput = divtest.querySelector('[name="tunjangan[]"]');

        renderTunjanganlist(); // Update list saat baris baru muncul
        $.unblockUI();
    })

    $(document).on('click', '.remove_tunjangan_fields', function () {
        _loading();
        var rid = $(this).data('room');
        $('.removeclass_tunjangan' + rid).remove();
        renderTunjanganlist(); // Update list saat baris baru muncul
        $.unblockUI();
    })

    // =================================================================================================== TAMBAH SETTING
    // Event ketika tombol "+ Tambah Setting" diklik
    $(document).on('click', '#btn-tambah-setting', function () {
        let hitungBlokSaatIni = $('.project-setting-block').length;
        generateNewSettingTable(hitungBlokSaatIni);
    });

    function generateNewSettingTable(currentTableIndex) {
        // 1. Ambil data dari Tab 2 (Sama seperti kode Anda)
        let unitKerjaList = [];
        $('input[name="unitkerja[]"]').each(function () {
            let val = $(this).val().trim();
            if (val !== '') unitKerjaList.push(val.toUpperCase());
        });

        let tunjanganList = [];
        $('input[name="tunjangan[]"]').each(function () {
            let val = $(this).val().trim();
            if (val !== '') tunjanganList.push(val.toUpperCase());
        });

        if (unitKerjaList.length === 0 || tunjanganList.length === 0) {
            alert('Mohon isi Unit Kerja dan Tunjangan di Tab 2 terlebih dahulu.');
            return;
        }

        // 2. Buat Template HTML untuk kontainer satu blok Project (Input Project + Tabel)
        // <h5>SETTING PROJECT #${currentTableIndex + 1}</h5>
        // ${currentTableIndex > 0 ? `<button type="button" class="btn btn-danger btn-sm btn-hapus-setting" data-id="${currentTableIndex}"><i class="ti ti-trash me-1"></i>Hapus</button>` : ''}
        let blockHtml = `
            <div class="project-setting-block border border-secondary p-3 mb-4 rounded bg-dark-custom" id="block-setting-${currentTableIndex}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-action-title">SETTING PROJECT #${currentTableIndex + 1}</h5>
                    <div class="card-action-element">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                ${currentTableIndex > 0 ? `<button type="button" class="btn btn-danger btn-sm btn-hapus-setting" data-id="${currentTableIndex}"><i class="ti ti-trash me-1"></i>Hapus</button>` : ''}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Input Pilih Project dengan Name terikat ke indexTabel -->
                <div class="mb-3">
                    <label class="form-label">PROJECT</label>
                    <div class="input-group">
                        <span class="form-control lokasipekerjaan-text" id="lokasipekerjaan-${currentTableIndex}"></span>
                        <div id="btnLokasi">
                            <button class="btn btn-dark changeLokasi-btn" type="button" data-id="${currentTableIndex}">
                                📍
                            </button>
                        </div>
                    </div>
                    <div class="fv-row-matrix">
                        <input type="hidden" class="form-control" id="project-${currentTableIndex}" name="setting[${currentTableIndex}][project_id]" readonly>
                        <input type="hidden" class="form-control" id="dtproject-${currentTableIndex}" name="setting[${currentTableIndex}][project_detail]" readonly>
                    </div>
                </div>

                <!-- Tabel Matriks Biaya -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-nowrap align-middle small fs-xs table-biaya-dinamis" id="table-biaya-${currentTableIndex}">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th class="align-middle" style="min-width: 25%;">JENIS BIAYA</th>
                                ${unitKerjaList.map(unit => `<th style="font-size: 0.85rem;">${unit}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
        `;

        // 3. Generate Body Tabel dengan Array 3 Dimensi
        tunjanganList.forEach(function (biaya, indexBiaya) {
            blockHtml += `<tr><td class="text-start fw-bold" style="font-size: 0.85rem;">${biaya}</td>`;

            unitKerjaList.forEach(function (unit, indexUnit) {
                // 💡 PERHATIKAN: Struktur name dirubah menjadi setting[tableIndex][nominal][biayaIndex][unitIndex]
                let fieldName = `setting[${currentTableIndex}][nominal][${indexBiaya}][${indexUnit}]`;
                blockHtml += `
                    <td class="p-1" style="font-size: 0.85rem;">
                        <div class="fv-row-matrix d-flex justify-content-center">
                            <input type="text" class="form-control text-end mask-rupiah input-nominal-dinamis d-inline-block w-auto" name="${fieldName}" placeholder="0" autocomplete="off">
                        </div>
                    </td>
                `;
            });

            blockHtml += '</tr>';
        });

        blockHtml += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

        // 4. Append blok baru ke dalam kontainer utama di Tab 3
        $('#kontainer-setting-project').append(blockHtml);
        // 5. Daftarkan FormValidation khusus untuk input nominal yang baru saja dibuat
        $(`#block-setting-${currentTableIndex} .input-nominal-dinamis`).each(function () {
            let nameAttr = $(this).attr('name');

            FormValidation3.addField(nameAttr, {
                validators: {
                    notEmpty: { message: 'Nominal wajib diisi' },
                    callback: {
                        message: 'Nominal tidak boleh kosong atau minus',
                        callback: function (input) {
                            let value = input.value.trim();
                            if (value === '') return false;
                            let angkaMurni = parseInt(value.replace(/\./g, ''), 10);
                            return angkaMurni >= 0;
                        }
                    }
                }
            });
        });

        let projectNameAttr = `setting[${currentTableIndex}][project_id]`;

        FormValidation3.addField(projectNameAttr, {
            validators: {
                notEmpty: {
                    message: 'Silakan pilih lokasi project terlebih dahulu'
                }
            }
        });
    }

    // Fungsi untuk menghapus baris settingan jika salah input
    $(document).on('click', '.btn-hapus-setting', function () {
        let id = $(this).data('id');

        // Hapus validasi nominal
        $(`#block-setting-${id} .input-nominal-dinamis`).each(function () {
            let nameAttr = $(this).attr('name');
            FormValidation3.removeField(nameAttr);
        });

        // 💡 TAMBAHKAN INI: Hapus validasi project_id dari form validation sebelum element dihancurkan
        let projectNameAttr = `setting[${id}][project_id]`;
        FormValidation3.removeField(projectNameAttr);

        $(`#block-setting-${id}`).remove();
    });

    // ============================================================================================================================== TAMPILKAN MODAL PERUBAHAN PROJECT
    const btnChange = $(".changeProject");

    var theme = $("html").hasClass("light-style")
        ? "default"
        : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", ".changeLokasi-btn", function () {
        let targetIndex = $(this).data('id');
        // 💡 SOLUSI KUNCI: Titipkan nomor indeks aktif ke dalam tombol konfirmasi modal
        $(".changeProject").data('target-idx', targetIndex);
        _loading();
        if (basicTree.jstree(true)) {
            basicTree.jstree(true).refresh();
        } else {
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
        }
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
        let targetIndex = $(this).data('target-idx');

        // Ambil data ID node yang dipilih dari jstree
        var id = $(this).data("id");

        if (id) {
            _loading();
            $("#project-" + targetIndex).val(id);

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
                        '<span class="badge ' + color[idx] + ' me-1 text-black">' + data.name + "</span>"
                    );
                });

                $("#dtproject-" + targetIndex).val(dtId.join(","));
                $("#lokasipekerjaan-" + targetIndex).html(lokasi.join(" "));

                let fieldProjectName = `setting[${targetIndex}][project_id]`;
                if (typeof FormValidation3 !== 'undefined') {
                    FormValidation3.revalidateField(fieldProjectName);
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
})