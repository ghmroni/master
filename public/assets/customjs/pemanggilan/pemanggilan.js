_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 300);

    $(document).on("click", "td.ceklisPanggil", function () {
        var ceklisPanggil = $(this).find("input[type=checkbox]");
        if (ceklisPanggil.length) {
            // toggle: kalau sudah checked → uncheck, kalau belum → check
            var isChecked = ceklisPanggil.prop("checked");
            ceklisPanggil.prop("checked", !isChecked).trigger("change");
        }
    });

    table = $("#tabelPemanggilan").DataTable({
        ajax: "/pemanggilan",
        processing: true,
        serverSide: true,
        // responsive: true,
        aaSorting: [[0, "asc"]],
        columns: [
            {
                data: "ceklis",
                name: "ceklis",
                orderable: false,
                searchable: false,
                className: "ceklisPanggil",
            },
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
            },
            {
                data: "judul",
                name: "judul",
                orderable: true,
                searchable: true,
            },
            {
                data: "pelamar.nama",
            },
            {
                data: "pelamar.jenjang_pendidikan",
            },
            {
                data: "pelamar.nama_sekolah",
            },
            {
                data: "pelamar.jurusan_sekolah",
            },
            {
                data: "pelamar.tinggi_badan",
                render: function (data, type, row) {
                    // pastikan data tidak null/undefined
                    return data ? data + " cm" : "-";
                },
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center",
            },
            {
                targets: [0, 1, 4, 7],
                className: "dt-center",
            },
        ],
    });

    const datePanggil = document.getElementById("tglpanggil");
    if (datePanggil) {
        datePanggil.flatpickr({
            monthSelectorType: "static",
            minDate: "today",
        });
    }

    // Array global untuk menampung semua ID yang dipilih
    let selectedIds = [];

    $(document).on("change", ".check-panggil", function () {
        const id = $(this).val();
        if ($(this).is(":checked")) {
            if (!selectedIds.includes(id)) selectedIds.push(id);
        } else {
            selectedIds = selectedIds.filter((item) => item !== id);
        }
        // update jumlah ke Blade
        $("#totalDipilih").text(selectedIds.length + " dipilih");
    });

    // cek dan checked di page berbeda apabila sudah di checked sebelumnya
    $("#tabelPemanggilan").on("draw.dt", function () {
        $(".check-panggil").each(function () {
            const id = $(this).val();
            $(this).prop("checked", selectedIds.includes(id));
        });

        // update header select-all sesuai kondisi baris
        const total = $(".check-panggil").length;
        const checked = $(".check-panggil:checked").length;
        $("#select-allpanggil").prop("checked", total > 0 && total === checked);
    });

    // ketika header select-all diubah
    $(document).on("change", "#select-allpanggil", function () {
        const checked = $(this).is(":checked");

        // centang/uncentang semua checkbox di halaman saat ini
        $(".check-panggil").prop("checked", checked);

        if (checked) {
            // tambahkan semua ID yang ada di halaman ke selectedIds
            $(".check-panggil").each(function () {
                const id = $(this).val();
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
            });
        } else {
            // hapus semua ID yang ada di halaman dari selectedIds
            const pageIds = $(".check-panggil")
                .map(function () {
                    return $(this).val();
                })
                .get();

            selectedIds = selectedIds.filter((id) => !pageIds.includes(id));
        }

        // update jumlah
        $("#totalDipilih").text(selectedIds.length + " dipilih");
    });

    document
        .getElementById("formPanggil")
        .addEventListener("submit", function (e) {
            // Cek Tanggal Pemanggilan
            if (!datePanggil.value) {
                e.preventDefault(); // cegah submit
                datePanggil.classList.add("is-invalid"); // border merah
                return;
            } else {
                datePanggil.classList.remove("is-invalid"); // hapus border merah kalau sudah diisi
            }

            // Cek Checklis Pelamar
            if (selectedIds.length === 0) {
                e.preventDefault();
                $("#errorChecklist").show();
                return;
            } else {
                $("#errorChecklist").hide();
            }

            // Tambahkan hidden input ke form
            selectedIds.forEach(function (id) {
                $("<input>")
                    .attr({
                        type: "hidden",
                        name: "noreg[]",
                        value: id,
                    })
                    .appendTo("form");
            });

            // ambil value dari input flatpickr
            const rawDate = datePanggil.value; // biasanya format Y-m-d (YYYY-MM-DD)
            // parsing ke objek Date
            const d = new Date(rawDate);
            // format ke DD MM YYYY
            const options = { day: "2-digit", month: "long", year: "numeric" };
            const formattedDate = d.toLocaleDateString("id-ID", options);

            e.preventDefault();
            Swal.fire({
                title: "Konfirmasi!",
                text:
                    "Sebanyak " +
                    selectedIds.length +
                    " akan diproses pemanggilan pada tanggal " +
                    formattedDate,
                icon: "question",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Proses",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton:
                        "btn btn-primary me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: "/pemanggilan",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            tgl: datePanggil.value,
                            noreg: selectedIds,
                        },
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
                                window.location.reload();
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

    let table1;
    $(document).on("click", ".invitePelamar", function () {
        _loading();
        $("#exLargeModal").modal("show");
        // jika tabel sudah ada, inisialisasi DataTable
        if ($.fn.DataTable.isDataTable("#tabelInvite")) {
            table1.destroy();
        }

        table1 = $("#tabelInvite").DataTable({
            ajax: "/pemanggilan/invite",
            processing: true,
            serverSide: true,
            responsive: true,
            aaSorting: [[0, "asc"]],
            columns: [
                {
                    data: "nama",
                },
                {
                    data: "jenjang_pendidikan",
                },
                {
                    data: "nama_sekolah",
                },
                {
                    data: "jurusan_sekolah",
                },
                {
                    data: "tinggi_badan",
                    render: function (data, type, row) {
                        return data ? data + " cm" : "-";
                    },
                },
                {
                    data: "ceklis",
                    name: "ceklis",
                    orderable: false,
                    searchable: false,
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [1, 4, 5],
                    className: "dt-center",
                },
            ],
        });

        // ISI Selectbox Keperluan Project
        $("#tabelInvite").on("xhr.dt", function (e, settings, json, xhr) {
            let $select = $("#lowonganid"); // id select di Blade
            $select.empty(); // kosongkan dulu
            $select.append('<option value="">-pilih-</option>');
            $.each(json.keperluan, function (i, item) {
                if (item.project) {
                    $select.append(
                        $("<option>", {
                            value: item.id,
                            text: item.project.inisial + " - " + item.judul,
                        }),
                    );
                }
            });
        });
        $.unblockUI();
    });

    // Array global untuk menampung semua ID yang dipilih
    let selectedInvite = [];

    $(document).on("change", ".check-invite", function () {
        const id = $(this).val();
        if ($(this).is(":checked")) {
            if (!selectedInvite.includes(id)) selectedInvite.push(id);
        } else {
            selectedInvite = selectedInvite.filter((item) => item !== id);
        }
        // update jumlah ke Blade
        $("#totalInvite").val(selectedInvite.length);
    });

    // cek dan checked di page berbeda apabila sudah di checked sebelumnya
    $("#tabelInvite").on("draw.dt", function () {
        $(".check-invite").each(function () {
            const id = $(this).val();
            $(this).prop("checked", selectedInvite.includes(id));
        });
    });

    $("#exLargeModal").on("hidden.bs.modal", function () {
        // reset array global
        selectedInvite = [];

        // reset semua checkbox ketika modal ditutup
        $('#tabelInvite input[type="checkbox"]').prop("checked", false);

        // reset jumlah di Blade
        $("#totalInvite").val(0);
    });

    const totalInvite = document.getElementById("totalInvite");

    document
        .getElementById("formInvite")
        .addEventListener("submit", function (e) {
            // Cek Checklis Pelamar
            if (selectedInvite.length === 0) {
                e.preventDefault();
                totalInvite.classList.add("is-invalid");
                return;
            } else {
                totalInvite.classList.remove("is-invalid");
            }

            // Tambahkan hidden input ke form
            selectedInvite.forEach(function (id) {
                $("<input>")
                    .attr({
                        type: "hidden",
                        name: "noreg[]",
                        value: id,
                    })
                    .appendTo("form");
            });

            e.preventDefault();
            Swal.fire({
                title: "Konfirmasi!",
                text:
                    "Sebanyak " +
                    selectedInvite.length +
                    " pelamar akan ditambahkan ke data pemanggilan.",
                icon: "question",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Tambah",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton:
                        "btn btn-warning me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    _loading();
                    $.ajax({
                        url: "/pemanggilan/invite",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            lowonganid: $("#lowonganid").val(),
                            noreg: selectedInvite,
                        },
                        success: function (response) {
                            $("#exLargeModal").modal("hide");
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
                                table.ajax.reload();
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

    $(document).on("click", ".cancelInvite", function () {
        var reg = $(this).data("reg");
        var nama = $(this).data("nama");
        Swal.fire({
            title: "Konfirmasi!",
            html: nama + "<br> akan dihapus dari daftar pemanggilan?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/pemanggilan/hapus",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        noreg: reg,
                    },
                    success: function (response) {
                        $("#exLargeModal").modal("hide");
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
                            table.ajax.reload();
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
});
