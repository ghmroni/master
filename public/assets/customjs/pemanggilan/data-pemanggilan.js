_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        backToDatatable();
        $.unblockUI();
    }, 300);

    function fetchData(page = 1) {
        let search = $("#searchInput").val();

        $.ajax({
            url: "/data-pemanggilan/?page=" + page,
            type: "GET",
            data: {
                search: search,
            },
            success: function (data) {
                $("#resultContainer").html(data);
            },
        });
    }

    $("#searchInput").on("input change", function () {
        fetchData();
        resetDetailPemanggilan();
    });

    function resetDetailPemanggilan() {
        // destroy DataTable jika ada
        if ($.fn.DataTable.isDataTable("#tabelDetail")) {
            $("#tabelDetail").DataTable().destroy();
        }

        // kosongkan isi
        $("#detail-pemanggilan").empty();

        // isi ulang dengan default placeholder
        $("#detail-pemanggilan").html(`
            <div class="bg-label-secondary p-2 rounded-2" style="height: 350px">
                <h4>
                    <i class="ti ti-arrow-left ti-lg me-1"></i> Pilih tanggal pemanggilan
                </h4>
                <h6 class="text-muted ms-5 fst-italic">Detail pemanggilan ditampilkan disini</h6>
                <div class="text-center mt-5">
                    <img src="/assets/img/illustrations/file.png" alt="" height="140">
                </div>
            </div>
        `);
    }

    // ================================================================================================================= DETAIL PEMANGGILAN
    $(document).on("click", ".tanggalPemanggilan", function () {
        // reset semua elemen ke kondisi awal
        $(".tanggalPemanggilan")
            .removeClass(
                "border-bottom border-info shadow py-2 px-1 rounded-3 bg-label-primary",
            )
            .addClass("border-bottom border-black rounded-3 p-2");

        // tandai elemen yang diklik
        $(this)
            .removeClass("border-bottom border-black rounded-3 p-2")
            .addClass(
                "border-bottom border-info shadow py-2 px-1 rounded-3 bg-label-primary",
            );

        // simpan tanggal yang dipilih
        window.selectedTanggal = $(this).data("tanggal");
        window.selectedUser = $(this).data("user");
        window.selectedStatus = $(this).data("status");

        // load partial view ke dalam #detail-pemanggilan
        $.get(
            "/pemanggilan/detail",
            {
                tgl_panggil: window.selectedTanggal,
                user: window.selectedUser,
                status: window.selectedStatus,
            },
            function (html) {
                $("#detail-pemanggilan").html(html);

                // jika tabel sudah ada, inisialisasi DataTable
                if ($.fn.DataTable.isDataTable("#tabelDetail")) {
                    window.detailTable.destroy();
                }

                window.detailTable = $("#tabelDetail").DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "/pemanggilan/detail",
                        data: function (d) {
                            d.tgl_panggil = window.selectedTanggal;
                            d.user = window.selectedUser;
                            d.status = window.selectedStatus;
                            d.datatable = true; // flag supaya controller return JSON
                        },
                    },
                    drawCallback: function () {
                        // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
                        var tooltipTriggerList = [].slice.call(
                            document.querySelectorAll(
                                '#tabelDetail [data-bs-toggle="tooltip"]',
                            ),
                        );
                        var tooltipList = tooltipTriggerList.map(
                            function (tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            },
                        );
                    },
                    columns: [
                        {
                            data: "ceklis",
                            name: "ceklis",
                            orderable: true,
                            searchable: false,
                        },
                        {
                            data: "nama",
                            name: "nama",
                            orderable: true,
                            searchable: true,
                        },
                        {
                            data: "lokasi",
                            name: "lokasi",
                            orderable: true,
                            searchable: false,
                        },
                        {
                            data: "dilamar",
                            name: "dilamar",
                            orderable: true,
                            searchable: false,
                        },
                        {
                            data: "action",
                            name: "action",
                            orderable: true,
                            searchable: false,
                        },
                    ],
                    columnDefs: [
                        {
                            targets: "_all",
                            className: "dt-head-center",
                        },
                        {
                            targets: [0, 4],
                            className: "dt-center",
                        },
                    ],
                });

                // setiap kali ajax selesai, update modal footer
                $("#tabelDetail").on(
                    "xhr.dt",
                    function (e, settings, json, xhr) {
                        renderFooterButtons(
                            json.status,
                            json.tanggal,
                            json.user,
                            json.total,
                            json.kirim,
                        );
                    },
                );
            },
        );
    });

    // =========================================================================================== RENDER TOMBOL DETAIL PEMANGGILAN
    function renderFooterButtons(status, tanggal, user, total, kirim) {
        let html = "";
        let keterangan = "";

        if (status === "PENDING") {
            html += '<div class="btn-group mt-0">';
            if (
                tanggal &&
                moment(tanggal).isSame(moment(), "day") &&
                total > 0
            ) {
                html += `<button class="btn btn-dark btn-sm cetakAbsensi" data-tgl="${tanggal}" data-user="${user}">
                        <i class="ti ti-printer me-1"></i>Print Absensi
                     </button>`;
                html += `<button class="btn btn-info btn-sm mulaiTest text-black" data-tgl="${tanggal}" data-user="${user}">
                        <i class="ti ti-palette me-1"></i>Mulai Test
                     </button>`;

                keterangan += `
                    <div class="row border rounded-2 bg-light">
                        <label class="col-sm-1 col-form-label text-center"><i class="fas fa-calendar-check text-success"></i></label>
                        <label class="col-sm-11 col-form-label">: Sudah melakukan absensi.</label>
                        <label class="col-sm-1 col-form-label text-center"><i class="ti ti-calendar text-warning"></i></label>
                        <label class="col-sm-11 col-form-label text-danger">: Belum melakukan absensi.</label>
                    </div>
                `;
            }

            if (
                tanggal &&
                moment(tanggal).isSameOrAfter(moment(), "day") &&
                total > 0
            ) {
                html += `<button class="btn btn-warning btn-sm text-black editTanggalAll" data-tgl="${tanggal}" data-user="${user}">
                        <i class="ti ti-calendar me-1"></i>Edit Tanggal Panggil (semua)
                     </button>`;
            }
            html += "</div>";
        }

        if (status === "PROSES") {
            html += `<div class="btn-group" role="group">
                        <button class="btn btn-info btn-sm text-black addNilai" data-tgl="${tanggal}" data-user="${user}">
                            <i class="ti ti-receipt me-1"></i> Input Nilai (semua)
                        </button>
                    `;

            if (kirim) {
                html += `<button class="btn btn-warning btn-sm text-black kirimUser" data-tgl="${tanggal}" data-user="${user}">
                                <i class="far fa-paper-plane me-1"></i> Kirim User (semua)
                             </button>`;
            }
            html += `</div>`;
        }

        $("#modalFooter").html(html);
        $("#keterangan").html(keterangan);
    }

    // =========================================================================================== NILAI PSIKOTEST
    $(document).on("click", ".addNilai", function () {
        var id = $(this).data("id");
        var tgl = $(this).data("tgl");
        var user = $(this).data("user");
        $("#fullscreenModal #dtRow").empty();
        var params = { id: id, tgl: tgl, user: user };

        $.getJSON("/input-nilai/?" + $.param(params), function (res) {
            $.each(res, function (Id, data) {
                $("#fullscreenModal #dtRow").append(
                    `
                    <tr>
                        <td class="text-center">` +
                        (Id + 1) +
                        `</td>
                        <td>
                            ` +
                        data.lowongan_apply.pelamar.nama +
                        `<br>
                            <span class="text-light badge">` +
                        data.lowongan_apply.lowongan.project.inisial +
                        `<span>
                        </td>
                        <td>
                            <input type="hidden" class="form-control" name="idpanggil[]" value="` +
                        data.id +
                        `">
                            <select class="form-select hasil" name="hasil[]" required>
                                <option value="" ${data.hasil_test == null ? "selected" : ""}>-pilih-</option>
                                <option value="1" ${data.hasil_test == 1 ? "selected" : ""}>LULUS</option>
                                <option value="0" ${data.hasil_test == 0 ? "selected" : ""}>TIDAK LULUS</option>
                            <select>
                        </td>
                        <td>
                            <textarea class="form-control catatan" name="catatan[]" placeholder="catatan">` +
                        (data.hasil_catatan ? data.hasil_catatan : "") +
                        `</textarea>
                        </td>
                    </tr>
                `,
                );
            });

            // Tambahkan listener setelah elemen dibuat, ini untuk pengecekan apabila tidak lulus maka catatan wajib diisi
            $("#fullscreenModal #dtRow")
                .find(".hasil")
                .on("change", function () {
                    var val = $(this).val();
                    var textarea = $(this).closest("tr").find(".catatan");
                    if (val == "0") {
                        textarea.attr("required", true);
                    } else {
                        textarea.removeAttr("required");
                    }
                });
        });
        $("#fullscreenModal").modal("show");
    });

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const bsValidationForms = document.querySelectorAll(".form-nilai");

    // Loop over them and prevent submission
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    event.preventDefault();
                    Swal.fire({
                        title: "Konfirmasi!",
                        text: "Simpan Nilai",
                        icon: "question",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showCancelButton: true,
                        confirmButtonText: "Ya, Simpan!",
                        cancelButtonText: "Batal",
                        customClass: {
                            confirmButton:
                                "btn btn-primary me-3 waves-effect waves-light",
                            cancelButton:
                                "btn btn-outline-secondary waves-effect waves-light",
                        },
                        buttonsStyling: false,
                    }).then(function (result) {
                        if (result.value) {
                            _loading();
                            $.ajax({
                                url: "/input-nilai",
                                type: "POST",
                                dataType: "JSON",
                                data: $("#formNilai").serialize(),
                                success: function (response) {
                                    $("#fullscreenModal").modal("hide");
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
                                        // reload DataTable
                                        if (window.detailTable) {
                                            window.detailTable.ajax.reload(
                                                null,
                                                false,
                                            );
                                        }

                                        // reload list tanggal
                                        $.get(
                                            "/data-pemanggilan",
                                            function (html) {
                                                $("#resultContainer").html(
                                                    html,
                                                );
                                            },
                                        );
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
                        }
                    });
                }

                form.classList.add("was-validated");
            },
            false,
        );
    });

    // =========================================================================================== KIRIM USER
    $(document).on("click", ".kirimUser", function () {
        var id = $(this).data("id");
        var tgl = $(this).data("tgl");
        var user = $(this).data("user");

        var params = { id: id, tgl: tgl, user: user };

        Swal.fire({
            title: "Konfirmasi!",
            text: "Kandidat akan dikirim ke user?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Kirim!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-warning me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/kirim-user",
                    type: "POST",
                    dataType: "JSON",
                    data: $.param(params),
                    success: function (response) {
                        $("#fullscreenModal").modal("hide");
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
                            // window.location.reload();
                            window.selectedStatus = response.status;
                            // reload DataTable
                            if (window.detailTable) {
                                window.detailTable.ajax.reload(null, false);
                            }
                            // reload list tanggal
                            $.get("/data-pemanggilan", function (html) {
                                $("#resultContainer").html(html);
                            });
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
            }
        });
    });

    // =========================================================================================== EDIT TANGGAL PEMANGGILAN
    const datePanggil = document.getElementById("tglpanggil");
    if (datePanggil) {
        datePanggil.flatpickr({
            monthSelectorType: "static",
            minDate: "today",
        });
    }

    $(document).on("click", ".editTanggal", function () {
        var id = $(this).data("id");
        _loading();
        $("#formTanggal")[0].reset();
        $("#dtDetail").empty();

        $.getJSON("/edit-tanggalpemanggilan/?id=" + id)
            .done(function (res) {
                if (!res || !res.lowongan_apply) {
                    // kalau data kosong
                    $("#dtDetail").html(
                        '<div class="alert alert-warning">Data pemanggilan tidak ditemukan.</div>',
                    );
                    $.unblockUI();
                    return;
                }

                $("#dtDetail").html(
                    `
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" readonly value="` +
                        res.lowongan_apply.pelamar.nama +
                        `" />
                            <input type="hidden" name="idpanggil" class="form-control" readonly value="` +
                        res.id +
                        `" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Lowongan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" readonly value="` +
                        res.lowongan_apply.lowongan.project.name +
                        `" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Posisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" readonly value="` +
                        res.lowongan_apply.lowongan.pekerjaan_dilamar +
                        `" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Tgl Panggil</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" readonly value="` +
                        res.tgl_panggil +
                        `" />
                        </div>
                    </div>
                `,
                );

                $("#modalTanggalPanggil .modal-title").html(
                    "Edit Tanggal Pemanggilan",
                );
                $("#modalTanggalPanggil").modal("show");
                $.unblockUI();
            })
            .fail(function (error) {
                // kalau request gagal (404, 500, dll)
                var errorMessage = error.statusText;
                if (error.status == 405) {
                    // Not Acceptable
                    var errors = $.parseJSON(error.responseText);
                    errorMessage = errors.errors;
                }

                Swal.fire({
                    title: $.parseJSON(error.status),
                    text: "Data Tidak Ditemukan",
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
                $.unblockUI();
            });
    });

    $("#formTanggal").submit(function (event) {
        if (!datePanggil.value) {
            event.preventDefault(); // cegah submit
            datePanggil.classList.add("is-invalid"); // border merah
            return;
        } else {
            datePanggil.classList.remove("is-invalid"); // hapus border merah kalau sudah diisi
        }
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Rubah Tanggal Pemanggilan ?",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Rubah!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-warning me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/update-tanggalpemanggilan",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formTanggal").serialize(),
                    success: function (response) {
                        $("#modalTanggalPanggil").modal("hide");
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
                            // reload DataTable
                            if (window.detailTable) {
                                window.detailTable.ajax.reload(null, false);
                            }

                            // reload list tanggal
                            $.get("/data-pemanggilan", function (html) {
                                $("#resultContainer").html(html);
                            });
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
            }
        });
    });

    $(document).on("click", ".editTanggalAll", function () {
        var tgl = $(this).data("tgl");
        var user = $(this).data("user");
        _loading();
        $("#formTanggal")[0].reset();
        $("#dtDetail").empty();

        $("#dtDetail").html(
            `
            <div class="text-center mb-4">
                <h3 class="mb-2 text-danger">Perhatian!</h3>
                <h6 class="text-danger">
                    Fitur ini akan merubah tanggal pemanggilan untuk semua pelamar yang ada di tabel.
                </h6>
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label for="nameBasic" class="form-label">Tanggal Pemanggilan Sebelumnya</label>
                    <input type="text" name="tglsebelum" class="form-control" readonly value="` +
                tgl +
                `">
                    <input type="hidden" name="idpanggil" class="form-control" readonly value="" />
                    <input type="hidden" name="user" class="form-control" readonly value="` +
                user +
                `" />
                </div>
            </div>
        `,
        );

        $("#modalTanggalPanggil .modal-title").html("");
        $("#modalTanggalPanggil").modal("show");
        $.unblockUI();
    });

    // =========================================================================================== PRINT ABSENSI
    $(document).on("click", ".cetakAbsensi", function () {
        var tgl = $(this).data("tgl");
        var user = $(this).data("user");

        $.ajax({
            url: "/data-pemanggilan/absensi",
            type: "POST",
            data: { tgl: tgl, user: user },
            xhrFields: {
                responseType: "blob", // supaya hasil PDF bisa ditangkap sebagai file
            },
            // success: function (data) {
            //     var blob = new Blob([data], { type: 'application/pdf' });
            //     var link = document.createElement('a');
            //     link.href = window.URL.createObjectURL(blob);
            //     link.target = "_blank"; // buka di tab baru
            //     link.click();
            // }
            success: function (data) {
                var blob = new Blob([data], { type: "application/pdf" });
                var url = window.URL.createObjectURL(blob);
                window.open(url, "_blank"); // langsung buka tab baru
            },
        });
    });

    // =========================================================================================== MULAI TEST
    $(document).on("click", ".mulaiTest", function () {
        var tgl = $(this).data("tgl");
        var user = $(this).data("user");

        Swal.fire({
            title: "Konfirmasi!",
            html: "Pastikan Pelamar sudah melakukan absensi ( <i class='fas fa-calendar-check text-success'></i> ).\n Pelamar yang belum melakukan absensi akan otomatis dikembalikan lagi ke data pelamar.",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Mulai!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-info me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/mulai-test",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        tgl: tgl,
                        user: user,
                    },
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
                            // reload DataTable
                            window.selectedStatus = "PROSES";

                            if (window.detailTable) {
                                window.detailTable.ajax.reload(null, false);
                            }

                            // reload list tanggal
                            $.get("/data-pemanggilan", function (html) {
                                $("#resultContainer").html(html);
                            });
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
            }
        });
    });

    // Tombol PSIKOTEST
    $(document).on("click", ".testBtn", function () {
        var token = $(this).data("token");
        var url = $(this).data("url");

        // simpan state item aktif
        localStorage.setItem("activeTanggal", window.selectedTanggal);
        localStorage.setItem("activeUser", window.selectedUser);
        localStorage.setItem("activeStatus", window.selectedStatus);

        _loading();
        setTimeout(() => {
            window.location.href = url + "?token=" + encodeURIComponent(token);
        }, 300);
    });

    function backToDatatable() {
        let tgl = localStorage.getItem("activeTanggal");
        let user = localStorage.getItem("activeUser");
        let status = localStorage.getItem("activeStatus");

        if (tgl && user && status) {
            $.get(
                "/pemanggilan/detail",
                { tgl_panggil: tgl, user: user, status: status },
                function (html) {
                    $("#detail-pemanggilan").html(html);

                    // highlight kembali item yang sesuai
                    $(".tanggalPemanggilan")
                        .removeClass(
                            "border-bottom border-info shadow py-2 px-1 rounded-3 bg-label-primary",
                        )
                        .addClass("border-bottom border-black rounded-3 p-2");

                    $(
                        `.tanggalPemanggilan[data-tanggal="${tgl}"][data-user="${user}"]`,
                    )
                        .removeClass("border-bottom border-black rounded-3 p-2")
                        .addClass(
                            "border-bottom border-info shadow py-2 px-1 rounded-3 bg-label-primary",
                        );

                    // inisialisasi ulang DataTable
                    if ($.fn.DataTable.isDataTable("#tabelDetail")) {
                        $("#tabelDetail").DataTable().destroy();
                    }

                    $("#tabelDetail").DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "/pemanggilan/detail",
                            data: function (d) {
                                d.tgl_panggil = tgl;
                                d.user = user;
                                d.status = status;
                                d.datatable = true;
                            },
                        },
                        columns: [
                            {
                                data: "ceklis",
                                name: "ceklis",
                                orderable: true,
                                searchable: false,
                            },
                            {
                                data: "nama",
                                name: "nama",
                                orderable: true,
                                searchable: true,
                            },
                            {
                                data: "lokasi",
                                name: "lokasi",
                                orderable: true,
                                searchable: false,
                            },
                            {
                                data: "dilamar",
                                name: "dilamar",
                                orderable: true,
                                searchable: false,
                            },
                            {
                                data: "action",
                                name: "action",
                                orderable: true,
                                searchable: false,
                            },
                        ],
                        columnDefs: [
                            { targets: "_all", className: "dt-head-center" },
                            { targets: [0, 4], className: "dt-center" },
                        ],
                    });

                    $("#tabelDetail").on(
                        "xhr.dt",
                        function (e, settings, json, xhr) {
                            renderFooterButtons(
                                json.status,
                                json.tanggal,
                                json.user,
                                json.total,
                                json.kirim,
                            );
                        },
                    );
                },
            );

            localStorage.removeItem("activeTanggal");
            localStorage.removeItem("activeUser");
            localStorage.removeItem("activeStatus");
        }
    }

    function openhref(location, token) {
        window.open(location, token);
    }

    // Tombol Wawancara
    $(document).on("click", ".wawancara", function () {
        var token = $(this).data("token");

        // simpan state item aktif
        localStorage.setItem("activeTanggal", window.selectedTanggal);
        localStorage.setItem("activeUser", window.selectedUser);
        localStorage.setItem("activeStatus", window.selectedStatus);

        _loading();
        setTimeout(() => {
            var location = "/wawancara/?token=" + token;
            openhref(location, token);
            $.unblockUI();
        }, 300);
    });
});
