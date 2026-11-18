_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    $('#formKaryawan :input[type="email"]').keyup(function () {
        this.value = this.value.toLowerCase();
    });

    $('#formKaryawan :input[type="text"],textarea').keyup(function () {
        this.value = this.value.toUpperCase();
    });


    setTimeout(function () {
        getKaryawan();
        $.unblockUI();
    }, 300);

    // PROVINSI
    $.getJSON('/getProvinsi', function (res) {
        _loading();
        $('#formKaryawan #provinsi').empty();
        $('#formKaryawan #provinsi').append('<option value="">pilih</option>');
        $.each(res, function (nama, id) {
            $('#formKaryawan #provinsi').append('<option value="' + id + '">' + nama + '</option>');
        });
        $.unblockUI();
    })
    // KABKOT
    $(document).on('change', '#provinsi', function () {
        _loading();
        var idprov = $(this).val();
        if (idprov) {
            $('#kabkot').empty();
            $('#kecamatan').empty();
            $('#desa').empty();

            $('#kabkot').append('<option value="">pilih</option>');
            $.getJSON('/getKabKot?provId=' + idprov, function (res) {
                $.each(res, function (nama, id) {
                    $('#formKaryawan #kabkot').append('<option value="' + id + '">' + nama + '</option>');
                })
            })
            $.unblockUI();
        } else {
            $('#kabkot').empty();
            $('#kecamatan').empty();
            $('#desa').empty();
            $.unblockUI();
        }

    });
    // KECAMATAN
    $(document).on('change', '#kabkot', function () {
        _loading();
        var idkab = $(this).val();
        if (idkab) {
            $('#kecamatan').empty();
            $('#desa').empty();

            $('#kecamatan').append('<option value="">pilih</option>');
            $.getJSON('/getKecamatan?kabId=' + idkab, function (res) {
                $.each(res, function (nama, id) {
                    $('#formKaryawan #kecamatan').append('<option value="' + id + '">' + nama + '</option>');
                })
            })
            $.unblockUI();
        } else {
            $('#kecamatan').empty();
            $('#desa').empty();
            $.unblockUI();
        }

    });
    // KELURAHAN
    $(document).on('change', '#kecamatan', function () {
        _loading();
        var idkec = $(this).val();
        if (idkec) {
            $('#desa').empty();

            $('#desa').append('<option value="">pilih</option>');
            $.getJSON('/getKelurahan?kecId=' + idkec, function (res) {
                $.each(res, function (nama, id) {
                    $('#formKaryawan #desa').append('<option value="' + id + '">' + nama + '</option>');
                })
            })
            $.unblockUI();
        } else {
            $('#desa').empty();
            $.unblockUI();
        }

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

    function getKaryawan() {
        $.getJSON('', function (res) {
            // Lokasi
            $.getJSON('/lokasi-project?lokasi=' + res.project, function (lok) {
                var lokasi = [];
                var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
                $.each(lok, function (id, data) {
                    lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black mt-1 mb-1">' + data.name + '</span>');
                })
                $('#lokasipekerjaan').html(lokasi.join(' '));
            })

            // LOAD JABATAN
            $.getJSON('/getJabatan', function (response) {
                $('#jabatan').empty();
                $('#jabatan').append('<option value="">pilih</option>');
                $.each(response, function (nama, id) {
                    $('#jabatan').append('<option value="' + id + '">' + nama + '</option>');
                    $("#jabatan option[value=" + res.jabatan + " ]").prop("selected", true);
                });
            })
            // LOAD UNITKERJA
            $.getJSON('/getUnitkerja', function (response) {
                $('#unitkerja').empty();
                $('#unitkerja').append('<option value="">pilih</option>');
                $.each(response, function (nama, id) {
                    $('#unitkerja').append('<option value="' + id + '">' + nama + '</option>');
                    $("#unitkerja option[value=" + res.unitkerja + " ]").prop("selected", true);
                });
            })

            $('#dtproject').val(res.dtproject).trigger('change');
            $('#kelamin').val(res.kelamin).trigger('change');
            $("#kawin").val(res.statuskawin_id).trigger("change");

            $('#provinsi').val(res.provinsi).trigger('change');
            $.getJSON('/getKabKot?provId=' + res.provinsi, function (response) {
                $.each(response, function (nama, id) {
                    $('#formKaryawan #kabkot').append('<option value="' + id + '">' + nama + '</option>');
                    $("#kabkot option[value=" + res.kabupaten + " ]").prop("selected", true);
                })
            })

            $.getJSON('/getKecamatan?kabId=' + res.kabupaten, function (response) {
                $.each(response, function (nama, id) {
                    $('#formKaryawan #kecamatan').append('<option value="' + id + '">' + nama + '</option>');
                    $("#kecamatan option[value=" + res.kecamatan + " ]").prop("selected", true);
                })
            })

            $.getJSON('/getKelurahan?kecId=' + res.kecamatan, function (response) {
                $.each(response, function (nama, id) {
                    $('#formKaryawan #desa').append('<option value="' + id + '">' + nama + '</option>');
                    $("#desa option[value=" + res.desa + " ]").prop("selected", true);
                })
            })
            // LOAD BANK
            $.getJSON("/getBank", function (response) {
                $("#bankid").empty();
                $("#bankid").append('<option value="">pilih</option>');
                $.each(response, function (nama, id) {
                    $("#bankid").append('<option value="' + id + '">' + nama + "</option>");
                    $("#bankid option[value=" + res.bank_id + " ]").prop("selected", true);
                });
            });

        });
        $.unblockUI();
    }

    $('#tanggallahir').flatpickr({
        maxDate: new Date().setFullYear(new Date().getFullYear() - 1)
    });

    // STATUS KELUARGA
    $(document).on("change", "#kelamin", function () {
        if ($(this).val() == "P") {
            $("#dtSuis #statussuis").val("SUAMI");
        } else {
            $("#dtSuis #statussuis").val("ISTRI");
        }
    });

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

    document.querySelectorAll(".tglkeluarga").forEach(function (el) {
        flatpickr(el, {
            dateFormat: "Y-m-d", // contoh format
            // opsi lain sesuai kebutuhan
        });
    });

    const formValidationKaryawan = document.getElementById("formKaryawan");
    const fv = FormValidation.formValidation(formValidationKaryawan, {
        fields: {
            // =================================================================================== BIODATA
            kk: {
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
            ktp: {
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
            nama: {
                validators: {
                    notEmpty: {
                        message: "Nama Lengkap masih kosong",
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
            tempat_lahir: {
                validators: {
                    notEmpty: {
                        message: "Tempat Lahir masih kosong",
                    },
                },
            },
            tanggal_lahir: {
                validators: {
                    notEmpty: {
                        message: "Tanggal Lahir masih kosong",
                    },
                },
            },
            jabatan: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            kelamin: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            agama: {
                validators: {
                    notEmpty: {
                        message: 'Inputan masih kosong'
                    }
                }
            },
            unitkerja: {
                validators: {
                    callback: {
                        message: 'Inputan masih kosong',
                        callback: function (input) {
                            const projectValue = formValidationKaryawan.querySelector('[name="dtproject"]').value;
                            const arr = projectValue.split(",");
                            // Jika projectnya Gapura
                            if (['11'].includes(arr[0])) {
                                return input.value !== '';
                            }
                            // Jika projectnya bukan Gapura, maka unitkerja dianggap valid (boleh kosong)
                            return true;
                        }
                    }
                }
            },
            grade: {
                validators: {
                    callback: {
                        message: 'Inputan masih kosong',
                        callback: function (input) {
                            const projectValue = formValidationKaryawan.querySelector('[name="dtproject"]').value;
                            const arr = projectValue.split(",");
                            // Jika projectnya Gapura
                            if (['11'].includes(arr[0])) {
                                return input.value !== '';
                            }
                            // Jika projectnya bukan GHM, maka Grade dianggap valid (boleh kosong)
                            return true;
                        }
                    },
                    regexp: {
                        regexp: /^[A-Za-z+\-]+$/,
                        message: 'Grade hanya boleh huruf, +, atau -'
                    },
                    stringLength: {
                        max: 2,
                        message: 'Grade maksimal 2 karakter'
                    },
                }
            },
            golongan: {
                validators: {
                    callback: {
                        message: 'Inputan masih kosong',
                        callback: function (input) {
                            const projectValue = formValidationKaryawan.querySelector('[name="dtproject"]').value;
                            const arr = projectValue.split(",");
                            // Jika projectnya 1 / GHM wajib diisi
                            if (arr[0] === '1') {
                                return input.value !== '';
                            }
                            // Jika projectnya bukan GHM, maka Golongan dianggap valid (boleh kosong)
                            return true;
                        }
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                    },
                    stringLength: {
                        max: 1,
                        message: 'Maksimal 1 digits'
                    },
                }
            },
            alamat_ktp: {
                validators: {
                    notEmpty: {
                        message: "Alamat KTP masih kosong",
                    },
                },
            },
            provinsi_id: {
                validators: {
                    notEmpty: {
                        message: "Provinsi masih kosong",
                    },
                },
            },
            kabkot_id: {
                validators: {
                    notEmpty: {
                        message: "Kabupaten / Kota masih kosong",
                    },
                },
            },
            kecamatan_id: {
                validators: {
                    notEmpty: {
                        message: "Kecamatan masih kosong",
                    },
                },
            },
            desa_id: {
                validators: {
                    notEmpty: {
                        message: "Desa / Kelurahan masih kosong",
                    },
                },
            },
            kodepos: {
                validators: {
                    notEmpty: {
                        message: "Kode Pos masih kosong",
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
                }
            },
            baju: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    }
                }
            },
            celana: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                    },
                    stringLength: {
                        max: 2,
                        message: 'Maksimal 2 digits'
                    },
                }
            },
            tb: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                    },
                    stringLength: {
                        max: 3,
                        message: 'Maksimal 3 digits'
                    },
                }
            },
            bb: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                    },
                    stringLength: {
                        max: 3,
                        message: 'Maksimal 3 digits'
                    },
                }
            },
            sepatu: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                    },
                    stringLength: {
                        max: 2,
                        message: 'Maksimal 2 digits'
                    },
                }
            },

            kawin: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                },
            },
            // =================================================================================== KELUARGA
            // SUIS
            ktpsuis: {
                validators: {
                    callback: {
                        message: "Harus diisi",
                        callback: function (input) {
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["2", "3", "4", "5"].includes(kawinValue)) {
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["2", "3", "4", "5"].includes(kawinValue)) {
                                return input.value !== "";
                            }
                            return true;
                        },
                    },
                    regexp: {
                        regexp: /^[A-Za-z. ]+$/,
                        message: 'Inputan hanya boleh huruf'
                    },
                },
            },
            lahirsuis: {
                validators: {
                    callback: {
                        message: "Harus diisi",
                        callback: function (input) {
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["2", "3", "4", "5"].includes(kawinValue)) {
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["2", "3", "4", "5"].includes(kawinValue)) {
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["2", "3", "4", "5"].includes(kawinValue)) {
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["3", "4", "5"].includes(kawinValue)) {
                                return input.value !== "";
                            }
                            return true;
                        },
                    },
                    regexp: {
                        regexp: /^[A-Za-z. ]+$/,
                        message: 'Inputan hanya boleh huruf'
                    },
                },
            },
            lahiranak1: {
                validators: {
                    callback: {
                        message: "Harus diisi",
                        callback: function (input) {
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["4", "5"].includes(kawinValue)) {
                                return input.value !== "";
                            }
                            return true;
                        },
                    },
                    regexp: {
                        regexp: /^[A-Za-z. ]+$/,
                        message: 'Inputan hanya boleh huruf'
                    },
                },
            },
            lahiranak2: {
                validators: {
                    callback: {
                        message: "Harus diisi",
                        callback: function (input) {
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                                formValidationKaryawan.querySelector(
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
                            if (["5"].includes(kawinValue)) {
                                return input.value !== "";
                            }
                            return true;
                        },
                    },
                    regexp: {
                        regexp: /^[A-Za-z. ]+$/,
                        message: 'Inputan hanya boleh huruf'
                    },
                },
            },
            lahiranak3: {
                validators: {
                    callback: {
                        message: "Harus diisi",
                        callback: function (input) {
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                            const kawinValue = formValidationKaryawan.querySelector('[name="kawin"]').value;
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
                                formValidationKaryawan.querySelector(
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
            // =================================================================================== PENDIDIKAN
            pendidikan: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    }
                }
            },
            namasekolah: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    }
                }
            },
            jurusan: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    }
                }
            },
            tahunlulus: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                    },
                    stringLength: {
                        min: 4,
                        max: 4,
                        message: 'Inputan harus 4 digits'
                    },
                }
            },
            nilai: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                    },
                    regexp: {
                        regexp: /^[0-9][0-9.,]*$/,
                        message: "Inputan harus diawali angka",
                    },
                    stringLength: {
                        max: 5,
                        message: 'Maksimal 5 digits'
                    },
                }
            },
            // =================================================================================== BANK
            bankid: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                        enabled: userCanEditBank // aktif hanya kalau punya izin
                    }
                }
            },
            namabank: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                        enabled: userCanEditBank // aktif hanya kalau punya izin
                    },
                    regexp: {
                        regexp: /^[A-Za-z. ]+$/,
                        message: 'Inputan hanya boleh huruf',
                        enabled: userCanEditBank // aktif hanya kalau punya izin
                    },
                }
            },
            norek: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong",
                        enabled: userCanEditBank // aktif hanya kalau punya izin
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Inputan hanya boleh berisi angka",
                        enabled: userCanEditBank // aktif hanya kalau punya izin
                    },
                }
            },
            // =================================================================================== KONTRAK
            tabkontrak: {
                validators: {
                    notEmpty: { message: "Nomor Kontrak masih kosong" }
                }
            },
            tabkontrakid: {
                validators: {
                    notEmpty: { message: "Nomor Kontrak masih kosong" }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleInvalidClass: "is-invalid",
                eleValidClass: "",
                rowSelector: ".row, .col-md-6, col-md-3, .col-sm-12, .col-md-12, td",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    // Saat submit, cek validasi
    formValidationKaryawan.addEventListener('submit', function (e) {
        e.preventDefault();

        fv.validate().then(function (status) {
            if (status === 'Valid') {
                _loading();
                // submit form jika semua valid
                // formValidationKaryawan.submit();
                $.ajax({
                    url: "/update-karyawan",
                    type: "POST",
                    data: $("#formKaryawan").serialize(),
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
                            window.location.reload()
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
            } else {
                // cari field pertama yang invalid
                const invalidElements = formValidationKaryawan.querySelectorAll('.is-invalid');
                if (invalidElements.length > 0) {
                    const invalidField = invalidElements[0];
                    const tabPane = invalidField.closest('.tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.getAttribute('id');
                        // buka tab sesuai error
                        document.querySelector('[data-bs-target="#' + tabId + '"]').click();
                        // fokuskan ke field error
                        setTimeout(() => invalidField.focus(), 200);
                    }
                }
            }
        });
    });


    // Pengaturan Disable or Enable
    $(document).on('change', '#dtproject', function () {
        const projectValue = formValidationKaryawan.querySelector('[name="dtproject"]').value;
        const arr = projectValue.split(",");
        const unitKerjaInput = document.querySelector('[name="unitkerja"]');
        const gradeInput = document.querySelector('[name="grade"]');
        const golInput = document.querySelector('[name="golongan"]');

        unitKerjaInput.setAttribute('disabled', true);
        gradeInput.setAttribute('disabled', true);
        golInput.setAttribute('disabled', true);

        const val = arr[0];
        // if (val === '1') { // GHM Pusat
        //     golInput.removeAttribute('disabled');
        //     fv.enableValidator('golongan');
        // }
        if (['11'].includes(val)) { // CGK contoh ['11','8'] 
            unitKerjaInput.removeAttribute('disabled');
            gradeInput.removeAttribute('disabled');
            fv.enableValidator('unitkerja');
            fv.enableValidator('grade');
        }
    });

    $(document).on('click', '.lihatLampiran', function () {
        var lampiran = $(this).data('lampiran');
        _loading();
        var url = '/lampiran/view/' + lampiran;
        // buka window baru dengan ukuran tertentu
        window.open(
            url,
            'PaklaringWindow', // nama window
            'width=800,height=600,scrollbars=yes,resizable=yes'
        );
        $.unblockUI();
    })

})